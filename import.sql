SET NAMES utf8mb4;
SET CHARACTER SET 'utf8mb4';
SET collation_connection = 'utf8mb4_general_ci';
SET FOREIGN_KEY_CHECKS=0;

/* Delete existing tables first */
DROP TABLE IF EXISTS `geo__alternate_names`;
DROP TABLE IF EXISTS `geo__top_admin_units`;
DROP TABLE IF EXISTS `geo__lesser_admin_units`;
DROP TABLE IF EXISTS `geo__countries`;
DROP TABLE IF EXISTS `geo__names`;

CREATE TABLE `geo__names` (
    `geonameId` int(11) NOT NULL,
    `name` varchar(200) DEFAULT NULL,
    `latitude` decimal(10,7) DEFAULT NULL,
    `longitude` decimal(10,7) DEFAULT NULL,
    `fclass` char(1) DEFAULT NULL,
    `fcode` varchar(10) DEFAULT NULL,
    `country` varchar(2) NOT NULL,
    `admin1` varchar(20) DEFAULT NULL,
    `admin2` varchar(80) DEFAULT NULL,
    `admin3` varchar(20) DEFAULT NULL,
    `admin4` varchar(20) DEFAULT NULL,
    `countryId` int(11) NOT NULL,
    `admin1Id` int(11) DEFAULT NULL,
    `admin2Id` int(11) DEFAULT NULL,
    `admin3Id` int(11) DEFAULT NULL,
    `admin4Id` int(11) DEFAULT NULL,
    `population` int(11) DEFAULT NULL,
    `moddate` date DEFAULT NULL,
    PRIMARY KEY (`geonameId`)
) DEFAULT CHARACTER SET 'utf8mb4';

CREATE TABLE `geo__top_admin_units` (
    `geonameId` int(11) NOT NULL,
    `code` varchar(20) DEFAULT NULL,
    `name` varchar(200) DEFAULT NULL,
    PRIMARY KEY (`geonameId`)
) DEFAULT CHARACTER SET 'utf8mb4';

CREATE TABLE `geo__lesser_admin_units` (
    `geonameId` int(11) NOT NULL,
    `code` varchar(20) DEFAULT NULL,
    `name` varchar(200) DEFAULT NULL,
    PRIMARY KEY (`geonameId`)
) DEFAULT CHARACTER SET 'utf8mb4';

CREATE TABLE `geo__countries` (
  `country` varchar(2) default null,
  `geonameId` int null,
  `name` varchar(200) null,
  `continent` varchar(2) null,
  PRIMARY KEY (`country`)
) DEFAULT CHARACTER SET 'utf8mb4';

CREATE TABLE `geo__alternate_names` (
  `alternatenameId` int(11) NOT NULL,
  `geonameId`int(11) NOT NULL,
  `isolanguage` varchar(7) DEFAULT NULL,
  `alternatename` varchar(200) DEFAULT NULL,
  `ispreferred` tinyint(1) DEFAULT NULL,
  `isshort` tinyint(1) DEFAULT NULL,
  `iscolloquial` tinyint(1) DEFAULT NULL,
  `ishistoric` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`alternatenameid`),
  FOREIGN KEY (`geonameId`)
        REFERENCES `geo__names` (`geonameId`)
        ON DELETE CASCADE
) DEFAULT CHARACTER SET 'utf8mb4';

LOAD DATA LOCAL INFILE 'countryInfo.txt' INTO TABLE `geo__countries` IGNORE 51 LINES (country, @skip, @skip, @skip, name, @skip, @skip, @skip, continent, @skip, @skip, @skip, @skip, @skip, @skip, @skip, geonameId, @skip, @skip);

/* treat North and South America and Europe and Asia as one continent */
UPDATE `geo__countries` SET continent = 'AM' WHERE (continent = 'NA' OR continent = 'SA');
UPDATE `geo__countries` SET continent = 'EA' WHERE (continent = 'EU' OR continent = 'AS');

/* Don't include dissolved countries */
DELETE FROM `geo__countries` WHERE geonameId = 0;

