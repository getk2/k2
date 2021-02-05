CREATE TABLE IF NOT EXISTS `#__k2_attachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `itemID` int(11) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `titleAttribute` text NOT NULL,
    `hits` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_hits` (`hits`),
    KEY `idx_itemID` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_categories` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `description` text NOT NULL,
    `parent` int(11) DEFAULT '0',
    `extraFieldsGroup` int(11) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    `access` int(11) NOT NULL DEFAULT '0',
    `ordering` int(11) NOT NULL DEFAULT '0',
    `image` varchar(255) NOT NULL,
    `params` text NOT NULL,
    `trash` smallint(6) NOT NULL DEFAULT '0',
    `plugins` mediumtext NOT NULL,
    `language` char(7) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_access` (`access`),
    KEY `idx_category` (`published`,`access`,`trash`),
    KEY `idx_language` (`language`),
    KEY `idx_ordering` (`ordering`),
    KEY `idx_parent` (`parent`),
    KEY `idx_published` (`published`),
    KEY `idx_trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `itemID` int(11) NOT NULL,
    `userID` int(11) NOT NULL,
    `userName` varchar(255) NOT NULL,
    `commentDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `commentText` text NOT NULL,
    `commentEmail` varchar(255) NOT NULL,
    `commentURL` varchar(255) NOT NULL,
    `published` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_commentDate` (`commentDate`),
    KEY `idx_countComments` (`itemID`,`published`),
    KEY `idx_itemID` (`itemID`),
    KEY `idx_latestComments` (`published`,`commentDate`),
    KEY `idx_published` (`published`),
    KEY `idx_userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `value` text NOT NULL,
    `type` varchar(255) NOT NULL,
    `group` int(11) NOT NULL,
    `published` tinyint(4) NOT NULL,
    `ordering` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_group` (`group`),
    KEY `idx_published` (`published`),
    KEY `idx_ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `catid` int(11) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    `introtext` mediumtext NOT NULL,
    `fulltext` mediumtext NOT NULL,
    `video` text,
    `gallery` varchar(255) DEFAULT NULL,
    `extra_fields` mediumtext NOT NULL,
    `extra_fields_search` mediumtext NOT NULL,
    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by` int(11) NOT NULL DEFAULT '0',
    `created_by_alias` varchar(255) NOT NULL,
    `checked_out` int(10) unsigned NOT NULL,
    `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by` int(11) NOT NULL DEFAULT '0',
    `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
    `plugins` mediumtext NOT NULL,
    `language` char(7) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_access` (`access`),
    KEY `idx_catid` (`catid`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_created` (`created`),
    KEY `idx_featured_ordering` (`featured_ordering`),
    KEY `idx_featured` (`featured`),
    KEY `idx_hits` (`hits`),
    KEY `idx_item` (`published`,`publish_up`,`publish_down`,`trash`,`access`),
    KEY `idx_language` (`language`),
    KEY `idx_ordering` (`ordering`),
    KEY `idx_published` (`published`),
    KEY `idx_publish_down` (`publish_down`),
    KEY `idx_publish_up` (`publish_up`),
    KEY `idx_trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_log` (
    `status` int(11) NOT NULL,
    `response` text NOT NULL,
    `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_rating` (
    `itemID` int(11) NOT NULL DEFAULT '0',
    `rating_sum` int(11) unsigned NOT NULL DEFAULT '0',
    `rating_count` int(11) unsigned NOT NULL DEFAULT '0',
    `lastip` varchar(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`itemID`),
    KEY `idx_rating_sum` (`rating_sum`),
    KEY `idx_rating_count` (`rating_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_tags_xref` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tagID` int(11) NOT NULL,
    `itemID` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tagID` (`tagID`),
    KEY `idx_itemID` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `published` smallint(6) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_user_groups` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `permissions` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__k2_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userID` int(11) NOT NULL,
    `userName` varchar(255) DEFAULT NULL,
    `group` int(11) NOT NULL DEFAULT '0',
    `description` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `gender` enum('m','f','n') NOT NULL DEFAULT 'n',
    `url` varchar(255) DEFAULT NULL,
    `ip` varchar(45) NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `notes` text NOT NULL,
    `plugins` mediumtext NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_userName` (`userName`),
    KEY `idx_userID` (`userID`),
    KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
