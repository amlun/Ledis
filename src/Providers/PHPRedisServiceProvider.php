<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:08
 */

namespace Amlun\Ledis\Providers;

use Amlun\Ledis\DataBase;
use Illuminate\Support\ServiceProvider;

class PHPRedisServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('phpredis', function ($app) {
            return new Database($app->config['database.redis']);
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['phpredis'];
    }
}