<?php declare(strict_types=1);
namespace Addwish\Awext\Helper;

use DateTime;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;


/**
 * Class Config
 */
class Config extends AbstractHelper {
    /**
     * Default enabled value
     */
    const DEFAULT_ENABLED_VALUE = "1";

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

     /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var TypeListInterface
     */
    protected $cacheHelper;

    /**
     * Module config
     */
    // reusing old field addwish_id on purpose here.
    const CONFIG_SCRIPT_SETTINGS = "addwish_configuration/general/addwish_id";
    const CONFIG_IS_MODULE_ENABLED = "addwish_configuration/general/is_active_module";
    const CONFIG_IS_JQUERY_ENABLED = "addwish_configuration/general/is_active_jquery";

    const CONFIG_INDEX_LAST_UPDATED = "addwish_configuration/general/index_is_updated";

    const CONFIG_IS_PAGES_ENABLED = "addwish_configuration/pages/is_active_pages";
    const CONFIG_IS_PAGES_BACKEND_RENDERING_ENABLED = "addwish_configuration/pages/is_active_backend_rendering";
    const CONFIG_IS_FPC_BUSTING_ENABLED = "addwish_configuration/pages/is_active_fpc_cache_buster";
    const CONFIG_DEFAULT_PAGES_CATEGORY_SETTING = "addwish_configuration/pages/default_pages_category_setting";
    const CONFIG_DEFAULT_PAGES_KEY = "addwish_configuration/pages/default_pages_key";
    const CONFIG_ELEMENTS_TO_HIDE = "addwish_configuration/pages/elements_to_hide";

    const CONFIG_IS_PRODUCT_FEED_ENABLED = "addwish_configuration/feed/is_active_product";
    const CONFIG_IS_ORDER_FEED_ENABLED = "addwish_configuration/feed/is_active_order";
    const CONFIG_IS_CATEGORY_FEED_ENABLED = "addwish_configuration/feed/is_active_category";
    const CONFIG_IS_STOCK_ITEM_DELTA_ENABLED = "addwish_configuration/feed/is_active_stock_delta";
    const CONFIG_IS_RELATED_PRODUCTS_IN_FEED_ENABLED = "addwish_configuration/feed/is_active_related_products";
    const CONFIG_IS_FULL_FEED_ON_REINDEX_ENABLED = "addwish_configuration/feed/is_active_index_full";
    const CONFIG_FEED_IP_WHITELIST = "addwish_configuration/feed/ip_whitelist";

    const CONFIG_IS_CART_TRACKING_ENABLED = "addwish_configuration/tracking/is_active_cart_tracking";
    const CONFIG_IS_CONVERSION_TRACKING_ENABLED = "addwish_configuration/tracking/is_active_conversion_tracking";
    const CONFIG_IS_EMAILS_IN_TRACKING_ENABLED = "addwish_configuration/tracking/is_active_include_emails_in_tracking";

    /**
     * Config constructor.
     *
    * @param Context $context
    * @param StoreManagerInterface $storeManager
    * @param ModuleListInterface $moduleList
    * @param TimezoneInterface $timezone
    * @param WriterInterface $configWriter
    * @param TypeListInterface $cacheHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        TimezoneInterface $timezone,
        WriterInterface $configWriter,
        TypeListInterface $cacheHelper
    ) {
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->timezone = $timezone;
        $this->configWriter = $configWriter;
        $this->cacheHelper = $cacheHelper;

        parent::__construct($context);
    }

    /**
     * Gets script settings
     *
     * @return string
     */
    public function getScriptSettings(): string {
        return (string) $this->getConfigValue(self::CONFIG_SCRIPT_SETTINGS);
    }

    /**
     * Checking if hello retail module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_MODULE_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if we can use jQuery
     *
     * @return bool
     */
    public function isJQueryEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_JQUERY_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if hello retail pages is enabled
     *
     * @return bool
     */
    public function isPagesEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_PAGES_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if hello retail pages backend rendering is enabled
     *
     * @return bool
     */
    public function isPagesBackendRenderingEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_PAGES_BACKEND_RENDERING_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    public function isPagesCacheBustingEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_FPC_BUSTING_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    public function disableFullPageCacheBuster() {
        $this->setConfigValue(self::CONFIG_IS_FPC_BUSTING_ENABLED, "0");
        return;
    }

