<?php


class ForumModel extends RoxModelBase
{
    public function getBoardByFilters($filters)
    {
        $board = new ForumFilteredBoard();
        $board->filters = $filters;
        return $board;
    }
    
    public function getTopicById($topic_id)
    {
        if (!$values = $this->singleLookup_assoc(
            "
SELECT
    forums_threads.id     AS IdThread,
    forums_threads.id     AS topic_id,
    forums_threads.title,
    forums_threads.IdTitle,
    forums_threads.replies,
    forums_threads.views,
    forums_threads.first_postid,
    forums_threads.expiredate,
    forums_threads.stickyvalue,
    forums_threads.continent,
    forums_threads.geonameid,    geonames_cache.name      AS geonames_name,
    forums_threads.admincode,    geonames_admincodes.name AS adminname,
    forums_threads.countrycode,  geonames_countries.name  AS countryname
FROM
    forums_threads
    LEFT JOIN geonames_cache       ON (forums_threads.geonameid = geonames_cache.geonameid)
    LEFT JOIN geonames_admincodes  ON (forums_threads.admincode = geonames_admincodes.admin_code AND forums_threads.countrycode = geonames_admincodes.country_code)
    LEFT JOIN geonames_countries   ON (forums_threads.countrycode = geonames_countries.iso_alpha2)
WHERE
    threadid = $topic_id
            "
        )) {
            return false;
        } else {
            return new ForumTopic($values, $this->dao);
        }
    }
}













?>