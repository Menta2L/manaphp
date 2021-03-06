<?php
namespace ManaPHP\Message\Queue\Adapter;

use ManaPHP\Component;
use ManaPHP\Message\Queue\Adapter\Redis\Exception as RedisException;
use ManaPHP\Message\QueueInterface;

/**
 * Class ManaPHP\Message\Queue\Adapter\Redis
 *
 * @package messageQueue\adapter
 *
 * @property \Redis $messageQueueRedis
 */
class Redis extends Component implements QueueInterface
{
    /**
     * @var string
     */
    protected $_prefix;

    /**
     * @var int[]
     */
    protected $_priorities = [self::PRIORITY_HIGHEST, self::PRIORITY_NORMAL, self::PRIORITY_LOWEST];

    /**
     * @var array[]
     */
    protected $_topicKeys = [];

    /**
     * Redis constructor.
     *
     * @param string|array $options
     */
    public function __construct($options = [])
    {
        if (is_string($options)) {
            $options = ['prefix' => $options];
        }

        if (isset($options['prefix'])) {
            $this->_prefix = $options['prefix'];
        }

        if (isset($options['priorities'])) {
            $this->_priorities = (array)$options['priorities'];
        }
    }

    /**
     * @param \ManaPHP\DiInterface $dependencyInjector
     *
     * @return static
     */
    public function setDependencyInjector($dependencyInjector)
    {
        parent::setDependencyInjector($dependencyInjector);
        $this->_dependencyInjector->setAliases('redis', 'messageQueueRedis');

        if ($this->_prefix === null) {
            $this->_prefix = $this->_dependencyInjector->configure->appID . ':message_queue:';
        }

        return $this;
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;

        return $this;
    }

    /**
     * @param string $topic
     * @param string $body
     * @param int    $priority
     *
     * @throws \ManaPHP\Message\Queue\Adapter\Redis\Exception
     */
    public function push($topic, $body, $priority = self::PRIORITY_NORMAL)
    {
        if (!in_array($priority, $this->_priorities, true)) {
            throw new RedisException('`:priority` priority of `:topic is invalid`', ['priority' => $priority, 'topic' => $topic]);
        }

        $this->messageQueueRedis->lPush($this->_prefix . $topic . ':' . $priority, $body);
    }

    /**
     * @param string $topic
     * @param int    $timeout
     *
     * @return string|false
     */
    public function pop($topic, $timeout = PHP_INT_MAX)
    {
        if (!isset($this->_topicKeys[$topic])) {
            $keys = [];
            foreach ($this->_priorities as $priority) {
                $keys[] = $this->_prefix . $topic . ':' . $priority;
            }

            $this->_topicKeys[$topic] = $keys;
        }
        if ($timeout === 0) {
            foreach ($this->_topicKeys[$topic] as $key) {
                $r = $this->messageQueueRedis->rPop($key);
                if ($r !== false) {
                    return $r;
                }
            }

            return false;
        } else {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $r = $this->messageQueueRedis->brPop($this->_topicKeys[$topic], $timeout);
            return isset($r[1]) ? $r[1] : false;
        }
    }

    /**
     * @param string $topic
     *
     * @return void
     */
    public function delete($topic)
    {
        foreach ($this->_priorities as $priority) {
            $this->messageQueueRedis->delete($this->_prefix . $topic . ':' . $priority);
        }
    }

    /**
     * @param string $topic
     * @param int    $priority
     *
     * @return         int
     */
    public function length($topic, $priority = null)
    {
        if ($priority === null) {
            $length = 0;
            /** @noinspection SuspiciousLoopInspection */
            foreach ($this->_priorities as $priority) {
                $length += $this->messageQueueRedis->lLen($this->_prefix . $topic . ':' . $priority);
            }

            return $length;
        } else {
            return $this->messageQueueRedis->lLen($this->_prefix . $topic . ':' . $priority);
        }
    }
}
