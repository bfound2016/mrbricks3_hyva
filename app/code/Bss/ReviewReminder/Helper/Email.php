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

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Helper Config Admin
     *
     * @var Data
     */
    protected $helper;

    /**
     * Scope Config Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * State Interface
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Transport Builder
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface
     */
    public function __construct(
        Context $context,
        Data $helper,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
    }

    /**
     * Send Email Function
     *
     * @param string|array $receivers
     * @param string $emailTemplate
     * @param array $templateVar
     * @return void
     */
    public function sendEmail($receivers, $emailTemplate, $templateVar, $storeId)
    {
        try {
            $email = $this->helper->configSenderEmail($storeId);

            $emailValue = 'trans_email/ident_'.$email.'/email';
            $emailNameValue = 'trans_email/ident_'.$email.'/name';
            $emailNameSender = $this->scopeConfig->getValue($emailNameValue, ScopeInterface::SCOPE_STORE, $storeId);
            $emailSender = $this->scopeConfig->getValue($emailValue, ScopeInterface::SCOPE_STORE, $storeId);
            $this->inlineTranslation->suspend();

            $sender = [
                'name' => $this->escaper->escapeHtml($emailNameSender),
                'email' => $this->escaper->escapeHtml($emailSender),
            ];
            /**
             * Check if store id of order equal 0, i will set default store id
             */
            if ($storeId == 0) {
                $storeId = $this->storeManager->getDefaultStoreView()->getId();
            }
            //Send Email
            $transport = $this->transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateVar)
            ->setFrom($sender)
            ->addTo($receivers);
            $configBcc = $this->helper->configBcc();

            $bccEmails = explode(",", $configBcc);
            if ($configBcc) {
                foreach ($bccEmails as $bccEmail) {
                    if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                        $transport->addBcc(str_replace(' ', '', $bccEmail));
                    }
                }
            }
            $transport = $transport->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