     /**
     * @return string
     */
    public function getDefaultPagesCategorySetting() {
        $value = $this->getConfigValue(self::CONFIG_DEFAULT_PAGES_CATEGORY_SETTING);

        return $value;
    }

     /**
     * @return string
     */
    public function getDefaultPagesKey() {
        $value = $this->getConfigValue(self::CONFIG_DEFAULT_PAGES_KEY);

        return $value;
    }

    /**
     * @return string
     */
    public function getElementsToHideForPages() {
        $value = $this->getConfigValue(self::CONFIG_ELEMENTS_TO_HIDE);

        return $value;
    }

    /**
     * return unix timestamp for last time price index was updated.
     *
     * @return int
     */
    public function getLastIndexUpdateTime() {
        return intval($this->getConfigValue(self::CONFIG_INDEX_LAST_UPDATED));
    }

    public function setLastIndexUpdateTime(int $unix) {
        $this->setConfigValue(self::CONFIG_INDEX_LAST_UPDATED, strval($unix));
        return;
    }

    /**
     * @return array
     */
    public function getWhitelistIPAddresses() {
        $value = $this->getConfigValue(self::CONFIG_FEED_IP_WHITELIST);

        return explode(",", $value);
    }

    /**
     * Checking if product feed is enabled
     *
     * @return bool
     */
    public function isProductFeedEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_PRODUCT_FEED_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if related products should be included in the product feed
     *
     * @return bool
     */
    public function isRelatedProductsInFeedEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_RELATED_PRODUCTS_IN_FEED_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if order feed is enabled
     *
     * @return bool
     */
    public function isOrderFeedEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_ORDER_FEED_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

     /**
     * Checking if category feed is enabled
     *
     * @return bool
     */
    public function isCategoryFeedEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_CATEGORY_FEED_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if cart tracking is enabled
     *
     * @return bool
     */
    public function isCartTrackingEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_CART_TRACKING_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if conversion tracking is enabled
     *
     * @return bool
     */
    public function isConversionTrackingEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_CONVERSION_TRACKING_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if include-emails-in-tracking is enabled
     *
     * @return bool
     */
    public function isEmailsInTrackingEnabled(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_EMAILS_IN_TRACKING_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if is_active_stock_delta is enabled
     * used by stock item afterSave() plugin to determine if delta items should be added.
     *
     * @return bool
     */
    public function isDeltaItemsUpdatedByStockChanges(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_STOCK_ITEM_DELTA_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Checking if is_active_index_full is enabled
     * used to determine if a magento product reindex should force a full feed run.
     *
     * @return bool
     */
    public function isReindexForcingFullFeed(): bool {
        $value = $this->getConfigValue(self::CONFIG_IS_FULL_FEED_ON_REINDEX_ENABLED);
        if ($value == self::DEFAULT_ENABLED_VALUE) {
            return true;
        }

        return false;
    }

    /**
     * Gets store id
     *
     * @return int
     */
    public function getStoreId(): int {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Gets Module Version
     *
     * @return string
     */
    public function getModuleVersion(): string {
        $data = $this->moduleList->getOne($this->_getModuleName());
        if (array_key_exists("setup_version", $data)) {
            return $data["setup_version"];
        }

        return "";
    }

    /**
     * Gets current date
     *
     * @return DateTime
     */
    public function getCurrentDate(): DateTime {
        return $this->timezone->date();
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return string
     */
    public function formatDate(string $date, string $format) {
        return $this->timezone->date($date)->format($format);
    }

    /**
     * Gets config value
     *
     * @param string $path
     *
     * @return string
     */
    protected function getConfigValue(string $path): string {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            null
        );

        return $value ?? "";
    }

     /**
     * Sets config value
     *
     * @param string $path
     *
     */
    protected function setConfigValue(string $path, string $value) {
        $this->configWriter->save(
            $path,
            $value,
            "default",
            0);

        // When we set a new value to the config we must also clean the config cache.
        // Otherwise getConfigValue will not get the new value, but the value from the last time the cache was updated.
        $this->cacheHelper->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }
}
