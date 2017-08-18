CREATE TABLE IF NOT EXISTS `#__k2_attachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `itemID` int(11) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `titleAttribute` text NOT NULL,
    `hits` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `hits` (`hits`),
    KEY `itemID` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_categories` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `parent` int(11) DEFAULT '0',
    `extraFieldsGroup` int(11) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    `access` int(11) NOT NULL DEFAULT '0',
    `ordering` int(11) NOT NULL DEFAULT '0',
    `image` varchar(255) NOT NULL,
    `params` text NOT NULL,
    `trash` smallint(6) NOT NULL DEFAULT '0',
    `plugins` text NOT NULL,
    `language` char(7) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `access` (`access`),
    KEY `category` (`published`,`access`,`trash`),
    KEY `language` (`language`),
    KEY `ordering` (`ordering`),
    KEY `parent` (`parent`),
    KEY `published` (`published`),
    KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `itemID` int(11) NOT NULL,
    `userID` int(11) NOT NULL,
    `userName` varchar(255) NOT NULL,
    `commentDate` datetime NOT NULL,
    `commentText` text NOT NULL,
    `commentEmail` varchar(255) NOT NULL,
    `commentURL` varchar(255) NOT NULL,
    `published` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `commentDate` (`commentDate`),
    KEY `countComments` (`itemID`,`published`),
    KEY `itemID` (`itemID`),
    KEY `latestComments` (`published`,`commentDate`),
    KEY `published` (`published`),
    KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `value` text NOT NULL,
    `type` varchar(255) NOT NULL,
    `group` int(11) NOT NULL,
    `published` tinyint(4) NOT NULL,
    `ordering` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `group` (`group`),
    KEY `published` (`published`),
    KEY `ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) DEFAULT NULL,
    `catid` int(11) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    `introtext` mediumtext NOT NULL,
    `fulltext` mediumtext NOT NULL,
    `video` text,
    `gallery` varchar(255) DEFAULT NULL,
    `extra_fields` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    `extra_fields_search` text NOT NULL,
    `created` datetime NOT NULL,
    `created_by` int(11) NOT NULL DEFAULT '0',
    `created_by_alias` varchar(255) NOT NULL,
    `checked_out` int(10) unsigned NOT NULL,
    `checked_out_time` datetime NOT NULL,
    `modified` datetime NOT NULL,
    `modified_by` int(11) NOT NULL DEFAULT '0',
    `publish_up` datetime NOT NULL,
    `publish_down` datetime NOT NULL,
    `trash` smallint(6) NOT NULL DEFAULT '0',
    `access` int(11) NOT NULL DEFAULT '0',
    `ordering` int(11) NOT NULL DEFAULT '0',
    `featured` smallint(6) NOT NULL DEFAULT '0',
    `featured_ordering` int(11) NOT NULL DEFAULT '0',
    `image_caption` text NOT NULL,
    `image_credits` varchar(255) NOT NULL,
    `video_caption` text NOT NULL,
    `video_credits` varchar(255) NOT NULL,
    `hits` int(10) unsigned NOT NULL,
    `params` text NOT NULL,
    `metadesc` text NOT NULL,
    `metadata` text NOT NULL,
    `metakey` text NOT NULL,
    `plugins` text NOT NULL,
    `language` char(7) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `access` (`access`),
    KEY `catid` (`catid`),
    KEY `created_by` (`created_by`),
    KEY `created` (`created`),
    KEY `featured_ordering` (`featured_ordering`),
    KEY `featured` (`featured`),
    KEY `hits` (`hits`),
    KEY `item` (`published`,`publish_up`,`publish_down`,`trash`,`access`),
    KEY `language` (`language`),
    KEY `ordering` (`ordering`),
    KEY `published` (`published`),
    KEY `publish_down` (`publish_down`),
    KEY `publish_up` (`publish_up`),
    KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_rating` (
    `itemID` int(11) NOT NULL DEFAULT '0',
    `rating_sum` int(11) unsigned NOT NULL DEFAULT '0',
    `rating_count` int(11) unsigned NOT NULL DEFAULT '0',
    `lastip` varchar(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`itemID`),
    KEY `rating_sum` (`rating_sum`),
    KEY `rating_count` (`rating_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_tags_xref` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tagID` int(11) NOT NULL,
    `itemID` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `tagID` (`tagID`),
    KEY `itemID` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userID` int(11) NOT NULL,
    `userName` varchar(255) DEFAULT NULL,
    `gender` enum('m','f') NOT NULL DEFAULT 'm',
    `description` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `url` varchar(255) DEFAULT NULL,
    `group` int(11) NOT NULL DEFAULT '0',
    `plugins` text NOT NULL,
    `ip` varchar(15) NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `notes` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `userID` (`userID`),
    KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_user_groups` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `permissions` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__k2_log` (
    `status` int(11) NOT NULL,
    `response` text NOT NULL,
    `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
