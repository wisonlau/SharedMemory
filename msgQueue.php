<?php
namespace queue;
/**
 * System V message queue IPC通信消息队列封装
 * Created by PhpStorm.
 * User: wison
 * Date: 2017/12/24
 * Time: 17:22
 */

class msgQueue
{
    // IPC通信KEY
    public $message_queue_key;
    // 队列标志
    public $message_queue;


    public function __construct($proj = 'a')
    {
        $this->checkFtok();
        $this->message_queue_key = ftok(__FILE__, $proj);
    }

    /**
     * 创建或附加到消息队列
     * @param int $perm 权限
     * @return mixed
     */
    public function create($perm = 0775)
    {
        $this->message_queue = msg_get_queue($this->message_queue_key, $perm);
    }

    /**
     * 获取
     * @param int $desiredmsgtype 0:则返回队列前面的消息,大于0:则返回该类型的第一条消息,小于0:则desiredmsgtype读取最小类型小于或等于绝对值的队列上的第一条消息,如果没有消息符合条件，则脚本将等待合适的消息到达队列中
     * @param int $msgtype 收到的消息的类型
     * @param int $maxsize 消息的最大大小被指定的被接受 maxsize; 如果队列中的消息大于此大小，则该函数将失败（除非flags按照以下说明设置 ）
     * @param string $message 接收到的消息
     * @param bool $unserialize 如果设置为 TRUE，则将消息视为使用与会话模块相同的机制进行序列化。是FALSE，则该消息将作为二进制安全字符串返回。
     * @param int $flags MSG_IPC_NOWAIT
     * [如果没有消息 desiredmsgtype，立即返回，不要等待。该函数将失败，并返回对应的整数值MSG_ENOMSG。
     * MSG_EXCEPT	将此标志与desiredmsgtype大于0 结合使用 会导致函数接收到不等于的第一条消息 desiredmsgtype。
     * MSG_NOERROR	如果消息长于maxsize，设置此标志将截断消息， maxsize不会发出错误信号。]
     * @param int $errorcode 如果函数失败，errorcode 则可选项将被设置为系统errno变量的值
     * @return mixed
     */
    public function get($desiredmsgtype = 0, $msgtype, $maxsize = 1024, $unserialize = TRUE, $flags = MSG_IPC_NOWAIT, $errorcode = '')
    {
        msg_receive($this->message_queue, $desiredmsgtype, $msgtype, $maxsize, $message, $unserialize, $flags, $errorcode);
        return $message;
    }

    /**
     * 设置
     * @param int $msgtype 消息的类型
     * @param string $message 小消息
     * @param bool $serialize
     * @param bool $blocking
     * [如果消息太大而无法放入队列，则脚本将等待另一个进程从队列中读取消息，并释放足够的空间以发送消息。这被称为阻塞; 您可以通过设置可选blocking参数来防止阻塞FALSE，在这种情况下，如果消息对于队列来说太大，msg_send（）将立即返回，并将可选参数FALSE设置 errorcode为MSG_EAGAIN，表示您稍后应该尝试再次发送消息。]
     * @param int $errorcode
     * @return bool
     */
    public function set($msgtype = 1, $message, $serialize = TRUE, $blocking = FALSE, $errorcode = '')
    {
        return msg_send($this->message_queue, $msgtype, $message, $serialize, $blocking, $errorcode);
    }

    /**
     * 是否存在
     * @param int $key
     */
    public function exists($key)
    {
        return msg_queue_exists($key);
    }

    /**
     * 删除
     */
    public function delete()
    {
        return msg_remove_queue($this->message_queue);
    }

    /**
     * 消息队列数据结构信息
     * @return mixed
     * msg_perm.uid	队列所有者的uid。
     * msg_perm.gid	队列所有者的gid。
     * msg_perm.mode	队列的文件访问模式。
     * msg_stime	最后一条消息发送到队列的时间。
     * msg_rtime	从队列中接收到最后一条消息的时间。
     * msg_ctime	队列最后更改的时间。
     * msg_qnum	等待从队列中读取的消息数量。
     * msg_qbytes	一个消息队列中允许的最大字节数。在Linux上，可以通过/ proc / sys / kernel / msgmnb读取和修改该值 。
     * msg_lspid	将最后一条消息发送到队列的进程的PID。
     * msg_lrpid	从队列中接收最后一条消息的进程的PID。
     */
    public function info()
    {
        return msg_stat_queue($this->message_queue);
    }

    /**
     * 消息队列数据结构中设置信息
     * @param array $data 允许更改基础消息队列数据结构的msg_perm.uid，msg_perm.gid，msg_perm.mode和msg_qbytes字段的值 (修改msg_qbytes需要root权限)
     * @return bool
     */
    public function setInfo($data)
    {
        if (in_array('msg_qbytes', array_keys($data)))
        {
            $user = get_current_user();
            if ($user !== 'root')
                throw new \Exception('changing msg_qbytes needs root privileges');
        }

        return msg_set_queue($this->message_queue, $data);
    }

    /**
     * 获取队列当前堆积状态
     */
    public function size()
    {
        $status = $this->info();
        return $status['msg_qnum'];
    }

    private function checkFtok()
    {
        if ( ! function_exists('ftok'))
        {
            die('ftok extention is must !' . PHP_EOL);
        }
    }
}

$queue = new msgQueue();
$queue->create();
var_dump($queue->exists(1));
$queue->set(1, 'wisonlau');
$queue->set(1, 'wison');
var_dump($queue->info());
$msg = $queue->get(0, 1, 1024);
var_dump($msg);
$queue->delete();