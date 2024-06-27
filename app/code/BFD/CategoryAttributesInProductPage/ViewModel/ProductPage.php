<?php

namespace BFD\CategoryAttributesInProductPage\ViewModel;

use Hyva\Theme\ViewModel\ProductPage as HyvaProductPage;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Catalog\Helper\Output as ProductOutputHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Block\Product\ImageFactory;

class ProductPage extends HyvaProductPage
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartHelper $cartHelper
     * @param ProductOutputHelper $productOutputHelper
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param ImageFactory $productImageFactory
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        CartHelper $cartHelper,
        ProductOutputHelper $productOutputHelper,
        ScopeConfigInterface $scopeConfigInterface,
        ImageFactory $productImageFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($registry, $priceCurrency, $cartHelper, $productOutputHelper, $scopeConfigInterface, $productImageFactory);
    }

    /**
     * Get multiple category attributes.
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getCategoriesData(ProductInterface $product): array
    {
        $categoryIds = $product->getCategoryIds();
        $categoriesData = [];

        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $categoriesData[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'category_logo' => $category->getCustomAttribute('category_logo')->getValue(),
                    'category_background' => $category->getCustomAttribute('category_background')->getValue()
                ];
            } catch (NoSuchEntityException $e) {
                // Handle the exception if needed
            }
        }

        return $categoriesData;
    }
}
