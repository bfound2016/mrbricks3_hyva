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
declare(strict_types=1);
namespace Bss\ReviewReminder\Plugin;

class EmailTemplate
{
    /**
     * @param \Magento\Email\Model\Template $subject
     * @return void
     */
    public function beforeBeforeSave(\Magento\Email\Model\Template $subject)
    {
        if ($subject->getOrigTemplateCode() == 'reviewreminder_general_email_template') {
            $subject->setData('is_legacy', 1);
        }
    }
}
