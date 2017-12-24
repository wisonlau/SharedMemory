<?php
namespace SharedMemory;
/**
 * Created by PhpStorm.
 * User: wison
 * Date: 2017/12/23
 * Time: 21:37
 */

class shmop
{
    public $key;
    public $shm_id;
    public $sem_id;
    public $perm;


    public function __construct($proj = 'a')
    {
        $this->checkFtok();
        $this->key = ftok(__FILE__, $proj);
    }

    /**
     * 创建一个共享内存
     * @param int $memsize  申请共享内存区的大小
     * @param int $perm     权限
     * @param string $flags 标记
     * [a :  创建一个只读的共享内存区, c :  如果共享内存区已存在，则打开该共享内存区，并尝试读写。否则新建共享内存区, w ： 创建一个读写共享内存区, n :  创建一个共享内存区，如果已存在，则返回失败]
     */
    public function create($memsize = 1024, $perm = 0775, $flags = 'c')
    {
        $this->perm = $perm;
        $this->shm_id = shmop_open($this->key, $flags, $perm, $memsize);

        return $this;
    }

    /**
     * 获取
     * @param int $start 从共享内存的那个字节开始读起
     * @param int $count 一次读取多少个字节,count值小于发送的信息长度，则信息会被截断
     * @return mixed
     */
    public function get($start, $count)
    {
        return shmop_read($this->shm_id, $start, $count);
    }

    /**
     * 设置(阻塞)
     * @param string $data        将要写入的数据
     * @param int    $offset      从共享内存块的那个位置开始写入
     * @param int    $max         同时只允许一个进程获取到此信号量
     * @param int    $autoRelease request结束之后，自动release此信号
     * @return mixed 返回值是写入数据的长度
     */
    public function set($data, $offset, $max = 1, $autoRelease = 0)
    {
        set_time_limit(0);
        // 信号
        $this->sem_id = sem_get($this->key, $max, $this->perm, $autoRelease);
        // 请求信号
        if (sem_acquire($this->sem_id))
        {
            return shmop_write($this->shm_id, $data, $offset);
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        shmop_delete($this->shm_id);
        shmop_close($this->shm_id);
    }

    /**
     * 返回当前共享内存块，已经使用的大小
     */
    public function size()
    {
        return shmop_size($this->shm_id);
    }

    /**
     * 删除Semaphore
     */
    public function semRemove()
    {
        return sem_remove($this->sem_id);
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
        // 释放信号
        sem_release($this->sem_id);
    }
}

$cache = new shmop();
$cache->create(1024, 0755, 'c');
$size = $cache->set('aaa', 0);
$size2 = $cache->set('wison', $size+1);
echo $cache->get(0, $size).PHP_EOL;
echo $cache->get($size+1, $size2).PHP_EOL;