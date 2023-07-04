<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ReviewReminder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReviewReminder\Model\ResourceModel;

/**
 * Class ConnectionDB
 * @package Bss\ReviewReminder\Model\ResourceModel
 */
class ConnectionDB
{
    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * ConnectionDB constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
    }
    /**
     * Update Data
     *
     * @param string $table
     * @param int $reviewId
     * @param array $data
     * @return mixed
     */
    public function updateData($table, $reviewId, $data)
    {
        $condition = 'review_id = ' . $reviewId;
        try {
            return $this->writeAdapter->update($table, $data, $condition);
        } catch (\Exception $e) {
            $this->logger->info('Review Reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Store Id From review_detail Table
     *
     * @param int $reviewId
     * @return string
     */
    public function getStoreId($reviewId)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('review_detail')],
                'store_id'
            )->where('review_id = ?', $reviewId);
        return $this->readAdapter->fetchOne($select);
    }

    /**
     * Get Table Name
     *
     * @param string $entity
     * @return bool|mixed
     */
    public function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Get Review Collection
     *
     * @param int $statusId
     * @param array $storeIs
     * @param array $sendGroup
     * @return array
     */
    public function getReviewCollection()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('review')],
                ['main_table.review_id', 'main_table.entity_pk_value', 'main_table.order_id']
            );
        $select->joinLeft(
            ['joint_table' => $this->getTableName('review_detail')],
            'main_table.review_id = joint_table.review_id',
            ['joint_table.store_id', 'joint_table.customer_id']
        );
        $select->where('main_table.status_id = ?', \Magento\Review\Model\Review::STATUS_APPROVED);
        $select->where('main_table.entity_id = ?', 1);
        $select->where('main_table.review_reminder_send_coupon = ?', 1);
        $select->limit(50);
        return $this->readAdapter->fetchAll($select);
    }

    /**
     * Get Order Id List
     *
     * @param string $customerId
     * @param string $customerEmail
     * @return array
     */
    public function getOrderId($customerId, $customerEmail)
    {
        $select = $this->readAdapter->select()
        ->from(
            ['main_table' => $this->getTableName('bss_reviewreminder_remindlog')],
            ['order_id']
        );
        $select->where('customer_id = ?', $customerId);
        $select->orWhere('order_email = ?', $customerEmail);
        return $this->readAdapter->fetchCol($select);
    }

    /**
     * @param int $productId
     * @param array $remindOrder
     * @return array
     */
    public function checkProductOrder($productId, $remindOrder)
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('sales_order_item')],
                ['order_id']
            )->where('product_id = ?', $productId)
            ->orWhere('product_options like \'%"product_id":"' . $productId . '"' . '%\'')
            ->where('order_id IN (' . implode(",", $remindOrder) . ')');
        return $this->readAdapter->fetchCol($select);
    }

    /**
     * @param int $productId
     * @param int $customerId
     * @param array $remindOrder
     * @param int $storeId
     * @return array
     */
    public function checkSendCouponBefore($productId, $customerId, $remindOrder, $storeId)
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('review')],
                ['review_id']
            )
            ->where('entity_id = ?', 1)
            ->where('main_table.entity_pk_value = ?', $productId)
            ->where('main_table.review_reminder_send_coupon = ?', 2);
        $condition = 'main_table.order_id IN (' . implode(",", $remindOrder) . ')';
        if ($customerId != 0) {
            $select->joinLeft(
                ['joint_table' => $this->getTableName('review_detail')],
                'main_table.review_id = joint_table.review_id',
                []
            );
            $select->where('joint_table.store_id = ?', $storeId);
            $condition .= ' OR joint_table.customer_id = ' . $customerId;
        }
        $select->where($condition);
        return $this->readAdapter->fetchCol($select);
    }

    /**
     * @param string $table
     * @return array
     */
    public function getIssetRemindLog($table)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$table],
                ['order_id']
            );
        return $this->readAdapter->fetchCol($select);
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertMultiple($table, $data)
    {
        try {
            $this->writeAdapter->insertMultiple($table, $data);
        } catch (\Exception $e) {
            $this->logger->info('Review Reminder: ' . $e->getMessage());
        }
    }
}
