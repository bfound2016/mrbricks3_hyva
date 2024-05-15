<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Data\Feeds;

/**
 * Interface OrderProviderInterface
 */
interface OrderProviderInterface extends ProviderInterface {
    /**
     * Default order nodes
     */
    const ORDER_NUMBER_NODE_NAME = "order_number";
    const ORDER_STATE_NODE_NAME = "order_state";
    const ORDER_STATUS_NODE_NAME = "order_status";
    const ORDER_CURRENCY_NODE_NAME = "order_display_currency";
    const ORDER_TOTAL_NODE_NAME = "order_display_total";
    const ORDER_TOTAL_UPDATED_NODE_NAME = "order_display_total_updated";
    const BASE_CURRENCY_NODE_NAME = "currency";
    const BASE_TOTAL_NODE_NAME = "total";
    const BASE_TOTAL_UPDATED_NODE_NAME = "total_updated";
    const DATE_NODE_NAME = "date";
    const DATE_UPDATED_NODE_NAME = "updated";
    const CUSTOMER_EMAIL_NODE_NAME = "email";
    const CUSTOMER_COUNTRY_NODE_NAME = "country";
    const CUSTOMER_GROUP_ID_NODE_NAME = "customer_group_id";
    const PRODUCTS_NODE_NAME = "products";
    const PRODUCT_NUMBER_NODE_NAME = "productnumber";
    const PRODUCT_IS_RETURNED_NODE_NAME = "is_returned";
    const PRODUCT_QTY_NODE_NAME = "qty";
    const PRODUCT_QTY_UPDATED_NODE_NAME = "qty_updated";
    const PRODUCT_CHILDREN_NODE_NAME = "children";
    const CHILD_PRODUCT_NUMBER_NODE_NAME = "child_productnumber";
}
