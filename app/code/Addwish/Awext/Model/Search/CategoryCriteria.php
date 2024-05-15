<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Search;

use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\Store;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class CategoryCriteria
 */
class CategoryCriteria {
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
     * CategoryCriteria constructor.
     *
     * @param FilterBuilder         $filterBuilder
     * @param FilterGroupBuilder    $filterGroupBuilder
     * @param SortOrderBuilder      $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigHelper          $configHelper
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
     * @return SearchCriteria
     */
    public function getSearchCriteria(int $currentPage = null, int $pageSize = null): SearchCriteria {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /* 
        Pagination is currently not in use as the CategoryListInterface's getList() 
        doesnt seem the respect the pagination params in the searchcriteria.
        if ($pageSize) {
            $searchCriteria->setPageSize($pageSize);
        }
        if ($currentPage) {
            $searchCriteria->setCurrentPage($currentPage);
        }
        */

        $categoryLevelFilterGroup = $this->filterGroupBuilder->setFilters(
                [
                    $this->filterBuilder
                        ->setField(Category::KEY_LEVEL)
                        ->setValue(2)
                        ->setConditionType("gteq")
                        ->create()
                ]
        )->create();

        $searchCriteria->setFilterGroups(
                [
                    $categoryLevelFilterGroup,
                ]
        );

        return $searchCriteria;
    }
}
