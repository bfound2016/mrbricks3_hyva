<?php declare(strict_types = 1);

namespace Addwish\Awext\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class TrackCode
 */
class TrackCode extends Template {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    /**
     * @var string
     */
    protected $_template = "Addwish_Awext::track-code.phtml";

    /**
     * Track constructor.
     *
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;

        parent::__construct($context, $data);
    }

    /**
     * Checking if module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled(): bool {
        return $this->configHelper->isModuleEnabled();
    }

    /**
     * Checking if cart tracking is enabled
     *
     * @return bool
     */
    public function isCartTrackingEnabled(): bool {
        return $this->configHelper->isCartTrackingEnabled();
    }

    /**
     * Checking if order tracking is enabled
     *
     * @return bool
     */
    public function isConversionTrackingEnabled(): bool {
        return $this->configHelper->isConversionTrackingEnabled();
    }

    /**
     * Gets script settings
     *
     * @return string
     */
    public function getScriptSettings(): string {
        return $this->configHelper->getScriptSettings();
    }

    /**
     * Check if current requested URL is secure
     * currently not in use, but might be useful again later
     * @return bool
     */
    public function isCurrentlySecure(): bool {
        return $this->_storeManager->getStore()->isCurrentlySecure();
    }

    /**
     * Return Addwish script url
     *
     * @return string
     */
    public function getAwAddGiftUrl(): string {
        $url = "https://d1pna5l3xsntoj.cloudfront.net/scripts/company/awAddGift.js#" . $this->getScriptSettings();
        return $url;
    }


    /**
     * Get jquery setting
     *
     * @return string
     */
    public function isJQueryEnabled(): bool {
        return $this->configHelper->isJQueryEnabled();
    }
}
