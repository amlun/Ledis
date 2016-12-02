<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:04
 */

namespace Amlun\Cache;

use Amlun\Ledis\Database;
use Illuminate\Contracts\Cache\Store;

class PHPRedisStore implements Store
{
    /**
     * The Redis database connection.
     *
     * @var Database
     */
    protected $redis;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * PHPRedisStore constructor.
     * @param Database $redis
     * @param string $prefix
     * @param string $connection
     */
    public function __construct(Database $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function get($key)
    {
        if ($value = $this->connection()->get($this->prefix . $key)) {
            return $this->unserialize($value);
        }
        return null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array $keys
     * @return array
     */
    public function many(array $keys)
    {
        $return = [];

        $prefixedKeys = array_map(function ($key) {
            return $this->prefix . $key;
        }, $keys);

        $values = $this->connection()->mget($prefixedKeys);

        foreach ($values as $index => $value) {
            $return[$keys[$index]] = $this->unserialize($value);
        }

        return $return;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed $value
     * @param  float|int $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $value = $this->serialize($value);

        $this->connection()->setex($this->prefix . $key, (int)max(1, $minutes * 60), $value);
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array $values
     * @param  float|int $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {
        $this->connection()->multi();

        foreach ($values as $key => $value) {
            $this->put($key, $value, $minutes);
        }

        $this->connection()->exec();
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrBy($this->prefix . $key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrBy($this->prefix . $key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function forever($key, $value)
    {
        $this->connection()->set($this->prefix . $key, $this->serialize($value));
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function forget($key)
    {
        return (bool)$this->connection()->del($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->connection()->flushDB();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function connection()
    {
        return $this->redis->connection('default');
    }

    /**
     * Set the connection name to be used.
     *
     * @param  string $connection
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = !empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * Serialize the value.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }

}