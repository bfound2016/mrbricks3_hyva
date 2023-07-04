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
 * @copyright Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Block;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template;

class PreOrderProduct extends Template
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * PreOrderProduct constructor.
     *
     * @param Template\Context          $context
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param array                     $data
     */
    public function __construct(
        Template\Context $context,
        \Bss\PreOrder\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Get Message Pre Order
     *
     * @return string
     */
    public function getMessage()
    {
        $typeId = $this->getParentType();
        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();
        $message = $this->helper->replaceVariableX(
            $this->getProduct()->getData("message"),
            $fromDate,
            $toDate
        );

        if ($typeId == Configurable::TYPE_CODE
            || ($typeId == ""
            && $this->getProduct()->getTypeId() == Configurable::TYPE_CODE)
        ) {
            return "";
        }

        if ($message == "") {
            $message = $this->helper->replaceVariableX(
                $this->helper->getMess(),
                $fromDate,
                $toDate
            );
        }

        return $message;
    }

    /**
     * Get Button Pre Order Html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonHtml()
    {
        $button = __("Pre-Order");
        if ($this->helper->getButton()) {
            $button = $this->helper->getButton();
        }

        return $button;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFromDate()
    {
        return $this->helper->formatDate($this->getProductDetail()->getPreOderFromDate());
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getToDate()
    {
        return $this->helper->formatDate($this->getProductDetail()->getPreOderToDate());
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetail()
    {
        return $this->helper->getProductById($this->getProduct()->getId());
    }

    /**
     * Check is Group Product
     *
     * @return bool
     */
    public function isGroupProduct()
    {
        $typeId = $this->getParentType();

        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return true;
        }

        return false;
    }

    /**
     * Get Notice Pre Order Mess
     *
     * @return array
     */
    public function getNote()
    {
        $getNote = $this->helper->getNote();
        if ($getNote !== null) {
            $note = explode(" ", $getNote);
        } else {
            $note = [];
        }

        $key = array_search("{date}", $note);
        $key2 = array_search("{preorder_date}", $note);

        if ($key !== false) {
            unset($note[$key]);
        }
        if ($key2 !== false) {
            unset($note[$key2]);
        }

        return $note;
    }

    /**
     * @return mixed|string
     */
    public function getAvailabilityMessage()
    {
        return $this->helper->getAvailabilityMessage($this->getProduct());
    }
}
