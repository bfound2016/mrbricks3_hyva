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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model\ResourceModel;

class PreOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * PreOrder constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->eavConfig = $eavConfig;
    }

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute', 'attribute_id');
    }

    /**
     * @param $productId
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPreOrder($productId, $storeId = null)
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'preorder');
        $attributeTable = $attribute->getBackend()->getTable();
        $linkField = $attribute->getEntity()->getLinkField();

        $connection = $this->getConnection();
        if ($storeId === null || $storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $select = $connection->select()->from($attributeTable, [$linkField, 'value'])
                ->where("{$linkField} IN (?)", $productId)
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $rows = $connection->fetchPairs($select);
        } else {
            $select = $connection->select()->from(
                ['t1' => $attributeTable],
                [$linkField => "t1.{$linkField}", 'value' => $connection->getIfNullSql('t2.value', 't1.value')]
            )->joinLeft(
                ['t2' => $attributeTable],
                "t1.{$linkField} = t2.{$linkField} AND t1.attribute_id = t2.attribute_id AND t2.store_id = {$storeId}"
            )->where(
                't1.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )->where(
                't1.attribute_id = ?',
                $attribute->getAttributeId()
            )->where(
                "t1.{$linkField} IN(?)",
                $productId
            );

            $rows = $connection->fetchPairs($select);
        }
        if (array_key_exists($productId, $rows)) {
            return $rows[$productId];
        }
        return 0;
    }
}
