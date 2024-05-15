<?php

namespace Addwish\Awext\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class CreatePagesAttributesForCategories implements DataPatchInterface, PatchRevertableInterface {
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

     /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply() {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(["setup" => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            "hello_retail_pages_enabled",
            [
                "type" => "int",
                "label" => "Enable Hello Retail Pages content",
                "input" => "select",
                "source" => "",
                "visible" => true,
                "frontend" => "",
                "unique" => false,
                "default" => "2",
                "required" => false,
                "global" => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            "hello_retail_pages_key",
            [
                "type" => "varchar",
                "label" => "Hello Retail Pages key",
                "input" => "text",
                "source" => "",
                "visible" => true,
                "default" => null,
                "required" => false,
                "global" => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies() {
        // If it is necessary to run this patch after other patches 
        // (might be relevant for other patches in the future)
        // we can specify those patch classes in the return
        // it is also possible to implement a getVersions() function
        // which can be used to determine if the patch should be executed
        // based on version number of our module already installed.
        return [
            //SomePreviousPatch::class
        ];
    }

    public function revert() {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(["setup" => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, "hello_retail_pages_enabled");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, "hello_retail_pages_key");
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases() {
        // If we ever want to change the name of this patch class we will have to add an alias
        // to this return
        return [];
    }
}