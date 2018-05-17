SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
ALTER TABLE `links`    ADD  `siteId`    VARCHAR( 100 ) NOT NULL DEFAULT '0' FIRST ;
ALTER TABLE `pages`    ADD `siteId`     VARCHAR( 100 ) NOT NULL DEFAULT '0' FIRST ;
ALTER TABLE `settings` ADD `siteId`     VARCHAR( 100 ) NOT NULL DEFAULT '0' FIRST ;
ALTER TABLE `users`    ADD `siteId`     VARCHAR( 100 ) NOT NULL DEFAULT '0' FIRST ;
ALTER TABLE `users` ADD `ActivateInfo`  TEXT NOT NULL ;
ALTER TABLE `pages` ADD `pageURL`       TEXT NOT NULL ;
ALTER TABLE `pages` ADD `menuTXT`       TEXT NOT NULL ;
ALTER TABLE `pages` ADD `last-updated`  TEXT NOT NULL ;
ALTER TABLE `pages` ADD `design`      int(1) NOT NULL default '1' ;
ALTER TABLE `pages` CHANGE `metaKeywords` `meta-keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `metaDescription` `meta-description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `plugins` (
  `siteId` int(100) NOT NULL default '0',
  `plugin` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `name` varchar(200) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `value` text character set utf8 collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `stat` (
  `siteId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `pageId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `ip` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `browser` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `date` text character set utf8 collate utf8_unicode_ci NOT NULL,
  FULLTEXT KEY `browser` (`browser`),
  FULLTEXT KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
