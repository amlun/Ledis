# Ledis

Ledis is a redis driver for Laravel or Lumen,it works with PhpRedis(the PECL Redis Extension).

Now it supports redis database, cache and queue.

You can find that Ledis is Similar to:
    
```
Illuminate\Redis\Database
Illuminate\Cache\RedisStore
Illuminate\Queue\RedisQueue
Illuminate\Queue\Jobs\RedisJob
Illuminate\Queue\Connectors\RedisConnector
```

# Usage

`composer require amlun/ledis`


# Use in Laravel
# Use in Lumen

Add in bootstrap/app.php 

```
$app->register(Amlun\Ledis\Providers\PHPRedisServiceProvider::class);
$app->register(Amlun\Ledis\Providers\CacheServiceProvider::class);
$app->register(Amlun\Ledis\Providers\QueueServiceProvider::class);
```

Config in config/cache.php

```
'phpredis' => [
    'driver' => 'phpredis',
    'connection' => env('CACHE_REDIS_CONNECTION', 'default'),
],
```

Config in config/queue.php
```
'phpredis' => [
    'driver' => 'phpredis',
    'connection' => 'queue',
    'queue' => 'default',
    'expire' => 60,
],
```

And then set the .env file

```
CACHE_DRIVER=phpredis
QUEUE_DRIVER=phpredis
``` 
