<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Search;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Catalog\Model\Product\VisibilityFactory;
use Magento\Catalog\Model\Product\Attribute\Source\StatusFactory;
use Magento\Store\Model\Store;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class ProductCriteria
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductCriteria {
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var VisibilityFactory
     */
    protected $visibilityFactory;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * ProductCriteria constructor.
     *
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param VisibilityFactory $visibilityFactory
     * @param StatusFactory $statusFactory
     * @param ConfigHelper $configHelper
     */ 
    public function __construct(
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        VisibilityFactory $visibilityFactory,
        StatusFactory $statusFactory,
        ConfigHelper $configHelper
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visibilityFactory = $visibilityFactory;
        $this->statusFactory = $statusFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * Gets search criteria
     *
     * @param int|null $currentPage
     * @param int|null $pageSize
     * @param bool $includeInactive
     * @return SearchCriteria
     */
    public function getSearchCriteria(
        int $currentPage = null,
        int $pageSize = null,
        bool $setVisibilityAndStatusFilter = true
    ): SearchCriteria {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $filterGroups = [];
        if ($currentPage) {
            $searchCriteria->setCurrentPage($currentPage);
        }
        if ($pageSize) {
            $searchCriteria->setPageSize($pageSize);
        }

        // NOTE: We used to handle delta feed filtering on updated_at in here.
        // this is now done directly in our custom repository ProductWithDeltaRepository.
        // This is because the default collectionProcossor doesnt accept filtering on joined fields.
        // (collectionProcossor is a helper used to add filters and sorting to a collection query).

        // Note about filters:
        // When miltiple filters are added to the same group they will be applied by OR statement.
        // Each group added to the searchCriteria are applied by AND statement.
        $storeFilterGroup = $this->filterGroupBuilder->setFilters(
                [
                    $this->filterBuilder
                        ->setField(Store::STORE_ID)
                        ->setConditionType("eq")
                        ->setValue($this->configHelper->getStoreId())
                        ->create()
                ]
        )->create();
        $filterGroups[] = $storeFilterGroup;
        // set the visibility and status filter unless setVisibilityAndStatusFilter is true.
        if ($setVisibilityAndStatusFilter) {
            $statusModel = $this->statusFactory->create();
            $statusFilterGroup = $this->filterGroupBuilder->setFilters(
                    [
                        $this->filterBuilder
                            ->setField(ProductInterface::STATUS)
                            ->setConditionType("in")
                            ->setValue($statusModel->getVisibleStatusIds())
                            ->create()
                    ]
            )->create();
            $filterGroups[] = $statusFilterGroup;

            $visibilityModel = $this->visibilityFactory->create();
            $visibilityFilterGroup = $this->filterGroupBuilder->setFilters(
                    [
                        $this->filterBuilder
                            ->setField(ProductInterface::VISIBILITY)
                            ->setConditionType("in")
                            ->setValue($visibilityModel->getVisibleInSiteIds())
                            ->create()
                    ]
            )->create();
            $filterGroups[] = $visibilityFilterGroup;

        }
        $searchCriteria->setFilterGroups($filterGroups);

        $sortOrder = $this->sortOrderBuilder->setField("entity_id")->setDirection("ASC")->create();
        $searchCriteria->setSortOrders([$sortOrder]);

        return $searchCriteria;
    }
}
