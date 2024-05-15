<?php

namespace Addwish\Awext\Service;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\ConfigurableFactory;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Service\SwatchesInterface;

/**
 * Service class used for returning swatch config for products
 * for a ajax request (to allow page cache to continue functioning)
 * @package Addwish\Awext\Service
 */
class Swatches implements SwatchesInterface {
    /**
     * @var Json $json
     */
    private $json;

    /**
     * @var Http
     */
    private $http;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ConfigurableFactory
     */
    protected $configurableFactory;

    /**
     * @param Http $http
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigurableFactory $configurableFactory
     */
    public function __construct(
        Http $http,
        ProductRepositoryInterface $productRepository,
        ConfigurableFactory $configurableFactory
    ) {
        $this->http = $http;
        $this->productRepository = $productRepository;
        $this->configurableFactory = $configurableFactory;
    }

    /**
     * @return string
     */
    public function getSwatches(): string {
        $data = [];
        $productIds = $this->http->getParam("productIds");
        if ($productIds != NULL) {
            $configurable = $this->configurableFactory->create();
            foreach (explode(",",$productIds) as $productId) {
                $product = $this->getProduct($productId);
                if ($product != FALSE) {
                    if($product->getTypeId() == "configurable") {
                        $configurable->setProduct($product);
                        $data[$product->getId()] = [
                            "jsonConfig" => $configurable->getJsonConfig(),
                            "jsonSwatchConfig" => $configurable->getJsonSwatchConfig(),
                            "numberToShow" => $configurable->getNumberSwatchesPerProduct()
                        ];
                    }
                }
            }
        }
        return json_encode($data);
    }

    /**
     * Gets product
     *
     * @param int $productId
     */
    protected function getProduct($productId) {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            // ignore
        }
        return FALSE;
    }
}

