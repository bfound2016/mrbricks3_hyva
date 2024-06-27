<?php

namespace BFD\CategoryAttributesInProductPage\Plugin;

use Hyva\Theme\ViewModel\ProductPage as Subject;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
class HyvaThemeProductPagePlugin
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
    }

    /**
     * Add a new method to get multiple category attributes.
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param ProductInterface $product
     * @return array
     */
    public function aroundGetCategoriesData(Subject $subject, callable $proceed, ProductInterface $product): array
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