# Ledis

PHPRedis For Laravel / Lumen

Most Likely:
    
```
Illuminate\Cache\RedisStore
Illuminate\Queue\RedisQueue
Illuminate\Queue\Jobs\RedisJob
Illuminate\Queue\Connectors\RedisConnector
```

# Laravel
# Lumen

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