LOAD DATA LOCAL INFILE 'admin1CodesASCII.txt' INTO TABLE `geo__top_admin_units` (code, name, @skip, geonameId);
LOAD DATA LOCAL INFILE 'admin2Codes.txt' INTO TABLE `geo__lesser_admin_units` (code, name, @skip, geonameId);

LOAD DATA LOCAL INFILE 'allCountries.txt' INTO TABLE `geo__names` CHARACTER SET 'utf8mb4' (geonameId, name, @skip, @skip, latitude, longitude, fclass, fcode, country, @skip, admin1, admin2, admin3, admin4, population, @skip, @skip, @skip, moddate);

/* LOAD DATA LOCAL INFILE 'adminCode5.txt' INTO TABLE `geo__names` (geonameId, admin5); */

/* Put all indices in place (should be faster after import) */
CREATE INDEX idx_name ON `geo__names` (name);
CREATE INDEX idx_latitude ON `geo__names` (latitude);
CREATE INDEX idx_longitude ON `geo__names` (longitude);
CREATE INDEX idx_fclass ON `geo__names` (fclass);
CREATE INDEX idx_fcode ON `geo__names` (fcode);
CREATE INDEX idx_country ON `geo__names` (country);
CREATE INDEX idx_admin1 ON `geo__names` (admin1);
CREATE INDEX idx_admin2 ON `geo__names` (admin1);
CREATE INDEX idx_admin3 ON `geo__names` (admin1);
CREATE INDEX idx_admin4 ON `geo__names` (admin1);

DELETE FROM `geo__names` WHERE (fclass != 'P') AND (fclass != 'A');

UPDATE `geo__names` SET admin1 = NULL WHERE admin1 = '';
UPDATE `geo__names` SET admin2 = NULL WHERE admin2 = '';
UPDATE `geo__names` SET admin3 = NULL WHERE admin3 = '';
UPDATE `geo__names` SET admin4 = NULL WHERE admin4 = '';

CREATE INDEX idx_name ON `geo__top_admin_units` (code);
CREATE INDEX idx_name ON `geo__lesser_admin_units` (code);

UPDATE geo__names g set g.countryID = (SELECT geonameId from geo__countries gc where g.country = gc.country);
UPDATE geo__names g set g.admin1ID = (SELECT geonameId from geo__top_admin_units ga where ga.code = concat(g.country, '.', g.admin1));
UPDATE geo__names g set g.admin2ID = (SELECT geonameId from geo__lesser_admin_units ga where ga.code = concat(g.country, '.', g.admin1, '.', g.admin2));
UPDATE geo__names g set g.admin3ID = (SELECT geonameId from geo__lesser_admin_units ga where ga.code = concat(g.country, '.', g.admin1, '.', g.admin3));
UPDATE geo__names g set g.admin4ID = (SELECT geonameId from geo__lesser_admin_units ga where ga.code = concat(g.country, '.', g.admin1, '.', g.admin4));

DROP TABLE IF EXISTS `geo__top_admin_units`;
DROP TABLE IF EXISTS `geo__lesser_admin_units`;

/* make sure the site is operational and include alternate names last */
LOAD DATA LOCAL INFILE 'alternateNamesV2.txt' INTO TABLE `geo__alternate_names` CHARACTER SET 'utf8mb4' (alternatenameid, geonameId, isolanguage, alternatename, ispreferred, isshort, iscolloquial, ishistoric, @skip, @skip);

DELETE FROM `geo__alternate_names` WHERE geonameId NOT IN (SELECT geonameId from geo__names);

CREATE INDEX idx_alternatename ON `geo__alternate_names` (alternatename);
CREATE INDEX idx_isoLanguage ON `geo__alternate_names` (isoLanguage);
CREATE INDEX idx_ispreferred ON `geo__alternate_names` (ispreferred);
CREATE INDEX idx_isshort ON `geo__alternate_names` (isshort);
CREATE INDEX idx_iscolloquial ON `geo__alternate_names` (iscolloquial);
CREATE INDEX idx_ishistoric ON `geo__alternate_names` (ishistoric);
CREATE INDEX idx_geonameid ON `geo__alternate_names` (geonameId);

SET FOREIGN_KEY_CHECKS=1;
