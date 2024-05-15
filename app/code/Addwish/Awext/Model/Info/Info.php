<?php declare(strict_types=1);

namespace Addwish\Awext\Model\Info;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Api\Info\InfoInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensionAttribute\Config;


/**
 * Class Info
 */
class Info implements InfoInterface {
    /**
     * Node names
     */
    const INFO_NODE = "info";
    const VERSION_NODE = "version";
    const CLIENT_IP = "clientIp";
    const ACCESS = "access";

    /**
     * @var array
     */
    protected $nodeArray = [];

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var Config
     */
    private $extensionAttributeConfig;

    /**
     * Info constructor.
     *
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        Config $extensionAttributeConfig
    ) {
        $this->configHelper = $configHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->extensionAttributeConfig = $extensionAttributeConfig;
    }

    /**
     * Gets module info
     *
     * @return array
     */
    public function getModuleInfo($isIpWhitelisted): array {
        $version = $this->configHelper->getModuleVersion();
        $this->setNode(self::VERSION_NODE, $version);
        $this->setNode(self::ACCESS, $isIpWhitelisted["result"] ? "true" : "false");
        $this->setNode(self::CLIENT_IP, $isIpWhitelisted["ip"]);
        if ($isIpWhitelisted["result"]) {
            $attributes = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributeRepository = $this->attributeRepository->getList(
                "catalog_product",
                $searchCriteria
            );
            foreach ($attributeRepository->getItems() as $items) {
                if ($items->getAttributeCode() == null) {
                    continue;
                }
                $attributes[] = [
                    "attribute_code" => $items->getAttributeCode(),
                    "attribute_name" => $items->getFrontendLabel() ? $items->getFrontendLabel() : ""
                ];
            }
            $this->setNodeArray("product_attributes", $attributes);

            $extensionAttributeNames = array_keys($this->extensionAttributeConfig->get(ProductInterface::class));
            foreach ($extensionAttributeNames as $extensionAttributeName) {
                $extAttributes[] = [
                    "attribute_code" => $extensionAttributeName
                ];
             }
            $this->setNodeArray("extensions_attributes", $extAttributes);
        }

        return [self::INFO_NODE => $this->nodeArray];
    }

    /**
     * Sets node to nodeArray
     *
     * @param string $key
     * @param string $value
     *
     * @return InfoInterface
     */
    public function setNode(string $key, string $value): InfoInterface {
        $this->nodeArray[$key] = $value;

        return $this;
    }

    /**
     * Sets node array  to nodeArray
     *
     * @param string $key
     * @param array  $value
     *
     * @return InfoInterface
     */
    public function setNodeArray(string $key, array $value): InfoInterface {
        $this->nodeArray[$key] = $value;

        return $this;
    }

    /**
     * Gets node by key
     *
     * @param string $key
     *
     * @return array
     */
    public function getNode(string $key): array {
        if (array_key_exists($key, $this->nodeArray[$key])) {
            return $this->nodeArray[$key];
        }

        return [];
    }
}
