<?php


class LastcommentsModel extends  RoxModelBase
{
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get last comments members have given each other.
     *
     * @param $limit Maximum number of results
     * @return array List of comments, empty if no results
     */
    public function GetLastComments($limit = 20) {
        $query = "
            SELECT
                m1.Username         AS UsernameFrom,
                m2.Username         AS UsernameTo,
                comments.updated,
                TextWhere,
                TextFree,
                comments.Quality,
                country1.iso_alpha2 AS IdCountryFrom,
                city1.geonameId     AS IdCityFrom,
                country1.name       AS CountryNameFrom,
                country2.iso_alpha2 AS IdCountryTo,
                city2.geonameId     AS IdCityTo,
                country2.name       AS CountryNameTo
            FROM
                comments,
                members            AS m1,
                members            AS m2,
                geonames_cache     AS city1,
                geonames_countries AS country1,
                geonames_cache     AS city2,
                geonames_countries AS country2
            WHERE
                m1.id = IdFromMember
                AND
                m2.id = IdToMember
                AND
                m1.Status = 'Active'
                AND
                m2.Status = 'Active'
                AND
                DisplayableInCommentOfTheMonth = 'Yes' 
                AND
                DisplayInPublic = 1
                AND
                city1.geonameId = m1.IdCity
                AND
                country1.iso_alpha2 = city1.fk_countrycode
                AND
                city2.geonameId = m2.IdCity
                AND
                country2.iso_alpha2 = city2.fk_countrycode
            ORDER BY
                comments.id DESC
            LIMIT
                $limit
            ";
        $result = $this->bulkLookup($query);
        return $result;
    }
}
