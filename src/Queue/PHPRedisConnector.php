<?php

/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午6:58
 */
namespace Amlun\Ledis\Queue;

use Illuminate\Support\Arr;
use Amlun\Ledis\DataBase;
use Illuminate\Queue\Connectors\ConnectorInterface;

class PHPRedisConnector implements ConnectorInterface
{
    /**
     * The Redis database instance.
     *
     * @var Database
     */
    protected $redis;

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new Redis queue connector instance.
     *
     * @param  Database $redis
     * @param  string|null $connection
     */
    public function __construct(DataBase $redis, $connection = null)
    {
        $this->redis = $redis;
        $this->connection = $connection;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new PHPRedisQueue(
            $this->redis, $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'expire', 60)
        );
    }
}