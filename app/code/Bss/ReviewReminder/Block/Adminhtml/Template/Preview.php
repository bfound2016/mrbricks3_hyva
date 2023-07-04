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

namespace Bss\ReviewReminder\Block\Adminhtml\Template;

use Magento\Email\Model\BackendTemplate;
use Magento\Framework\Mail\Template\FactoryInterface;

class Preview extends \Magento\Backend\Block\Widget
{
    /**
     * Template Factory
     *
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailFactory;

    /**
     * Transport Builder
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Transport Builder
     *
     * @var \Bss\ReviewReminder\Helper\Data
     */
    protected $helper;

    /**
     * Order Factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * RemindLog Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    protected $message;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * Preview constructor.
     * @param FactoryInterface $templateFactory
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Mail\MessageInterface $message,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        FactoryInterface $templateFactory,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->remindLogFactory = $remindLogFactory;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->message = $message;
        $this->productMetadata = $productMetadata;
        $this->templateFactory = $templateFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $remindID = (int)$this->getRequest()->getParam('remindlog_id');
        $remindLog = $this->remindLogFactory->create()->load($remindID);
        $orderId = $remindLog->getOrderId();
        $order = $this->orderFactory->create()->load($orderId);

        $emailTemplate = $this->helper->configEmailTemplate();
        $templateVar = [
            'order' => $order,
            'storeName' => $order->getStore(),
            'customerName' => $order->getCustomerName(),
            'incrementId' => $order->getIncrementId(),
            'createdAt' => $order->getCreatedAt()
        ];

        $templateModel = BackendTemplate::class;
        $template = $this->templateFactory->get($emailTemplate, $templateModel)
            ->setVars($templateVar)
            ->setOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $order->getStoreId(),
                ]
            );
        $html = $template->processTemplate();
        return $html;
    }

    /**
     * @return \Magento\Framework\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }
}
