SET NAMES utf8mb4;
SET CHARACTER SET 'utf8mb4';
SET collation_connection = 'utf8mb4_general_ci';
SET FOREIGN_KEY_CHECKS=0;

/* Delete existing tables first */
DROP TABLE IF EXISTS `geonamesalternatenames`;
DROP TABLE IF EXISTS `geonamesadminunits`;
DROP TABLE IF EXISTS `geonames`;
DROP TABLE IF EXISTS `geonamescountries`;

CREATE TABLE `geonames` (
  `geonameid` int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `fclass` char(1) DEFAULT NULL,
  `fcode` varchar(10) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `admin1` varchar(20) DEFAULT NULL,
  `population` int(11) DEFAULT NULL,
  `moddate` date DEFAULT NULL,
  PRIMARY KEY (`geonameid`)
) DEFAULT CHARACTER SET 'utf8mb4';

CREATE TABLE `geonamesadminunits` (
  `geonameid` int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `fclass` char(1) DEFAULT NULL,
  `fcode` varchar(10) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `admin1` varchar(20) DEFAULT NULL,
  `moddate` date DEFAULT NULL,
  PRIMARY KEY (`geonameid`)
) DEFAULT CHARACTER SET 'utf8mb4';

/* must drop Donation.country constraint because it blocks the creation of the following table */
ALTER TABLE `donations` DROP INDEX IF EXISTS UNIQ_CDE98962C13B2037;
CREATE TABLE `geonamescountries` (
  `country` varchar(2) default null,
  `geonameId` int null,
  `name` varchar(200) null,
  `continent` varchar(2) null,
  PRIMARY KEY (`country`)
) DEFAULT CHARACTER SET 'utf8mb4';
ALTER TABLE `donations` ADD CONSTRAINT UNIQ_CDE98962C13B2037 UNIQUE (IdCountry);

CREATE TABLE `geonamesalternatenames` (
  `alternatenameId` int(11) NOT NULL,
  `geonameid`int(11) NOT NULL,
  `isolanguage` varchar(7) DEFAULT NULL,
  `alternatename` varchar(200) DEFAULT NULL,
  `ispreferred` tinyint(1) DEFAULT NULL,
  `isshort` tinyint(1) DEFAULT NULL,
  `iscolloquial` tinyint(1) DEFAULT NULL,
  `ishistoric` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`alternatenameid`),
  FOREIGN KEY (`geonameid`)
        REFERENCES `geonames` (`geonameid`)
        ON DELETE CASCADE
) DEFAULT CHARACTER SET 'utf8mb4';

LOAD DATA LOCAL INFILE './docker/db/countryInfo.txt' INTO TABLE `geonamescountries` IGNORE 51 LINES (country, @skip, @skip, @skip, name, @skip, @skip, @skip, continent, @skip, @skip, @skip, @skip, @skip, @skip, @skip, geonameid, @skip, @skip);

/* treat North and South America and Europe and Asia as one continent */
UPDATE `geonamescountries` SET continent = 'AM' WHERE (continent = 'NA' OR continent = 'SA');
UPDATE `geonamescountries` SET continent = 'EA' WHERE (continent = 'EU' OR continent = 'AS');

/* Don't include dissolved countries */
DELETE FROM `geonamescountries` WHERE geonameid = 0;

LOAD DATA LOCAL INFILE './docker/db/allCountries.txt' INTO TABLE `geonames` CHARACTER SET 'utf8mb4' (geonameid, name, @skip, @skip, latitude, longitude, fclass, fcode, country, @skip, admin1, @skip, @skip, @skip, population, @skip, @skip, @skip, moddate);

/* fill the geonamesadminunits table based on the content of the geonames table (much faster with a separate table)*/
INSERT INTO `geonamesadminunits` SELECT geonameid, name, fclass, fcode, country, admin1, moddate FROM `geonames`WHERE fclass = 'A';

/* REMOVE every non-place from the geonames table to save space */
DELETE FROM `geonames` WHERE (fclass != 'P') AND (fclass != 'A');

/* Put all indices in place (should be faster after import) */
CREATE INDEX idx_name ON `geonames` (name);
CREATE INDEX idx_latitude ON `geonames` (latitude);
CREATE INDEX idx_longitude ON `geonames` (longitude);
CREATE INDEX idx_fclass ON `geonames` (fclass);
CREATE INDEX idx_fcode ON `geonames` (fcode);
CREATE INDEX idx_country ON `geonames` (country);
CREATE INDEX idx_admin1 ON `geonames` (admin1);

CREATE INDEX idx_name ON `geonamesadminunits` (name);
CREATE INDEX idx_fclass ON `geonamesadminunits` (fclass);
CREATE INDEX idx_fcode ON `geonamesadminunits` (fcode);
CREATE INDEX idx_country ON `geonamesadminunits` (country);
CREATE INDEX idx_admin1 ON `geonamesadminunits` (admin1);

/* make sure the site is operational and include alternate names last */
LOAD DATA LOCAL INFILE './docker/db/alternateNames.txt' INTO TABLE `geonamesalternatenames` CHARACTER SET 'utf8mb4' (alternatenameid, geonameid, isolanguage, alternatename, ispreferred, isshort, iscolloquial, ishistoric);

CREATE INDEX idx_alternatename ON `geonamesalternatenames` (alternatename);
CREATE INDEX idx_isoLanguage ON `geonamesalternatenames` (isoLanguage);
CREATE INDEX idx_ispreferred ON `geonamesalternatenames` (ispreferred);
CREATE INDEX idx_isshort ON `geonamesalternatenames` (isshort);
CREATE INDEX idx_iscolloquial ON `geonamesalternatenames` (iscolloquial);
CREATE INDEX idx_ishistoric ON `geonamesalternatenames` (ishistoric);
CREATE INDEX idx_geonameid ON `geonamesalternatenames` (geonameid);

SET FOREIGN_KEY_CHECKS=1;
