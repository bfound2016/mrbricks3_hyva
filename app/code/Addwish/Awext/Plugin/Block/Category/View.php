<?php declare(strict_types = 1);

namespace Addwish\Awext\Plugin\Block\Category;

use Addwish\Awext\Helper\Config as ConfigHelper;
use \Magento\Catalog\Model\Layer\Resolver;
use \Magento\Framework\HTTP\ClientInterface;


/**
 * Class View
 *
 * This plugin will wrap the standard category page output in a hidden div for fallback purposes
 * as well as adding the necessary pages html snippet.
 *
 *  */
class View {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * HTTP client interface
     *
     * @var \Magento\Framework\HTTP\ClientInterface
     */
    protected $httpClient;

    public function __construct(
        ConfigHelper $configHelper,
        Resolver $layerResolver,
        ClientInterface $httpClient
    ) {
        $this->configHelper = $configHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->httpClient = $httpClient;
    }

    /**
     * @return string
     */
    public function aroundGetProductListHtml($subject, callable $proceed) {
        // $subject is the context we were triggered from, 
        // so in this case it will be an instance of the Magento\Catalog\Block\Category\View.php block.
        try {
            $isModuleEnabled = $this->configHelper->isModuleEnabled();
            $isPagesEnabled = $this->configHelper->isPagesEnabled();
            if ($isModuleEnabled && $isPagesEnabled) {
                $pagesDefaultCategorySetting = $this->configHelper->getDefaultPagesCategorySetting();
                $category = $subject->getCurrentCategory();
                $categoryPagesSetting = $category->getData("hello_retail_pages_enabled");
                $isPagesEnabledForThisCategory = filter_var($pagesDefaultCategorySetting, FILTER_VALIDATE_BOOLEAN);
                if ($categoryPagesSetting == "0" || $categoryPagesSetting == "1") {
                    //if the value is either NULL or "2" it means we should use the default from config.
                    $isPagesEnabledForThisCategory = filter_var($categoryPagesSetting, FILTER_VALIDATE_BOOLEAN);
                }
                $pagesKey = $category->getData("hello_retail_pages_key");
                if (empty($pagesKey)) {
                    $pagesKey = $this->configHelper->getDefaultPagesKey();
                }
                if ($isPagesEnabledForThisCategory && $pagesKey) {
                    $hierarchy = [];
                    foreach ($category->getParentCategories() as $parent) {
                        $hierarchy[] = $parent->getName();
                    }
                    $hierarchy = json_encode($hierarchy);
                    $baseFilter = json_encode(["extraDataList.categoryIds" => $category->getId()]);

                    $otherElementsToHide = $this->configHelper->getElementsToHideForPages();
                    if ($this->configHelper->isPagesBackendRenderingEnabled()) {
                        return $this->backendRender($subject, $proceed, $pagesKey, $baseFilter, $hierarchy, $otherElementsToHide);
                    } else {
                        return $this->frontendRender($proceed, $pagesKey, $baseFilter, $hierarchy, $otherElementsToHide);
                    }
                }
            }
        } catch (\Exception $e) {
            // do nothing, we will call $proceed in the end
            // and show default content
        }
        return $proceed();
    }

