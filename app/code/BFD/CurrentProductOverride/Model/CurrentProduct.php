<?php
namespace BFD\CurrentProductOverride\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;

class CurrentProduct extends \Hyva\Theme\ViewModel\CurrentProduct
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductInterfaceFactory $productFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductInterfaceFactory $productFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        parent::__construct($productFactory);
    }

    /**
     * Get cutoff time from TIG PostNL module
     *
     * @return string
     */
    public function getCutoffTime()
    {
        // The path where cutoff time is stored in the store configuration
        $path = 'tig_option_postnl/general/cutoff_time';

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}