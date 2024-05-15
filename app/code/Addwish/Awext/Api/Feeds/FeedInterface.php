<?php declare(strict_types = 1);

namespace Addwish\Awext\Api\Feeds;

/**
 * Interface FeedInterface
 */
interface FeedInterface {
    /**
     * Gets data feed
     *
     * @return array
     */
    public function getFeed(): array;
}
