
-- --------------------------------------------------------

-- 
-- Table structure for table `message`
-- 

CREATE TABLE `message` (
  `message_id` int(10) unsigned NOT NULL default '0',
  `sender_id_foreign` int(10) unsigned NOT NULL default '0',
  `recipients` varchar(255) NOT NULL default '' COMMENT 'comma separated user_ids',
  `subject` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `refcount` tinyint(3) unsigned NOT NULL default '0' COMMENT 'tracks how many users have this msg in their in/outbox, will be deleted if it reaches 0',
  PRIMARY KEY  (`message_id`),
  KEY `sender_id_foreign` (`sender_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_inbox`
-- 

CREATE TABLE `user_inbox` (
  `user_id_foreign` int(10) unsigned NOT NULL default '0',
  `message_id_foreign` int(10) unsigned NOT NULL default '0',
  `seen` tinyint(3) unsigned NOT NULL default '0',
  `replied` tinyint(3) unsigned NOT NULL default '0' COMMENT '1 if user sent a reply',
  KEY `user_id_foreign` (`user_id_foreign`),
  KEY `message_id_foreign` (`message_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_outbox`
-- 

CREATE TABLE `user_outbox` (
  `user_id_foreign` int(10) unsigned NOT NULL default '0',
  `message_id_foreign` int(10) unsigned NOT NULL default '0',
  KEY `user_id_foreign` (`user_id_foreign`),
  KEY `message_id_foreign` (`message_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `message`
-- 
-- 2006-11-24 12:22:14 rs 
-- Does not work (mysql-4.1.21), gives 150 error 'No index defined' but that's rubbish as there is an index..
-- http://dev.mysql.com/doc/refman/5.0/en/innodb-foreign-key-constraints.html
-- * table collations are the same as is the datatype.
-- 
-- ALTER TABLE `message`
--   ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`sender_id_foreign`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `user_inbox`
-- 
ALTER TABLE `user_inbox`
  ADD CONSTRAINT `user_inbox_ibfk_2` FOREIGN KEY (`message_id_foreign`) REFERENCES `message` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_inbox_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `user_outbox`
-- 
ALTER TABLE `user_outbox`
  ADD CONSTRAINT `user_outbox_ibfk_2` FOREIGN KEY (`message_id_foreign`) REFERENCES `message` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_outbox_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
        
