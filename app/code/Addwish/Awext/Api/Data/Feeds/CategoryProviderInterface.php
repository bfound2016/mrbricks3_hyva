<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Data\Feeds;

/**
 * Interface CategoryProviderInterface
 */
interface CategoryProviderInterface extends ProviderInterface {
    /**
     * Default category nodes
     */
    const CATEGORY_ID = "id";
    const CATEGORY_IS_VISIBLE = "isVisibleInMenu";
    const CATEGORY_URL = "url";
    const CATEGORY_IMAGE = "imgUrl";
    #const CATEGORY_THUMBNAIL  = "thumbnail";
    const CATEGORY_NAME = "name";
    const CATEGORY_META_NAME = "metaName";
    const CATEGORY_KEYWORDS = "keywords";
    const CATEGORY_DESCRIPTION = "description";
    const CATEGORY_RICH_DESCRIPTION = "richDescription";
    const CATEGORY_META_DESCRIPTION = "metaDescription";
    const CATEGORY_HIERARCHY = "hierarchy";
}
