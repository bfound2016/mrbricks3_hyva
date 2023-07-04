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

namespace Bss\ReviewReminder\Helper;

/**
 * Class Data
 * @package Bss\ReviewReminder\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Remind Log
     *
     * @var \Bss\ReviewReminder\Model\ResourceModel\RemindLog
     */
    protected $remindLog;

    /**
     * Scope Config Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Review Collection
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * State
     *
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory
     */
    protected $generationSpecFactory;

    /**
     * @var \Magento\SalesRule\Model\Service\CouponManagementService
     */
    protected $couponManagementService;

    /**
     * Data constructor.
     * @param \Magento\SalesRule\Model\Service\CouponManagementService $couponManagementService
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory $generationSpecFactory
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Bss\ReviewReminder\Model\ResourceModel\RemindLog $remindLog
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\SalesRule\Model\Service\CouponManagementService $couponManagementService,
        \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory $generationSpecFactory,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Bss\ReviewReminder\Model\ResourceModel\RemindLog $remindLog,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->couponManagementService = $couponManagementService;
        $this->generationSpecFactory = $generationSpecFactory;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->logger = $context->getLogger();
        $this->reviewCollection = $reviewCollection;
        $this->remindLog = $remindLog;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Get Remind Less Than Date
     *
     * @param string $date
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemindLessThanDate($date)
    {
        return $this->remindLog->getRemindLessThanDate($date);
    }

    /**
     * Get Remind By Sent Count
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemindBySentCount()
    {
        return $this->remindLog->getRemindBySentCount($this->configMaxEmail());
    }

    /**
     * Config Clear Log After X Days
     *
     * @return string
     */
    public function configClearLog()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/clear_log',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Config Order Status
     *
     * @return string
     */
    public function configOrderStatus()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $storeCode
     * @return mixed
     */
    public function configDaySendMail($storeCode)
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/after_day',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Config sender email
     *
     * @param string $storeId
     * @return string
     */
    public function configSenderEmail($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Max email per order
     *
     * @return string
     */
    public function configMaxEmail()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/max_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * BCC
     *
     * @return string
     */
    public function configBcc()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/email_bcc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Config Email Template
     *
     * @return string
     */
    public function configEmailTemplate()
    {
        {
            return $this->scopeConfig->getValue(
                'reviewreminder/general/email_template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
    }

    /**
     * Config Coupon Email Template
     *
     * @return string
     */
    public function configCouponEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/coupon/email_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Config Customer Group
     *
     * @return string
     */
    public function configCustomerGroup()
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/customergroups',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param mixed $storeId
     * @return mixed
     */
    public function configEnable($storeId = false)
    {
        if ($storeId) {
            return $this->scopeConfig->getValue(
                'reviewreminder/general/enable',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->scopeConfig->getValue(
            'reviewreminder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $storeCode
     * @return mixed
     */
    public function configEnableByStore($storeCode)
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Check Enable Send Coupon
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnableSendCoupon($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            'reviewreminder/coupon/choose',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Coupon Rule
     *
     * @return int|mixed
     */
    public function getCouponRule($storeId)
    {
        $cartRuleId = $this->scopeConfig->getValue(
            'reviewreminder/coupon/rule',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$cartRuleId) {
            $cartRuleId = 0;
        }
        return $cartRuleId;
    }

    /**
     * Get Send Coupon Customer Group
     *
     * @return array|mixed
     */
    public function getSendCouponCustomerGroup($storeId)
    {
        $sendGroup = $this->scopeConfig->getValue(
            'reviewreminder/coupon/customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $sendGroup = explode(",", $sendGroup);
        return $sendGroup;
    }

    /**
     * Config Coupon
     *
     * @param mixed $customerId
     * @return bool|mixed
     */
    public function configCoupon($customerId)
    {
        if (!$this->scopeConfig->isSetFlag(
            'reviewreminder/coupon/choose',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return false;
        }
        $sendGroup = $this->scopeConfig->getValue(
            'reviewreminder/coupon/customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $sendGroup = explode(",", $sendGroup);
        if ($customerId == null || !$customerId) {
            $customerGroup = 2;
        } else {
            $customerGroup = 1;
        }
        if (!in_array($customerGroup, $sendGroup)) {
            return false;
        }
        $cartRuleId = $this->scopeConfig->getValue(
            'reviewreminder/coupon/rule',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$cartRuleId || $cartRuleId == 0) {
            return false;
        }
        return $cartRuleId;
    }

    /**
     * Customer Reviewed
     *
     * @param array $array
     * @param int $customerId
     * @return bool
     */
    public function customerReviewed($array, $customerId)
    {
        if (!empty($array)) {
            foreach ($array as $item) {
                if (isset($item['customer_id']) && $item['customer_id'] == $customerId) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check Order Reviewed
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     * @return bool
     */
    public function checkOrderReviewed($order)
    {
        $items = $order->getAllItems();
        $customerId = $order->getCustomerId();
        $productNeedReview = 0;
        $reviewed = 0;
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $productNeedReview++;
            $reviews = $this->reviewCollection->create()
                ->addStoreFilter($order->getStoreId())
                ->addEntityFilter(
                    'product',
                    $productId
                );
            if ($this->customerReviewed($reviews->getData(), $customerId)) {
                $reviewed++;
            }
        }

        if ($reviewed == $productNeedReview) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Write Log
     *
     * @param string $string
     */
    public function writeLog($string)
    {
        if ($string) {
            $this->logger->info('Review Reminder - Send Mail: ' . $string . ' errors send emails.');
        }
    }

    /**
     * Get Current Store
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Get State Area
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStateArea()
    {
        return $this->state->getAreaCode();
    }

    /**
     * Get Coupon Code
     *
     * @param array $data
     * @return string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCouponCode($data)
    {
        $couponSpec = $this->generationSpecFactory->create(['data' => $data]);
        $couponSpec->setQuantity(1);
        $couponCode = $this->couponManagementService->generate($couponSpec);
        return $couponCode;
    }

    /**
     * Get Send Coupon Depend Review Status Config
     *
     * @param int $storeId
     * @return mixed
     */
    public function getSendCouponDependReviewStatusConfig($storeId)
    {
        return $this->scopeConfig->getValue(
            'reviewreminder/coupon/send_coupon_rule',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
