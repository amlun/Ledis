<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:11
 */

namespace Amlun\Ledis\Providers;

use Amlun\Ledis\Queue\PHPRedisConnector;
use Illuminate\Support\ServiceProvider;
use Queue;

class QueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        $app = $this->app;
        Queue::addConnector('phpredis', function () use ($app) {
            return new PHPRedisConnector($app->make('phpredis'), $app->config['cache.stores.phpredis.connection']);
        });
    }
}