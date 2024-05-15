<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Data\Feeds;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface {
    /**
     * Generating data
     *
     * @param array $dataArray
     *
     * @return array
     */
    public function generate(array $dataArray): array;
}
