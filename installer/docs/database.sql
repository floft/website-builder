SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `links` (
  `siteId` text NOT NULL,
  `linkId` text NOT NULL,
  `pageId` text NOT NULL,
  `membersPage` tinyint(1) NOT NULL default '0',
  FULLTEXT KEY `linkId` (`linkId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pages` (
  `siteId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `pageId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `pageURL` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `design` int(1) NOT NULL default '1',
  `pageName` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `pageContents` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `bodyextra` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `membersPage` tinyint(1) NOT NULL default '0',
  `meta-keywords` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `meta-description` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `last-updated` text character set utf8 collate utf8_unicode_ci NOT NULL,
  FULLTEXT KEY `pageContents` (`pageContents`),
  FULLTEXT KEY `pageName` (`pageName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `plugins` (
  `siteId` int(100) NOT NULL default '0',
  `plugin` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `name` varchar(200) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `value` text character set utf8 collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `questions` (
  `id` bigint(100) NOT NULL auto_increment,
  `cat` varchar(100) NOT NULL default '',
  `time` varchar(100) NOT NULL default '',
  `ip` text NOT NULL,
  `browser` text NOT NULL,
  `subject` text NOT NULL,
  `message` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  FULLTEXT KEY `subject` (`subject`,`message`),
  FULLTEXT KEY `message` (`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `settings` (
  `siteId` text NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `value` (`value`)
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

CREATE TABLE IF NOT EXISTS `users` (
  `siteId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `loginId` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `username` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `password` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `name` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `email` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `hobbies` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `profileText` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `member` varchar(10) character set utf8 collate utf8_unicode_ci NOT NULL,
  `newpassword` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `ActivateInfo` text NOT NULL,
  FULLTEXT KEY `loginId` (`loginId`),
  FULLTEXT KEY `profileText` (`profileText`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;