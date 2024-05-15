<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Feeds;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Addwish\Awext\Model\Search\CategoryCriteria;
use Addwish\Awext\Model\Data\Feeds\CategoryProvider;
use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Feeds\CategoryFeedInterface;

/**
 * Class CategoryFeed
 */
class CategoryFeed extends AbstractFeed implements CategoryFeedInterface {
     /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $pageSize = 20;

    /**
     * @var CategoryListInterface
     */
    protected $categoryRepository;

    /**
     * @var CategoryCriteria
     */
    protected $categoryCriteria;

    /**
     * @var CategoryProvider
     */
    protected $categoryProvider;

    /**
     * Categoryfeed constructor.
     * @param ConfigHelper $configHelper
     * @param CategoryListInterface $categoryRepository
     * @param CategoryProvider $categoryProvider
     * @param CategoryCriteria $categoryCriteria
     */
    public function __construct(
        ConfigHelper $configHelper,
        CategoryListInterface $categoryRepository,
        CategoryProvider $categoryProvider,
        CategoryCriteria $categoryCriteria
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryProvider = $categoryProvider;
        $this->categoryCriteria = $categoryCriteria;

        parent::__construct(
            $configHelper,
            "categories"
        );
    }

    /**
     * Execute
     *
     * @return CategoryFeedInterface
     */
    public function execute(int $currentPage, int $pageSize): CategoryFeedInterface {
        /* Pagination is currently not in use as the CategoryListInterface's getList() doesnt seem the respect the pagination params in the searchcriteria.
        $this->init($currentPage, $pageSize);
        */
        $categoryList = $this->getCategoryList();
        $categoryFeed = $this->categoryProvider->generate($categoryList);

        $this->populateFeedData($categoryFeed);

        return $this;
    }

    /**
     * Initialize data
     *
     * @return CategoryFeedInterface
     */
    protected function init(int $currentPage, int $pageSize): CategoryFeedInterface {
        $this->currentPage = $currentPage ? $currentPage + 1 : $this->currentPage;
        $this->pageSize = $pageSize > 0 ? $pageSize : null;

        return $this;
    }

    /**
     * Gets category list
     *
     * @return CategoryInterface[]
     */
    protected function getCategoryList(): array {
        /* Pagination is currently not in use as the CategoryListInterface's getList() doesnt seem the respect the pagination params in the searchcriteria.
        $searchCriteria = $this->categoryCriteria->getSearchCriteria($this->currentPage, $this->pageSize);
        */
        $searchCriteria = $this->categoryCriteria->getSearchCriteria();
        $searchResults = $this->categoryRepository->getList($searchCriteria);

        /* no need to set last Pagenumber when not using pagination
        $this->setLastPageNumber($searchResults);
        */
        return $searchResults->getItems();
    }

     /**
     * @param SearchResultsInterface $searchResult
     *
     * @return CategoryFeed
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
