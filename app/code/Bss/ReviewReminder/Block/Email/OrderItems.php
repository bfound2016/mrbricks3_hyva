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
 * @package    Bss_ReviewReminder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReviewReminder\Block\Email;

class OrderItems extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $grouped;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $bundleSelection;

    /**
     * OrderItems constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Magento\Bundle\Model\Product\Type $bundleSelection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Bundle\Model\Product\Type $bundleSelection,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->storeManager = $context->getStoreManager();
        $this->_productloader = $_productloader;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->bundleSelection = $bundleSelection;
        parent::__construct($context, $data);
    }

    /**
     * Get Product Url FrontEnd
     *
     * @param $order
     * @param int $orderId
     * @return string
     */
    public function getProductUrlFrontEnd($order, $orderId)
    {
        $productId = $this->getParentProductId($order->getProductType(), $order->getProductId());
        if ($productId === null) {
            $productId = $order->getProductId();
        }
        $storeId = $order->getStore()->getId();
        $storeCode = $order->getStore()->getCode();
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            $params = ['orderId' => $orderId, 'storeCode' => $storeCode];
            $params = base64_encode($this->serializer->serialize($params));
            return $product->getProductUrl() . "?review=" . $params;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get parent product id
     * @param string $productType
     * @param int $childId
     * @return int|mixed
     */
    public function getParentProductId($productType, $childId)
    {
        $productId = null;
        switch ($productType) {
            case 'configurable':
                $product = $this->configurable->getParentIdsByChild($childId);
                if (isset($product[0])) {
                    $productId = $product[0];
                }
                break;
            case 'grouped':
                $groupIds = $this->grouped->getParentIdsByChild($childId);
                if (isset($groupIds[0])) {
                    $productId = $groupIds[0];
                }
                break;
            case 'bundle':
                $bundleId = $this->bundleSelection->getParentIdsByChild($childId);
                if (isset($bundleId[0])) {
                    $productId = $bundleId[0];
                }
                break;
        }
        return $productId;
    }

    /**
     * Get Product Not Be Revewed
     *
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    public function getProductNotBeRevewed($order)
    {
        $items = $order->getAllItems();
        foreach ($items as $key => $item) {
            if ($item->getParentItemId() != null) {
                unset($items[$key]);
            }
        }
        return $items;
    }
}
