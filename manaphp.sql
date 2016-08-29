
# \ManaPHP\Task\Metadata\Adapter\Db\Model
CREATE TABLE `manaphp_task_metadata` (
  `id` char(32) NOT NULL,
  `key` char(128) NOT NULL,
  `value` varchar(4000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#\ManaPHP\Counter\Adapter\Db\Model
CREATE TABLE `manaphp_counter` (
  `hash` char(32) CHARACTER SET ascii NOT NULL,
  `type` varchar(255) NOT NULL,
  `id` varchar(255) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

#ManaPHP\Cache\Engine\Db\Model'
CREATE TABLE `manaphp_cache` (
  `hash` char(32) CHARACTER SET ascii NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `expired_time` int(11) NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8