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
namespace Bss\PreOrder\Plugin\Api;

use Bss\PreOrder\Model\Attribute\Source\Order;

class ProductRepository
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helperApi;

    /**
     * @param \Bss\PreOrder\Helper\Data $helperApi
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helperApi
    ) {
        $this->helperApi = $helperApi;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    ) {
        if ($this->helperApi->isEnable()) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setIsPreOrder($this->helperApi->checkPreOrderAvailability($entity));
            $entity->setExtensionAttributes($extensionAttributes);
            return $entity;
        }
        return $entity;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface {
        if ($this->helperApi->isEnable()) {
            $products = [];
            foreach ($searchCriteria->getItems() as $entity) {
                $extensionAttributes = $entity->getExtensionAttributes();
                $extensionAttributes->setIsPreOrder($this->helperApi->checkPreOrderAvailability($entity));
                $entity->setExtensionAttributes($extensionAttributes);
                $products[] = $entity;
            }
            $searchCriteria->setItems($products);
            return $searchCriteria;
        }
        return $searchCriteria;
    }
}
