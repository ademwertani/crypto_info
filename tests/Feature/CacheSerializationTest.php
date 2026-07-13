<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Regression test for a project-wide bug: config/cache.php's
 * 'serializable_classes' => false (Laravel's default hardening against
 * object-injection) silently converts every cached object — stdClass DB
 * rows, Collections, Paginators — into __PHP_Incomplete_Class on retrieval
 * from the file cache store. Most pages didn't crash (Blade only reads
 * properties, which still work on the stub), but any code calling a method
 * on a cached value after a cache hit (e.g. Collection::pluck(),
 * Paginator::currentPage()) would fatal. The phpunit "array" cache driver
 * doesn't reproduce this, so it's tested explicitly against "file" here.
 */
class CacheSerializationTest extends TestCase
{
    public function test_stdclass_survives_a_file_cache_roundtrip(): void
    {
        $store = Cache::store('file');
        $store->forget('regression_stdclass');

        $store->put('regression_stdclass', (object) ['name' => 'Bitcoin'], 60);
        $result = $store->get('regression_stdclass');

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame('Bitcoin', $result->name);

        $store->forget('regression_stdclass');
    }

    public function test_collection_methods_are_callable_after_a_file_cache_roundtrip(): void
    {
        $store = Cache::store('file');
        $store->forget('regression_collection');

        $store->put('regression_collection', collect([(object) ['name' => 'Bitcoin'], (object) ['name' => 'Ethereum']]), 60);
        $result = $store->get('regression_collection');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertSame(['Bitcoin', 'Ethereum'], $result->pluck('name')->all());

        $store->forget('regression_collection');
    }
}
