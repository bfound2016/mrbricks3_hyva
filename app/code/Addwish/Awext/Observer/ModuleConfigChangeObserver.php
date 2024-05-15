<?php
namespace Addwish\Awext\Observer;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ModuleConfigChangeObserver implements ObserverInterface {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    public function execute(Observer $observer) {
        try {
            // This is used to make sure our cache busting can only be enabled
            // if both our module, pages and pages backend rendering is enabled.
            // if any of those are not, then we force cache busting off as well.

            // this obeserver event doesnt hold any information besides the store and the website.
            // if we ever need info on the config fields posted we need to change this to a plugin
            // possibly an afterSave for \Magento\Config\Model\Config
            if (!$this->configHelper->isModuleEnabled() 
                    || !$this->configHelper->isPagesEnabled()
                    || !$this->configHelper->isPagesBackendRenderingEnabled() ) {
                $this->configHelper->disableFullPageCacheBuster();
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }
}