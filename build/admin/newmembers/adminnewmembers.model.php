<?php

use App\Doctrine\MemberStatusType;

/**
 * Class AdminNewMembersModel
 */
class AdminNewMembersModel extends RoxModelBase {

    private $_statuses = null;

    /**
     * The statuses a new member is listed with: active members for the greeters, also the members who haven't
     * finished signing up for the Safety Team and the checkers.
     */
    private function statuses($safetyTeamOrAdmin): string
    {
        $statuses = [MemberStatusType::ACTIVE];
        if ($safetyTeamOrAdmin) {
            $statuses = array_merge($statuses, MemberStatusType::SIGNUP_UNFINISHED);
        }

        return "'" . implode("', '", $statuses) . "'";
    }

    /**
     *
     */
    public function getMembersCount($safetyTeamOrAdmin) {
        $query = "
            SELECT
                count(*) as cnt
            FROM
                member m
            WHERE
                m.Status IN (" . $this->statuses($safetyTeamOrAdmin) . ")";
        if (!$safetyTeamOrAdmin) {
            $query .= " AND m.bewelcomed < 3";
        }
        $query .= " AND DATEDIFF(NOW(), m.created) < 90";
        $row = $this->singleLookup($query);
        return $row->cnt;
    }

    /**
     * The members who signed up in the last 90 days, with their place, about me text and languages.
     *
     * @param $first
     * @param $count
     * @param $safetyTeamOrAdmin
     * @return array
     * @throws PException
     */
    public function getMembers($first, $count, $safetyTeamOrAdmin) {
        $langarr = explode('-', (string) $this->session->get('lang'));
        $lang = $this->dao->escape($langarr[0] ?: 'en');

        // place and country in the member's language, else in English, else as named by GeoNames
        $placeName = static fn (string $alias) => "COALESCE(
                    (SELECT t.content FROM location_translation t WHERE t.object_id = {$alias}.geoname_id AND t.field = 'name'
                        AND t.locale IN ('{$lang}', 'en') ORDER BY t.locale = '{$lang}' DESC LIMIT 1),
                    {$alias}.name)";

        $str = "
            SELECT
                m.id,
                m.Username,
                m.created,
                m.Status,
                m.Email,
                m.bewelcomed,
                m.AboutMe,
                " . $placeName('g') . " AS CityName,
                " . $placeName('c') . " AS CountryName
            FROM
                member m
            LEFT JOIN
                address a ON a.member_id = m.id AND a.active = 1
            LEFT JOIN
                location g ON g.geoname_id = a.location
            LEFT JOIN
                location c ON c.geoname_id = g.country
            WHERE
                m.Status IN (" . $this->statuses($safetyTeamOrAdmin) . ")
                AND DATEDIFF(NOW(), m.created) < 90";
        if (!$safetyTeamOrAdmin) {
            $str .= " AND m.bewelcomed < 3";
        }
        $str .= "
            ORDER BY m.created " . ($safetyTeamOrAdmin ? 'ASC' : 'DESC') . "
            LIMIT " . (int) $first . ", " . (int) $count;

        $members = [];
        foreach ($this->bulkLookup($str) as $member) {
            $member->ProfileSummary = MOD_layoutbits::truncate_words(strip_tags((string) $member->AboutMe, '<b><i><br>'), 70);
            if ($safetyTeamOrAdmin) {
                $member->EmailAddress = $member->Email;
            }
            // language names are translated as lang_<code>, the levels as language.level.<level>
            $member->languages = $this->bulkLookup("
                SELECT
                    mll.level AS Level,
                    CONCAT('lang_', mll.language) AS WordCode
                FROM
                    member_language_level mll
                WHERE
                    mll.member_id = " . (int) $member->id . "
                ORDER BY
                    FIELD(mll.level, 'mother.tongue', 'expert', 'fluent', 'intermediate', 'beginner', 'hello.only')");
            $members[] = $member;
        }

        return $members;
    }

    public function getStatuses() {
        if (!isset($this->_statuses)) {
            $MembersModel = new MembersModel();
            $this->_statuses = $MembersModel->getStatuses();
        }
        return $this->_statuses;
    }

    public function localGreetingSent($member) {
        $bewelcomed = $member->bewelcomed;
        $bewelcomed |= 1;
        $member->bewelcomed = $bewelcomed;
        $member->update();
    }

    public function globalGreetingSent($member) {
        $bewelcomed = $member->bewelcomed;
        $bewelcomed |= 2;
        $member->bewelcomed = $bewelcomed;
        $member->update();
    }
}
