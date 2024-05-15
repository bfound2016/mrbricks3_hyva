<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Service;

/**
 * Interface ManagementInterface
 * @api
 */
interface ManagementInterface {
    /**
     * Gets product feed
     *
     * @return mixed[]
     */
    public function getProductFeed(): array;

    /**
     * Gets order feed
     *
     * @return mixed[]
     */
    public function getOrderFeed(): array;

    /**
     * Gets category feed
     *
     * @return mixed[]
     */
    public function getCategoryFeed(): array;

    /**
     * Gets module info
     *
     * @return mixed[]
     */
    public function getModuleInfo(): array;
}
