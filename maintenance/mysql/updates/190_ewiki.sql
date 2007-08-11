CREATE TABLE `ewiki` (
  `pagename` varchar(160) NOT NULL,
  `version` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned default '0',
  `content` mediumtext,
  `author` varchar(100) default 'ewiki',
  `created` int(10) unsigned default '1168175948',
  `lastmodified` int(10) unsigned default '0',
  `refs` mediumtext,
  `meta` mediumtext,
  `hits` int(10) unsigned default '0',
  PRIMARY KEY  (`pagename`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ewiki` (`pagename`, `version`, `flags`, `content`, `author`, `created`, `lastmodified`, `refs`, `meta`, `hits`) VALUES ('FrontPage', 1, 1, 'Welcome to the Mytravelbook Wiki!\n\nJust start editing any page... :-)', 'Install (-)', 1168940979, 1168940979, '\n\n\n\n', 'a:1:{s:10:"user-agent";s:116:"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.0.4) Gecko/20060614 Fedora/1.5.0.4-1.2.fc5 Firefox/1.5.0.4 pango-text";}', 1);
