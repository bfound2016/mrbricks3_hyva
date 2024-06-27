<?php

namespace BFD\CategoryAttributesInProductPage\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductPage extends \Hyva\Theme\ViewModel\ProductPage
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        parent::__construct();
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
