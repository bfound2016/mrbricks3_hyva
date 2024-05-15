<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Info;

/**
 * Interface InfoInterface
 */
interface InfoInterface
{
    /**
     * Gets module info
     *
     * @return array
     */
    public function getModuleInfo($isIpWhitelisted): array;
}
