<?php

namespace Addwish\Awext\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PagesDefaultSelect implements ArrayInterface {
    /**
     * Custom options array for config select
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            ["value" => 0, "label" => __("Disabled")],
            ["value" => 1, "label" => __("Enabled")]
        ];
    }
}
