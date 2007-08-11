-- --------------------------------------------------------

-- 
-- Table structure for table `blog`
-- 

CREATE TABLE `blog` (
  `blog_id` int(10) unsigned NOT NULL default '0',
  `flags` blob NOT NULL,
  `blog_created` datetime NOT NULL,
  `user_id_foreign` int(10) unsigned default NULL,
  `country_id_foreign` int(10) unsigned default NULL,
  `trip_id_foreign` int(10) unsigned default NULL,
  PRIMARY KEY  (`blog_id`),
  KEY `user_id_foreign` (`user_id_foreign`),
  KEY `country_id_foreign` (`country_id_foreign`),
  KEY `trip_id_foreign` (`trip_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_categories`
-- 

CREATE TABLE `blog_categories` (
  `blog_category_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `user_id_foreign` int(10) unsigned default NULL,
  PRIMARY KEY  (`blog_category_id`),
  KEY `user_id_foreign` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_categories`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_comments`
-- 

CREATE TABLE `blog_comments` (
  `id` int(10) unsigned NOT NULL default '0',
  `blog_id_foreign` int(10) unsigned NOT NULL default '0',
  `user_id_foreign` int(10) unsigned NOT NULL default '0',
  `created` datetime NOT NULL,
  `title` varchar(75) NOT NULL default '',
  `text` mediumtext NOT NULL,
  KEY `id` (`id`),
  KEY `blog_id_foreign` (`blog_id_foreign`),
  KEY `user_id_foreign` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_comments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_data`
-- 

CREATE TABLE `blog_data` (
  `blog_id` int(10) unsigned NOT NULL default '0',
  `edited` datetime default NULL,
  `blog_title` varchar(255) NOT NULL default '',
  `blog_text` longtext NOT NULL,
  `blog_start` datetime default NULL COMMENT 'when this subtrip starts',
  `blog_end` datetime default NULL COMMENT 'when this subtrip ends',
  `blog_latitude` float NOT NULL default '0',
  `blog_longitude` float NOT NULL default '0',
  `blog_geonameid` int(10) unsigned default NULL,
  `blog_display_order` int(10) unsigned NOT NULL default '0' COMMENT 'TODO: only used if start and end are unset and user wants to reorder',
  KEY `blog_id` (`blog_id`),
  FULLTEXT KEY `blog_title` (`blog_title`),
  FULLTEXT KEY `blog_text` (`blog_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_tags`
-- 

CREATE TABLE `blog_tags` (
  `blog_tag_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`blog_tag_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_tags`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_to_category`
-- 

CREATE TABLE `blog_to_category` (
  `created` datetime NOT NULL,
  `blog_category_id_foreign` int(10) unsigned NOT NULL default '0',
  `blog_id_foreign` int(10) unsigned NOT NULL default '0',
  KEY `blog_category_id_foreign` (`blog_category_id_foreign`),
  KEY `blog_id_foreign` (`blog_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_to_category`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog_to_tag`
-- 

CREATE TABLE `blog_to_tag` (
  `blog_id_foreign` int(10) unsigned NOT NULL default '0',
  `blog_tag_id_foreign` int(10) unsigned NOT NULL default '0',
  KEY `blog_tag_id_foreign` (`blog_tag_id_foreign`),
  KEY `blog_id_foreign` (`blog_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `blog_to_tag`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cal_event_to_tag`
-- 

CREATE TABLE `cal_event_to_tag` (
  `tag_id` int(10) unsigned default NULL,
  `event_id` int(10) unsigned default NULL,
  KEY `tag_id` (`tag_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cal_event_to_tag`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cal_eventdata`
-- 

CREATE TABLE `cal_eventdata` (
  `event_id` int(10) unsigned default NULL,
  `title` varchar(255) NOT NULL,
  `link` text,
  `description` text,
  KEY `event_id` (`event_id`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cal_eventdata`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cal_events`
-- 

CREATE TABLE `cal_events` (
  `event_id` int(10) unsigned NOT NULL,
  `user_id_foreign` int(10) unsigned default NULL COMMENT 'Owner of event',
  `from` datetime NOT NULL,
  `to` datetime default NULL,
  `type` int(2) unsigned NOT NULL default '0' COMMENT 'visibility or other options',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`event_id`),
  KEY `member_id` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cal_events`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cal_tags`
-- 

CREATE TABLE `cal_tags` (
  `id` int(10) unsigned NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cal_tags`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `gallery`
-- 

CREATE TABLE `gallery` (
  `id` int(10) unsigned NOT NULL,
  `user_id_foreign` int(10) unsigned NOT NULL,
  `flags` blob NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id_foreign` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gallery`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `gallery_items`
-- 

CREATE TABLE `gallery_items` (
  `id` int(10) unsigned NOT NULL,
  `user_id_foreign` int(10) unsigned NOT NULL,
  `file` varchar(40) NOT NULL,
  `original` varchar(255) NOT NULL,
  `flags` blob NOT NULL,
  `mimetype` varchar(75) NOT NULL,
  `width` int(5) unsigned NOT NULL,
  `height` int(5) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file` (`file`),
  KEY `user_id_foreign` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gallery_items`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `gallery_items_to_gallery`
-- 

CREATE TABLE `gallery_items_to_gallery` (
  `item_id_foreign` int(10) unsigned NOT NULL,
  `gallery_id_foreign` int(10) unsigned NOT NULL,
  KEY `item_id_foreign` (`item_id_foreign`),
  KEY `gallery_id_foreign` (`gallery_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gallery_items_to_gallery`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `geonames_cache`
-- 

CREATE TABLE `geonames_cache` (
  `geonameid` int(10) unsigned NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `name` varchar(200) NOT NULL,
  `population` int(10) unsigned NOT NULL,
  `fk_countrycode` char(2) NOT NULL,
  `fk_admincode` char(2) default NULL,
  PRIMARY KEY  (`geonameid`),
  KEY `fk_countrycode` (`fk_countrycode`),
  KEY `fk_admincode` (`fk_admincode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `geonames_cache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `geonames_countries`
-- 

CREATE TABLE `geonames_countries` (
  `iso_alpha2` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `continent` enum('AF','AN','AS','EU','NA','OC','SA') NOT NULL,
  `languages` varchar(128) NOT NULL,
  PRIMARY KEY  (`iso_alpha2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `geonames_countries`
-- 

INSERT INTO `geonames_countries` VALUES ('AD', 'Andorra', 'EU', 'ca,fr-AD');
INSERT INTO `geonames_countries` VALUES ('AE', 'United Arab Emirates', 'AS', 'ar-AE');
INSERT INTO `geonames_countries` VALUES ('AF', 'Afghanistan', 'AS', 'fa-AF,ps,ug,uz-AF');
INSERT INTO `geonames_countries` VALUES ('AG', 'Antigua and Barbuda', 'NA', 'en-AG');
INSERT INTO `geonames_countries` VALUES ('AI', 'Anguilla', 'NA', 'en-AI');
INSERT INTO `geonames_countries` VALUES ('AL', 'Albania', 'EU', 'sq');
INSERT INTO `geonames_countries` VALUES ('AM', 'Armenia', 'AS', 'hy');
INSERT INTO `geonames_countries` VALUES ('AN', 'Netherlands Antilles', 'NA', 'nl-AN');
INSERT INTO `geonames_countries` VALUES ('AO', 'Angola', 'AF', 'kg,kj,pt-AO');
INSERT INTO `geonames_countries` VALUES ('AQ', 'Antarctica', 'AN', '');
INSERT INTO `geonames_countries` VALUES ('AR', 'Argentina', 'SA', 'es-AR,cy-AR,gn');
INSERT INTO `geonames_countries` VALUES ('AS', 'American Samoa', 'OC', 'en-AS,sm');
INSERT INTO `geonames_countries` VALUES ('AT', 'Austria', 'EU', 'de-AT,hu');
INSERT INTO `geonames_countries` VALUES ('AU', 'Australia', 'OC', 'en-AU,YI');
INSERT INTO `geonames_countries` VALUES ('AW', 'Aruba', 'NA', 'nl-AW');
INSERT INTO `geonames_countries` VALUES ('AX', 'Aland Islands', 'EU', 'sv-AX');
INSERT INTO `geonames_countries` VALUES ('AZ', 'Azerbaijan', 'AS', 'av,az,os');
INSERT INTO `geonames_countries` VALUES ('BA', 'Bosnia and Herzegovina', 'EU', 'bs,hr-BA,sr-BA');
INSERT INTO `geonames_countries` VALUES ('BB', 'Barbados', 'NA', 'en-BB');
INSERT INTO `geonames_countries` VALUES ('BD', 'Bangladesh', 'AS', 'bn-BD');
INSERT INTO `geonames_countries` VALUES ('BE', 'Belgium', 'EU', 'fr-BE,nl-BE,de-BE,en-BE,wa,yi');
INSERT INTO `geonames_countries` VALUES ('BF', 'Burkina Faso', 'AF', 'bm,fr-BF,ha');
INSERT INTO `geonames_countries` VALUES ('BG', 'Bulgaria', 'EU', 'bg,cu,tr-BG');
INSERT INTO `geonames_countries` VALUES ('BH', 'Bahrain', 'AS', 'ar-BH');
INSERT INTO `geonames_countries` VALUES ('BI', 'Burundi', 'AF', 'fr-BI,rn');
INSERT INTO `geonames_countries` VALUES ('BJ', 'Benin', 'AF', 'fr-BJ');
INSERT INTO `geonames_countries` VALUES ('BM', 'Bermuda', 'NA', 'en-BM');
INSERT INTO `geonames_countries` VALUES ('BN', 'Brunei', 'AS', 'en-BN,ms-BN');
INSERT INTO `geonames_countries` VALUES ('BO', 'Bolivia', 'SA', 'ay,es-BO');
INSERT INTO `geonames_countries` VALUES ('BR', 'Brazil', 'SA', 'pt-BR');
INSERT INTO `geonames_countries` VALUES ('BS', 'Bahamas', 'NA', 'en-BS');
INSERT INTO `geonames_countries` VALUES ('BT', 'Bhutan', 'AS', 'dz,ne');
INSERT INTO `geonames_countries` VALUES ('BV', 'Bouvet Island', 'AN', '');
INSERT INTO `geonames_countries` VALUES ('BW', 'Botswana', 'AF', 'en-BW,sn,tn-BW');
INSERT INTO `geonames_countries` VALUES ('BY', 'Belarus', 'EU', 'be,cu,yi');
INSERT INTO `geonames_countries` VALUES ('BZ', 'Belize', 'NA', 'en-BZ');
INSERT INTO `geonames_countries` VALUES ('CA', 'Canada', 'NA', 'en-CA,fr-CA,cr,iu,oj,yi');
INSERT INTO `geonames_countries` VALUES ('CC', 'Cocos (Keeling) Islands', 'AS', 'ms-CC');
INSERT INTO `geonames_countries` VALUES ('CD', 'Congo (Kinshasa)', 'AF', 'fr-CD,kg,ln-CD,lu');
INSERT INTO `geonames_countries` VALUES ('CF', 'Central African Republic', 'AF', 'fr-CF,sg');
INSERT INTO `geonames_countries` VALUES ('CG', 'Congo (Brazzaville)', 'AF', 'fr-CG,kg,ln-CG');
INSERT INTO `geonames_countries` VALUES ('CH', 'Switzerland', 'EU', 'de-CH,fr-CH,it-CH,rm');
INSERT INTO `geonames_countries` VALUES ('CI', 'Ivory Coast', 'AF', 'ak,bm,fr-CI');
INSERT INTO `geonames_countries` VALUES ('CK', 'Cook Islands', 'OC', 'en-CK');
INSERT INTO `geonames_countries` VALUES ('CL', 'Chile', 'SA', 'ay,es-CL');
INSERT INTO `geonames_countries` VALUES ('CM', 'Cameroon', 'AF', 'en-CM,fr-CM');
INSERT INTO `geonames_countries` VALUES ('CN', 'China', 'AS', 'bo,i,ii,za,zh-CN');
INSERT INTO `geonames_countries` VALUES ('CO', 'Colombia', 'SA', 'es-CO');
INSERT INTO `geonames_countries` VALUES ('CR', 'Costa Rica', 'NA', 'es-CR');
INSERT INTO `geonames_countries` VALUES ('CS', 'Serbia And Montenegro', 'EU', 'cu,hu,sq,sr');
INSERT INTO `geonames_countries` VALUES ('CU', 'Cuba', 'NA', 'es-CU');
INSERT INTO `geonames_countries` VALUES ('CV', 'Cape Verde', 'AF', 'pt-CV');
INSERT INTO `geonames_countries` VALUES ('CX', 'Christmas Island', 'AS', 'ms-CC');
INSERT INTO `geonames_countries` VALUES ('CY', 'Cyprus', 'AS', 'el-CY,tr-CY');
INSERT INTO `geonames_countries` VALUES ('CZ', 'Czech Republic', 'EU', 'cs');
INSERT INTO `geonames_countries` VALUES ('DE', 'Germany', 'EU', 'de,de-DE,de-AT,de-CH,da-DE,dsb,fy-DE,hsb,lb,nds,wen,yi');
INSERT INTO `geonames_countries` VALUES ('DJ', 'Djibouti', 'AF', 'aa-DJ,fr-DJ,so-DJ');
INSERT INTO `geonames_countries` VALUES ('DK', 'Denmark', 'EU', 'da-DK,de-DK');
INSERT INTO `geonames_countries` VALUES ('DM', 'Dominica', 'NA', 'en-DM');
INSERT INTO `geonames_countries` VALUES ('DO', 'Dominican Republic', 'NA', 'es-DO');
INSERT INTO `geonames_countries` VALUES ('DZ', 'Algeria', 'AF', 'ar-DZ');
INSERT INTO `geonames_countries` VALUES ('EC', 'Ecuador', 'SA', 'es-EC');
INSERT INTO `geonames_countries` VALUES ('EE', 'Estonia', 'EU', 'et,yi');
INSERT INTO `geonames_countries` VALUES ('EG', 'Egypt', 'AF', 'ar-EG');
INSERT INTO `geonames_countries` VALUES ('EH', 'Western Sahara', 'AF', '');
INSERT INTO `geonames_countries` VALUES ('ER', 'Eritrea', 'AF', 'aa-ER,byn,en-ER,gez-ER,ti-ER,tig');
INSERT INTO `geonames_countries` VALUES ('ES', 'Spain', 'EU', 'es-ES,ca,an,eu,gl');
INSERT INTO `geonames_countries` VALUES ('ET', 'Ethiopia', 'AF', 'aa-ET,am,en-ET,gez-ET,om-ET,sid,so-ET,ti-ET,wal');
INSERT INTO `geonames_countries` VALUES ('FI', 'Finland', 'EU', 'fi-FI,smn,sv-FI');
INSERT INTO `geonames_countries` VALUES ('FJ', 'Fiji', 'OC', 'en-FJ,fj');
INSERT INTO `geonames_countries` VALUES ('FK', 'Falkland Islands', 'SA', 'en-FK');
INSERT INTO `geonames_countries` VALUES ('FM', 'Micronesia', 'OC', 'en-FM');
INSERT INTO `geonames_countries` VALUES ('FO', 'Faroe Islands', 'EU', 'da-FO,fo');
INSERT INTO `geonames_countries` VALUES ('FR', 'France', 'EU', 'fr-FR,de-FR,br,co,oc');
INSERT INTO `geonames_countries` VALUES ('GA', 'Gabon', 'AF', 'fr-GA');
INSERT INTO `geonames_countries` VALUES ('GB', 'United Kingdom', 'EU', 'en,en-GB,cy-GB,fr-GB,ga-GB,gd,gv,kw');
INSERT INTO `geonames_countries` VALUES ('GD', 'Grenada', 'NA', 'en-GD');
INSERT INTO `geonames_countries` VALUES ('GE', 'Georgia', 'AS', 'ab,ka,os');
INSERT INTO `geonames_countries` VALUES ('GF', 'French Guiana', 'SA', 'fr-GF');
INSERT INTO `geonames_countries` VALUES ('GG', 'Guernsey', 'EU', 'en');
INSERT INTO `geonames_countries` VALUES ('GH', 'Ghana', 'AF', 'ak,ee,en-GH,tw');
INSERT INTO `geonames_countries` VALUES ('GI', 'Gibraltar', 'EU', 'en-GI');
INSERT INTO `geonames_countries` VALUES ('GL', 'Greenland', 'NA', 'da-GL,kl');
INSERT INTO `geonames_countries` VALUES ('GM', 'Gambia', 'AF', 'bm,en-GM,wo');
INSERT INTO `geonames_countries` VALUES ('GN', 'Guinea', 'AF', 'fr-GN');
INSERT INTO `geonames_countries` VALUES ('GP', 'Guadeloupe', 'NA', 'fr-GP');
INSERT INTO `geonames_countries` VALUES ('GQ', 'Equatorial Guinea', 'AF', 'es-GQ');
INSERT INTO `geonames_countries` VALUES ('GR', 'Greece', 'EU', 'el-GR');
INSERT INTO `geonames_countries` VALUES ('GS', 'South Georgia and the South Sandwich Islands', 'AN', '');
INSERT INTO `geonames_countries` VALUES ('GT', 'Guatemala', 'NA', 'es-GT');
INSERT INTO `geonames_countries` VALUES ('GU', 'Guam', 'OC', 'ch-GU,en-GU');
INSERT INTO `geonames_countries` VALUES ('GW', 'Guinea-Bissau', 'AF', 'pt-GW');
INSERT INTO `geonames_countries` VALUES ('GY', 'Guyana', 'SA', 'en-GY');
INSERT INTO `geonames_countries` VALUES ('HK', 'Hong Kong S.A.R., China', 'AS', 'en-HK,zh,zh-HK');
INSERT INTO `geonames_countries` VALUES ('HM', 'Heard Island and McDonald Islands', 'AN', '');
INSERT INTO `geonames_countries` VALUES ('HN', 'Honduras', 'NA', 'es-HN');
INSERT INTO `geonames_countries` VALUES ('HR', 'Croatia', 'EU', 'hr-HR,it-HR');
INSERT INTO `geonames_countries` VALUES ('HT', 'Haiti', 'NA', 'ht,fr-HT');
INSERT INTO `geonames_countries` VALUES ('HU', 'Hungary', 'EU', 'de-HU,hu-HU,sk-HU,sr-HU');
INSERT INTO `geonames_countries` VALUES ('ID', 'Indonesia', 'AS', 'id,jv,su');
INSERT INTO `geonames_countries` VALUES ('IE', 'Ireland', 'EU', 'en-IE,ga-IE');
INSERT INTO `geonames_countries` VALUES ('IL', 'Israel', 'AS', 'ar-IL,en-IL,he,yi');
INSERT INTO `geonames_countries` VALUES ('IM', 'Isle of Man', 'EU', 'en');
INSERT INTO `geonames_countries` VALUES ('IN', 'India', 'AS', 'en-IN,ar-IN,as,bh,bn-IN,gu,hi,kn,kok,ks,ml,mr,ne,or,pa,pi,sa,sd-IN,ta-IN,te,ur-IN');
INSERT INTO `geonames_countries` VALUES ('IO', 'British Indian Ocean Territory', 'AS', 'en-IO');
INSERT INTO `geonames_countries` VALUES ('IQ', 'Iraq', 'AS', 'ar-IQ,ku');
INSERT INTO `geonames_countries` VALUES ('IR', 'Iran', 'AS', 'ae,fa-IR,ku');
INSERT INTO `geonames_countries` VALUES ('IS', 'Iceland', 'EU', 'is');
INSERT INTO `geonames_countries` VALUES ('IT', 'Italy', 'EU', 'it-IT,co,de-IT,fr-IT,sc');
INSERT INTO `geonames_countries` VALUES ('JE', 'Jersey', 'EU', 'en');
INSERT INTO `geonames_countries` VALUES ('JM', 'Jamaica', 'NA', 'en-JM');
INSERT INTO `geonames_countries` VALUES ('JO', 'Jordan', 'AS', 'ar-JO');
INSERT INTO `geonames_countries` VALUES ('JP', 'Japan', 'AS', 'ja');
INSERT INTO `geonames_countries` VALUES ('KE', 'Kenya', 'AF', 'en-KE,ki,om-KE,so-KE,sw-KE');
INSERT INTO `geonames_countries` VALUES ('KG', 'Kyrgyzstan', 'AS', 'ky,ug');
INSERT INTO `geonames_countries` VALUES ('KH', 'Cambodia', 'AS', 'km');
INSERT INTO `geonames_countries` VALUES ('KI', 'Kiribati', 'OC', 'en-KI');
INSERT INTO `geonames_countries` VALUES ('KM', 'Comoros', 'AF', 'fr-KM');
INSERT INTO `geonames_countries` VALUES ('KN', 'Saint Kitts and Nevis', 'NA', 'en-KN');
INSERT INTO `geonames_countries` VALUES ('KP', 'North Korea', 'AS', 'ko-KP');
INSERT INTO `geonames_countries` VALUES ('KR', 'South Korea', 'AS', 'ko-KR');
INSERT INTO `geonames_countries` VALUES ('KW', 'Kuwait', 'AS', 'ar-KW');
INSERT INTO `geonames_countries` VALUES ('KY', 'Cayman Islands', 'NA', 'en-KY');
INSERT INTO `geonames_countries` VALUES ('KZ', 'Kazakhstan', 'AS', 'av,kk,os,ug');
INSERT INTO `geonames_countries` VALUES ('LA', 'Laos', 'AS', 'lo');
INSERT INTO `geonames_countries` VALUES ('LB', 'Lebanon', 'AS', 'ar-LB,fr-LB');
INSERT INTO `geonames_countries` VALUES ('LC', 'Saint Lucia', 'NA', 'en-LC');
INSERT INTO `geonames_countries` VALUES ('LI', 'Liechtenstein', 'EU', 'de-LI');
INSERT INTO `geonames_countries` VALUES ('LK', 'Sri Lanka', 'AS', 'si');
INSERT INTO `geonames_countries` VALUES ('LR', 'Liberia', 'AF', 'en-LR');
INSERT INTO `geonames_countries` VALUES ('LS', 'Lesotho', 'AF', 'en-LS,st,xh');
INSERT INTO `geonames_countries` VALUES ('LT', 'Lithuania', 'EU', 'lt,yi');
INSERT INTO `geonames_countries` VALUES ('LU', 'Luxembourg', 'EU', 'de-LU,fr-LU,lb');
INSERT INTO `geonames_countries` VALUES ('LV', 'Latvia', 'EU', 'lv,yi');
INSERT INTO `geonames_countries` VALUES ('LY', 'Libya', 'AF', 'ar-LY');
INSERT INTO `geonames_countries` VALUES ('MA', 'Morocco', 'AF', 'ar-MA');
INSERT INTO `geonames_countries` VALUES ('MC', 'Monaco', 'EU', 'fr-MC');
INSERT INTO `geonames_countries` VALUES ('MD', 'Moldova', 'EU', 'mo,ro,tr,uk,yi');
INSERT INTO `geonames_countries` VALUES ('MG', 'Madagascar', 'AF', 'fr-MG,mg');
INSERT INTO `geonames_countries` VALUES ('MH', 'Marshall Islands', 'OC', 'en-MH,mh');
INSERT INTO `geonames_countries` VALUES ('MK', 'Macedonia', 'EU', 'cu,mk');
INSERT INTO `geonames_countries` VALUES ('ML', 'Mali', 'AF', 'bm,fr-ML');
INSERT INTO `geonames_countries` VALUES ('MM', 'Myanmar', 'AS', 'my');
INSERT INTO `geonames_countries` VALUES ('MN', 'Mongolia', 'AS', 'mn');
INSERT INTO `geonames_countries` VALUES ('MO', 'Macao S.A.R., China', 'AS', 'zh,zh-MO');
INSERT INTO `geonames_countries` VALUES ('MP', 'Northern Mariana Islands', 'OC', 'ch-MP,en-MP');
INSERT INTO `geonames_countries` VALUES ('MQ', 'Martinique', 'NA', 'fr-MQ');
INSERT INTO `geonames_countries` VALUES ('MR', 'Mauritania', 'AF', 'ar-MR,wo');
INSERT INTO `geonames_countries` VALUES ('MS', 'Montserrat', 'NA', 'en-MS');
INSERT INTO `geonames_countries` VALUES ('MT', 'Malta', 'EU', 'en-MT,mt');
INSERT INTO `geonames_countries` VALUES ('MU', 'Mauritius', 'AF', 'en-MU');
INSERT INTO `geonames_countries` VALUES ('MV', 'Maldives', 'AS', 'dv');
INSERT INTO `geonames_countries` VALUES ('MW', 'Malawi', 'AF', 'en-MW,ny');
INSERT INTO `geonames_countries` VALUES ('MX', 'Mexico', 'NA', 'es-MX');
INSERT INTO `geonames_countries` VALUES ('MY', 'Malaysia', 'AS', 'jv,ms-MY');
INSERT INTO `geonames_countries` VALUES ('MZ', 'Mozambique', 'AF', 'pt-MZ,sn');
INSERT INTO `geonames_countries` VALUES ('NA', 'Namibia', 'AF', 'en-NA,hz,ng');
INSERT INTO `geonames_countries` VALUES ('NC', 'New Caledonia', 'OC', 'fr-NC');
INSERT INTO `geonames_countries` VALUES ('NE', 'Niger', 'AF', 'ff-NE,fr-NE,ha,kr');
INSERT INTO `geonames_countries` VALUES ('NF', 'Norfolk Island', 'OC', 'en-NF');
INSERT INTO `geonames_countries` VALUES ('NG', 'Nigeria', 'AF', 'en-NG,ff-NG,ha,ig,kr,yo');
INSERT INTO `geonames_countries` VALUES ('NI', 'Nicaragua', 'NA', 'es-NI');
INSERT INTO `geonames_countries` VALUES ('NL', 'Netherlands', 'EU', 'nl-NL,fy-NL,li');
INSERT INTO `geonames_countries` VALUES ('NO', 'Norway', 'EU', 'no,nb,nn');
INSERT INTO `geonames_countries` VALUES ('NP', 'Nepal', 'AS', 'ne');
INSERT INTO `geonames_countries` VALUES ('NR', 'Nauru', 'OC', 'en-NR,na');
INSERT INTO `geonames_countries` VALUES ('NU', 'Niue', 'OC', 'en-NU');
INSERT INTO `geonames_countries` VALUES ('NZ', 'New Zealand', 'OC', 'en-NZ,mi');
INSERT INTO `geonames_countries` VALUES ('OM', 'Oman', 'AS', 'ar-OM');
INSERT INTO `geonames_countries` VALUES ('PA', 'Panama', 'NA', 'es-PA');
INSERT INTO `geonames_countries` VALUES ('PE', 'Peru', 'SA', 'ay,es-PE,qu');
INSERT INTO `geonames_countries` VALUES ('PF', 'French Polynesia', 'OC', 'fr-PF,ty');
INSERT INTO `geonames_countries` VALUES ('PG', 'Papua New Guinea', 'OC', 'en-PG,ho');
INSERT INTO `geonames_countries` VALUES ('PH', 'Philippines', 'AS', 'en-PH,tl');
INSERT INTO `geonames_countries` VALUES ('PK', 'Pakistan', 'AS', 'en-PK,sd-PK,ur-PK');
INSERT INTO `geonames_countries` VALUES ('PL', 'Poland', 'EU', 'de-PL,pl,yi');
INSERT INTO `geonames_countries` VALUES ('PM', 'Saint Pierre and Miquelon', 'NA', 'fr-PM');
INSERT INTO `geonames_countries` VALUES ('PN', 'Pitcairn', 'OC', 'en-PN');
INSERT INTO `geonames_countries` VALUES ('PR', 'Puerto Rico', 'NA', 'en-PR,es-PR');
INSERT INTO `geonames_countries` VALUES ('PS', 'Palestinian Territory', 'AS', 'ar-PS');
INSERT INTO `geonames_countries` VALUES ('PT', 'Portugal', 'EU', 'pt-PT');
INSERT INTO `geonames_countries` VALUES ('PW', 'Palau', 'OC', 'en-PW');
INSERT INTO `geonames_countries` VALUES ('PY', 'Paraguay', 'SA', 'es-PY,gn');
INSERT INTO `geonames_countries` VALUES ('QA', 'Qatar', 'AS', 'ar-QA');
INSERT INTO `geonames_countries` VALUES ('RE', 'Reunion', 'AF', 'fr-RE');
INSERT INTO `geonames_countries` VALUES ('RO', 'Romania', 'EU', 'ro,cu,hu,yi');
INSERT INTO `geonames_countries` VALUES ('RU', 'Russia', 'EU', 'ru-RU,av,ba,ce,cu,cv,kv,os,tt,yi');
INSERT INTO `geonames_countries` VALUES ('RW', 'Rwanda', 'AF', 'en-RW,fr-RW,rw');
INSERT INTO `geonames_countries` VALUES ('SA', 'Saudi Arabia', 'AS', 'ar-SA');
INSERT INTO `geonames_countries` VALUES ('SB', 'Solomon Islands', 'OC', 'en-SB');
INSERT INTO `geonames_countries` VALUES ('SC', 'Seychelles', 'AF', 'en-SC,fr-SC');
INSERT INTO `geonames_countries` VALUES ('SD', 'Sudan', 'AF', 'ar-SD,din,ha');
INSERT INTO `geonames_countries` VALUES ('SE', 'Sweden', 'EU', 'sv-SE,se,fi-SE,sma,sme');
INSERT INTO `geonames_countries` VALUES ('SG', 'Singapore', 'AS', 'bn-SG,en-SG,ms-SG,ta-SG,zh-SG');
INSERT INTO `geonames_countries` VALUES ('SH', 'Saint Helena', 'AF', 'en-SH');
INSERT INTO `geonames_countries` VALUES ('SI', 'Slovenia', 'EU', 'hu-SI,it-SI');
INSERT INTO `geonames_countries` VALUES ('SJ', 'Svalbard and Jan Mayen', 'EU', '');
INSERT INTO `geonames_countries` VALUES ('SK', 'Slovakia', 'EU', 'sk,hu');
INSERT INTO `geonames_countries` VALUES ('SL', 'Sierra Leone', 'AF', 'en-SL');
INSERT INTO `geonames_countries` VALUES ('SM', 'San Marino', 'EU', 'it-SM');
INSERT INTO `geonames_countries` VALUES ('SN', 'Senegal', 'AF', 'ff-SN,wo');
INSERT INTO `geonames_countries` VALUES ('SO', 'Somalia', 'AF', 'ar-SO,en-SO,so-SO');
INSERT INTO `geonames_countries` VALUES ('SR', 'Suriname', 'SA', 'jv,nl-SR');
INSERT INTO `geonames_countries` VALUES ('ST', 'Sao Tome and Principe', 'AF', 'pt-ST');
INSERT INTO `geonames_countries` VALUES ('SV', 'El Salvador', 'NA', 'es-SV');
INSERT INTO `geonames_countries` VALUES ('SY', 'Syria', 'AS', 'ar-SY,syr');
INSERT INTO `geonames_countries` VALUES ('SZ', 'Swaziland', 'AF', 'en-SZ,ss-SZ');
INSERT INTO `geonames_countries` VALUES ('TC', 'Turks and Caicos Islands', 'NA', 'en-TC');
INSERT INTO `geonames_countries` VALUES ('TD', 'Chad', 'AF', 'ar-TD,fr-TD');
INSERT INTO `geonames_countries` VALUES ('TF', 'French Southern Territories', 'AN', '');
INSERT INTO `geonames_countries` VALUES ('TG', 'Togo', 'AF', 'ee,fr-TG,ha');
INSERT INTO `geonames_countries` VALUES ('TH', 'Thailand', 'AS', 'th,si');
INSERT INTO `geonames_countries` VALUES ('TJ', 'Tajikistan', 'AS', 'os,tg,ug');
INSERT INTO `geonames_countries` VALUES ('TK', 'Tokelau', 'OC', 'en-TK');
INSERT INTO `geonames_countries` VALUES ('TL', 'East Timor', 'OC', 'pt-TL');
INSERT INTO `geonames_countries` VALUES ('TM', 'Turkmenistan', 'AS', 'os,tk');
INSERT INTO `geonames_countries` VALUES ('TN', 'Tunisia', 'AF', 'ar-TN');
INSERT INTO `geonames_countries` VALUES ('TO', 'Tonga', 'OC', 'en-TO,to');
INSERT INTO `geonames_countries` VALUES ('TR', 'Turkey', 'AS', 'tr-TR,ab,av,ku,ug');
INSERT INTO `geonames_countries` VALUES ('TT', 'Trinidad and Tobago', 'NA', 'en-TT');
INSERT INTO `geonames_countries` VALUES ('TV', 'Tuvalu', 'OC', 'gil,tvl');
INSERT INTO `geonames_countries` VALUES ('TW', 'Taiwan', 'AS', 'zh,zh-TW,zh-min-nan');
INSERT INTO `geonames_countries` VALUES ('TZ', 'Tanzania', 'AF', 'sw-TZ');
INSERT INTO `geonames_countries` VALUES ('UA', 'Ukraine', 'EU', 'ru-UA,ab,cu,hu,os,pl,ro,uk,yi');
INSERT INTO `geonames_countries` VALUES ('UG', 'Uganda', 'AF', 'en-UG,lg');
INSERT INTO `geonames_countries` VALUES ('UM', 'United States Minor Outlying Islands', 'OC', 'en-UM');
INSERT INTO `geonames_countries` VALUES ('US', 'United States', 'NA', 'en-US,es-US,haw,ik,nv,oj,yi');
INSERT INTO `geonames_countries` VALUES ('UY', 'Uruguay', 'SA', 'es-UY');
INSERT INTO `geonames_countries` VALUES ('UZ', 'Uzbekistan', 'AS', 'uz,uz-UZ,os,ug');
INSERT INTO `geonames_countries` VALUES ('VA', 'Vatican', 'EU', 'fr,it,la');
INSERT INTO `geonames_countries` VALUES ('VC', 'Saint Vincent and the Grenadines', 'NA', 'en-VC');
INSERT INTO `geonames_countries` VALUES ('VE', 'Venezuela', 'SA', 'es-VE');
INSERT INTO `geonames_countries` VALUES ('VG', 'British Virgin Islands', 'NA', 'en-VG');
INSERT INTO `geonames_countries` VALUES ('VI', 'U.S. Virgin Islands', 'NA', 'en-VI');
INSERT INTO `geonames_countries` VALUES ('VN', 'Vietnam', 'AS', 'vi');
INSERT INTO `geonames_countries` VALUES ('VU', 'Vanuatu', 'OC', 'bi,en-VU,fr-VU');
INSERT INTO `geonames_countries` VALUES ('WF', 'Wallis and Futuna', 'OC', 'fr-WF');
INSERT INTO `geonames_countries` VALUES ('WS', 'Samoa', 'OC', 'en-WS,sm');
INSERT INTO `geonames_countries` VALUES ('YE', 'Yemen', 'AS', 'ar-YE');
INSERT INTO `geonames_countries` VALUES ('YT', 'Mayotte', 'AF', 'fr-YT');
INSERT INTO `geonames_countries` VALUES ('ZA', 'South Africa', 'AF', 'en-ZA,af,nr,ss-ZA,tn-ZA,ts,ve,xh,yi,zu');
INSERT INTO `geonames_countries` VALUES ('ZM', 'Zambia', 'AF', 'en-ZM');
INSERT INTO `geonames_countries` VALUES ('ZW', 'Zimbabwe', 'AF', 'en-ZW,nd,sn,ve,zu');
INSERT INTO `geonames_countries` VALUES ('ME', 'Montenegro', 'EU', 'Montenegro');
INSERT INTO `geonames_countries` VALUES ('RS', 'Serbia', 'EU', 'cu,hu,sq,sr');

-- --------------------------------------------------------

-- 
-- Table structure for table `geonames_admincodes`
-- 

CREATE TABLE `geonames_admincodes` (
  `code` char(5) NOT NULL,
  `country_code` char(2) NOT NULL,
  `admin_code` char(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY  (`code`),
  KEY `country_code` (`country_code`),
  KEY `admin_code` (`admin_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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

-- 
-- Dumping data for table `message`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_apps`
-- 

CREATE TABLE `mod_user_apps` (
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(75) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_apps`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_auth`
-- 

CREATE TABLE `mod_user_auth` (
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(75) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_auth`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_authgroups`
-- 

CREATE TABLE `mod_user_authgroups` (
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(75) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_authgroups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_authrights`
-- 

CREATE TABLE `mod_user_authrights` (
  `auth_id` int(10) unsigned default NULL,
  `right_id` int(10) unsigned default NULL,
  KEY `auth_id` (`auth_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_authrights`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_groupauth`
-- 

CREATE TABLE `mod_user_groupauth` (
  `auth_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  KEY `user_id` (`auth_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_groupauth`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_grouprights`
-- 

CREATE TABLE `mod_user_grouprights` (
  `group_id` int(10) unsigned default NULL,
  `right_id` int(10) unsigned default NULL,
  KEY `group_id` (`group_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_grouprights`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_implications`
-- 

CREATE TABLE `mod_user_implications` (
  `right_id` int(10) unsigned default NULL,
  `implies_id` int(10) unsigned default NULL,
  KEY `right_id` (`right_id`),
  KEY `implies_id` (`implies_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_implications`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mod_user_rights`
-- 

CREATE TABLE `mod_user_rights` (
  `id` int(10) unsigned NOT NULL,
  `app_id` int(10) unsigned default NULL,
  `name` varchar(75) NOT NULL,
  `has_implied` int(1) unsigned NOT NULL default '0',
  `level` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `app_id` (`app_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `mod_user_rights`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `trip`
-- 

CREATE TABLE `trip` (
  `trip_id` int(10) unsigned NOT NULL,
  `trip_options` blob NOT NULL,
  `trip_touched` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id_foreign` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`trip_id`),
  KEY `user_id_foreign` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;

-- 
-- Dumping data for table `trip`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `trip_data`
-- 

CREATE TABLE `trip_data` (
  `trip_id` int(10) unsigned NOT NULL,
  `edited` datetime NOT NULL default '0000-00-00 00:00:00',
  `trip_name` varchar(255) NOT NULL,
  `trip_text` mediumtext NOT NULL,
  `trip_descr` longtext NOT NULL,
  KEY `trip_id` (`trip_id`),
  FULLTEXT KEY `trip_name` (`trip_name`),
  FULLTEXT KEY `trip_text` (`trip_text`),
  FULLTEXT KEY `trip_descr` (`trip_descr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `trip_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `trip_to_gallery`
-- 

CREATE TABLE `trip_to_gallery` (
  `trip_id_foreign` int(10) unsigned NOT NULL,
  `gallery_id_foreign` int(10) unsigned NOT NULL,
  KEY `trip_id_foreign` (`trip_id_foreign`),
  KEY `gallery_id_foreign` (`gallery_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `trip_to_gallery`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL default '0',
  `auth_id` int(10) unsigned default NULL,
  `handle` varchar(255) NOT NULL default '',
  `email` varchar(75) NOT NULL,
  `pw` text character set utf8 collate utf8_bin NOT NULL,
  `active` int(1) unsigned NOT NULL default '0',
  `lastlogin` datetime default NULL,
  `location` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`auth_id`),
  KEY `handle` (`handle`),
  KEY `email` (`email`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_friends`
-- 

CREATE TABLE `user_friends` (
  `user_id_foreign` int(10) unsigned NOT NULL,
  `user_id_foreign_friend` int(10) unsigned NOT NULL,
  KEY `user_id_foreign` (`user_id_foreign`),
  KEY `user_id_foreign_friend` (`user_id_foreign_friend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_friends`
-- 


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

-- 
-- Dumping data for table `user_inbox`
-- 


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
-- Dumping data for table `user_outbox`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_settings`
-- 

CREATE TABLE `user_settings` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `setting` varchar(25) NOT NULL default '',
  `value` text,
  `valueint` int(11) default NULL,
  `valuedate` datetime default NULL,
  KEY `member_id` (`user_id`),
  KEY `setting` (`setting`),
  KEY `valueint` (`valueint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_settings`
-- 

-- 
-- Table structure for table `forums_posts`
-- 

CREATE TABLE `forums_posts` (
  `postid` int(10) unsigned NOT NULL auto_increment,
  `threadid` mediumint(8) unsigned default NULL,
  `authorid` int(10) unsigned NOT NULL,
  `create_time` datetime NOT NULL,
  `message` text NOT NULL,
  `last_edittime` datetime default NULL,
  `last_editorid` int(10) unsigned default NULL,
  `edit_count` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`postid`),
  KEY `authorid` (`authorid`),
  KEY `last_editorid` (`last_editorid`),
  KEY `threadid` (`threadid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `forums_tags`
-- 

CREATE TABLE `forums_tags` (
  `tagid` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(64) NOT NULL,
  PRIMARY KEY  (`tagid`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Data for table `forums_tags`
--

INSERT INTO `forums_tags` (`tagid`, `tag`) VALUES ('1', 'Help and Support');


-- 
-- Table structure for table `forums_threads`
-- 

CREATE TABLE `forums_threads` (
  `threadid` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `first_postid` int(10) unsigned default NULL,
  `last_postid` int(10) unsigned default NULL,
  `replies` smallint(5) unsigned NOT NULL default '0',
  `views` mediumint(8) unsigned NOT NULL default '0',
  `geonameid` int(10) unsigned default NULL,
  `admincode` char(2) default NULL,
  `countrycode` char(2) default NULL,
  `continent` enum('AF','AN','AS','EU','NA','OC','SA') default NULL,
  `tag1` int(10) unsigned default NULL,
  `tag2` int(10) unsigned default NULL,
  `tag3` int(10) unsigned default NULL,
  `tag4` int(10) unsigned default NULL,
  `tag5` int(10) unsigned default NULL,
  PRIMARY KEY  (`threadid`),
  KEY `first_postid` (`first_postid`),
  KEY `last_postid` (`last_postid`),
  KEY `geonameid` (`geonameid`),
  KEY `tag1` (`tag1`),
  KEY `tag2` (`tag2`),
  KEY `tag3` (`tag3`),
  KEY `tag4` (`tag4`),
  KEY `tag5` (`tag5`),
  KEY `admincode` (`admincode`),
  KEY `countrycode` (`countrycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- 
-- Table structure for table `ewiki`
-- 

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

-- 
-- Dumping data for table `ewiki`
-- 

INSERT INTO `ewiki` (`pagename`, `version`, `flags`, `content`, `author`, `created`, `lastmodified`, `refs`, `meta`, `hits`) VALUES ('FrontPage', 1, 1, 'Welcome to the myTravelbook Wiki!\r\n\r\nBrowse the Wiki by continent:\r\n* [Africa]\r\n* [Asia]\r\n* [Europe]\r\n* [NorthAmercia]\r\n* [SouthAmerica]\r\n* [Oceania]\r\n* [Antarctica]', 'Initial Version', 1167609600, 1167609600, '\r\nAfrica\r\nAsia\r\nEurope\r\nNorthAmercia\r\nSouthAmerica\r\nOceania\r\nAntarctica\r\n\r\n', '-', 1),
('Africa', 1, 1, '* [Algeria]\r\n* [Angola]\r\n* [Benin]\r\n* [Botswana]\r\n* [BurkinaFaso]\r\n* [Burundi]\r\n* [Cameroon]\r\n* [CapeVerde]\r\n* [CentralAfricanRepublic]\r\n* [Chad]\r\n* [Comoros]\r\n* [Congo]\r\n* [Djibouti]\r\n* [Egypt]\r\n* [EquatorialGuinea]\r\n* [Eritrea]\r\n* [Ethiopia]\r\n* [Gabon]\r\n* [Gambia]\r\n* [Ghana]\r\n* [Guinea]\r\n* [Guinea-Bissau]\r\n* [IvoryCoast]\r\n* [Kenya]\r\n* [Lesotho]\r\n* [Liberia]\r\n* [Libya]\r\n* [Madagascar]\r\n* [Malawi]\r\n* [Mali]\r\n* [Mauritania]\r\n* [Mauritius]\r\n* [Mayotte]\r\n* [Morocco]\r\n* [Mozambique]\r\n* [Namibia]\r\n* [Niger]\r\n* [Nigeria]\r\n* [Reunion]\r\n* [Rwanda]\r\n* [SaintHelena]\r\n* [SaoTomeAndPrincipe]\r\n* [Senegal]\r\n* [Seychelles]\r\n* [SierraLeone]\r\n* [Somalia]\r\n* [SouthAfrica]\r\n* [Sudan]\r\n* [Swaziland]\r\n* [Tanzania]\r\n* [Togo]\r\n* [Tunisia]\r\n* [Uganda]\r\n* [WesternSahara]\r\n* [Zambia]\r\n* [Zimbabwe]', 'Initial Version', 1167609600, 1167609600, 'Algeria\r\nAngola\r\nBenin\r\nBotswana\r\nBurkinaFaso\r\nBurundi\r\nCameroon\r\nCapeVerde\r\nCentralAfricanRepublic\r\nChad\r\nComoros\r\nCongo\r\nDjibouti\r\nEgypt\r\nEquatorialGuinea\r\nEritrea\r\nEthiopia\r\nGabon\r\nGambia\r\nGhana\r\nGuinea\r\nGuinea-Bissau\r\nIvoryCoast\r\nKenya\r\nLesotho\r\nLiberia\r\nLibya\r\nMadagascar\r\nMalawi\r\nMali\r\nMauritania\r\nMauritius\r\nMayotte\r\nMorocco\r\nMozambique\r\nNamibia\r\nNiger\r\nNigeria\r\nReunion\r\nRwanda\r\nSaintHelena\r\nSaoTomeAndPrincipe\r\nSenegal\r\nSeychelles\r\nSierraLeone\r\nSomalia\r\nSouthAfrica\r\nSudan\r\nSwaziland\r\nTanzania\r\nTogo\r\nTunisia\r\nUganda\r\nWesternSahara\r\nZambia\r\nZimbabwe\r\n\r\n', '-', 1),
('Asia', 1, 1, '* [Afghanistan]\r\n* [Armenia]\r\n* [Azerbaijan]\r\n* [Bahrain]\r\n* [Bangladesh]\r\n* [Bhutan]\r\n* [BritishIndianOceanTerritory]\r\n* [Brunei]\r\n* [Cambodia]\r\n* [China]\r\n* [ChristmasIsland]\r\n* [CocosIslands]\r\n* [Cyprus]\r\n* [Georgia]\r\n* [HongKong]\r\n* [India]\r\n* [Indonesia]\r\n* [Iran]\r\n* [Iraq]\r\n* [Israel]\r\n* [Japan]\r\n* [Jordan]\r\n* [Kazakhstan]\r\n* [Kuwait]\r\n* [Kyrgyzstan]\r\n* [Laos]\r\n* [Lebanon]\r\n* [Macao]\r\n* [Malaysia]\r\n* [Maldives]\r\n* [Mongolia]\r\n* [Myanmar]\r\n* [Nepal]\r\n* [NorthKorea]\r\n* [Oman]\r\n* [Pakistan]\r\n* [PalestinianTerritory]\r\n* [Philippines]\r\n* [Qatar]\r\n* [SaudiArabia]\r\n* [Singapore]\r\n* [SouthKorea]\r\n* [SriLanka]\r\n* [Syria]\r\n* [Taiwan]\r\n* [Tajikistan]\r\n* [Thailand]\r\n* [Turkey]\r\n* [Turkmenistan]\r\n* [UnitedArabEmirates]\r\n* [Uzbekistan]\r\n* [Vietnam]\r\n* [Yemen]', 'Initial Version', 1167609600, 1167609600, '\r\nAfghanistan\r\nArmenia\r\nAzerbaijan\r\nBahrain\r\nBangladesh\r\nBhutan\r\nBritishIndianOceanTerritory\r\nBrunei\r\nCambodia\r\nChina\r\nChristmasIsland\r\nCocosIslands\r\nCyprus\r\nGeorgia\r\nHongKong\r\nIndia\r\nIndonesia\r\nIran\r\nIraq\r\nIsrael\r\nJapan\r\nJordan\r\nKazakhstan\r\nKuwait\r\nKyrgyzstan\r\nLaos\r\nLebanon\r\nMacao\r\nMalaysia\r\nMaldives\r\nMongolia\r\nMyanmar\r\nNepal\r\nNorthKorea\r\nOman\r\nPakistan\r\nPalestinianTerritory\r\nPhilippines\r\nQatar\r\nSaudiArabia\r\nSingapore\r\nSouthKorea\r\nSriLanka\r\nSyria\r\nTaiwan\r\nTajikistan\r\nThailand\r\nTurkey\r\nTurkmenistan\r\nUnitedArabEmirates\r\nUzbekistan\r\nVietnam\r\nYemen\r\n\r\n', '-', 1),
('Europe', 1, 1, '* [AlandIslands]\r\n* [Albania]\r\n* [Andorra]\r\n* [Austria]\r\n* [Belarus]\r\n* [Belgium]\r\n* [BosniaAndHerzegovina]\r\n* [Bulgaria]\r\n* [Croatia]\r\n* [CzechRepublic]\r\n* [Denmark]\r\n* [Estonia]\r\n* [FaroeIslands]\r\n* [Finland]\r\n* [France]\r\n* [Germany]\r\n* [Gibraltar]\r\n* [Greece]\r\n* [Guernsey]\r\n* [Hungary]\r\n* [Iceland]\r\n* [Ireland]\r\n* [IsleOfMan]\r\n* [Italy]\r\n* [Jersey]\r\n* [Latvia]\r\n* [Liechtenstein]\r\n* [Lithuania]\r\n* [Luxembourg]\r\n* [Macedonia]\r\n* [Malta]\r\n* [Moldova]\r\n* [Monaco]\r\n* [Montenegro]\r\n* [Netherlands]\r\n* [Norway]\r\n* [Poland]\r\n* [Portugal]\r\n* [Romania]\r\n* [Russia]\r\n* [SanMarino]\r\n* [Serbia]\r\n* [SerbiaAndMontenegro]\r\n* [Slovakia]\r\n* [Slovenia]\r\n* [Spain]\r\n* [SvalbardAndJanMayen]\r\n* [Sweden]\r\n* [Switzerland]\r\n* [Ukraine]\r\n* [UnitedKingdom]\r\n* [Vatican]', 'Initial Version', 1167609600, 1167609600, '\r\nAlandIslands\r\nAlbania\r\nAndorra\r\nAustria\r\nBelarus\r\nBelgium\r\nBosniaAndHerzegovina\r\nBulgaria\r\nCroatia\r\nCzechRepublic\r\nDenmark\r\nEstonia\r\nFaroeIslands\r\nFinland\r\nFrance\r\nGermany\r\nGibraltar\r\nGreece\r\nGuernsey\r\nHungary\r\nIceland\r\nIreland\r\nIsleOfMan\r\nItaly\r\nJersey\r\nLatvia\r\nLiechtenstein\r\nLithuania\r\nLuxembourg\r\nMacedonia\r\nMalta\r\nMoldova\r\nMonaco\r\nMontenegro\r\nNetherlands\r\nNorway\r\nPoland\r\nPortugal\r\nRomania\r\nRussia\r\nSanMarino\r\nSerbia\r\nSerbiaAndMontenegro\r\nSlovakia\r\nSlovenia\r\nSpain\r\nSvalbardAndJanMayen\r\nSweden\r\nSwitzerland\r\nUkraine\r\nUnitedKingdom\r\nVatican\r\n\r\n', '-', 1),
('NorthAmercia', 1, 1, '* [Anguilla]\r\n* [AntiguaAndBarbuda]\r\n* [Aruba]\r\n* [Bahamas]\r\n* [Barbados]\r\n* [Belize]\r\n* [Bermuda]\r\n* [BritishVirginIslands]\r\n* [Canada]\r\n* [CaymanIslands]\r\n* [CostaRica]\r\n* [Cuba]\r\n* [Dominica]\r\n* [DominicanRepublic]\r\n* [ElSalvador]\r\n* [Greenland]\r\n* [Grenada]\r\n* [Guadeloupe]\r\n* [Guatemala]\r\n* [Haiti]\r\n* [Honduras]\r\n* [Jamaica]\r\n* [Martinique]\r\n* [Mexico]\r\n* [Montserrat]\r\n* [NetherlandsAntilles]\r\n* [Nicaragua]\r\n* [Panama]\r\n* [PuertoRico]\r\n* [SaintKittsAndNevis]\r\n* [SaintLucia]\r\n* [SaintPierreAndMiquelon]\r\n* [SaintVincentAndTheGrenadines]\r\n* [TrinidadAndTobago]\r\n* [TurksAndCaicosIslands]\r\n* [U.S.VirginIslands]\r\n* [UnitedStates]', 'Initial Version', 1167609600, 1167609600, '\r\nAnguilla\r\nAntiguaAndBarbuda\r\nAruba\r\nBahamas\r\nBarbados\r\nBelize\r\nBermuda\r\nBritishVirginIslands\r\nCanada\r\nCaymanIslands\r\nCostaRica\r\nCuba\r\nDominica\r\nDominicanRepublic\r\nElSalvador\r\nGreenland\r\nGrenada\r\nGuadeloupe\r\nGuatemala\r\nHaiti\r\nHonduras\r\nJamaica\r\nMartinique\r\nMexico\r\nMontserrat\r\nNetherlandsAntilles\r\nNicaragua\r\nPanama\r\nPuertoRico\r\nSaintKittsAndNevis\r\nSaintLucia\r\nSaintPierreAndMiquelon\r\nSaintVincentAndTheGrenadines\r\nTrinidadAndTobago\r\nTurksAndCaicosIslands\r\nU.S.VirginIslands\r\nUnitedStates\r\n\r\n', '-', 1),
('SouthAmerica', 1, 1, '* [Argentina]\n* [Bolivia]\n* [Brazil]\n* [Chile]\n* [Colombia]\n* [Ecuador]\n* [FalklandIslands]\n* [FrenchGuiana]\n* [Guyana]\n* [Paraguay]\n* [Peru]\n* [Suriname]\n* [Uruguay]\n* [Venezuela]', 'Initial Version', 1167609600, 1167609600, '\n\nArgentina\nBolivia\nBrazil\nChile\nColombia\nEcuador\nFalklandIslands\nFrenchGuiana\nGuyana\nParaguay\nPeru\nSuriname\nUruguay\nVenezuela\n\n', '-', 1),
('Oceania', 1, 1, '* [AmericanSamoa]\n* [Australia]\n* [CookIslands]\n* [EastTimor]\n* [Fiji]\n* [FrenchPolynesia]\n* [Guam]\n* [Kiribati]\n* [MarshallIslands]\n* [Micronesia]\n* [Nauru]\n* [NewCaledonia]\n* [NewZealand]\n* [Niue]\n* [NorfolkIsland]\n* [NorthernMarianaIslands]\n* [Palau]\n* [PapuaNewGuinea]\n* [Pitcairn]\n* [Samoa]\n* [SolomonIslands]\n* [Tokelau]\n* [Tonga]\n* [Tuvalu]\n* [UnitedStatesMinorOutlyingIslands]\n* [Vanuatu]\n* [WallisAndFutuna]', 'Initial Version', 1167609600, 1167609600, '\n\nAmericanSamoa\nAustralia\nCookIslands\nEastTimor\nFiji\nFrenchPolynesia\nGuam\nKiribati\nMarshallIslands\nMicronesia\nNauru\nNewCaledonia\nNewZealand\nNiue\nNorfolkIsland\nNorthernMarianaIslands\nPalau\nPapuaNewGuinea\nPitcairn\nSamoa\nSolomonIslands\nTokelau\nTonga\nTuvalu\nUnitedStatesMinorOutlyingIslands\nVanuatu\nWallisAndFutuna\n\n', '-', 1),
('Antarctica', 1, 1, '* [Antarctica]\n* [BouvetIsland]\n* [FrenchSouthernTerritories]\n* [HeardIslandAndMcDonaldIslands]\n* [SouthGeorgiaAndTheSouthSandwichIslands]', 'Initial Version', 1167609600, 1167609600, '\n\nAntarctica\nBouvetIsland\nFrenchSouthernTerritories\nHeardIslandAndMcDonaldIslands\nSouthGeorgiaAndTheSouthSandwichIslands\n\n', '-', 1),
('Algeria', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Angola', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Benin', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Botswana', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('BurkinaFaso', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Burundi', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Cameroon', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CapeVerde', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CentralAfricanRepublic', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Chad', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Comoros', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Congo', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Djibouti', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Egypt', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('EquatorialGuinea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Eritrea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Ethiopia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Gabon', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Gambia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Ghana', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guinea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guinea-Bissau', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('IvoryCoast', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Kenya', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Lesotho', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Liberia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Libya', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Madagascar', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Malawi', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mali', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mauritania', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mauritius', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mayotte', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Morocco', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mozambique', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Namibia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Niger', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Nigeria', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Reunion', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Rwanda', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaintHelena', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaoTomeAndPrincipe', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Senegal', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Seychelles', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SierraLeone', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Somalia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SouthAfrica', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Sudan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Swaziland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tanzania', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Togo', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tunisia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Uganda', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('WesternSahara', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Zambia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Zimbabwe', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Afghanistan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Armenia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Azerbaijan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bahrain', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bangladesh', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bhutan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('BritishIndianOceanTerritory', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Brunei', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Cambodia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('China', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('ChristmasIsland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CocosIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Cyprus', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Georgia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('HongKong', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('India', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Indonesia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Iran', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Iraq', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Israel', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Japan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Jordan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Kazakhstan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Kuwait', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Kyrgyzstan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Laos', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Lebanon', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Macao', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Malaysia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Maldives', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mongolia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Myanmar', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Nepal', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NorthKorea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Oman', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Pakistan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('PalestinianTerritory', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Philippines', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Qatar', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaudiArabia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Singapore', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SouthKorea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SriLanka', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Syria', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Taiwan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tajikistan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Thailand', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Turkey', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Turkmenistan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('UnitedArabEmirates', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Uzbekistan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Vietnam', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Yemen', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('AlandIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Albania', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Andorra', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Austria', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Belarus', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Belgium', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('BosniaAndHerzegovina', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bulgaria', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Croatia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CzechRepublic', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Denmark', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Estonia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('FaroeIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Finland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('France', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Germany', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Gibraltar', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Greece', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guernsey', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Hungary', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Iceland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Ireland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('IsleOfMan', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Italy', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Jersey', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Latvia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Liechtenstein', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Lithuania', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Luxembourg', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Macedonia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Malta', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Moldova', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Montenegro', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Monaco', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Netherlands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Norway', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Poland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Portugal', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Romania', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Russia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SanMarino', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Serbia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SerbiaAndMontenegro', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Slovakia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Slovenia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Spain', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1);
INSERT INTO `ewiki` (`pagename`, `version`, `flags`, `content`, `author`, `created`, `lastmodified`, `refs`, `meta`, `hits`) VALUES ('SvalbardAndJanMayen', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Sweden', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Switzerland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Ukraine', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('UnitedKingdom', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Vatican', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Anguilla', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('AntiguaAndBarbuda', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Aruba', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bahamas', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Barbados', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Belize', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bermuda', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('BritishVirginIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Canada', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CaymanIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CostaRica', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Cuba', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Dominica', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('DominicanRepublic', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('ElSalvador', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Greenland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Grenada', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guadeloupe', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guatemala', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Haiti', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Honduras', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Jamaica', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Martinique', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Mexico', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Montserrat', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NetherlandsAntilles', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Nicaragua', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Panama', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('PuertoRico', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaintKittsAndNevis', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaintLucia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaintPierreAndMiquelon', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SaintVincentAndTheGrenadines', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('TrinidadAndTobago', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('TurksAndCaicosIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('U.S.VirginIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('UnitedStates', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Argentina', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Bolivia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Brazil', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Chile', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Colombia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Ecuador', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('FalklandIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('FrenchGuiana', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guyana', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Paraguay', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Peru', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Suriname', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Uruguay', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Venezuela', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('AmericanSamoa', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Australia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('CookIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('EastTimor', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Fiji', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('FrenchPolynesia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Guam', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Kiribati', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('MarshallIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Micronesia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Nauru', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NewCaledonia', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NewZealand', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Niue', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NorfolkIsland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('NorthernMarianaIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Palau', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('PapuaNewGuinea', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Pitcairn', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Samoa', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SolomonIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tokelau', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tonga', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Tuvalu', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('UnitedStatesMinorOutlyingIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Vanuatu', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('WallisAndFutuna', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('BouvetIsland', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('FrenchSouthernTerritories', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('HeardIslandAndMcDonaldIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('SouthGeorgiaAndTheSouthSandwichIslands', 1, 1, '!!Regions\n\n\n!!Cities\n\n\n!!Other Destinations\n\n\n!!Getting there\n\n\n!!Getting around\n\n\n!!What to do\n\n\n!!People\n\n\n!!Eat and Drink\n\n\n!!Accomodation\n\n\n!!Learn more', 'Initial Version', 1167609600, 1167609600, '\n\n\n\n', '-', 1),
('Congo(Brazzaville)', 1, 1, 'See [Congo]', 'Initial Version', 1167609600, 1167609600, '\n\nCongo\n\n', '-', 1),
('Congo(Kinshasa)', 1, 1, 'See [Congo]', 'Initial Version', 1167609600, 1167609600, '\n\nCongo\n\n', '-', 1),
('Cocos(Keeling)Islands', 1, 1, 'See [CocosIslands]', 'Initial Version', 1167609600, 1167609600, '\n\nCocosIslands\n\n', '-', 1),
('MacaoS.A.R.,China', 1, 1, 'See [Macao]', 'Initial Version', 1167609600, 1167609600, '\n\nMacao\n\n', '-', 1),
('HongKongS.A.R.,China', 1, 1, 'See [HongKong]', 'Initial Version', 1167609600, 1167609600, '\n\nHongKong\n\n', '-', 1);



-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `blog`
-- 
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `blog_categories`
-- 
ALTER TABLE `blog_categories`
  ADD CONSTRAINT `blog_categories_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `blog_comments`
-- 
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_ibfk_2` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`blog_id_foreign`) REFERENCES `blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `blog_to_category`
-- 
ALTER TABLE `blog_to_category`
  ADD CONSTRAINT `blog_to_category_ibfk_5` FOREIGN KEY (`blog_category_id_foreign`) REFERENCES `blog_categories` (`blog_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_to_category_ibfk_6` FOREIGN KEY (`blog_id_foreign`) REFERENCES `blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `blog_to_tag`
-- 
ALTER TABLE `blog_to_tag`
  ADD CONSTRAINT `blog_to_tag_ibfk_1` FOREIGN KEY (`blog_id_foreign`) REFERENCES `blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_to_tag_ibfk_2` FOREIGN KEY (`blog_tag_id_foreign`) REFERENCES `blog_tags` (`blog_tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `cal_event_to_tag`
-- 
ALTER TABLE `cal_event_to_tag`
  ADD CONSTRAINT `cal_event_to_tag_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `cal_events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cal_event_to_tag_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `cal_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `cal_events`
-- 
ALTER TABLE `cal_events`
  ADD CONSTRAINT `cal_events_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `gallery`
-- 
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `gallery_items`
-- 
ALTER TABLE `gallery_items`
  ADD CONSTRAINT `gallery_items_ibfk_2` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `gallery_items_to_gallery`
-- 
ALTER TABLE `gallery_items_to_gallery`
  ADD CONSTRAINT `gallery_items_to_gallery_ibfk_2` FOREIGN KEY (`gallery_id_foreign`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gallery_items_to_gallery_ibfk_1` FOREIGN KEY (`item_id_foreign`) REFERENCES `gallery_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `geonames_cache`
-- 
ALTER TABLE `geonames_cache`
  ADD CONSTRAINT `geonames_cache_ibfk_2` FOREIGN KEY (`fk_admincode`) REFERENCES `geonames_admincodes` (`admin_code`),
  ADD CONSTRAINT `geonames_cache_ibfk_1` FOREIGN KEY (`fk_countrycode`) REFERENCES `geonames_countries` (`iso_alpha2`);

-- 
-- Constraints for table `geonames_admincodes`
-- 
ALTER TABLE `geonames_admincodes`
  ADD CONSTRAINT `geonames_admincodes_ibfk_1` FOREIGN KEY (`country_code`) REFERENCES `geonames_countries` (`iso_alpha2`);

-- 
-- Constraints for table `mod_user_authrights`
-- 
ALTER TABLE `mod_user_authrights`
  ADD CONSTRAINT `mod_user_authrights_ibfk_1` FOREIGN KEY (`auth_id`) REFERENCES `mod_user_auth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mod_user_authrights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `mod_user_rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `mod_user_groupauth`
-- 
ALTER TABLE `mod_user_groupauth`
  ADD CONSTRAINT `mod_user_groupauth_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `mod_user_authgroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mod_user_groupauth_ibfk_3` FOREIGN KEY (`auth_id`) REFERENCES `mod_user_auth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `mod_user_grouprights`
-- 
ALTER TABLE `mod_user_grouprights`
  ADD CONSTRAINT `mod_user_grouprights_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `mod_user_authgroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mod_user_grouprights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `mod_user_rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `mod_user_implications`
-- 
ALTER TABLE `mod_user_implications`
  ADD CONSTRAINT `mod_user_implications_ibfk_1` FOREIGN KEY (`right_id`) REFERENCES `mod_user_rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mod_user_implications_ibfk_2` FOREIGN KEY (`implies_id`) REFERENCES `mod_user_rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `mod_user_rights`
-- 
ALTER TABLE `mod_user_rights`
  ADD CONSTRAINT `mod_user_rights_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `mod_user_apps` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `trip`
-- 
ALTER TABLE `trip`
  ADD CONSTRAINT `trip_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `trip_to_gallery`
-- 
ALTER TABLE `trip_to_gallery`
  ADD CONSTRAINT `trip_to_gallery_ibfk_2` FOREIGN KEY (`gallery_id_foreign`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `trip_to_gallery_ibfk_1` FOREIGN KEY (`trip_id_foreign`) REFERENCES `trip` (`trip_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `user`
-- 
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`location`) REFERENCES `geonames_cache` (`geonameid`),
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`auth_id`) REFERENCES `mod_user_auth` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 
-- Constraints for table `user_friends`
-- 
ALTER TABLE `user_friends`
  ADD CONSTRAINT `user_friends_ibfk_2` FOREIGN KEY (`user_id_foreign_friend`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_friends_ibfk_1` FOREIGN KEY (`user_id_foreign`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

-- 
-- Constraints for table `user_settings`
-- 
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `forums_posts`
-- 
ALTER TABLE `forums_posts`
  ADD CONSTRAINT `forums_posts_ibfk_7` FOREIGN KEY (`last_editorid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `forums_posts_ibfk_5` FOREIGN KEY (`threadid`) REFERENCES `forums_threads` (`threadid`),
  ADD CONSTRAINT `forums_posts_ibfk_6` FOREIGN KEY (`authorid`) REFERENCES `user` (`id`);

-- 
-- Constraints for table `forums_threads`
-- 
ALTER TABLE `forums_threads`
  ADD CONSTRAINT `forums_threads_ibfk_11` FOREIGN KEY (`first_postid`) REFERENCES `forums_posts` (`postid`),
  ADD CONSTRAINT `forums_threads_ibfk_12` FOREIGN KEY (`last_postid`) REFERENCES `forums_posts` (`postid`),
  ADD CONSTRAINT `forums_threads_ibfk_13` FOREIGN KEY (`geonameid`) REFERENCES `geonames_cache` (`geonameid`),
  ADD CONSTRAINT `forums_threads_ibfk_14` FOREIGN KEY (`admincode`) REFERENCES `geonames_admincodes` (`admin_code`),
  ADD CONSTRAINT `forums_threads_ibfk_15` FOREIGN KEY (`countrycode`) REFERENCES `geonames_countries` (`iso_alpha2`),
  ADD CONSTRAINT `forums_threads_ibfk_16` FOREIGN KEY (`tag1`) REFERENCES `forums_tags` (`tagid`),
  ADD CONSTRAINT `forums_threads_ibfk_17` FOREIGN KEY (`tag2`) REFERENCES `forums_tags` (`tagid`),
  ADD CONSTRAINT `forums_threads_ibfk_18` FOREIGN KEY (`tag3`) REFERENCES `forums_tags` (`tagid`),
  ADD CONSTRAINT `forums_threads_ibfk_19` FOREIGN KEY (`tag4`) REFERENCES `forums_tags` (`tagid`),
  ADD CONSTRAINT `forums_threads_ibfk_20` FOREIGN KEY (`tag5`) REFERENCES `forums_tags` (`tagid`);
