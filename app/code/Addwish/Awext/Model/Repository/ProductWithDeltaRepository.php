<?php declare(strict_types=1);

namespace Addwish\Awext\Model\Repository;

use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;

use Magento\Framework\Api\SortOrder;


class ProductWithDeltaRepository {
    /**
     * @var ProductRepository
     */
    private $defaultProductRepository;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * ProductWithDeltaRepository constructor.
     * @param ProductRepository $defaultProductRepository
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionFactory $collectionFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ReadExtensions $readExtensions
     */
    public function __construct(
        ProductRepository $defaultProductRepository,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionFactory $collectionFactory,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        ReadExtensions $readExtensions
    ) {
        $this->defaultProductRepository = $defaultProductRepository;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $this->getCollectionProcessor();
        $this->readExtensions = $readExtensions;
    }

    public function getList(SearchCriteriaInterface $searchCriteria, $deltaToken) {
        if (!boolval($deltaToken)) {
            // This is a full feed - just use default getList()
            return $this->defaultProductRepository->getList($searchCriteria);
        }

        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();

        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $updatedAt = $connection->quoteIdentifier(ProductInterface::UPDATED_AT);
        $productIdField = $connection->quoteIdentifier($collection->getEntity()->getIdFieldName());
        $productTableAlias = $connection->quoteIdentifier($collection::MAIN_TABLE_ALIAS);
        $deltaProductIdField = $connection->quoteIdentifier("product_id");
        $deltaTableUnquoted = $connection->getTableName("hello_retail_delta");
        $deltaTableAliasUnquoted = "delta_item";
        $deltaTableAlias = $connection->quoteIdentifier($deltaTableAliasUnquoted);

        // table params will automatically get quoted when parsed to joinLet.
        // use unquoted version here to avoid double quoting.
        $tableWithAliasToJoin = array($deltaTableAliasUnquoted => $deltaTableUnquoted);
        $fieldsToJoinOn = sprintf(
            "(%s.%s = %s.%s)",
            $productTableAlias,
            $productIdField,
            $deltaTableAlias,
            $deltaProductIdField
        );
        
        // if we need to select anything from the delta table. new name => field
        // $fieldsToSelect = array('delta_updated_at' => 'delta_item.updated_at');
        $fieldsToSelect = array();
        $where = array(
            sprintf(
                "(%s.%s >= %s)",
                $productTableAlias,
                $updatedAt,
                $connection->quote(date("Y-m-d H:i:s", $deltaToken))
            ),
            sprintf(
                "(%s.%s >= %s)",
                $deltaTableAlias,
                $updatedAt,
                $connection->quote($deltaToken)
            )
        );
        $collection->getSelect()
        ->joinLeft(
            $tableWithAliasToJoin,
            $fieldsToJoinOn,
            $fieldsToSelect)
        ->where(implode(' OR ', $where));

        // process will apply the searchCriteria to the collection.
        // this is the default productCollectionProcessor,
        // so it doesnt support filtering on joined fields.
        // that is why we have the where clause manually inserted above.
        $this->collectionProcessor->process($searchCriteria, $collection);

        // to check the actual query: var_dump($collection->getSelect()->__toString());
        $collection->load();
        $collection->addCategoryIds();
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    // we can unfortunately not use dependency injection on ProductCollectionProcessor class
    // as it is a virtualType that doesnt actually exist. its only defined in di.xml
    // it is the same in default productRepository
    private function getCollectionProcessor() {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                // phpstan:ignore "Class Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor not found."
                \Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }

}