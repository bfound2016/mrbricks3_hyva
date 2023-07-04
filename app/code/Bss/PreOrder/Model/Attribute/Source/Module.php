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
namespace Bss\PreOrder\Model\Attribute\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Class Module
 */
class Module
{
    const ATTRIBUTE_PREORDER = 'preorder';

    /**
     * @var null|int
     */
    protected $attributeIdPreOrder = null;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * Module constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Get attribute id preorder
     *
     * @return int
     */
    public function getAttributeIdPreOrder()
    {
        if (!$this->attributeIdPreOrder) {
            try {
                $attribute = $this->attributeRepository->get(Product::ENTITY, self::ATTRIBUTE_PREORDER);
                $this->attributeIdPreOrder = $attribute->getAttributeId();
            } catch (\Exception $exception) {
                $this->attributeIdPreOrder = null;
            }

        }

        return $this->attributeIdPreOrder;
    }
}
