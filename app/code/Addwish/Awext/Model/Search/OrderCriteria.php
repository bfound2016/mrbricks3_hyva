<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Search;

use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class OrderCriteria
 */
class OrderCriteria {
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
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * OrderCriteria constructor.
     *
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigHelper $configHelper
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configHelper = $configHelper;
    }

    /**
     * Gets search criteria
     *
     * @param string $dateFrom
     *
     * @return SearchCriteria
     */
    public function getSearchCriteria(string $dateFrom, int $currentPage = null, int $pageSize = null): SearchCriteria {
        // If the orderfeed runs frequently
        // there is a good chance that we pick up the order between it gettin created and it being cancelled.
        // but we will still avoid some, which is better than nothing.
        $statusFilterGroup = $this->filterGroupBuilder->setFilters(
                [
                    $this->filterBuilder
                        ->setField(OrderInterface::STATUS)
                        ->setConditionType("neq")
                        ->setValue(Order::STATE_CANCELED)
                        ->create()
                ]
        )->create();

        $storeFilterGroup = $this->filterGroupBuilder->setFilters(
                [
                    $this->filterBuilder
                        ->setField(OrderInterface::STORE_ID)
                        ->setValue($this->configHelper->getStoreId())
                        ->setConditionType("eq")
                        ->create()
                ]
        )->create();

        # Eventually we want to change this from using created_at to using updated_at
        # this would be when we are able to handle returns and updated orders properly.
        $dateFromFilterGroup = $this->filterGroupBuilder->setFilters(
                [
                    $this->filterBuilder
                        ->setField(OrderInterface::CREATED_AT)
                        ->setConditionType("from")
                        ->setValue($dateFrom)
                        ->create()
                ]
        )->create();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setSortOrders(
                [
                    $this->sortOrderBuilder
                        ->setField(OrderInterface::CREATED_AT)
                        ->setDescendingDirection()
                        ->create()
                ]
        );

        if ($currentPage) {
            $searchCriteria->setCurrentPage($currentPage);
        }

        if ($pageSize) {
            $searchCriteria->setPageSize($pageSize);
        }


        $searchCriteria->setFilterGroups(
                [
                    $statusFilterGroup,
                    $storeFilterGroup,
                    $dateFromFilterGroup,
                ]
        );

        return $searchCriteria;
    }
}
