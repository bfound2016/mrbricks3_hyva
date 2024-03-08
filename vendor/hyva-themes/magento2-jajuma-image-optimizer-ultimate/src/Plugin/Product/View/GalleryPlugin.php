<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Hyva\JajumaImageOptimizerUltimate\Plugin\Product\View;

class GalleryPlugin
{
    protected $helper;

    protected $storeManager;

    protected $scopeConfig;

    public function __construct(
        \Jajuma\ImageOptimizerUltimate\Helper\Data $imageOptimizerHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $imageOptimizerHelper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {
        $newImagesItems = [];
        $imagesItems = json_decode($result);

        foreach ($imagesItems as $itemImage) {
            $thumbImage = $this->helper->handleImageUrlBeforeConvert($itemImage->thumb);
            $imgImage = $this->helper->handleImageUrlBeforeConvert($itemImage->img);
            $fullImage = $this->helper->handleImageUrlBeforeConvert($itemImage->full);
            if($this->helper->isHighResolutionEnabled()) {
                $this->getHighResItemImageByType('thumb', $itemImage);
                // check high resolution enable for main image
                if($this->isHighResolutionProductMainEnabled()) {
                    $this->getHighResItemImageByType('img', $itemImage);
                    $this->getHighResItemImageByType('full', $itemImage);
                } else {
                    $itemImage->img_highres = $itemImage->img;
                    $webpImgImageUrl = $this->helper->convert($imgImage);
                    $itemImage->img_webp = $this->helper->handleImageUrlAfterConvert($webpImgImageUrl);
                    $avifImgImageUrl = $this->helper->checkIfConvertImageExists($imgImage, false, 'avif');
                    $itemImage->img_avif = $this->helper->handleImageUrlAfterConvert($avifImgImageUrl);
                    $itemImage->full_highres = $itemImage->full;
                    $webpFullImageUrl = $this->helper->convert($fullImage);
                    $itemImage->full_webp = $this->helper->handleImageUrlAfterConvert($webpFullImageUrl);
                    $avifFullImageUrl = $this->helper->checkIfConvertImageExists($fullImage, false, 'avif');
                    $itemImage->full_avif = $this->helper->handleImageUrlAfterConvert($avifFullImageUrl);
                }
            } else {
                $webpThumbImageUrl = $this->helper->convert($thumbImage);
                $itemImage->thumb_webp = $this->helper->handleImageUrlAfterConvert($webpThumbImageUrl);
                $webpImgImageUrl = $this->helper->convert($imgImage);
                $itemImage->img_webp = $this->helper->handleImageUrlAfterConvert($webpImgImageUrl);
                $webpFullImageUrl = $this->helper->convert($fullImage);
                $itemImage->full_webp = $this->helper->handleImageUrlAfterConvert($webpFullImageUrl);
                $avifThumbImageUrl = $this->helper->checkIfConvertImageExists($thumbImage, false, 'avif');
                $itemImage->thumb_avif = $this->helper->handleImageUrlAfterConvert($avifThumbImageUrl);
                $avifImgImageUrl = $this->helper->checkIfConvertImageExists($imgImage, false, 'avif');
                $itemImage->img_avif = $this->helper->handleImageUrlAfterConvert($avifImgImageUrl);
                $avifFullImageUrl = $this->helper->checkIfConvertImageExists($fullImage, false, 'avif');
                $itemImage->full_avif = $this->helper->handleImageUrlAfterConvert($avifFullImageUrl);
            }
            $newImagesItems[] = $itemImage;
        }

        return json_encode($newImagesItems);
    }

    /**
     * @param string $imageUrl
     * @return array
     */
    public function getHighResImageArray($imageUrl) 
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrlWithOutIndexDotPhp = str_replace('index.php/', '', $baseUrl);
        // remove cache/[cache_id] in $imageUrl
        $imageUrlWithoutCacheArr = $this->helper->removeCacheFromUrl($imageUrl);
        $imageUrlWithoutCache = false;
        $cachePath = false;
        if (is_array($imageUrlWithoutCacheArr)) {
            $imageUrlWithoutCache = $imageUrlWithoutCacheArr['imageUrlWithoutCache'];
            $cachePath = $imageUrlWithoutCacheArr['cachePath'];
        }

        // high resolution image process
        $productImageText = 'catalog/product';
        $isProductImage = strpos($imageUrlWithoutCache, $productImageText);
        if ($isProductImage) {
            $localImagePath = str_replace($baseUrl, "", $imageUrl);
            $localImagePath = str_replace($baseUrlWithOutIndexDotPhp, "", $localImagePath);
            if ($this->helper->isFileExists($localImagePath)) {       
                $pathInfo = $this->helper->fileGetContent($localImagePath);
                list($width, $height, $type, $attribute) = getimagesizefromstring($pathInfo);
                // copy and resize image by config resolution
                $highresImageArr = $this->helper->highresImage(
                    $imageUrlWithoutCache,
                    $width,
                    $height,
                    false,
                    $cachePath
                );
                return $highresImageArr;
            }
        }

        return [];
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isHighResolutionProductMainEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            'imageoptimizer/high_resolution_images/enable_high_resolution_main_image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getHighResItemImageByType($type, &$itemImage)
    {
        $highresWebpUrlArr = array();
        $highresAvifUrlArr = array();
        $newHighresImageArr = array();
        $type_webp = $type.'_webp';
        $type_avif = $type.'_avif';
        $type_highres = $type. '_highres';
        $imageUrl = $this->helper->handleImageUrlBeforeConvert($itemImage->$type);
        $highresImageArr = $this->getHighResImageArray($this->helper->handleImageUrlBeforeConvert($imageUrl));
        //get high res array for image origin, web, avif
        if (is_array($highresImageArr) && !array_key_exists('error', $highresImageArr)) {
            foreach ($highresImageArr as $key => $highresImage) {
                $newhighresImage = $this->helper->handleImageUrlAfterConvert($highresImage);
                $newHighresImageArr[$key] = $newhighresImage;
                $webpUrlHighRes = $this->helper->convert($highresImage, $key);
                $webpUrlHighRes = $this->helper->handleImageUrlAfterConvert($webpUrlHighRes);
                $highresWebpUrlArr[$key] = $webpUrlHighRes;
                $convertUrlHighRes = $this->helper->checkIfConvertImageExists($highresImage, $key, 'avif');
                $convertUrlHighRes = $this->helper->handleImageUrlAfterConvert($convertUrlHighRes);
                $highresAvifUrlArr[$key] = $convertUrlHighRes;
            }
        }
        //high res webp image
        $imageUrlWebp = $this->helper->convert($imageUrl);
        $image_webp = $this->helper->handleImageUrlAfterConvert($imageUrlWebp);
        $itemImage->$type_webp = $this->convertImageHighResArrayToString($highresWebpUrlArr, $image_webp);
        //high res avif
        $imageUrlAvif = $this->helper->checkIfConvertImageExists($imageUrl, false, 'avif');
        if($imageUrlAvif) {
            $image_avif = $this->helper->handleImageUrlAfterConvert($imageUrlAvif);
            $itemImage->$type_avif = $this->convertImageHighResArrayToString($highresAvifUrlArr, $image_avif);
        } else {
            $itemImage->$type_avif = '';
        }
        //high res origin image
        $itemImage->$type_highres = $this->convertImageHighResArrayToString($newHighresImageArr, $itemImage->$type);
    }

    /**
     * @param array $newHighresImageArr
     * @param String $imageHighres
     * @return String
     */
    public function convertImageHighResArrayToString($newHighresImageArr, $imageHighres) {
        if ($newHighresImageArr && count($newHighresImageArr)) {
            $imageHighres .= ' 800w, ';
            // get last key of array
            $arrayKeys = array_keys($newHighresImageArr);
            $lastKey = array_pop($arrayKeys);
            foreach ($newHighresImageArr as $key => $highresImage) {
                if ($key != $lastKey) {
                    $imageHighres .=  $this->helper->addBaseUrlWithPubMediaToUrl($highresImage) . ' 1600w, ';
                } else {
                    $imageHighres .=  $this->helper->addBaseUrlWithPubMediaToUrl($highresImage) . ' 1600w';
                }
            }
        }
        return $imageHighres;
    }

}
