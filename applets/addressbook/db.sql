CREATE TABLE `addressbook_contacts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`first_name` varchar(150) DEFAULT NULL,
`last_name` varchar(150) DEFAULT NULL,
`title` varchar(150) DEFAULT NULL,
`company` varchar(250) DEFAULT NULL,
`email` varchar(250) DEFAULT NULL,
`phone` varchar(30) DEFAULT NULL,
`street` varchar(250) DEFAULT NULL,
`city` varchar(150) DEFAULT NULL,
`state` varchar(150) DEFAULT NULL,
`zip` varchar(30) DEFAULT NULL,
`country` varchar(150) DEFAULT NULL,
`website` varchar(250) DEFAULT NULL,
`bday` date DEFAULT NULL,
`notes` text,
`private` tinyint(1) DEFAULT '0',
`profile_img` varchar(250) DEFAULT NULL,
`data` text,
`created` datetime DEFAULT NULL,
`updated` datetime DEFAULT NULL,
`user_id` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
FULLTEXT KEY `GENERAL_SEARCH` (`first_name`,`last_name`,`email`,`phone`,`company`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `addressbook_groups` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(150) default NULL,
`color` varchar(30) default NULL,
`count` int(11) default NULL,
`created` datetime default NULL,
`user_id` int(11) default NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `addressbook_tags` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(150) default NULL,
`count` int(11) default NULL,
`created` datetime default NULL,
`user_id` int(11) default NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
