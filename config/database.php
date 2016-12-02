<?php

/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:15
 */

return [

    'redis' => [

        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'password' => env('REDIS_PASSWORD', null),
        ],

        'queue' => [
            'host' => env('REDIS_QUEUE_HOST', '127.0.0.1'),
            'port' => env('REDIS_QUEUE_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'password' => env('REDIS_PASSWORD', null),
        ],
    ],

];