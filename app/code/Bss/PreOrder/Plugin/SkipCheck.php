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
 * @category  BSS
 * @package   Bss_PreOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

class SkipCheck
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * SkipCheck constructor.
     *
     * @param Data                       $helper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Data $helper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    /**
     * Skip Check Is Salable For PreOrder Product
     *
     * @param  Product $subject
     * @param  bool    $result
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterIsSalable(Product $subject, bool $result)
    {
        if ($this->helper->isEnable() && !$result) {
            $preOrder = $subject->getData('preorder');
            if ($preOrder === null) {
                $subject = $this->productRepository->getById($subject->getId());
                $preOrder = $subject->getData('preorder');
            }
            if ($this->checkPreOrderProduct($subject, $preOrder)) {
                return true;
            }
            if ($subject->getTypeId() == 'grouped') {
                $childProductCollection = $subject->getTypeInstance()
                    ->getAssociatedProductCollection($subject)
                    ->addAttributeToSelect('preorder', 'left')
                    ->getData();
                $x = [];
                foreach ($childProductCollection as $child) {
                    $x[] = $child['preorder'];
                }
                if (in_array(1, $x) || in_array(2, $x)) {
                    return true;
                }
            }
        }
        return $result;
    }

    /**
     * @param  Product    $subject
     * @param  int|string $preOrder
     * @return bool
     */
    protected function checkPreOrderProduct(Product $subject, $preOrder): bool
    {
        if (($preOrder == 1 && $this->helper->isAvailablePreOrderFromFlatData(
            $subject->getData('pre_oder_from_date'),
            $subject->getData('pre_oder_to_date')
        )) || $preOrder == 2
        ) {
            return true;
        }
        return false;
    }
}
