<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午7:00
 */

namespace Amlun\Ledis\Queue;

use Amlun\Ledis\DataBase;
use Illuminate\Queue\LuaScripts;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;

class PHPRedisQueue extends Queue implements QueueContract
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
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $expire = 60;

    /**
     * Create a new Redis queue instance.
     *
     * @param  Database $redis
     * @param  string $default
     * @param  string $connection
     * @param  int $expire
     */
    public function __construct(DataBase $redis, $default = 'default', $connection = null, $expire = 60)
    {
        $this->redis = $redis;
        $this->expire = $expire;
        $this->default = $default;
        $this->connection = $connection;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string $queue
     * @return int
     */
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);
        return $this->getConnection()->eval(LuaScripts::size(), [$queue, $queue . ':delayed', $queue . ':reserved'], 3);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->getConnection()->rPush($this->getQueue($queue), $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int $delay
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $this->getConnection()->zAdd(
            $this->getQueue($queue) . ':delayed', $this->getTime() + $this->getSeconds($delay), $payload
        );

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $original = $queue ?: $this->default;

        $queue = $this->getQueue($queue);

        $this->migrateExpiredJobs($queue . ':delayed', $queue);

        if (!is_null($this->expire)) {
            $this->migrateExpiredJobs($queue . ':reserved', $queue);
        }

        list($job, $reserved) = $this->getConnection()->eval(LuaScripts::pop(), [$queue, $queue . ':reserved', $this->getTime() + $this->expire], 2);

        if ($reserved) {
            return new PHPRedisJob($this->container, $this, $job, $reserved, $original);
        }
        return null;
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param  string $queue
     * @param  string $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        $this->getConnection()->zRem($this->getQueue($queue) . ':reserved', $job);
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param  string $queue
     * @param  string $job
     * @param  int $delay
     * @return void
     */
    public function deleteAndRelease($queue, $job, $delay)
    {
        $queue = $this->getQueue($queue);

        $this->getConnection()->eval(LuaScripts::release(), [$queue . ':delayed', $queue . ':reserved', $job, $this->getTime() + $delay], 2);
    }

    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param  string $from
     * @param  string $to
     * @return void
     */
    public function migrateExpiredJobs($from, $to)
    {
        $this->getConnection()->eval(LuaScripts::migrateExpiredJobs(), [$from, $to, $this->getTime()], 2);
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     * @return string
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        $payload = $this->setMeta(
            parent::createPayload($job, $data), 'id', $this->getRandomId()
        );

        return $this->setMeta($payload, 'attempts', 1);
    }

    /**
     * Get a random ID string.
     *
     * @return string
     */
    protected function getRandomId()
    {
        return Str::random(32);
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return 'queues:' . ($queue ?: $this->default);
    }

    /**
     * Get the connection for the queue.
     *
     * @return \Redis
     */
    protected function getConnection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Get the underlying Redis instance.
     *
     * @return Database
     */
    public function getRedis()
    {
        return $this->redis;
    }
}