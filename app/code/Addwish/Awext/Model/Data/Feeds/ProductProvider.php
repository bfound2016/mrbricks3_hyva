<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Data\Feeds;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
/* used directly with objectmanager as magento 2.2 doesnt have them.
when we drop support for 2.2 we should use dependency injection instead
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySales\Model\GetProductSalableQty;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
currently not in use, see note in getStockInfoMSI:
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
*/
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\BlockFactory;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\App\Emulation;

use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;

use Magento\Swatches\Block\Product\Renderer\Listing\ConfigurableFactory as ConfigurableRendererFactory;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ConfigurableRenderer;

use Addwish\Awext\Api\Data\Feeds\ProductProviderInterface;

use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

/**
 * Class ProductProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProvider implements ProductProviderInterface {
    /**
     * @var int
     */
    protected $rootCategoryId;

    /**
     * @var array
     */
    protected $productArray = [];

    /**
     * @var array
     */
    protected $categoryArray = [];

    /**
     * @var array
     */
    protected $attributeValueCollection = [];

    /**
     * @var array
     */
    protected $attributeOptionsAlreadyLoaded = [];

    /**
     * @var array
     */
    protected $attributesWithoutOptionsAlreadyLoaded = [];

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistryInterface;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var IsProductSalableInterface
     */
    protected $isProductSalableInterface;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    protected $getSourceItemsBySkuInterface;

    /**
     * @var GetProductSalableQty
     */
    protected $getProductSalableQty;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var Currency
     */
    protected $currencyModel;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var ConfigurableRendererFactory
     */
    protected $configurableRendererFactory;

    /**
     * @var ConfigurableRendere
     */
    protected $configurableRenderer;

    /**
     * @var customerGroup
     */
    protected $customerGroup;

    /**
     * ProductProvider constructor.
     *
     * @param StockRegistryInterface $stockRegistryInterface
     * @param ModuleManager $moduleManager
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadataInterface $productMetadataInterface
     * @param StoreManagerInterface $storeManager
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param MediaConfig $mediaConfig
     * @param BlockFactory $blockFactory
     * @param Emulation $appEmulation
     * @param Currency $currencyModel
     * @param CurrencyFactory $currencyFactory
     * @param ConfigurableRendererFactory $configurableRendererFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StockRegistryInterface $stockRegistryInterface,
        ProductMetadataInterface $productMetadataInterface,
        ModuleManager $moduleManager,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        MediaConfig $mediaConfig,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        Currency $currencyModel,
        CurrencyFactory $currencyFactory,
        ConfigurableRendererFactory $configurableRendererFactory,
        CustomerGroup $customerGroup
    ) {
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->mediaConfig = $mediaConfig;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
        $this->currencyModel = $currencyModel;
        $this->currencyFactory = $currencyFactory;
        $this->configurableRendererFactory = $configurableRendererFactory;
        $this->customerGroup = $customerGroup;
        $this->rootCategoryId = $this->getRootCategoryId();
    }

    /**
     * Generating data
     *
     * @param array $dataArray
     * @param array $extraAttributes
     * @param bool|null $includeOrphans
     * @param bool|null $deltaFeed
     *
     * @return array
     */
    public function generate(
        array $dataArray,
        array $extraAttributes = [],
        bool $includeOrphans = null,
        bool $includeSwatches = null,
        bool $includeRelatedProducts = null,
        bool $deltaFeed = null
    ): array {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();

        // Api only has access to original img
        // in order to get access to cached/resized image we must emulate to be in frontend
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        // prepare list of all categories in store
        // used when generating hierachies from path ids.
        $this->prepareCategoryArray();

        // prepare list of available currencies
        $baseCurrencyConverter = $this->currencyFactory->create()->load($store->getBaseCurrency()->getCode());
        $rateFromCurrentToBase = 1 / $baseCurrencyConverter->convert(1, $store->getCurrentCurrency());
        $convert = function($amount, $currency) use($baseCurrencyConverter, $rateFromCurrentToBase) {
            $base = $amount * $rateFromCurrentToBase;
            return $baseCurrencyConverter->convert($base, $currency);
        };
        $availableCurrencies = $this->currencyModel->getConfigAllowCurrencies();
        //getting customer groups as a id and value like [id => value] array index is the id
        $customerGroups = $this->getCustomerGroups();
        // from 2.3 we need to support the multisource stock system, but that functionality doesnt exist in prior versions.
        // it can also happen that it isnt installed if the shop upgraded from 2.2 -> 2.3 rather than clean install.
        $magentoMinorVersion = (int)explode(".", $this->productMetadataInterface->getVersion())[1];
        $stockId = 1; // the default stock id.
        $stockFunctionReference = "getStockInfo";
        if ($magentoMinorVersion >= 3 && $this->moduleManager->isEnabled("Magento_InventorySales")) {
            $stockFunctionReference = "getStockInfoMSI";
            $stockByWebsiteIdResolver = $this->objectManager->get("Magento\InventorySales\Model\StockByWebsiteIdResolver");
            $stockId = $stockByWebsiteIdResolver->execute((int)$store->getWebsiteId())->getStockId();
            $this->getProductSalableQty = $this->objectManager->get("Magento\InventorySales\Model\GetProductSalableQty");
            $this->isProductSalableInterface = $this->objectManager->get("Magento\InventorySalesApi\Api\IsProductSalableInterface");
            // currently not in use, see note in getStockInfoMSI:
            // $this->getSourceItemsBySkuInterface = $this->objectManager->get("Magento\InventoryApi\Api\GetSourceItemsBySkuInterface");
        }

        // get json data necessary for swatches.
        if ($includeSwatches) {
            $this->configurableRenderer = $this->configurableRendererFactory->create();
        }

        // loop the products array and generate output
        foreach ($dataArray as $product) {
            if ($product instanceof ProductInterface) {
                $productArray = [];
                $variants = [];
                $variantPrices = [];
                $bundleProductPrices = [];

                // if this is a deltaFeed it can (by providing a parameter) include products,
                // that have changed status or visibility to "disabled" and "not visible"
                // we check each product to checks its status and visibility.
                // if the product is here because we need to deactivate it we only return its url.
                if ($product->getVisibility() == 1 || $product->getStatus() == 2) {
                    $productArray[self::URL_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getProductUrl());
                } else {
                    // its an active product - output all necessary product data.

                    // prepare hierarchies and categoryids
                    $productHierarchies = $this->getProductCategoryHierarchy($product);
                    // We will skip product if it doesnt exists in the current stores root category,
                    // optionally including orphans without any categories
                    if ((!$includeOrphans && count($productHierarchies["hierarchies"]) == 0) 
                            && !$productHierarchies["productExistsInStoreRootCategory"]) {
                        continue;
                    }
                   
                    // prepare variant, grouped and bundle type specific data
                    if ($product->getTypeId() == "configurable") {
                        $variants = $this->getProductsFromConfigurable($product, $extraAttributes);
                        // get json config data for swatches support - only works for configurable products.
                        if ($includeSwatches) {
                            $this->configurableRenderer->setProduct($product);
                            $productArray[self::SWATCHES_XML_NODE_NAME] = [
                                self::SWATCHES_CONF_XML_NODE_NAME => $this->configurableRenderer->getJsonConfig(),
                                self::SWATCHES_SWATCHCONF_XML_NODE_NAME => $this->configurableRenderer->getJsonSwatchConfig(),
                                self::SWATCHES_NUMTOSHOW_XML_NODE_NAME => $this->configurableRenderer->getNumberSwatchesPerProduct()
                            ];
                        }
                    } else if ($product->getTypeId() == "grouped") {
                        $variants = $this->getProductsFromGroup($product, $extraAttributes);
                    } else if ($product->getTypeId() == "bundle") {
                        //saving them in a variable first so we can easily get them when we have to do currency conversions.
                        $bundleProductPrices = [
                            self::MINIMAL_BUNDLE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("final_price")->getMinimalPrice()->getValue(), 2)),
                            self::MINIMAL_BUNDLE_BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("final_price")->getMinimalPrice()->getBaseAmount(), 2)),
                            self::MINIMAL_BUNDLE_REGULAR_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("regular_price")->getMinimalPrice()->getValue(), 2)),
                            self::MINIMAL_BUNDLE_REGULAR_BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("regular_price")->getMinimalPrice()->getBaseAmount(), 2)),
                            self::MAXIMAL_BUNDLE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("final_price")->getMaximalPrice()->getValue(), 2)),
                            self::MAXIMAL_BUNDLE_BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("final_price")->getMaximalPrice()->getBaseAmount(), 2)),
                            self::MAXIMAL_BUNDLE_REGULAR_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("regular_price")->getMaximalPrice()->getValue(), 2)),
                            self::MAXIMAL_BUNDLE_REGULAR_BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                round($product->getPriceInfo()->getPrice("regular_price")->getMaximalPrice()->getBaseAmount(), 2)),
                        ];
                        $productArray = array_merge($productArray, $bundleProductPrices);
                    }

                    // prepare stock and variant price data.
                    $stockInfo = $this->$stockFunctionReference($product, $variants, $stockId);
                    if (isset($stockInfo["stockInfo"]["variantsInStock"])) {
                        $variantsInStock = $stockInfo["stockInfo"]["variantsInStock"];
                        if (empty($variantsInStock)) {
                            $productArray[self::HAS_VARIANT_IN_STOCK_XML_NODE_NAME] = $this->toAllowedXmlValue(false);
                        } else {
                            $variantPriceList = [];
                            $variantProductNumbers = [];
                            foreach ($variantsInStock as $variant) {
                                $variantPriceList[] = $variant->getPriceInfo()->getPrice("final_price")->getAmount()->getValue();
                                $variantProductNumbers[] = $variant->getSku();
                            }
                            //saving them in a variable first so we can easily get them when we have to do currency conversions.
                            $variantPrices = [
                                self::CHEAPEST_VARIANT_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                    round(min($variantPriceList)), 2),
                                self::MOST_EXPENSIVE_VARIANT_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                    round(max($variantPriceList)), 2),
                            ];
                            $productArray[self::HAS_VARIANT_IN_STOCK_XML_NODE_NAME] = $this->toAllowedXmlValue(true);
                            $productArray[self::VARIANT_EXTRAATTRIBUTES_XML_NODE_NAME] = $this->getExtraAttributes(
                                $variantsInStock, $extraAttributes);
                            $productArray[self::VARIANT_PRODUCT_NUMBERS_XML_NODE_NAME] = $variantProductNumbers;
                            $productArray = array_merge($productArray, $variantPrices);

                        }
                    }

                    if ($includeRelatedProducts) {
                        $productArray[self::RELATED_PRODUCT_NUMBERS_XML_NODE_NAME] = [];
                        $productArray[self::CROSSELL_PRODUCT_NUMBERS_XML_NODE_NAME] = [];
                        $productArray[self::UPSELL_PRODUCT_NUMBERS_XML_NODE_NAME] = [];
                        $relatedProducts = $product->getRelatedProducts();
                        if ($relatedProducts) {
                            foreach ($relatedProducts as  $relatedProduct) {
                                $productArray[self::RELATED_PRODUCT_NUMBERS_XML_NODE_NAME][] = $this->toAllowedXmlValue($relatedProduct->getSku());
                            }
                        }
                        $crossellProducts = $product->getCrossSellProducts();
                        if ($crossellProducts) {
                            foreach ($crossellProducts as  $crossellProduct) {
                                $productArray[self::CROSSELL_PRODUCT_NUMBERS_XML_NODE_NAME][] = $this->toAllowedXmlValue($crossellProduct->getSku());
                            }
                        }
                        $upsellProducts = $product->getUpSellProducts();
                        if ($upsellProducts) {
                            foreach ($upsellProducts as  $upsellProduct) {
                                $productArray[self::UPSELL_PRODUCT_NUMBERS_XML_NODE_NAME][] = $this->toAllowedXmlValue($upsellProduct->getSku());
                            }
                        }
                        
                    }

                    // get the main product's prices.
                    $price = $product->getPriceInfo()->getPrice("final_price")->getAmount()->getValue();
                    $basePrice = $product->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount();
                    $specialPrice = $product->getPriceInfo()->getPrice("special_price")->getAmount()->getValue();
                    $specialBasePrice = $product->getPriceInfo()->getPrice("special_price")->getAmount()->getBaseAmount();
                    $regularPrice = $product->getPriceInfo()->getPrice("regular_price")->getAmount()->getValue();
                    $regularBasePrice = $product->getPriceInfo()->getPrice("regular_price")->getAmount()->getBaseAmount();
                    // Sometimes customers use the regular price as sale price and special price as regular price.
                    // In that case we have to switch around the values.
                    if ($regularPrice and $specialPrice and $regularPrice < $specialPrice) {
                        $regularPrice = $specialPrice;
                        $regularBasePrice = $specialBasePrice;
                    }
                    if ($regularPrice == false) {
                        $regularPrice = $price;
                        $regularBasePrice = $basePrice;
                    }

                    if ($product->getTypeId() == "grouped" && !empty($variantPrices)) {
                        // Grouped products can act weird. 
                        // The price gathered above reflects the cheapest variant regardless of its stock / availability.
                        // Catalog rules also dont apply to grouped product, as they technically doen't have their own price.
                        // - it only applies to the products in the group.
                        // So it is safe to assume the grouped product price will come from the cheapest available product in the group.
                        // reset prices to cheapestVariantPrice.
                        $price = $basePrice = $specialPrice = $specialBasePrice = $regularPrice = $regularBasePrice 
                            = $variantPrices[self::CHEAPEST_VARIANT_PRICE_XML_NODE_NAME];
                    }
                    // prepare prices in available currencies
                    if (count($availableCurrencies) > 1) {
                        $prices = [];
                        foreach ($availableCurrencies as $currency) {
                            try {
                                $prices[$currency] = [
                                    self::CURRENCY_CODE_XML_NODE_NAME => $currency,
                                    self::PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                        round($convert($price, $currency), 2)),
                                    self::BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                        round($convert($basePrice, $currency), 2)),
                                    self::PREVIOUS_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                        round($convert($regularPrice, $currency), 2)),
                                    self::PREVIOUS_BASE_PRICE_XML_NODE_NAME => $this->toAllowedXmlValue(
                                        round($convert($regularBasePrice, $currency), 2))
                                ];
                                if (!empty($variantPrices)) {
                                    foreach ($variantPrices as $variantPriceKey => $variantPriceValue) {
                                        $prices[$currency] = array_merge(
                                            $prices[$currency],
                                            [$variantPriceKey => $this->toAllowedXmlValue(
                                                round($convert($variantPriceValue, $currency), 2))]);
                                    }
                                }
                                if (!empty($bundleProductPrices)) {
                                    foreach ($bundleProductPrices as $bundleProductPriceKey => $bundleProductPriceValue) {
                                        $prices[$currency] = array_merge(
                                            $prices[$currency],
                                            [$bundleProductPriceKey => $this->toAllowedXmlValue(
                                                round($convert($bundleProductPriceValue, $currency), 2))]);
                                    }
                                }
                            } catch (\Exception|\Throwable $e) {
                                // unable to get the rate of a currency
                            }
                        }
                        $productArray[self::CURRENCY_PRICES_XML_NODE_NAME] = $prices;
                    }

                    // set the output
                    $productArray[self::IN_STOCK_XML_NODE_NAME] = $this->toAllowedXmlValue($stockInfo["stockInfo"]["inStock"]);
                    $productArray[self::TITLE_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getName());
                    $productArray[self::URL_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getProductUrl());
                    $productArray[self::PRODUCT_NUMBER_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getSku());
                    $productArray[self::EXTRAATTRIBUTES_XML_NODE_NAME] = $this->getExtraAttributes(array($product), $extraAttributes);
                    $productArray[self::ID_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getEntityId());
                    $productArray[self::CURRENCY_CODE_XML_NODE_NAME] = $store->getCurrentCurrency()->getCode();
                    $productArray[self::PRICE_XML_NODE_NAME] = $this->toAllowedXmlValue(round($price, 2));
                    $productArray[self::BASE_PRICE_XML_NODE_NAME] = $this->toAllowedXmlValue(round($basePrice, 2));
                    $productArray[self::TIER_PRICE_XML_NODE_NAME] = $this->getTierPrices($product, $customerGroups);
                    $productArray[self::PREVIOUS_PRICE_XML_NODE_NAME] = $this->toAllowedXmlValue(round($regularPrice, 2));
                    $productArray[self::PREVIOUS_BASE_PRICE_XML_NODE_NAME] = $this->toAllowedXmlValue(round($regularBasePrice, 2));
                    $productArray[self::QTY_XML_NODE_NAME] = $this->toAllowedXmlValue($stockInfo["qty"]);
                    $productArray[self::MIN_BUY_QTY_XML_NODE_NAME] = $this->toAllowedXmlValue($stockInfo["minBuyQty"]);
                    $productArray[self::DESCRIPTION_XML_NODE_NAME] = strip_tags($this->toAllowedXmlValue($product->getDescription()));
                    $productArray[self::RICH_DESCRIPTION_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getDescription());
                    $productArray[self::IMG_URL_XML_NODE_NAME] = $this->toAllowedXmlValue($this->getProductImageUrl($product));
                    $productArray[self::BASE_IMG_URL_XML_NODE_NAME] = $this->toAllowedXmlValue($this->getProductBaseImageUrl($product));
                    $productArray[self::KEYWORDS_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getMetaKeyword());
                    $productArray[self::TYPE_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getTypeId());
                    $productArray[self::VISIBILITY_XML_NODE_NAME] = $this->toAllowedXmlValue($product->getVisibility());
                    $productArray[self::HIERARCHIES_XML_NODE_NAME] = $productHierarchies["hierarchies"];
                    $productArray[self::CATEGORY_IDS_XML_NODE_NAME] = $productHierarchies["categoryIds"];
                    $productArray[self::CATEGORY_IDS_NO_PARENTS_XML_NODE_NAME] = $productHierarchies["categoryIdsWithoutParents"];
                }

                $this->productArray[]["product"] = $productArray;
            }
        }

        // emulation is no longer required and is shut down
        $this->appEmulation->stopEnvironmentEmulation();

        return $this->productArray;
    }

    /**
    * Helper to get the Customer group values
    *
    * @return array
    */

    public function getCustomerGroups()
    {
        $customerGroups = $this->customerGroup->toOptionArray();
        foreach ($customerGroups as $key => $value) {
            $customer_array[$value["value"]] = $value["label"];
        }
        return $customer_array;
    }

    /**
     * Replace characters not allowed in xml with space
     *
     * @param string $value
     *
     * @return string
     */
    protected function toAllowedXmlValue($value) {
        if (is_null($value)) {
            $value = "";
        } else if (is_array($value)) {
            foreach($value as $k => $v) {
                $value[$k] = $this->toAllowedXmlValue($v);
            }
        } else if (is_string($value)) {
            $value = str_replace(
                array(
                    // The C0 control block, except a few specifically allowed characters.
                    "\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
                    "\x08", "\x0B", "\x0C", "\x0E", "\x0F", "\x10", "\x11", "\x12",
                    "\x13", "\x14", "\x15", "\x16", "\x17", "\x18", "\x19", "\x1A",
                    "\x1B", "\x1C", "\x1D", "\x1E", "\x1F",
                ),
                " ",
                $value);
        }
        return $value;
    }

    /**
     * Prepare attribute labels and values and save them in an array so we dont have to look up the same attribute multiple times.
     *
     * @param ProductInterface $product
     * @param string $attributeLabel
     *
     */
    protected function prepareExtraAttribute($product, $attributeLabel) {
        if (!in_array($attributeLabel, $this->attributeOptionsAlreadyLoaded) 
                && !in_array($attributeLabel, $this->attributesWithoutOptionsAlreadyLoaded)) {
            // this attribute has not been loaded before - so we load it.
            $attr = $product->getResource()->getAttribute($attributeLabel);
            if ($attr) {
                // check if the attribute has options.
                if ($attr->usesSource()) {
                    $this->attributeValueCollection[$attributeLabel] = [];
                    $options = $attr->getSource()->getAllOptions();
                    foreach ((array) $options as $option) {
                        // we add the attribute's options to the attributeValueCollection array 
                        // so the labels can be fetched later.
                        $this->attributeValueCollection[$attributeLabel][$option["value"]] = $option["label"];
                    }
                    $this->attributeOptionsAlreadyLoaded[] = $attributeLabel;
                } else {
                    $this->attributesWithoutOptionsAlreadyLoaded[] = $attributeLabel;
                }
            }
        }
    }
     /**
     * Get all getTierPrices's values for a certain product.
     *
     * @param array $products
     * @param string tier_price
     */
    protected function getTierPrices($product, $customerGroups) {
        $extraAttributesCollected = [];
        $tierPrices = $product->getTierPrices();
        if (!empty($tierPrices)) {
            $allGroupPrices = []; //will be used for all groups because all groups option is not present in custom groups
            $otherGroupPrices = []; //will be used for other groups
            //this loop saves the price value group wise, later we will get the minimun amount per each group from the arrays 
            foreach ($tierPrices as $tierPrice) {
                $data = $tierPrice->getData();
                if($data["customer_group_id"] != 32000) {
                    //value is of string type so we need to convert it into float and then round it
                    $otherGroupPrices[$data["customer_group_id"]][] = round((float) $data["value"], 2);                     
                }else{
                    $allGroupPrices[] = round((float) $data["value"], 2);
                }
            } 
            // in which all group option is selected
            if(sizeof($allGroupPrices)) {
                $min = min($allGroupPrices);
                $extraAttributesCollected["tier_price_all_group"]["cheapest_tier_price"] = $min;
            }
            //for others groups in which all groups is not selected
            if(sizeof($otherGroupPrices)) {
                foreach ($otherGroupPrices as $key => $otherGroupPrice) {
                    $min = min($otherGroupPrice);
                    $extraAttributesCollected["tier_price_".$customerGroups[$key]]["cheapest_tier_price"] = $min;
                }
            }
        }
        
        return  $extraAttributesCollected;
    }

    /**
     * Get all extraAttributes's values for a certain product.
     *
     * @param array $products
     * @param array $extraAttributes
     */
    protected function getExtraAttributes($products, $extraAttributes) {
        $extraAttributesCollected = [];
        foreach ($products as $product) {
            foreach ($extraAttributes as $attributeLabel) {
                $extraAttributeValues = $this->getExtraAttributeValues($product, $attributeLabel);
                if (!empty($extraAttributeValues)) {
                    if (!array_key_exists($attributeLabel, $extraAttributesCollected)) {
                        $extraAttributesCollected[$attributeLabel] = [];
                    }
                    $extraAttributesCollected[$attributeLabel] = array_merge(
                        $extraAttributesCollected[$attributeLabel], $extraAttributeValues);
                }
            }
        }
        foreach ($extraAttributesCollected as $key => $values) {
            if (!$this->isMultiDimensionalArray($values)) {
                $extraAttributesCollected[$key] = array_unique($values);
            }
        }
        
        return  $extraAttributesCollected;
    }

    /**
     * Get values/labels for the specified extraAttribute
     *
     * @param ProductInterface $product
     * @param string $attributeLabel
     */
    protected function getExtraAttributeValues($product, $attributeLabel) {
        $attributeValues = [];
        $attributeData = $product->getData($attributeLabel);
        if (!$attributeData) {
            // check if the attribute can be found as an extension attribute.
            $extensionAttributes = $product->getExtensionAttributes();
            // $extensionAttributes = $product->getExtensionAttributes()->getConfigurableProductOptions();

            // getExtensionAttributes returns a class with functions, not a data array like getData().
            // It's a generated class that contains a getter and a setter for each extension attribute on the entity.
            if ($extensionAttributes) {
                // Here we attempt to convert the snake_cased extension_attribute_code to pascalCased getter.
                $pascalCasedGetter = 'get'.str_replace('_', '', ucwords($attributeLabel, '_'));
                if (method_exists($extensionAttributes, $pascalCasedGetter)) {
                    $attributeData = $extensionAttributes->$pascalCasedGetter();
                }
            }
        }
        if ($attributeData) {
            $this->prepareExtraAttribute($product, $attributeLabel);
            // if attribute is in the attributeLabelsAlreadyLoaded array then it means it has options
            // attributes with options has been loaded already so we just need to fetch the right value.
            if (in_array($attributeLabel, $this->attributeOptionsAlreadyLoaded)) {
                $attributeLabelIds = explode(",", $attributeData);
                foreach ($attributeLabelIds as $attributeLabelId) {
                    // An option might have been deleted after it has been selected on a product
                    if (array_key_exists($attributeLabel, $this->attributeValueCollection) 
                            && array_key_exists($attributeLabelId, $this->attributeValueCollection[$attributeLabel])) {
                        $attributeValues[] = $this->attributeValueCollection[$attributeLabel][$attributeLabelId];
                    }
                }
            } else { 
                // no options - just use the data returned from getData()
                $attributeValues[] = $attributeData;
            }
        }
        foreach ($attributeValues as $key => $value) {
            $attributeValues[$key] = $this->toAllowedXmlValue($value);
        }
        return $attributeValues;
    }

    /**
     * Helper to check if array is multidimensional.
     *
     * @return bool
     */
    protected function isMultiDimensionalArray($array) {
        return count($array) != count($array, COUNT_RECURSIVE);
    }

    /**
     * Preparing category array
     *
     * @return ProductProvider
     */
    protected function prepareCategoryArray(): ProductProvider {
        $categoryList = [];
        try {
            $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect([
                    self::ENTITY_ID_ATTRIBUTE_NAME,
                    self::PATH_ATTRIBUTE_NAME,
                    self::NAME_ATTRIBUTE_NAME])
            ->addAttributeToFilter(self::PATH_ATTRIBUTE_NAME, ["like" => "1/{$this->rootCategoryId}/%"]);
            $categoryList = $categoryCollection->getItems();
        } catch (\Exception|\Throwable $e) {
            return $this;
        }

        foreach ($categoryList as $item) {
            $this->categoryArray[$item->getId()] = $this->toAllowedXmlValue($item->getName());
        }

        return $this;
    }

    /**
     * Gets product category hierarchy
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getProductCategoryHierarchy(ProductInterface $product): array {
        $hierarchies = [
            "productExistsInStoreRootCategory" => false,
            "hierarchies" => [],
            "categoryIds" => [],
            "categoryIdsWithoutParents" => []
        ];
        $categoryItems = null;
        if ($product instanceof ProductInterface) {
            $categoryItems = $product->getCategoryCollection()->addAttributeToSelect(["is_active"])->getItems();
        }
        if (is_array($categoryItems)) {
            $categoryIds = [];
            foreach ($categoryItems as $item) {
                if ($item->getIsActive()) {
                    $categoryPath = $item->getPath();
                    $categoryTree = $this->getCategoryTree($categoryPath);
                    // check if the category path contains id of rootcategory
                    if (strpos($categoryPath, "1/".$this->rootCategoryId) !== FALSE) {
                        $hierarchies["productExistsInStoreRootCategory"] = true;
                    }
                    if (count($categoryTree) > 0) {
                        $hierarchies["hierarchies"][] = array_values($categoryTree);
                        $categoryIds = array_merge($categoryIds, array_keys($categoryTree));
                        $hierarchies["categoryIdsWithoutParents"][] = $item->getId();
                    }
                }
            }
            $hierarchies["categoryIds"] = array_unique($categoryIds);
        }
        return $hierarchies;
    }

    /**
     * Gets category tree
     *
     * @param string $path
     *
     * @return array
     */
    protected function getCategoryTree(string $path): array {
        $dataArray   = [];
        $categoryIds = explode("/", $path);
        foreach ($categoryIds as $item) {
            // We are only adding children categories of the current stores chosen rootcategory.
            if ($item != $this->rootCategoryId && isset($this->categoryArray[$item])) {
                $dataArray[$item] = $this->toAllowedXmlValue($this->categoryArray[$item]);
            }
        }
        return $dataArray;
    }
    /**
     * Get simple variant products from configurable product.
     *
     * @param Product $product
     *
     * @param array $extraAttributes
     *
     * @return array
     */
    protected function getProductsFromConfigurable($product, $extraAttributes): array {
        $attributesToSelect = array_merge($extraAttributes, array("sku", "price", "status"));
        $variantCollection = $product->getTypeInstance()->getUsedProductCollection($product)
            ->addAttributeToSelect($attributesToSelect);
        $variantsArray = [];
        foreach ($variantCollection as $variant) {
            $variantsArray[] = $variant;
        }
        return $variantsArray;
    }

    /**
     * Get products from grouped product.
     *
     * @param Product $product
     *
     * @param array $extraAttributes
     *
     * @return array
     */
    protected function getProductsFromGroup($product, $extraAttributes): array {
        $attributesToSelect = array_merge($extraAttributes, array("sku", "price", "status"));
        $groupCollection = $product->getTypeInstance()->getAssociatedProductCollection($product)
            ->addAttributeToSelect($attributesToSelect);
        $groupProductsArray = [];
        foreach ($groupCollection as $groupProduct) {
            $groupProductsArray[] = $groupProduct;
        }
        return $groupProductsArray;
    }

    /**
     * Get stock information for a product - multi source (post 2.3)
     *
     * @param Product $product
     *
     * @param array $variants
     *
     * @param int $stockId
     *
     * @return array
    */
    protected function getStockInfoMSI($product, $variants, $stockId): array {
        # a stock is a combination of all sources that apply to a website.
        # so each website can only have one stock assigned.
        # each stock can have multiple sources though.
        $qty = 0;
        # TODO FIX MIN BUY QUANTITY.
        $minBuyQty = 1;
        $stockInfo = [];
        try {
            /*
            This is currently not in use as the need for it got solved in another way.
            also, getSourceItemsBySkuInterface get all source items, not just those connected to the current stock.
            so if we were to use this, we should figure out a way to filter out irrelevant sources.

            $stockInfo["stockSources"] = [];
            $sourceItems = $this->getSourceItemsBySkuInterface->execute($product->getSku());
            foreach ($sourceItems as $sourceItemId => $sourceItem) {
                $stockInfo["stockSources"][$sourceItem->getSourceCode] = (int)$sourceItem->getStatus();
            }
            */
            $stockInfo["inStock"] = $this->isProductSalableInterface->execute($product->getSku(), $stockId) && $product->isSaleable();
            if (!empty($variants)) {
                $variantsInStock = [];
                foreach ($variants as $variant) {
                    if ($variant->getStatus() == 1 && $this->isProductSalableInterface->execute($variant->getSku(), $stockId)) {
                        $variantsInStock[] = $variant;
                        $qty += $this->getProductSalableQty->execute($variant->getSku(), $stockId);
                    }
                }
                $stockInfo["variantsInStock"] = $variantsInStock;
                $stockInfo["inStock"] = $stockInfo["inStock"] && !empty($variantsInStock);
            }
            else {
                $qty = $this->getProductSalableQty->execute($product->getSku(), $stockId);
            }
        } catch(\Exception|\Throwable $e) {
            // try the old way of getting the stock info
            return $this->getStockInfo($product, $variants, $stockId);
        }
        return ["stockInfo" => $stockInfo, "qty" => $qty, "minBuyQty" => $minBuyQty];
    }

    /**
     * Get stock information for a product (pre 2.3)
     *
     * @param Product $product
     *
     * @param array $variants
     *
     * @param int $stockId
     *
     * @return array
     */
    protected function getStockInfo($product, $variants, $stockId): array {
        $qty = 0;
        $minBuyQty = 1;
        $stockInfo = [];
        try {
            $stockItem = $this->stockRegistryInterface->getStockItem($product->getId());
            $stockInfo["inStock"] = $stockItem->getIsInStock() && $product->isSaleable();
            $minBuyQty = $stockItem->getMinSaleQty();
            if (!empty($variants)) {
                $variantsInStock = [];
                foreach ($variants as $variant) {
                    if ($variant->getStatus() == 1) {
                        $variantStockItem = $this->stockRegistryInterface->getStockItem($variant->getId());
                        if ($variantStockItem && $variantStockItem->getIsInStock() && $variant->isSaleable()) {
                            $variantsInStock[] = $variant;
                            $qty += $variantStockItem->getQty();
                        }
                    }
                }
                $stockInfo["variantsInStock"] = $variantsInStock;
                $stockInfo["inStock"] = $stockInfo["inStock"] && !empty($variantsInStock);
            }
            else{
                $qty = $stockItem->getQty();
            }
        } catch (\Exception|\Throwable $e) {
            $stockInfo["inStock"] = false;
        }
        return ["stockInfo" => $stockInfo, "qty" => $qty, "minBuyQty" => $minBuyQty];
    }

    /**
     * Gets product image url
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    protected function getProductImageUrl(ProductInterface $product): string {
        $imageUrl = "";
        if ($product instanceof ProductInterface) {
            try {
                $imageBlock = $this->blockFactory->createBlock("Magento\Catalog\Block\Product\ListProduct");
                $productImage = $imageBlock->getImage($product, "category_page_grid");
                $imageUrl = $productImage->getImageUrl();
            } catch(\Exception|\Throwable $e) {
                // The category_page_grid probably does exist
            }
        }
        return $imageUrl;
    }

     /**
     * Gets product image url
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    protected function getProductBaseImageUrl(ProductInterface $product): string {
        $mediaImagePath = $this->mediaConfig->getBaseMediaUrl();
        if ($product instanceof ProductInterface) {
            return $mediaImagePath . $product->getImage();
        }
        return "";
    }

    /**
     * Gets root category id
     *
     * @return int
     */
    protected function getRootCategoryId(): int {
        $storeId = $this->storeManager->getStore()->getId();
        $store = $this->storeManager->getStore($storeId);
        if ($store instanceof Store) {
            return (int) $store->getRootCategoryId();
        }
        return 0;
    }
}
