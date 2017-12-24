<?php
namespace SharedMemory;
/**
 * Created by PhpStorm.
 * User: wison
 * Date: 2017/12/23
 * Time: 21:37
 */

class sysvshm
{
    public $key;
    public $shm_id;


    public function __construct($proj = 'a')
    {
        $this->checkFtok();
        $this->key = ftok(__FILE__, $proj);
    }

    /**
     * 创建一个共享内存
     * @param int $memsize 大小
     * @param int $perm    权限
     * @return mixed
     */
    public function create($memsize = 1024, $perm = 0775)
    {
        $this->shm_id = shm_attach($this->key, $memsize, $perm);

        return $this;
    }

    /**
     * 获取
     */
    public function get($key)
    {
        return shm_get_var($this->shm_id, $key);
    }

    /**
     * 设置
     * @param int    $key
     * @param string $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return shm_put_var($this->shm_id, (int)$key, $value);
    }

    /**
     * 是否存在
     */
    public function exists($key)
    {
        return shm_has_var($this->shm_id, $key);
    }

    /**
     * 删除
     */
    public function delete($key)
    {
        return shm_remove_var($this->shm_id, $key);
    }

    private function checkFtok()
    {
        if ( ! function_exists('ftok'))
        {
            die('ftok extention is must !' . PHP_EOL);
        }
    }

    public function __destruct()
    {
        // 关闭共享内存
        shm_detach($this->shm_id);
    }
}

$cache = new sysvshm();
$cache->create(1024, 0775);
$cache->set(1,'wisonlau');
echo $cache->exists(1).PHP_EOL;
echo $cache->get(1).PHP_EOL;