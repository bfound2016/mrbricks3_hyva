<?php

namespace Addwish\Awext\Api\Service;

/**
 * Interface StateInterface
 * @package Addwish\Awext\Api\Service
 * @api
 */
interface StateInterface {
    /**
     * @return mixed[]
     */
    public function getState(): array;
}
