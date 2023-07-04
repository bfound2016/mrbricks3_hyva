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

namespace Bss\ReviewReminder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class BeforeLoad
 * @package Bss\ReviewReminder\Observer
 */
class BeforeLoad implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\Encoder
     */
    protected $urlEncoder;

    /**
     * BeforeLoad constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Url\Encoder $urlEncoder
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Url\Encoder $urlEncoder
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $reviewData = $this->request->getParam('review');
        if (!$reviewData) {
            return $this;
        }
        try {
            //@codingStandardsIgnoreStart
            $params = base64_decode($reviewData);
            //@codingStandardsIgnoreEnd
            $params = $this->serializer->unserialize($params);
        } catch (\Exception $e) {
            return $this;
        }
        if (!isset($params['orderId']) || !isset($params['storeCode'])) {
            return $this;
        }

        $orderId = $params['orderId'];
        $storeCode = $params['storeCode'];
        if ($orderId && $storeCode) {
            $currentStore = $this->storeManagerInterface->getStore();
            $currentStoreCode = $currentStore->getCode();
            if ($currentStoreCode != $storeCode) {
                $response = $observer->getResponse();
                $query =
                    '___store/' . $storeCode . '/'
                    . '___from_store/' . $currentStoreCode
                    . '/uenc/' . $this->urlEncoder->encode($currentStore->getCurrentUrl());
                $finalRedirect = $currentStore->getBaseUrl() . 'stores/store/redirect/' . $query;
                $response->setRedirect($finalRedirect);
            }
        }
        return $this;
    }
}
