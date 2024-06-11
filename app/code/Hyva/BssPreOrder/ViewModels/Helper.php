<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Hyva_BssMultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Hyva\BssPreOrder\ViewModels;

use Bss\PreOrder\Helper\Data as BssPreOrder;
use Bss\PreOrder\Helper\ProductData;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Class Helper
 * @package Hyva\BssPreOrder\ViewModels
 */
class Helper implements ArgumentInterface
{
    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var
     */
    protected $productData;

    /**
     * @var BssPreOrder
     */
    private BssPreOrder $helper;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var
     */
    protected $categoryFactory;

    /**
     * @var Data
     */
    protected $helperMagento;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var
     */
    protected $productCollectionFactory;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Construct.
     *
     * @param BssPreOrder $data
     * @param SerializerInterface $serializer
     * @param ProductData $productData
     * @param Configurable $configurable
     * @param CategoryFactory $categoryFactory
     * @param Data $helperMagento
     * @param Http $request
     * @param CollectionFactory $productCollectionFactory
     * @param Collection $collection
     */
    public function __construct(
        BssPreOrder $data,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Bss\PreOrder\Helper\ProductData $productData,
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $configurable,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Data $helperMagento,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->configurable = $configurable;
        $this->productData = $productData;
        $this->serializer = $serializer;
        $this->helper = $data;
        $this->helperMagento = $helperMagento;
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->collection = $collection;
    }

    /**
     * @return BssPreOrder
     */
    public function getHelper(): BssPreOrder
    {
        return $this->helper;
    }

    /**
     * Get Product Child Configurable
     *
     * @param $product
     * @return false|string
     * @throws NoSuchEntityException
     */
    public function getProductChildConfigurable($product)
    {
        if ($product->getTypeId() == 'configurable')
        {
            $arr = $this->productData->getAllData($this->configurable->getAllowProducts());
            return $this->serializer->serialize($arr);
        }
        return "";
    }

    /**
     * @return bool
     */
    public function getIsMix() {
        return $this->getHelper()->isMix();
    }

    /**
     * get IsPreOrder
     *
     * @param $product
     * @return bool|void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIsPreOrder($product) {
        if($product->getTypeId() === 'grouped') {
            $childProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $simpleIndex = [];
            foreach ($childProducts as $key => $childProduct) {
                $childProductId = $childProduct->getId();
                $stockStatusG = $this->getHelper()->getIsInStock($childProductId);
                $fromDate =  $this->getHelper()->getPreOrderFromDate($childProductId);
                $toDate =  $this->getHelper()->getPreOrderToDate($childProductId);
                $isPreOderG = $this->getHelper()->isPreOrder($this->getHelper()->getPreOrder($childProductId),$stockStatusG);
                if ($isPreOderG && $this->getHelper()->isAvailablePreOrderFromFlatData($fromDate, $toDate) ) {
                    $simpleIndex[$childProductId] = ["mess" => $this->getHelper()->getAvailabilityMessageByPid($childProductId), "button" => $this->getHelper()->getButton()];
                }
            }
            $isPreOder = count($childProducts) === count($simpleIndex);
            return $isPreOder;
        }

        if($product->getTypeId() === 'simple') {
            $productId = $product->getId();
            $stockStatus = $this->getHelper()->getIsInStock($productId);
            $fromDate =  $this->getHelper()->getPreOrderFromDate($productId);
            $toDate =  $this->getHelper()->getPreOrderToDate($productId);
            $isPreOder = $this->getHelper()->isPreOrder($this->getHelper()->getPreOrder($productId),$stockStatus) && $this->getHelper()->isAvailablePreOrderFromFlatData($fromDate, $toDate);
            return $isPreOder;
        }
    }

    /**
     * get Simple Index of Configurable
     *
     * @param $product
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSimpleIndexConfigurable($product) {
        $simpleIndex=[];
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($childProducts as $key => $childProduct) {
            $childProductId = $childProduct->getId();
            $stockStatusCP = $this->getHelper()->getIsInStock($childProductId);
            $fromDate =  $this->getHelper()->getPreOrderFromDate($childProductId);
            $toDate =  $this->getHelper()->getPreOrderToDate($childProductId);
            $isPreOderCP = $this->getHelper()->isPreOrder($this->getHelper()->getPreOrder($childProductId),$stockStatusCP);
            if ($isPreOderCP && $this->getHelper()->isAvailablePreOrderFromFlatData($fromDate, $toDate) ) {
                $simpleIndex[$childProductId] = ["mess" => $this->getHelper()->getAvailabilityMessageByPid($childProductId),"button" => $this->getHelper()->getButton(),"availability" => $this->getHelper()->getAvailabilityMessage($childProduct)];
            }
        }
        return $simpleIndex;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        $categoryObject = $this->helperMagento->getCategory();
        $categoryId = $categoryObject->getId();
        return $this->categoryFactory->create()->load($categoryId);
    }

    /**
     * get All ProductChild
     *
     * @return bool|string
     */
    public function getAllProductChild()
    {
        $allProductConfigurable = [];
        if($this->request->getFullActionName() === "catalog_category_view") {
            $allProductConfigurable =  $this->getCategory()->getProductCollection()->addAttributeToSelect('*');
        }
        if($this->request->getFullActionName() === "catalogsearch_result_index") {
            $collection = $this->productCollectionFactory->create();
            $allProductConfigurable = $collection->addAttributeToFilter('type_id', "configurable");
        }
        $arr = [];
        foreach ($allProductConfigurable as $product) {
            if($product['type_id'] === 'configurable') {
                $allowProduct = $product->getTypeInstance()->getUsedProducts($product, null);
                $arr[] = $this->productData->getAllData($allowProduct);
            }
        }
        return $this->serializer->serialize($arr);
    }
}
