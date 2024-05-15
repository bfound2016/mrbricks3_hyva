<?php


namespace Addwish\Awext\Plugin\Admin;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\Save;

class UrlRewrite {
    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var DeltaItemHelper
     */
    private $deltaItemHelper;

    /**
     * @param RequestInterface $requestInterface
     * @param DeltaItemHelper $deltaItemHelper
     */
    public function __construct(
        RequestInterface $requestInterface,
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->requestInterface = $requestInterface;
        $this->deltaItemHelper = $deltaItemHelper;
    }

    public function afterExecute(Save $subject, $result) {
        try {
            $productId = (int)$this->requestInterface->getParam("product", 0);
            if ($productId){
                $this->deltaItemHelper->updateDeltaItemsByIds([$productId], false);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }
}