    private function backendRender($subject, $proceed, $pagesKey, $baseFilter, $hierarchy, $otherElementsToHide) {
        $request = $subject->getRequest();
        $filters = [];
        $sorting = [];
        if ($request->getParam("hr-page") != null) {
            $decoded = json_decode($request->getParam("hr-page"), true);
            if (isset($decoded["filters"])) {
                $filters = $decoded["filters"];
            }
            if (isset($decoded["sorting"])) {
                $sorting = $decoded["sorting"];
            }
        }
        $trackingUserId = "000000000000000000000000";
        if ($this->configHelper->isPagesCacheBustingEnabled()) {
            $trackingUserId = $_COOKIE["hello_retail_id"];
        }
        $postData = [
            "url" => $request->getScheme() . "://" . $request->getHttpHost(false) . $request->getRequestUri(),
            "layout" => true,
            "products" => [
                "filters" => $filters,
                "sorting" => $sorting
            ],
            "params" => [
                "filters" => $baseFilter,
                "hierarchy" => $hierarchy
            ],
            "firstLoad" => true,
            "trackingUserId" => $trackingUserId
        ];
        $this->httpClient->setHeaders(["Content-Type" => "application/json"]);
        $this->httpClient->setTimeout(2);
        $this->httpClient->post("https://core.helloretail.com/serve/pages/" . $pagesKey, json_encode($postData));
        if ($this->httpClient->getStatus() === 200) {
            $helloRetailPagesContent = json_decode($this->httpClient->getBody(), true);
            if (isset($helloRetailPagesContent["products"]["html"]) && isset($helloRetailPagesContent["products"]["style"])) {
                $otherElementsToHideStyleTag = "";
                if (!empty($otherElementsToHide)) {
                    $otherElementsToHideStyleTag = "<style>" . $otherElementsToHide . "{display: none}</style>\n";
                }
                $result = "<div id=\"hr-category-page-" . $pagesKey . "\">" . $helloRetailPagesContent["products"]["html"] . "</div>\n" .
                "<style>" . $helloRetailPagesContent["products"]["style"] . "</style>\n" . $otherElementsToHideStyleTag .
                "<script>\n" .
                "   _awev=(window._awev||[]);_awev.push([\"bind_once\", \"context_ready\", function(){\n" .
                "       (function(_, container, data, page){" .
                "           " . $helloRetailPagesContent["products"]["javascript"] .
                "        })(ADDWISH_PARTNER_NS, document.getElementById(\"hr-category-page-" . $pagesKey . "\"), " . json_encode($postData) . ", {key:\"" . $pagesKey . "\"})\n" .
                "    }]);\n" .
                "</script>\n";

                // this will let the filters block know that our content has replaced the default content
                // so the filters should not be rendered.
                $this->catalogLayer->setData("hello_retail_pages_shown", true);

                return $result;
            }
        }
        return $proceed();
    }

    private function frontendRender($proceed, $pagesKey, $baseFilter, $hierarchy, $otherElementsToHide) {
        $pagesId = "helloretail-category-page-" . $pagesKey;
        $fallbackId = "platform-default-category-page";
        $hiddenElementsToFind = "[]";

        $result = "<div id=\"" . $pagesId . "\" data-filters=\"" . htmlspecialchars($baseFilter, ENT_QUOTES, "UTF-8") . "\" data-hierarchy=\"" . htmlspecialchars($hierarchy, ENT_QUOTES, "UTF-8") . "\"></div>\n";
        if (!empty($otherElementsToHide)) {
            $result .= "<style>" . $otherElementsToHide . "{display: none}</style>\n";
            $hiddenElementsToFind = "document.querySelectorAll(\"" . $otherElementsToHide . "\")";
        }
        $fallbackContent = "<div style=\"display:none\" id=\"" . $fallbackId . "\">\n\t" . $proceed() . "</div>\n" .
        "<script>" .
        "   setTimeout(function() {\n" .
        "      var helloretailContent = document.getElementById(\"" . $pagesId . "\");\n" .
        "      var fallbackContent = document.getElementById(\"" . $fallbackId . "\");\n" .
        "      var otherHiddenElements = " . $hiddenElementsToFind . ";\n" .
        "      if (helloretailContent.childNodes.length == 0) {\n" .
        "          helloretailContent.style.display=\"none\";\n" .
        "          fallbackContent.style.display=\"block\";\n" .
        "          otherHiddenElements.forEach(hiddenElement => {\n" .
        "              hiddenElement.style.display=\"block\";\n" .
        "          });\n" .
        "      }\n" .
        "   }, 2000);\n" .
        "</script>\n";

        return $result . $fallbackContent;
    }
}
