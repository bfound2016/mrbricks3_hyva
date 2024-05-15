<?php declare(strict_types=1);

namespace Addwish\Awext\Model\Feeds;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Data\Collection;
use Addwish\Awext\Helper\Config as ConfigHelper;

/**
 * Class AbstractFeed
 */
abstract class AbstractFeed {
    /**
     * Default index names
     */
    const LAST_PAGE_NUMBER_NODE_INDEX  = "last_page_number";
    const EXTENSION_VERSION_NODE_INDEX = "extension_version";
    const DELTA_TOKEN = "deltaToken";

    /**
     * @var array
     */
    protected $feedArray = [];

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var
     */
    protected $lastPageNumber = 0;

    /**
     * @var
     */
    protected $rootNodeName;

    /**
     * AbstractFeed constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        $rootNodeName
    ) {
        $this->configHelper = $configHelper;
        $this->rootNodeName = $rootNodeName;
    }

    /**
     * Gets feed response
     *
     * @return array
     */
    public function getFeed(): array {
        $this->setFeedNode(self::LAST_PAGE_NUMBER_NODE_INDEX, (string) $this->getLastPageNumber());
        $this->setFeedNode(self::EXTENSION_VERSION_NODE_INDEX, $this->getModuleVersion());
        $this->setFeedNode(self::DELTA_TOKEN, (string) time());

        return $this->feedArray;
    }

    /**
     * Gets feed data
     *
     * @return array
     */
    public function getFeedData(): array {
        return $this->feedArray;
    }

    /**
     * Sets feed node to feedArray
     *
     * @param string $key
     * @param string $node
     *
     * @return $this
     */
    public function setFeedNode(string $key, string $node): self {
        if ($key) {
            $this->feedArray[0][$key] = $node;
        }

        return $this;
    }

    /**
     * Sets feed node to feedArray
     *
     * @param string $key
     * @param array  $node
     *
     * @return $this
     */
    public function setFeedNodeArray(string $key, array $node): self {
        if ($key) {
            $this->feedArray[0][$key] = $node;
        }

        return $this;
    }

    /**
     * Gets feed node by key
     *
     * @param string $key
     *
     * @return string
     */
    public function getFeedNode(string $key): string {
        if (array_key_exists($key, $this->feedArray[0][$key])) {
            return $this->feedArray[0][$key];
        }

        return "";
    }

    /**
     * Sets feed data to root node
     *
     * @param array $feedData
     *
     * @return $this
     */
    public function populateFeedData(array $feedData): self {
        $this->setFeedNodeArray($this->rootNodeName, $feedData);

        return $this;
    }

    /**
     * Sets Last Page Number
     *
     * @param SearchResultsInterface $searchResult
     *
     * @return $this
     */
    public function setLastPageNumber(SearchResultsInterface $searchResult): self {
        if ($searchResult instanceof Collection) {
            $this->lastPageNumber = $searchResult->getLastPageNumber();
        } else {
            $pageSize = $searchResult->getSearchCriteria()->getPageSize();
            $totalCount = $searchResult->getTotalCount();

            if ($pageSize) {
                $this->lastPageNumber = ceil(($totalCount / $pageSize));
            }
        }

        return $this;
    }

    /**
     * Gets calculated last page number
     *
     * @return int
     */
    public function getLastPageNumber(): int {
        return (int) $this->lastPageNumber;
    }

    /**
     * Gets module version
     *
     * @return string
     */
    public function getModuleVersion(): string {
        return $this->configHelper->getModuleVersion();
    }

}
