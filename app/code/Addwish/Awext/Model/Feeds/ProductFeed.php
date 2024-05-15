<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Feeds;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

// original Magento\Catalog\Api\ProductRepositoryInterface;
use Addwish\Awext\Model\Repository\ProductWithDeltaRepository as ProductRepositoryInterface;

use Addwish\Awext\Model\Search\ProductCriteria;
use Addwish\Awext\Model\Data\Feeds\ProductProvider;
use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Feeds\ProductFeedInterface;
use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;

/**
 * Class ProductFeed
 */
class ProductFeed extends AbstractFeed implements ProductFeedInterface {
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductCriteria
     */
    protected $productCriteria;

    /**
     * @var ProductProvider
     */
    protected $productProvider;

    /**
     * @var DeltaItemHelper
     */
    protected $deltaItemHelper;

    /**
     * ProductFeed constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigHelper $configHelper
     * @param ProductCriteria $productCriteria
     * @param ProductProvider $productProvider
     * @param DeltaItemHelper $deltaItemHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigHelper $configHelper,
        ProductCriteria $productCriteria,
        ProductProvider $productProvider,
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productCriteria = $productCriteria;
        $this->deltaItemHelper = $deltaItemHelper;

        parent::__construct(
            $configHelper,
            "products"
        );
    }

    /**
     * Execute
     *
     * @param int|null $currentPage
     * @param int|null $pageSize
     * @param string|null $extraAttributes
     * @param string|null $deltaToken
     *
     * @return ProductFeedInterface
     */
    public function execute(
        int $currentPage = null,
        int $pageSize = null,
        bool $includeOrphans = false,
        bool $includeSwatches = false,
        bool $includeInactive = false,
        bool $includeRelatedProducts = false,
        array $extraAttributes = [],
        int $deltaToken = null
    ): ProductFeedInterface {
        $setVisibilityAndStatusFilter = true;
        $requestedDeltaFeed = boolval($deltaToken);
        if ($requestedDeltaFeed) {
            // a delta feed is requested
            $setVisibilityAndStatusFilter = !$includeInactive;
            $lastIndexUpdateTime = $this->configHelper->getLastIndexUpdateTime();
            $forceFullFeedOnReindex = $this->configHelper->isReindexForcingFullFeed();
            if ($forceFullFeedOnReindex && $lastIndexUpdateTime && $lastIndexUpdateTime >= $deltaToken) {
                // lastIndexUpdateTime indicates the last time a critical index was updated.
                // if this was later then the delta token we need to do a full feed,
                // which can be done by ignoring the delta token
                $deltaToken = null;
            }
        }
        $searchCriteria = $this->productCriteria->getSearchCriteria($currentPage, $pageSize, $setVisibilityAndStatusFilter);
        $productList = $this->getProductList($searchCriteria, $deltaToken);
        if ($currentPage <= $this->getLastPageNumber() + 1) {
            $productData = $this->productProvider->generate(
                    $productList,
                    $extraAttributes,
                    $includeOrphans,
                    $includeSwatches,
                    $includeRelatedProducts,
                    $deltaToken ? true : null);
            $this->populateFeedData($productData);
        }
        if (!$requestedDeltaFeed && ($currentPage == $this->getLastPageNumber() + 1 || $this->getLastPageNumber() == 0)) {
            // this is the last page of a full feed request. use this step to clean a bit in deltaItems table.
            $this->deltaItemHelper->removeOldDeltaItems();
        }

        return $this;
    }

    /**
     * Gets product list
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return array
     */
    protected function getProductList(SearchCriteriaInterface $searchCriteria, $deltaToken): array {
        $searchResults = $this->productRepository->getList($searchCriteria, $deltaToken);

        $this->setLastPageNumber($searchResults);

        return $searchResults->getItems();
    }

    /**
     * @param SearchResultsInterface $searchResult
     *
     * @return ProductFeed
     */
    public function setLastPageNumber(SearchResultsInterface $searchResult): AbstractFeed {
        if ($searchResult->getSearchCriteria()->getPageSize()) {
            $pageSize = $searchResult->getSearchCriteria()->getPageSize();
            $totalCount = $searchResult->getTotalCount();
            $this->lastPageNumber = ceil($totalCount / $pageSize) - 1;
        }

        if ($this->lastPageNumber < 0) {
            $this->lastPageNumber = 0;
        }

        return $this;
    }
}
