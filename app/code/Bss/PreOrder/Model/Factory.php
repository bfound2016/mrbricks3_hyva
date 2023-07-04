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
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model;

class Factory
{
    protected $stock;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var mixed
     */
    protected $dataBySku = null;

    /**
     * @var mixed
     */
    protected $sourceListItem = null;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Bss\PreOrder\Model\Stock $stock,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->stock = $stock;
        $this->_objectManager = $objectManager;
    }

    /**
     * Create model
     *
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    public function create()
    {
        return $this->_objectManager->create(\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku::class);
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku|mixed
     */
    public function getSalableQtyBySku()
    {
        if ($this->dataBySku == null) {
            $this->dataBySku = $this->create();
        }
        return $this->dataBySku;
    }

    /**
     * @return \Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku|mixed
     */
    protected function createSourceList()
    {
        return $this->_objectManager->create(\Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku::class);
    }

    /**
     * @param $sku
     * @return float|int|null
     */
    public function getSalableQtyBySource($sku)
    {
        if ($this->sourceListItem == null) {
            $this->sourceListItem = $this->createSourceList();
        }
        $sourceItems = $this->sourceListItem->execute($sku);
        $qty = 0;
        foreach ($sourceItems as $sourceItem) {
            $qty += $sourceItem->getQuantity();
        }
        return $qty;
    }

    /**
     * Get salable qty by stock current website
     *
     * @param string $sku
     * @return int|float
     */
    public function getSalableQtyOnlyStock($sku)
    {
        return $this->stock->getProductSalableQty($sku);
    }
}
