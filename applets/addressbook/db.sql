CREATE TABLE IF NOT EXISTS `addressbook_contacts` (
`id` int(11) NOT NULL auto_increment,
`first_name` varchar(150) default NULL,
`last_name` varchar(150) default NULL,
`title` varchar(150) default NULL,
`company` varchar(250) default NULL,
`email` varchar(250) default NULL,
`phone` varchar(30) default NULL,
`street` varchar(250) default NULL,
`city` varchar(150) default NULL,
`state` varchar(150) default NULL,
`zip` varchar(30) default NULL,
`country` varchar(150) default NULL,
`website` varchar(250) default NULL,
`bday` date default NULL,
`notes` text,
`private` tinyint(1) default '0',
`profile_img` varchar(250) default NULL,
`data` text,
`created` datetime default NULL,
`updated` datetime default NULL,
`user_id` int(11) default NULL,
PRIMARY KEY  (`id`)
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
