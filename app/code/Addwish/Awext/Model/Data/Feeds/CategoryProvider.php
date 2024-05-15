<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Data\Feeds;

use Addwish\Awext\Api\Data\Feeds\CategoryProviderInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;


/**
 * Class CategoryProvider
 */
class CategoryProvider implements CategoryProviderInterface {
    /**
     * @var array
     */
    protected $categoryFeedArray = [];

    /**
     * @var array
     */
    protected $categoryArray = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem ;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var int
     */
    protected $rootCategoryId;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        AdapterFactory $imageFactory
    ) {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->rootCategoryId = $this->getRootCategoryId();
    }

    /**
     * Generating data
     *
     * @param array $dataArray
     *
     * @return array
     */
    public function generate(array $dataArray): array {
        foreach ($dataArray as $category) {
            $this->categoryArray[$category->getId()] = $category->getName();
        }

        foreach ($dataArray as $category) {
            if ($category instanceof CategoryInterface) {
                if ($category->getIsActive() && $category->getPathIds()[1] == $this->rootCategoryId) {
                    $this->categoryFeedArray[] = [
                        self::CATEGORY_ID => $category->getId(),
                        self::CATEGORY_IS_VISIBLE => $category->getIncludeInMenu(),
                        self::CATEGORY_URL => $category->getUrl(),
                        self::CATEGORY_IMAGE => $this->getCategoryImage($category),
                        #self::CATEGORY_THUMBNAIL  => $this->getCategoryThumbnail($category, 256, 256),
                        self::CATEGORY_NAME => $this->toAllowedXmlValue($category->getName()),
                        self::CATEGORY_META_NAME => $this->toAllowedXmlValue($category->getMetaTitle()),
                        self::CATEGORY_KEYWORDS => $this->toAllowedXmlValue($category->getMetaKeywords()),
                        self::CATEGORY_DESCRIPTION => $this->toAllowedXmlValue($this->getCategoryDescription($category)),
                        self::CATEGORY_RICH_DESCRIPTION => $this->toAllowedXmlValue($category->getDescription()),
                        self::CATEGORY_META_DESCRIPTION => $this->toAllowedXmlValue($category->getMetaDescription()),
                        self::CATEGORY_HIERARCHY => $this->getCategoryHierarchy($category)
                    ];
                }

            }

        }

        return $this->categoryFeedArray;
    }

    /**
     * Replace characters not allowed in xml with space
     *
     * @param string $value
     *
     * @return string
     */
    protected function toAllowedXmlValue($value) {
        if (is_null($value)) {
            $value = "";
        } else if (is_bool($value)) {
            return $value;
        } else if (is_numeric($value)) {
            return $value;
        } else {
            $value = str_replace(
                array(
                    // The C0 control block, except a few specifically allowed characters.
                    "\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
                    "\x08", "\x0B", "\x0C", "\x0E", "\x0F", "\x10", "\x11", "\x12",
                    "\x13", "\x14", "\x15", "\x16", "\x17", "\x18", "\x19", "\x1A",
                    "\x1B", "\x1C", "\x1D", "\x1E", "\x1F",
                ),
                " ",
                $value);
        }
        return $value;
    }

    /**
     * Gets category description
     *
     * @param CategoryInterface $category
     *
     * @return string
     */
    protected function getCategoryDescription($category) {
        if ($category->getDescription()) {
            return strip_tags($category->getDescription());
        }
        return "";
    }

    /**
     * Gets category image
     *
     * @param CategoryInterface $category
     *
     * @return string
     */
    protected function getCategoryImage($category) {
        if ($category->getImage()) {
            return $category->getImageUrl();
        }
        return "";
    }

    /**
     * Gets/creates category thumbnail
     * at this moment the image is stored in pup/media/resized - no cache
     * category doesnt seem to have a cache like products do.
     * we could hack it and use the products cache and save it in pup/media/catalog/products/cache/awcategory
     * if we save in products/cache the admin "clear image cache" button can be used to delete our images again.
     *
     * @param CategoryInterface $category
     *
     * @return string
     */
    protected function getCategoryThumbnail($category, $width = null, $height = null) {
        if ($category->getImage()) {
            $image = $category->getImage();
            $imageResized = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath("resized/".$width."/").$image;
            $existsAlready = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->isFile($imageResized);

            if (!$existsAlready) {
                $imageResize = $this->imageFactory->create();
                $imageResize->open($category->getImageUrl());
                $imageResize->constrainOnly(false);
                $imageResize->keepAspectRatio(true);
                $imageResize->resize($width,$height);
                $destination = $imageResized ;
                $imageResize->save($destination);
            }
            $resizedURL = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                ."resized/".$width."/".$image;
            return $resizedURL;
        }
        return "";
    }

    /**
     * Gets category hierarchy
     *
     * @param CategoryInterface $category
     *
     * @return array
     */
    protected function getCategoryHierarchy(CategoryInterface $category): array {
        $hierarchy = [];
        // remove the 2 first as they are store root and root category
        $categoryIds = array_slice($category->getPathIds(),2);
        if (is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                if (array_key_exists($categoryId,$this->categoryArray)) {
                    $hierarchy[] = $this->toAllowedXmlValue($this->categoryArray[$categoryId]);
                }
            }
        }
        return $hierarchy;
    }

    /**
     * Gets root category id
     *
     * @return int
     */
    protected function getRootCategoryId(): int {
        $storeId = $this->storeManager->getStore()->getId();
        $store   = $this->storeManager->getStore($storeId);
        if ($store instanceof Store) {
            return (int) $store->getRootCategoryId();
        }

        return 0;
    }


}

