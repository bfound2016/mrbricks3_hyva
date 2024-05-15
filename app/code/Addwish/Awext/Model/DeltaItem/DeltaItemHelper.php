<?php

namespace Addwish\Awext\Model\DeltaItem;

use InvalidArgumentException;

use Psr\Log\LoggerInterface;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;

use Addwish\Awext\Model\ResourceModel\DeltaItem\CollectionFactory as DeltaItemCollectionFactory;


class DeltaItemHelper extends AbstractHelper {
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ResourceConnection
    */
    protected $resourceConnection;
    
    /**
     * @var DeltaItemCollectionFactory
    */
    protected $deltaItemCollectionFactory;

    /**
     * @var Type
     */
    protected $productTypeHelper;

    /**
     * @var AbstractType[]
     */
    protected $preparedCompositeTypes;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param ResourceConnection $resourceConnection
     * @param DeltaItemCollectionFactory $deltaItemCollectionFactory
     * @param Type $productTypeHelper 
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        ResourceConnection $resourceConnection,
        DeltaItemCollectionFactory $deltaItemCollectionFactory,
        Type $productTypeHelper,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->deltaItemCollectionFactory = $deltaItemCollectionFactory;
        $this->productTypeHelper = $productTypeHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public const HR_DELTA_TABLE_NAME = "hello_retail_delta";
    public const HR_DELTA_FIELD_PRODUCT_ID = "product_id";
    public const HR_DELTA_FIELD_UPDATED_AT = "updated_at";

    // helper to fetch deltaItems directly. currently not used
    public function getDeltaItemsUpdatedAfter(string $deltaToken) {
        $deltaItemCollection = [];
        try {
            $deltaItemCollection = $this->deltaItemCollectionFactory->create()
            ->addFieldToSelect(self::HR_DELTA_FIELD_PRODUCT_ID)
            ->addFieldToFilter(self::HR_DELTA_FIELD_UPDATED_AT, ["gteq" => $deltaToken])
            ->load()
            ->getItems();
        } catch (\Exception|\Throwable $e) {
            // do nothing.
        }

        return $deltaItemCollection;
    }

    // update deltaItems
    public function updateDeltaItems(array $products, bool $updateParents = true) {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }
        $this->updateDeltaItemsByIds($productIds, $updateParents);
    }

    public function updateDeltaItemsByIds(array $productIds, bool $updateParents = true) {
        if ($updateParents) {
            $productIds = array_merge($productIds, $this->getCompositeParentProductIds($productIds));
        }
        $this->markDeltaItemsUpdated($productIds);
    }

    protected function markDeltaItemsUpdated(array $productIds) {
        $deltaItemsTable = $this->resourceConnection->getTableName(self::HR_DELTA_TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        if ($connection->isTableExists($deltaItemsTable)) {
            $timeAsUnix = time();
            $bulkData = [];
            foreach (array_unique($productIds) as $productId) {
                $bulkData[] = array(
                    self::HR_DELTA_FIELD_PRODUCT_ID => $productId,
                    self::HR_DELTA_FIELD_UPDATED_AT => $timeAsUnix
                );
            }
            $updateOnDuplicate = array(
                self::HR_DELTA_FIELD_UPDATED_AT => $timeAsUnix
            );

            $connection->insertOnDuplicate($deltaItemsTable, $bulkData, $updateOnDuplicate);
        }
    }

    // remove deltaItems
    // this will remove everything older than 24 hours. 
    // we save the last 24 hours as a backup in case the full feed fails in our feed reader.
    // if that happens then the next delta feed will still be able to pick up all updates since last delta run.
    public function removeOldDeltaItems() {
        $deltaItemsTable = $this->resourceConnection->getTableName(self::HR_DELTA_TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        if ($connection->isTableExists($deltaItemsTable)) {
            $oneDayAgoAsUnix = time() - 86400;
            $where = array(
                sprintf(
                    "(%s < %s)",
                    self::HR_DELTA_FIELD_UPDATED_AT,
                    $connection->quote($oneDayAgoAsUnix)
                )
            );
            $connection->delete($deltaItemsTable, $where);
            // we can use truncate if we decide to just remove everything on each full.
            // $connection->truncateTable($deltaItemsTable);
        }

        return;
    }

    // helpers 
    // - consider moving getCompositeParentProductIds and getCompositeProductTypeInstances into a general purpose helper.
    // TODO: implement logging in every try catch used in our plugins and event observers
    public function logErrorMessage(string $message) {
        $this->logger->log(500, "Hello Retail Delta: " . $message);
    }

    public function getCompositeParentProductIds(array $productIds) {
        $compositeParentProductIds = [];
        foreach ($this->getCompositeProductTypeInstances() as $compositeTypeInstance) {
            $compositeParentProductIds = array_merge(
                    $compositeParentProductIds,
                    $compositeTypeInstance->getParentIdsByChild($productIds));
        }

        return $compositeParentProductIds;
    }

    protected function getCompositeProductTypeInstances() {
        if ($this->preparedCompositeTypes === null) {
            $typeIdContainer = $this->dataObjectFactory->create();
            foreach ($this->productTypeHelper->getCompositeTypes() as $typeId) {
                $typeIdContainer->setTypeId($typeId);
                // factory() takes a product type instance, however it only uses the typeId value from it.
                // so we can just create a simple dataObject to pass the typeId.
                $this->preparedCompositeTypes[] = $this->productTypeHelper->factory($typeIdContainer);
            }
        }

        return $this->preparedCompositeTypes;
    }

}
