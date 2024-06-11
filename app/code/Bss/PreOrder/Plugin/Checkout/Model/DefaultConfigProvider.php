<?php


namespace Bss\PreOrder\Plugin\Checkout\Model;


use Magento\Checkout\Model\Session as CheckoutSession;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

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
        $this->helper           = $helper;
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

            $result['quoteItemData'][$index]['preorder']    = $this->helper->getPreOrder($_productId);
            $result['quoteItemData'][$index]['restock']     = $this->helper->formatDate( $_product->getData("restock") );
        }
        return $result;
    }
}