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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class LoadCustomerQuoteObserver
{
    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * LoadCustomerQuoteObserver constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param PreOrderHelper $preOrderHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param CheckBeforeAdd $checkBeforeAdd
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        PreOrderHelper $preOrderHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->preOrderHelper = $preOrderHelper;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Observer\LoadCustomerQuoteObserver $observer
     * @param Observer $observer
     */
    public function beforeExecute($subject, Observer $observer)
    {
        $customerId = $this->customerSessionFactory->create()->getCustomerId();
        $checkoutSession = $this->checkoutSession;
        try {
            $customerQuote = $this->quoteRepository->getForCustomer($customerId);
            if ($this->preOrderHelper->isEnable() && !$this->preOrderHelper->isMix()) {
                $customerQuoteItems = $customerQuote->getAllItems();
                $sessionItems = $checkoutSession->getQuote()->getAllItems();
                foreach ($sessionItems as $item) {
                    $currentProduct = $item->getProduct();
                    $requestInfo = $item->getBuyRequest();
                    $requestInfo['qty'] = $item->getQty();
                    $requestInfo['product'] = $currentProduct->getId();
                    $preOrderItem = $this->preOrderHelper->checkPreOrderItem($currentProduct, $requestInfo, true);
                    $this->preOrderHelper->validateWithCart($customerQuoteItems, $preOrderItem);
                }
            }
        } catch (NoSuchEntityException $entityException) {
            return [$observer];
        } catch (\Exception $exception) {
            $customerQuote = $this->quoteRepository->getForCustomer($customerId);
            $customerQuote->setStoreId($this->storeManager->getStore()->getId());
            if ($customerQuote->getId() && $checkoutSession->getQuoteId() != $customerQuote->getId()) {
                $customerQuote->setStoreId($this->storeManager->getStore()->getId());
                if ($checkoutSession->getQuoteId()) {
                    $this->quoteRepository->save(
                        $customerQuote->collectTotals()
                    );
                }
                $this->quoteRepository->delete($checkoutSession->getQuote());
                $checkoutSession->setQuoteId($customerQuote->getId());
                $checkoutSession->replaceQuote($customerQuote);
            }
            throw new LocalizedException(__($exception->getMessage()));
        }

        return [$observer];
    }
}
