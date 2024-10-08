<?php


namespace Bss\PreOrder\Plugin\Checkout\Model;


use Magento\Checkout\Model\Session as CheckoutSession;
use Bss\PreOrder\Helper\Data as PreOrderHelper;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->checkoutSession  = $checkoutSession;
        $this->preOrderHelper   = $helper;
    }

    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);

            $_product       =   $quoteItem->getProduct();
            $_productId     =   $_product->getId();

            $result['quoteItemData'][$index]['preorder']    = $this->preOrderHelper->getPreOrder($_productId);
            $result['quoteItemData'][$index]['restock']     = $this->preOrderHelper->formatDate( $_product->getData("restock") );
        }
        return $result;
    }
}