<?php

use Phinx\Migration\AbstractMigration;

/**************************************
 * Class RemoveOldAlternateNames
 *
 * Drop table geonames_alternate_names
 *
 * See ticket: #2208
 *
 */
class RemoveOldAlternateNames extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('geonames_alternate_names');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
            CREATE TABLE
              `geonames_alternate_names` (
                  `alternateNameId` int(11) NOT NULL,
                  `geonameId` int(11) NOT NULL,
                  `isoLanguage` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
                  `alternateName` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
                  `isPreferredName` binary(1) NOT NULL DEFAULT '0',
                  `isShortName` binary(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`alternateNameId`),
                  KEY `geonameId` (`geonameId`),
                  KEY `alternateName` (`alternateName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='DEPRECATED. Use geonamesalternatenames instead';
        ");
    }
}