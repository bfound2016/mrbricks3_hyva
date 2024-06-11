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
namespace Bss\PreOrder\Block;

use Magento\Framework\View\Element\Template;

class PreOrder extends Template
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * PreOrder constructor.
     * @param Template\Context $context
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param array $data
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
     * @return array
     */
    public function getNote()
    {
        $note=explode(" ", $this->helper->getNote());
        $key = array_search("{date}", $note);
        if ($key!==false) {
            unset($note[$key]);
        }
        return $note;
    }

    /**
     * @return mixed
     */
    public function getCartMess()
    {
        return $this->helper->getCartMess();
    }
}
