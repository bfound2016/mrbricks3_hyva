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
namespace Bss\ReviewReminder\Model;

class RemindLog extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag constant
     *
     * @var string
     */
    const CACHE_TAG = 'bss_reviewreminder_remindlog';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'bss_reviewreminder_remindlog';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_reviewreminder_remindlog';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\ReviewReminder\Model\ResourceModel\RemindLog::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
