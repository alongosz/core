<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Persistence\Cache;

use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class AbstractHandler.
 *
 * Abstract handler for use in other Persistence Cache Handlers.
 */
abstract class AbstractHandler
{
    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    protected $cache;

    /** @var \Ibexa\Contracts\Core\Persistence\Handler */
    protected $persistenceHandler;

    /** @var \Ibexa\Core\Persistence\Cache\PersistenceLogger */
    protected $logger;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface */
    protected $cacheIdentifierGenerator;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer */
    protected $cacheIdentifierSanitizer;

    /** @var \Ibexa\Core\Persistence\Cache\LocationPathConverter */
    protected $locationPathConverter;

    /**
     * Setups current handler with everything needed.
     *
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \Ibexa\Contracts\Core\Persistence\Handler $persistenceHandler
     * @param \Ibexa\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface $cacheIdentifierGenerator
     * @param \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer $cacheIdentifierSanitizer
     * @param \Ibexa\Core\Persistence\Cache\LocationPathConverter $locationPathConverter
     */
    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger,
        CacheIdentifierGeneratorInterface $cacheIdentifierGenerator,
        CacheIdentifierSanitizer $cacheIdentifierSanitizer,
        LocationPathConverter $locationPathConverter
    ) {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
        $this->cacheIdentifierGenerator = $cacheIdentifierGenerator;
        $this->cacheIdentifierSanitizer = $cacheIdentifierSanitizer;
        $this->locationPathConverter = $locationPathConverter;
    }

    /**
     * Helper for getting multiple cache items in one call and do the id extraction for you.
     *
     * Cache items must be stored with a key in the following format "${keyPrefix}${id}", like "ez-content-info-${id}",
     * in order for this method to be able to prefix key on id's and also extract key prefix afterwards.
     *
     * It also optionally supports a key suffixs, for use on a variable argument that affects all lookups,
     * like translations, i.e. "ez-content-${id}-${translationKey}" where $keySuffixes = [$id => "-${translationKey}"].
     *
     * @param array $ids
     * @param string $keyPrefix E.g "ez-content-"
     * @param callable $missingLoader Function for loading missing objects, gets array with missing id's as argument,
     *                                expects return value to be array with id as key. Missing items should be missing.
     * @param callable $loadedTagger Function for tagging loaded object, gets object as argument, return array of tags.
     * @param array $keySuffixes Optional, key is id as provided in $ids, and value is a key suffix e.g. "-eng-Gb"
     *
     * @return array
     */
    final protected function getMultipleCacheItems(
        array $ids,
        string $keyPrefix,
        callable $missingLoader,
        callable $loadedTagger,
        array $keySuffixes = []
    ): array {
        if (empty($ids)) {
            return [];
        }

        // Generate unique cache keys
        $cacheKeys = [];
        $cacheKeysToIdMap = [];
        foreach (\array_unique($ids) as $id) {
            $key = $keyPrefix . $id . ($keySuffixes[$id] ?? '');
            $cacheKeys[] = $key;
            $cacheKeysToIdMap[$key] = $id;
        }

        // Load cache items by cache keys (will contain hits and misses)
        /** @var \Symfony\Component\Cache\CacheItem[] $list */
        $list = [];
        $cacheMisses = [];
        foreach ($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = $cacheKeysToIdMap[$key];
            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
            } else {
                $cacheMisses[] = $id;
                $list[$id] = $cacheItem;
            }
        }

        // No misses, return completely cached list
        if (empty($cacheMisses)) {
            return $list;
        }

        // Load missing items, save to cache & apply to list if found
        $loadedList = $missingLoader($cacheMisses);
        foreach ($cacheMisses as $id) {
            if (isset($loadedList[$id])) {
                $this->cache->save(
                    $list[$id]
                        ->set($loadedList[$id])
                        ->tag($loadedTagger($loadedList[$id]))
                );
                $list[$id] = $loadedList[$id];
            } else {
                unset($list[$id]);
            }
        }

        return $list;
    }
}

class_alias(AbstractHandler::class, 'eZ\Publish\Core\Persistence\Cache\AbstractHandler');
