<?php
namespace BFD_HyvaOverrides\CurrentProductOverride\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CurrentProduct extends \Hyva\Theme\ViewModel\CurrentProduct
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
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