<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 2016/12/2
 * Time: 下午6:52
 */

namespace Amlun\Ledis;

use Illuminate\Support\Arr;


class DataBase
{
    /**
     * @var array
     */
    protected $clients;

    /**
     * @var array
     */
    protected $database;

    /**
     *
     *
     * Database constructor.
     * @param array $servers
     */
    public function __construct(array $servers = [])
    {
        Arr::forget($servers, 'cluster');
        Arr::forget($servers, 'options');
        $this->clients = $this->createClient($servers);
        $this->database = $this->saveDatabases($servers);
    }

    /**
     *
     *
     * @param array $servers
     * @return array
     */
    protected function saveDatabases(array $servers)
    {
        $database = [];
        foreach ($servers AS $key => $server) {
            $database[$key] = $server['database'];
        }

        return $database;
    }

    /**
     *
     *
     * @param array $servers
     * @return array
     */
    protected function createClient(array $servers)
    {
        ini_set('default_socket_timeout', -1);
        $clients = [];
        foreach ($servers AS $key => $server) {
            $redis = new \Redis();
            $redis->pconnect($server['host'], $server['port'], 10.0);
            if (!empty($server['password'])) {
                $redis->auth($server['password']);
            }
            if (!empty($server['database'])) {
                $redis->select($server['database']);
            }
            $clients[$key] = $redis;
        }
        return $clients;
    }

    /**
     *
     *
     * @param string $name
     * @return \Redis
     */
    public function connection($name = 'default')
    {
        $connection = Arr::get($this->clients, $name ?: 'default');
        return $connection;
    }

    /**
     * @return array
     */
    public function getArrClient()
    {
        return array_keys($this->clients);
    }

    /**
     * @param $method
     * @param array $parameters
     * @return mixed
     */
    public function command($method, array $parameters = [])
    {
        return call_user_func_array([$this->clients['default'], $method], $parameters);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }
}