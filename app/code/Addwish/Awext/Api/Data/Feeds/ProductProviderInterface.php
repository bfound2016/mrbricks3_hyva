<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Data\Feeds;

/**
 * Interface ProductProviderInterface
 */
interface ProductProviderInterface extends ProviderInterface {
    /**
     * Default product nodes
     */
    const TITLE_XML_NODE_NAME = "title";
    const URL_XML_NODE_NAME = "url";
    const PRODUCT_NUMBER_XML_NODE_NAME = "productnumber";
    const VARIANT_PRODUCT_NUMBERS_XML_NODE_NAME = "variantproductnumbers";
    const RELATED_PRODUCT_NUMBERS_XML_NODE_NAME = "relatedproductnumbers";
    const CROSSELL_PRODUCT_NUMBERS_XML_NODE_NAME = "crossellproductnumbers";
    const UPSELL_PRODUCT_NUMBERS_XML_NODE_NAME = "upsellproductnumbers";
    const VISIBILITY_XML_NODE_NAME = "visibility";
    const ID_XML_NODE_NAME = "id";
    const PRICE_XML_NODE_NAME = "price";
    const TIER_PRICE_XML_NODE_NAME = "tier_prices";
    const BASE_PRICE_XML_NODE_NAME = "baseprice";
    const PREVIOUS_PRICE_XML_NODE_NAME = "previousprice";
    const PREVIOUS_BASE_PRICE_XML_NODE_NAME = "previousbaseprice";
    const CHEAPEST_VARIANT_PRICE_XML_NODE_NAME = "cheapestvariantprice";
    const MOST_EXPENSIVE_VARIANT_PRICE_XML_NODE_NAME = "mostexpensivevariantprice";
    const IN_STOCK_XML_NODE_NAME = "instock";
    const HAS_VARIANT_IN_STOCK_XML_NODE_NAME = "hasvariantinstock";
    const QTY_XML_NODE_NAME = "qty";
    const MIN_BUY_QTY_XML_NODE_NAME = "minBuyQty";
    const DESCRIPTION_XML_NODE_NAME = "description";
    const RICH_DESCRIPTION_XML_NODE_NAME = "richdescription";
    const IMG_URL_XML_NODE_NAME = "imgurl";
    const BASE_IMG_URL_XML_NODE_NAME = "baseimgurl";
    const KEYWORDS_XML_NODE_NAME = "keywords";
    const TYPE_XML_NODE_NAME = "type";
    const HIERARCHIES_XML_NODE_NAME = "hierarchies";
    const CATEGORY_IDS_XML_NODE_NAME = "categoryids";
    const CATEGORY_IDS_NO_PARENTS_XML_NODE_NAME = "categoryidswithoutparents";
    const EXTRAATTRIBUTES_XML_NODE_NAME = "extraattributes";
    const VARIANT_EXTRAATTRIBUTES_XML_NODE_NAME = "variantextraattributes";
    const CURRENCY_PRICES_XML_NODE_NAME = "currency_prices";
    const CURRENCY_CODE_XML_NODE_NAME = "currency_code";
    const SWATCHES_XML_NODE_NAME = "swatchesconfigs";
    const SWATCHES_CONF_XML_NODE_NAME = "jsonconfig";
    const SWATCHES_SWATCHCONF_XML_NODE_NAME = "jsonswatchconfig";
    const SWATCHES_NUMTOSHOW_XML_NODE_NAME = "numbertoshow";
    const MINIMAL_BUNDLE_PRICE_XML_NODE_NAME = "minimalbundleprice";
    const MINIMAL_BUNDLE_BASE_PRICE_XML_NODE_NAME = "minimalbundlebaseprice";
    const MINIMAL_BUNDLE_REGULAR_PRICE_XML_NODE_NAME = "minimalbundleregularprice";
    const MINIMAL_BUNDLE_REGULAR_BASE_PRICE_XML_NODE_NAME = "minimalbundleregularbaseprice";
    const MAXIMAL_BUNDLE_PRICE_XML_NODE_NAME = "maximalbundleprice";
    const MAXIMAL_BUNDLE_BASE_PRICE_XML_NODE_NAME = "maximalbundlebaseprice";
    const MAXIMAL_BUNDLE_REGULAR_PRICE_XML_NODE_NAME = "maximalbundleregularprice";
    const MAXIMAL_BUNDLE_REGULAR_BASE_PRICE_XML_NODE_NAME = "maximalbundleregularbaseprice";

    /**
     * Default product attributes
     */
    const ENTITY_ID_ATTRIBUTE_NAME = "entity_id";
    const NAME_ATTRIBUTE_NAME = "name";
    const PATH_ATTRIBUTE_NAME = "path";
    const EAN_ATTRIBUTE_NAME = "ean";
    const BRAND_ATTRIBUTE_NAME = "manufacturer";
}
