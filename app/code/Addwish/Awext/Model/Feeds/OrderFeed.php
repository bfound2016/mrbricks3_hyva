<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Feeds;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Addwish\Awext\Model\Search\OrderCriteria;
use Addwish\Awext\Model\Data\Feeds\OrderProvider;
use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Feeds\OrderFeedInterface;


/**
 * Class OrderFeed
 */
class OrderFeed extends AbstractFeed implements OrderFeedInterface {
    /**
     * Default number of days to go back when fetching orders;
     */
    const DEFAULT_DAYSBACK_PARAM = "2";

    /**
     * @var string
     */
    protected $daysBack;

    /**
     * @var string
     */
    protected $dateFrom;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderCriteria
     */
    protected $orderCriteria;

    /**
     * @var OrderProvider
     */
    protected $orderProvider;

    /**
     * OrderFeed constructor.
     * @param ConfigHelper $configHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderProvider $orderProvider
     * @param OrderCriteria $orderCriteria
     */
    public function __construct(
        ConfigHelper $configHelper,
        OrderRepositoryInterface $orderRepository,
        OrderProvider $orderProvider,
        OrderCriteria $orderCriteria
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderProvider = $orderProvider;
        $this->orderCriteria = $orderCriteria;

        parent::__construct(
            $configHelper,
            "orders"
        );
    }

    /**
     * Execute
     *
     * @param string $daysBack
     * @param int|null $currentPage
     * @param int|null $pageSize
     *
     * @return OrderFeedInterface
     */
    public function execute(string $daysBack, int $currentPage, int $pageSize): OrderFeedInterface {
        $this->init($daysBack, $currentPage, $pageSize);

        $orderList = $this->getOrderList();
        $orderFeed = $this->orderProvider->generate($orderList);

        $this->populateFeedData($orderFeed);

        return $this;
    }

    /**
     * Initialize data
     *
     * @param string $daysBack
     * @param int|null $currentPage
     * @param int|null $pageSize
     *
     * @return OrderFeedInterface
     */
    protected function init(string $daysBack, int $currentPage, int $pageSize): OrderFeedInterface {
        $currentDate = $this->configHelper->getCurrentDate();
        $currentDateFormatted = $currentDate->format("Y-m-d H:i:s");        

        if ($daysBack) {
            $this->dateFrom = $currentDate->modify("-" . $daysBack . "days")->format("Y-m-d H:i:s");
        }
        else {
            $this->dateFrom = $currentDate->modify("-" . self::DEFAULT_DAYSBACK_PARAM . "days")->format("Y-m-d H:i:s");
        }

        $this->currentPage = $currentPage ? $currentPage + 1 : $this->currentPage;
        $this->pageSize = $pageSize > 0 ? $pageSize : null;

        return $this;
    }

    /**
     * Gets order list
     *
     * @return OrderInterface[]
     */
    protected function getOrderList(): array {
        $searchCriteria = $this->orderCriteria->getSearchCriteria($this->dateFrom, $this->currentPage, $this->pageSize);
        $searchResults = $this->orderRepository->getList($searchCriteria);

        $this->setLastPageNumber($searchResults);

        return $searchResults->getItems();
    }

     /**
     * @param SearchResultsInterface $searchResult
     *
     * @return OrderFeed
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
