<?php

/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:06
 */

namespace Amlun\Ledis\Providers;

use Amlun\Cache\PHPRedisStore;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Cache::extend('phpredis', function ($app) {
            return Cache::repository(new PHPRedisStore($app->make('phpredis'), $app->config['cache.prefix'], $app->config['cache.stores.phpredis.connection']));
        });
    }
}