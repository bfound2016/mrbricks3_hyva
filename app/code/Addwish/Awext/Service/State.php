<?php

namespace Addwish\Awext\Service;

use Addwish\Awext\Api\Service\StateInterface;
use Addwish\Awext\Helper\Config as ConfigHelper;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\UrlInterface;


/**
 * Service class used for returning current cart and order state
 * for a ajax request (to allow page cache to continue functioning)
 * @package Addwish\Awext\Service
 */
class State implements StateInterface {
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Cart constructor.
     * @param Session $session
     * @param UrlInterface $url
     * @param ConfigHelper $configHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $session,
        UrlInterface $url,
        ConfigHelper $configHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->session = $session;
        $this->url = $url;
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @return mixed[]
     */
    public function getState(): array {
        $data = [];
        if ($this->configHelper->isCartTrackingEnabled()) {
            $data["cart"] = $this->getCart();
        }
        if ($this->configHelper->isConversionTrackingEnabled()
                && $this->session->getAddwishOrderSuccess()) {
            $this->session->unsAddwishOrderSuccess();
            $data["order"] = $this->getOrder();
        }
        return [$data];
    }

    protected function getCart(): array {
        $quote = $this->session->getQuote();
        $cart = [
            "total" => 0,
            "productNumbers" => [],
            "url" => ""
        ];
        $cartContents = [];

        if (null !== $quote) {
            if ($this->configHelper->isEmailsInTrackingEnabled()) {
                $cart["email"] = $quote->getCustomerEmail();
            }
            if (null !== $quote->getSubtotalWithDiscount()) {
                $cart["total"] = $quote->getSubtotalWithDiscount();
            } elseif(null !== $quote->getGrandTotal()) {
                $cart["total"] = $quote->getGrandTotal();
            }

            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $product = $quoteItem->getProduct();
                $productOptions = $product->getTypeInstance(true)->getOrderOptions($product);

                # this will get the variant's sku instead of config master's sku: $quoteItem->getSku());
                $productNumber = $product->getData("sku");
                if (!in_array($productNumber, $cart["productNumbers"])) {
                    $cart["productNumbers"][] = $productNumber;
                }

                if (array_key_exists("info_buyRequest", $productOptions)) {
                    $buyRequest = $productOptions["info_buyRequest"];
                    
                    $superAttributes = [];
                    if (array_key_exists("super_attribute", $buyRequest) && is_array($buyRequest["super_attribute"])){
                        foreach ($buyRequest["super_attribute"] as $attributeKey => $attributeValue ){
                            if (is_array($attributeValue)){
                                foreach ($attributeValue as $key => $value){
                                    if ($value) {
                                        $attributeValue[$key] = urlencode(urlencode($value));
                                    }
                                }
                                $attributeValue = implode(":is:",$attributeValue);
                            } else {
                                $attributeValue = urlencode(urlencode($attributeValue));
                            }
                            if ($attributeValue) {
                                $superAttributes[] = urlencode(urlencode((string)$attributeKey)) 
                                    . ":kv:" 
                                    . $attributeValue;
                            }
                        }
                    }

                    $options = [];
                    if (array_key_exists("options", $buyRequest) && is_array($buyRequest["options"])){
                        foreach ($buyRequest["options"] as $optionKey => $optionValue ){
                            if (is_array($optionValue)){
                                foreach ($optionValue as $key => $value){
                                    if ($value) {
                                        $optionValue[$key] = urlencode(urlencode($value));
                                    }
                                }
                                $optionValue = implode(":is:", $optionValue);
                            } else {
                                $optionValue = urlencode(urlencode($optionValue));
                            }
                            if ($optionValue) {
                                $options[] = urlencode(urlencode((string)$optionKey)) . ":kv:" . $optionValue;
                            }
                        }
                    }

                    $bundleOption = [];
                    if (array_key_exists("bundle_option", $buyRequest) && is_array($buyRequest["bundle_option"])){
                        foreach ($buyRequest["bundle_option"] as $bundleOptionKey => $bundleOptionValue ){
                            if (is_array($bundleOptionValue)){
                                foreach ($bundleOptionValue as $key => $value){
                                    if ($value) {
                                        $bundleOptionValue[$key] = urlencode(urlencode($value));
                                    }
                                }
                                $bundleOptionValue = implode(":is:", $bundleOptionValue);
                            } else {
                                $bundleOptionValue = urlencode(urlencode($bundleOptionValue));
                            }
                            if ($bundleOptionValue) {
                                $bundleOption[] = urlencode(urlencode((string)$bundleOptionKey))
                                    . ":kv:"
                                    . $bundleOptionValue;
                            }
                        }
                    }

                    $bundleOptionQty = [];
                    if (array_key_exists("bundle_option_qty", $buyRequest) && is_array($buyRequest["bundle_option_qty"])) {
                        foreach ($buyRequest["bundle_option_qty"] as $bundleOptionQtyKey => $bundleOptionQtyValue ){
                            if (is_array($bundleOptionQtyValue)){
                                foreach ($bundleOptionQtyValue as $key => $value){
                                    if ($value) {
                                        $bundleOptionQtyValue[$key] = urlencode(urlencode($value));
                                    }
                                }
                                $bundleOptionQtyValue = implode(":is:", $bundleOptionQtyValue);
                            } else {
                                $bundleOptionQtyValue = urlencode(urlencode($bundleOptionQtyValue));
                            }
                            if ($bundleOptionQtyValue) {
                                $bundleOptionQty[] = urlencode(urlencode((string)$bundleOptionQtyKey))
                                    . ":kv:"
                                    . $bundleOptionQtyValue;
                            }
                        }
                    }
                    
                    /*
                    :p: = part delimiter (part :p: part)
                    :kv: = key value indicator (key :kv: value)
                    :s: = list separator (key:kv:value :s: key:kv:value)
                    :is: = inner list separator (key :kv: value :is: value)

                    Values are double url encoded to make sure : does not appear as a character
                    in the parameters. The browser will decode once when the parameter is used
                    and the setCart method will decode once more manually
                    */
                    if (array_key_exists("product", $buyRequest) && array_key_exists("qty", $buyRequest)) {
                        $cartContents[] = implode(":p:", [
                            $buyRequest["product"],
                            $buyRequest["qty"],
                            implode(":s:", $superAttributes),
                            implode(":s:", $options),
                            implode(":s:", $bundleOption),
                            implode(":s:", $bundleOptionQty)]);
                    }
                }
            }
            if (count($cartContents) > 0) {
                $cart["url"] = $this->url->getUrl() . "addwish/cart/set?contents=" . implode(",", $cartContents);
            }
        }
        return $cart;
    }

    protected function getOrder() : array {
        $order = $this->session->getLastRealOrder();
        $result = [
            "orderNumber" => $order->getIncrementId(),
            "total" => $order->getSubtotal(),
            "products" => [],
        ];
        if ($this->configHelper->isEmailsInTrackingEnabled()) {
            $result["email"] = $order->getCustomerEmail();
        }

        foreach($order->getAllVisibleItems() as $item) {
            $result["products"][] = [
                "url" => $this->getProductUrl($item),
                "productNumber" => $item->getSku(),
                "quantity" => $item->getQtyOrdered(),
            ];
        }
        return $result;
    }

    /**
     * Gets product url
     *
     * @param OrderItemInterface $item
     *
     * @return string
     */
    protected function getProductUrl($item): string {
        $url = "";
        try {
            $productId = $item->getProductId();
            $product   = $this->productRepository->getById($productId);
            $url = $product->getProductUrl();
        } catch (\Exception $e) {
            // ignore
        }
        return $url;
    }

}

