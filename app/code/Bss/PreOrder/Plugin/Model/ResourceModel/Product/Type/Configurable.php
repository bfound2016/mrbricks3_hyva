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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;

class Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolverInt;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Configurable constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolverInt
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolverInt
    ) {
        $this->helper = $helper;
        $this->scopeResolverInt = $scopeResolverInt;
        $this->productMetadata = $productMetadata;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Compare Current Site Version with 2.2.0
     *
     * @return bool
     */
    public function checkVersion()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.0') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Get Options For Configurable Product
     *
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $subject
     * @param callable $proceed
     * @param object $superAttribute
     * @param int $productId
     * @return mixed
     * @throws \Exception
     */
    public function aroundGetAttributeOptions($subject, callable $proceed, $superAttribute, $productId)
    {
        if ($this->helper->isEnable() && $this->checkVersion()) {
            $scope  = $this->scopeResolverInt->getScope();
            $select = $subject->getConnection()->select()->from(
                ['super_attribute' => $subject->getTable('catalog_product_super_attribute')],
                [
                    'sku' => 'entity.sku',
                    'product_id' => 'product_entity.entity_id',
                    'attribute_code' => 'attribute.attribute_code',
                    'value_index' => 'entity_value.value',
                    'option_title' => $subject->getConnection()->getIfNullSql(
                        'option_value.value',
                        'default_option_value.value'
                    ),
                    'default_title' => 'default_option_value.value',
                ]
            )->joinInner(
                ['product_entity' => $subject->getTable('catalog_product_entity')],
                "product_entity.{$this->getProductEntityLinkField()} = super_attribute.product_id",
                []
            )->joinInner(
                ['product_link' => $subject->getTable('catalog_product_super_link')],
                'product_link.parent_id = super_attribute.product_id',
                []
            )->joinInner(
                ['attribute' => $subject->getTable('eav_attribute')],
                'attribute.attribute_id = super_attribute.attribute_id',
                []
            )->joinInner(
                ['entity' => $subject->getTable('catalog_product_entity')],
                'entity.entity_id = product_link.product_id',
                []
            )->joinInner(
                ['entity_value' => $superAttribute->getBackendTable()],
                implode(
                    ' AND ',
                    [
                        'entity_value.attribute_id = super_attribute.attribute_id',
                        'entity_value.store_id = 0',
                        "entity_value.{$this->getProductEntityLinkField()} = "
                        . "entity.{$this->getProductEntityLinkField()}",
                    ]
                ),
                []
            )->joinLeft(
                ['option_value' => $subject->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'option_value.option_id = entity_value.value',
                        'option_value.store_id = ' . $scope->getId(),
                    ]
                ),
                []
            )->joinLeft(
                ['default_option_value' => $subject->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'default_option_value.option_id = entity_value.value',
                        'default_option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                ),
                []
            )->joinInner(
                ['attribute_option' => $subject->getTable('eav_attribute_option')],
                'attribute_option.option_id = entity_value.value',
                []
            )->order(
                'attribute_option.sort_order ASC'
            )->where(
                'super_attribute.product_id = ?',
                $productId
            )->where(
                'attribute.attribute_id = ?',
                $superAttribute->getAttributeId()
            );

            $data = [];
            $query = $subject->getConnection()->query($select);
            while ($row = $query->fetch()) {
                array_push($data, $row);
            }
            return $data;
        }
        return $proceed($superAttribute, $productId);
    }

    /**
     * Get Product Entity Link Field
     *
     * @return string
     * @throws \Exception
     */
    public function getProductEntityLinkField()
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }
}
