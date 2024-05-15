<?php declare(strict_types=1);

namespace Addwish\Awext\Service;

use Magento\Framework\App\Request\Http as HttpRequest;
use Addwish\Awext\Model\Feeds\ProductFeedFactory;
use Addwish\Awext\Model\Feeds\OrderFeedFactory;
use Addwish\Awext\Model\Feeds\CategoryFeedFactory;
use Addwish\Awext\Model\Info\Info as InfoModel;
use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Service\ManagementInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * Class AwextManagement
 */
class AwextManagement implements ManagementInterface {
    /**
     * @var ProductFeedFactory
     */
    protected $productFeed;
    /**
     * @var OrderFeedFactory
     */
    protected $orderFeed;
     /**
     * @var CategoryFeedFactory
     */
    protected $categoryFeed;
    /**
     * @var HttpRequest
     */
    protected $httpRequest;
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    /**
     * @var InfoModel
     */
    protected $infoModel;
    /**
     * @var Request
     */
    private $request;

    /**
     * AwextManagement constructor.
     *
     * @param ProductFeedFactory $productFeed
     * @param OrderFeedFactory $orderFeed
     * @param CategoryFeedFactory $categoryFeed
     * @param HttpRequest $httpRequest
     * @param ConfigHelper $configHelper
     * @param InfoModel $infoModel
     * @param Request $request
     */
    public function __construct(
        ProductFeedFactory $productFeed,
        OrderFeedFactory $orderFeed,
        CategoryFeedFactory $categoryFeed,
        HttpRequest $httpRequest,
        ConfigHelper $configHelper,
        InfoModel $infoModel,
        Request $request
    ) {
        $this->productFeed = $productFeed;
        $this->orderFeed = $orderFeed;
        $this->categoryFeed = $categoryFeed;
        $this->httpRequest = $httpRequest;
        $this->configHelper = $configHelper;
        $this->infoModel = $infoModel;
        $this->request = $request;
    }

