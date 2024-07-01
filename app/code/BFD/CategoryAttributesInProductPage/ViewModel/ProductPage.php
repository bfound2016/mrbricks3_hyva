<?php

namespace BFD\CategoryAttributesInProductPage\ViewModel;

use Composer\Util\Url;
use Hyva\Theme\ViewModel\ProductPage as HyvaProductPage;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Catalog\Helper\Output as ProductOutputHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class ProductPage extends HyvaProductPage
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartHelper $cartHelper
     * @param ProductOutputHelper $productOutputHelper
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param ImageFactory $productImageFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        CartHelper $cartHelper,
        ProductOutputHelper $productOutputHelper,
        ScopeConfigInterface $scopeConfig,
        ImageFactory $productImageFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($registry, $priceCurrency, $cartHelper, $productOutputHelper, $scopeConfig, $productImageFactory);
    }

    /**
     * Get multiple category attributes.
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getCategoriesData(ProductInterface $product): array
    {
        $categoryIds = $product->getCategoryIds();
        $categoriesData = [];

        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $categoryLogoAttribute = $category->getCustomAttribute('category_logo');
                $categoryBackgroundAttribute = $category->getCustomAttribute('category_background');

                $categoriesData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'category_logo' => $categoryLogoAttribute ? $categoryLogoAttribute->getValue() : null,
                    'category_background' => $categoryBackgroundAttribute ? $categoryBackgroundAttribute->getValue() : null
                ];
            } catch (NoSuchEntityException $e) {
                // Handle the exception if needed
            }
        }

        return $categoriesData;
    }

    public function getStoreUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getUrlSuffix(): string
    {
        return (string)$this->scopeConfig->getValue('catalog/seo/category_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getCategoryUrl($categoryId): string
    {
        try{
            $category = $this->categoryRepository->get($categoryId);
            $urlKey = $category->getUrlKey();
            $url = rtrim($this->urlInterface->getUrl($urlKey),'/');

            return $url . $this->getUrlSuffix();
        } catch (NoSuchEntityException $e) {
            // Handle the exception if needed
            return '';
        }
    }

    public function getMediaUrl(): string
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (NoSuchEntityException $e) {
            // Handle the exception if needed
            return '';
        }
    }
}
