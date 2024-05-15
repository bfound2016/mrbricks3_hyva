<?php declare(strict_types = 1);

namespace Addwish\Awext\Plugin\Block;

/**
 * Class Navigation
 *
 * This plugin will intercept and stop the rendering of default category filters
 * when we have intercepted and replaced the default category content with our pages content.
 *
 *  */
class Navigation {
    public function aroundCanShowBlock($subject, callable $proceed) {
        // this value will only be set when pages backend rendering is active.
        // for frontend rendering this will be cached and so we cannot use this method.
        // but instead need to use css to hide default content like this.
        $dontShowDeafultFiltersBlock = $subject->getLayer()->getData("hello_retail_pages_shown");
        $subject->getLayer()->unsetData("hello_retail_pages_shown");
        if ($dontShowDeafultFiltersBlock) {
            return FALSE;
        }
        return $proceed();
    }
}