    /**
     * get client requester ip
     *
     * @return string
     */
    private function getClientIp() {
        $ips = [];
        foreach (array(
                "HTTP_CLIENT_IP",
                "HTTP_X_FORWARDED_FOR",
                "HTTP_X_FORWARDED",
                "HTTP_X_CLUSTER_CLIENT_IP",
                "HTTP_FORWARDED_FOR",
                "HTTP_FORWARDED",
                "REMOTE_ADDR") as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(",", $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var(
                        $ip,
                        FILTER_VALIDATE_IP,
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                    ) !== false) {
                        $ips[] = $ip;
                    }
                }
            }
        }
        if (!empty($ips)) {
            return implode(",", array_unique($ips));
        }
        return $this->request->getClientIp();
    }

    /**
     * Check if ip is whitelisted
     *
     * @return mixed[]
     */
    private function isIpWhitelisted() {
        $ipWhitelist   = $this->configHelper->getWhitelistIPAddresses();
        $remoteAddresses = $this->getClientIp();
        if (empty(implode($ipWhitelist))) {
            return array("result" => true, "ip" => $remoteAddresses);
        }
        $remoteAddresses = explode(",", $remoteAddresses);
        foreach ($remoteAddresses as $remoteAddress) {
            $remoteAddress = trim($remoteAddress);
            $remoteAddress = explode(":", $remoteAddress)[0];
            if (in_array($remoteAddress, $ipWhitelist, true)) {
                return array("result" => true, "ip" => implode(", ", $remoteAddresses));
            }
        }
        return array("result" => false, "ip" => implode(", ", $remoteAddresses));
    }

    /**
     * Gets product feed
     *
     * @return mixed[]
     */
    public function getProductFeed(): array {
        $isModuleEnabled = $this->configHelper->isModuleEnabled();
        if (!$isModuleEnabled) {
            return ["The Hello Retail Module is disabled"];
        }
        $isIpWhitelisted = $this->isIpWhitelisted();
        if (!$isIpWhitelisted["result"]) {
            return [sprintf("Access denied from %s", $isIpWhitelisted["ip"])];
        }

        $response = [];
        $currentPage = (int) $this->httpRequest->getParam("page");
        $pageSize = (int) $this->httpRequest->getParam("pageSize");
        $includeOrphans = (bool) $this->httpRequest->getParam("includeOrphans");
        $includeSwatches = (bool) $this->httpRequest->getParam("includeSwatches");
        $includeInactive = (bool) $this->httpRequest->getParam("includeInactive");
        // $includeInactive currently only affects delta feeds.
        $deltaToken = (int) $this->httpRequest->getParam("deltaToken");
        $hasExtraAttributes = (bool) $this->httpRequest->getParam("extraAttributes");

        $isEnabled = $this->configHelper->isProductFeedEnabled();
        $includeRelatedProducts = $this->configHelper->isRelatedProductsInFeedEnabled();

        $extraAttributes = [];
        if ($hasExtraAttributes){
            $extraAttributes = explode(",", (string) $this->httpRequest->getParam("extraAttributes"));
        }
        if ($currentPage) {
            //our feedType "magento 2, paginated" starts from 0 so we have to increment by 1.
            //old setups prevents us from just changing that.
            $currentPage ++;
        }
        if ($isEnabled) {
            $productFeet = $this->productFeed
                ->create()
                ->execute(
                    $currentPage,
                    $pageSize,
                    $includeOrphans,
                    $includeSwatches,
                    $includeInactive,
                    $includeRelatedProducts,
                    $extraAttributes,
                    $deltaToken
                );

            if ($currentPage <= $productFeet->getLastPageNumber() + 1) {
                $response = $productFeet->getFeed();
            }
        }

        return $response;
    }

    /**
     * Gets order feed
     *
     * @return mixed[]
     */
    public function getOrderFeed(): array {
        $isModuleEnabled = $this->configHelper->isModuleEnabled();
        if (!$isModuleEnabled) {
            return ["The Hello Retail Module is disabled"];
        }
        $isIpWhitelisted = $this->isIpWhitelisted();
        if (!$isIpWhitelisted["result"]) {
            return [sprintf("Access denied from %s", $isIpWhitelisted["ip"])];
        }

        $response = [];
        $daysBack = (string) $this->httpRequest->getParam("daysBack");
        $currentPage = (int) $this->httpRequest->getParam("page");
        $pageSize = (int) $this->httpRequest->getParam("pageSize");
        $isEnabled = $this->configHelper->isOrderFeedEnabled();
        if ($isEnabled) {
            $orderFeed = $this->orderFeed
                ->create()
                ->execute($daysBack, $currentPage, $pageSize);

            if ($currentPage <= $orderFeed->getLastPageNumber()) {
                $response = $orderFeed->getFeed();
            }
        }
        return $response;
    }

     /**
     * Gets Category feed
     *
     * @return mixed[]
     */
    public function getCategoryFeed(): array {
        $isModuleEnabled = $this->configHelper->isModuleEnabled();
        if (!$isModuleEnabled) {
            return ["The Hello Retail Module is disabled"];
        }
        $isIpWhitelisted = $this->isIpWhitelisted();
        if (!$isIpWhitelisted["result"]) {
            return [sprintf("Access denied from %s", $isIpWhitelisted["ip"])];
        }

        $response = [];
        $currentPage = (int) $this->httpRequest->getParam("page");
        $pageSize = (int) $this->httpRequest->getParam("pageSize");
        $isEnabled = $this->configHelper->isCategoryFeedEnabled();
        if ($isEnabled) {
            $categoryFeed = $this->categoryFeed
                ->create()
                ->execute($currentPage, $pageSize);

            /* 
            Pagination is currently not in use as the CategoryListInterface"s getList()
            doesnt seem the respect the pagination params in the searchcriteria.
            if ($currentPage <= $categoryFeed->getLastPageNumber()) {
                $response = $categoryFeed->getFeed();
            }

            $response = $categoryFeed->getFeed();
            */
            $response = $categoryFeed->getFeedData();
        }

        return $response;
    }

    /**
     * Gets module info
     *
     * @return mixed[]
     */
    public function getModuleInfo(): array {
        return $this->infoModel->getModuleInfo($this->isIpWhitelisted());
    }
}