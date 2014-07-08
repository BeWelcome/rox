<?php

include("../lib/pchart-2.1.3/class/pDraw.class.php");
include("../lib/pchart-2.1.3/class/pImage.class.php");
include("../lib/pchart-2.1.3/class/pData.class.php");
include("../lib/pchart-2.1.3/class/pPie.class.php");

/**
 * AboutStatisticsPage
 *
 * @package about
 * @author Philipp
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class StatsModel extends RoxModelBase
{
    public function __construct()
    {
        parent::__construct();
    }

    //---------------------------------
    // needed for statistics page
    //---------------------------------

    /**
     * retrieve the number of members for each country
     */
    public function getMembersPerCountry() {
		// This query countes the number of members in the same way as HC or CS
        $query = "
            SELECT
                geonames_countries.name AS countryname,
                count(*) AS cnt
            FROM
                members,
                geonames_countries,
                geonames_cache
            WHERE
                members.Status IN (" . Member::ACTIVE_ALL . ")
                AND
                members.IdCity = geonames_cache.geonameId
                AND
                geonames_cache.fk_countrycode = geonames_countries.iso_alpha2
            GROUP BY
                geonames_countries.iso_alpha2
            ORDER BY
                cnt DESC
            ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of members per Country!');
        }
        $result = array();
        $i=0;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<14) {
                $result[$row->countryname] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * retrieve the number of members for each languages
     */
    public function getLanguages() {
		// This fetches the languages spoken by the members along with their count
        $query = "
            SELECT
                l.englishname language,
                COUNT(m.id) cnt
            FROM
                memberslanguageslevel mll,
                languages l,
                members m
            WHERE
                l.id = mll.IdLanguage
                AND mll.idMember = m.id
                AND m.Status IN (" . Member::ACTIVE_ALL . ")
            GROUP BY
                l.name
            ORDER BY
                cnt DESC";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of languages per members!');
        }
        $result = array();
        $i=1;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<10) {
                $result[$row->language] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * retrieve the number of members for each country
     */
    public function getPreferredLanguages() {
		// This fetches the languages spoken by the members along with their count
        $query = "
            SELECT
                COUNT(m.id) cnt, l.englishname language
            FROM
                languages l, `members` m
            LEFT JOIN
                memberspreferences mp
            ON
                m.id = mp.idmember
                AND mp.idpreference = 1
            WHERE
                m.status IN (" . Member::ACTIVE_ALL . ")
                AND l.id = IFNULL(mp.value, 0)
            GROUP BY
                language
            ORDER BY
                cnt DESC";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of preferred languages per members!');
        }
        $result = array();
        $i=0;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<14) {
                $result[$row->language] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

/**
     * retrieve the last login date from the db
     */
    public function getLastLoginRank() {
        $query = '
SELECT
    TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
    COUNT(*) AS cnt
FROM members
WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
AND status IN (' . Member::ACTIVE_ALL . ')
GROUP BY logindiff
ORDER BY logindiff ASC';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve last login listing!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                    $result[$row->logindiff] = $row->cnt;
        }
        return $result;
    }

    public function getLastLoginRankGrouped() {
        $query = '
SELECT
    TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
    COUNT(*) AS cnt
FROM members
WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
AND status IN (' . Member::ACTIVE_ALL . ')
GROUP BY logindiff
ORDER BY logindiff ASC
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve last login listing!');
        }
        $result = array();

        $result['1 day'] = 0;
        $result['1 week'] = 0;
        $result['1-2 weeks'] = 0;
        $result['2-4 weeks'] = 0;
        $result['1-3 months'] = 0;
        $result['3-6 months'] = 0;
        $result['6-12 months'] = 0;
        $result['longer'] = 0;


        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($row->logindiff==1) {
                    $result['1 day'] = $result['1 day'] + $row->cnt;
            } elseif ($row->logindiff<=7) {
                    $result['1 week'] = $result['1 week'] + $row->cnt;
            } elseif ($row->logindiff<=14) {
                    $result['1-2 weeks'] = $result['1-2 weeks'] + $row->cnt;
            } elseif ($row->logindiff<=30) {
                    $result['2-4 weeks'] = $result['2-4 weeks'] + $row->cnt;
            } elseif ($row->logindiff<=90) {
                    $result['1-3 months'] = $result['1-3 months'] + $row->cnt;
            } elseif ($row->logindiff<=182) {
                    $result['3-6 months'] = $result['3-6 months'] + $row->cnt;
            } elseif ($row->logindiff<=365) {
                    $result['6-12 months'] = $result['6-12 months'] + $row->cnt;
            } else {
                $result['longer'] =  $result['longer'] + $row->cnt;
            }
        }
        return $result;
    }


    /**
     * retrieve the stats from db - all time weekly average
     */
    public function getStatsLogAll() {
        $query = '
SELECT
    MAX(NbActiveMembers) AS NbActiveMembers,
    MAX(NbMessageSent) AS NbMessageSent,
    MAX(NbMessageRead) AS NbMessageRead,
    MAX(NbMemberWithOneTrust) AS NbMemberWithOneTrust,
    MAX(NbMemberWhoLoggedToday) AS NbMemberWhoLoggedToday,
    created,
    YEARWEEK(created) AS week
FROM stats
GROUP BY week
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve statistics table!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * retrieve the stats from db - daily for last 2months
     */
    public function getStatsLog2Month() {
        $query = '
SELECT *
FROM stats
ORDER BY id DESC
LIMIT 0,60
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve statistics table!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $result[] = $row;
        }
        $result = array_reverse($result);
        return $result;
    }

    private function drawCharts($filename, $yAxisLabel, $dataSeriesAllTime, $labelSeriesAllTime, $dataSeriesLast2Month, $labelSeriesLast2Month)
    {
        /* Build the dataset for all time*/
        $allTimeData = new pData();
        $allTimeData->addPoints($dataSeriesAllTime, "members");
        $allTimeData->setAxisName(0, $yAxisLabel);
        $allTimeData->addPoints($labelSeriesAllTime, "Labels");
        $serieSettings = array("R"=>74,"G"=>102,"B"=>27, "Alpha" => 90);
        $allTimeData->setPalette("members",$serieSettings);
        // $allTimeData->setSerieOnAxis("Labels", 1);
        $allTimeData->setAbscissa("Labels");

        /* Create the chart*/
        $allTimePicture = new pImage(400,250,$allTimeData, true);
        $allTimePicture->setFontProperties(array("FontName"=>"../lib/pchart-2.1.3/fonts/verdana.ttf","FontSize"=>6));

        /* Add a border to the picture */
        $allTimePicture->setGraphArea(50,20,380,210);
        $allTimePicture->drawFilledRectangle(50,20,380,210,array("R"=>232,"G"=>238,"B"=>248,"Alpha"=>100));
        $allTimePicture->drawScale(array("XMargin" => 0, "DrawSubTicks" =>true, "DrawXLines" => false,
            "Mode"=>SCALE_MODE_START0, "CycleBackground" => true, "LabelSkip"=> 52, "ForceTransparency" => 0));
        $allTimePicture->drawAreaChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO, ""));
        $allTimePicture->render($filename . "-alltime.png");

        /* Build the dataset for the last two month */
        $last2MonthData = new pData();
        $last2MonthData->addPoints($dataSeriesLast2Month, "members");
        $last2MonthData->setAxisName(0, $yAxisLabel);
        $last2MonthData->addPoints($labelSeriesLast2Month, "Labels");
        $last2MonthData->setPalette("members",$serieSettings);
        $last2MonthData->setAbscissa("Labels");

        /* Create the chart*/
        $last2MonthPicture = new pImage(400,250,$last2MonthData, true);
        $last2MonthPicture->setFontProperties(array("FontName"=>"../lib/pchart-2.1.3/fonts/verdana.ttf","FontSize"=>6));
        $last2MonthPicture->Antialias = true;

        /* Add a border to the picture */
        $last2MonthPicture->setGraphArea(50,20,380,210);
        $miny = min($dataSeriesLast2Month);
        $maxy = max($dataSeriesLast2Month);
        $axisBoundaries = array(0=>array("Min"=>$miny,"Max"=>$maxy));
        $last2MonthPicture->drawFilledRectangle(50,20,380,210,array("R"=>232,"G"=>238,"B"=>248,"Alpha"=>100));
        $last2MonthPicture->drawScale(array("DrawSubTicks" =>true, "DrawXLines" => true, "CycleBackground"=>true, "LabelSkip"=> 13,
                        "Mode" => SCALE_MODE_MANUAL, "ManualScale"=>$axisBoundaries));

        // get min and max values set scales accordingly
        $last2MonthPicture->drawAreaChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO, ""));
        $last2MonthPicture->render($filename . "-last2month.png");
    }

    private function drawPieChart($filename, $dataSeries, $labelSeries) {
        $dataSeries = array_values($dataSeries);

        /* Create and populate the pData object */
        $data = new pData();
        $data->addPoints($dataSeries);

        /* Calculate percentage and add to the labels */
        $total = array_sum($dataSeries);

        for($i = 0; $i < count($dataSeries); $i++) {
            $labelSeries[$i] .= " (" . (ceil($dataSeries[$i] / $total * 100)) . "%)";
        }

        /* Define the absissa serie */
        $data->addPoints($labelSeries,"Labels");
        $data->setAbscissa("Labels");

        /* Create the pChart object */
        $picture = new pImage(400,250,$data, true);
        /* Set the default font properties */
        $picture->setFontProperties(array("FontName"=>"../lib/pchart-2.1.3/fonts/verdana.ttf","FontSize"=>8));

        /* Enable shadow computing */
        $picture->setShadow(true,array("X"=>2,"Y"=>2,"R"=>150,"G"=>150,"B"=>150,"Alpha"=>100));

        /* Create the pPie object */
        $pieChart = new pPie($picture,$data);

        /* Draw a simple pie chart */
        $pieChart->draw2DPie(115, 125, array("Radius" => 110, "SecondPass"=>true));

        $pieChart->drawPieLegend(250, 10);
        $picture->render($filename . '.png');
    }

    private function drawBarChart($filename, $yAxisLabel, $dataSeries, $labelSeries) {
        /* Create and populate the pData object */
        $MyData = new pData();
        $MyData->addPoints($dataSeries,"Members");
        $MyData->setAxisName(0,"Members");
        $MyData->addPoints($labelSeries,"Languages");
        $MyData->setSerieDescription("Languages","Languages");
        $MyData->setAbscissa("Languages");

        /* Create the pChart object */
        $myPicture = new pImage(400,250,$MyData, true);
        //$myPicture->drawGradientArea(0,0,500,500,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
        //$myPicture->drawGradientArea(0,0,500,500,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
        $myPicture->setFontProperties(array("FontName"=>"../lib/pchart-2.1.3/fonts/verdana.ttf","FontSize"=>6));

        /* Draw the chart scale */
        $myPicture->setGraphArea(70,20,380,200);
        $myPicture->drawScale(array("CycleBackground"=>TRUE, "LabelRotation" => 30, "DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10));

        /* Turn on shadow computing */
        $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

        /* Create the per bar palette */
        $Palette = array("0"=>array("R"=>188,"G"=>224,"B"=>46,"Alpha"=>100),
                         "1"=>array("R"=>224,"G"=>100,"B"=>46,"Alpha"=>100),
                         "2"=>array("R"=>224,"G"=>214,"B"=>46,"Alpha"=>100),
                         "3"=>array("R"=>46,"G"=>151,"B"=>224,"Alpha"=>100),
                         "4"=>array("R"=>176,"G"=>46,"B"=>224,"Alpha"=>100),
                         "5"=>array("R"=>224,"G"=>46,"B"=>117,"Alpha"=>100),
                         "6"=>array("R"=>92,"G"=>224,"B"=>46,"Alpha"=>100),
                         "7"=>array("R"=>224,"G"=>176,"B"=>46,"Alpha"=>100),
                         "8"=>array("R"=>120,"G"=>59,"B"=>19,"Alpha"=>100),
                         "9"=>array("R"=>10,"G"=>79,"B"=>39,"Alpha"=>100),
                         "10"=>array("R"=>100,"G"=>200,"B"=>59,"Alpha"=>100));

        /* Draw the chart */
        $myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_OUTSIDE,"DisplayValues"=>TRUE,"Surrounding"=>30,"OverrideColors"=>$Palette));

        /* Write the legend */
        $myPicture->drawLegend(570,215,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
        $myPicture->render($filename . ".png");
    }

    public function generateStatsImages() {
        $statsDir = new PDataDir('statimages');
        $statsdir = $statsDir->dirName() . "/";
        $statsfile = "laststats.txt";
        if ($statsDir->fileExists( $statsfile )) {
            $stats = fopen($statsdir. $statsfile, "r");
            $dateLine = fgets($stats);
            if ($dateLine == date('Y-m-d')) {
                return;
            }
        }

        $countryrank = $this->getMembersPerCountry();
        $loginrank = $this->getLastLoginRank();
        $loginrankgrouped = $this->getLastLoginRankGrouped();
        $statsall = $this->getStatsLogAll();
        $statslast = $this->getStatsLog2Month();
        $languages = $this->getLanguages();
        $preferredLanguages = $this->getPreferredLanguages();

        // get number of members per country
        $i = 0;
        foreach ( $countryrank as $key => $val ) {
            $country[$i] = $key;
            $countrycnt[$i] = $val;
            $i++;
        }

        // get last login grouped by time
        $i = 0;
        foreach ( $loginrankgrouped as $key => $val ) {
            $lastlogingrouped[$i] = $key;
            $lastlogingroupedcnt[$i] = $val;
            $i++;
        }

        // get login rank
        $i = 0;
        foreach ( $loginrank as $key => $val ) {
            $lastlogin[$i] = "\"" . $key . "\"";
            $lastlogincnt[$i] = "[" . $key . "," . $val . "]";
            $i++;
        }

        // get all values from stats table
        $i = 0;
        $labelsAllTime = $membersAllTime = $newMembersAllTime = $newMembersPercentAllTime = $membersLoggedInAllTime = $membersLoggedInPercentAllTime = $memberWithPositiveCommentsAllTime = $messageSentAllTime = $messageReadAllTime = array ();
        foreach ( $statsall as $val ) {
            $membersAllTime[$i] = $val->NbActiveMembers;
            $yearweek = strtotime(substr($val->week, 0, 4) . "-W" . substr($val->week, 4, 2) . "-1");
            // echo $yearweek . ": " . date( "Y-m-d", $yearweek) . "<br />";
            $labelsAllTime[] = date("Y-m-d", $yearweek);
            if ($i == 0) {
                $newMembersAllTime[$i] = 0;
            } else {
                $newMembersAllTime[$i] = $membersAllTime[$i] - $membersAllTime[$i - 1];
            }
            if ($i == 0) {
                $newMembersPercentAllTime[$i] = 0;
            } else {
                if ($membersAllTime[$i] == 0) {
                    $newMembersPercentAllTime[$i] = 0;
                } else {
                    $newMembersPercentAllTime[$i] = $newMembersAllTime[$i] / $membersAllTime[$i] * 100;
                }
            }
            $messageSentAllTime[$i] = $val->NbMessageSent;
            $membersWithPositiveCommentsAllTime[$i] = $val->NbMemberWithOneTrust;
            $membersLoggedInAllTime[$i] = $val->NbMemberWhoLoggedToday;
            if ($membersAllTime[$i] == 0) {
                $membersLoggedInPercentAllTime[$i] = 0;
            } else {
                $membersLoggedInPercentAllTime[$i] = $membersLoggedInAllTime[$i] / $membersAllTime[$i] * 100;
            }
            $i++;
        }

        // get all values from stats table (last 2 months)
        $i = 0;
        $labelsLast2Month = $membersLast2Month = $newMembersLast2Month = $membersPercentLast2Month = $membersLoggedInLast2Month = $membersLoggedInPercentLast2Month = $memberWithPositiveCommentsLast2Month = $messageSentLast2Month = $messageReadLast2Month = array ();
        foreach ( $statslast as $val ) {
            $membersLast2Month[$i] = $val->NbActiveMembers;
            if ($i == 0) {
                $newMembersLast2Month[0] = 0;
            } else {
                $newMembersLast2Month[$i] = $membersLast2Month[$i] - $membersLast2Month[$i - 1];
            }
            $labelsLast2Month[] = date("Y-m-d", strtotime("-" . (60 - $i) . "days"));
            if ($i == 0) {
                $newMembersPercentLast2Month[$i] = 0;
            } else {
                if ($membersLast2Month[$i - 1] == 0) {
                    $newMembersPercentLast2Month[$i] = 0;
                } else {
                    $newMembersPercentLast2Month[$i] = $newMembersLast2Month[$i] / $membersLast2Month[$i - 1] * 100;
                }
            }
            $messageSentLast2Month[$i] = $val->NbMessageSent;
            $membersWithPositiveCommentsLast2Month[$i] = $val->NbMemberWithOneTrust;
            $membersLoggedInLast2Month[$i] = $val->NbMemberWhoLoggedToday;
            if ($membersLast2Month[$i] == 0) {
                $membersLoggedInPercentLast2Month[$i] = 0;
            } else {
                $membersLoggedInPercentLast2Month[$i] = $membersLoggedInLast2Month[$i] / $membersLast2Month[$i] * 100;
            }
            $i++;
        }

        $this->drawCharts($statsdir . 'allmembers', 'Members', $membersAllTime, $labelsAllTime, $membersLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'newmembers', 'Members', $newMembersAllTime, $labelsAllTime, $newMembersLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'percentmembers', '% members', $newMembersPercentAllTime, $labelsAllTime, $newMembersPercentLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'login', 'Members', $membersLoggedInAllTime, $labelsAllTime, $membersLoggedInLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'percentlogin', '% members', $membersLoggedInPercentAllTime, $labelsAllTime, $membersLoggedInPercentLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'trust', 'Members', $membersWithPositiveCommentsAllTime, $labelsAllTime, $membersWithPositiveCommentsLast2Month, $labelsLast2Month);
        $this->drawCharts($statsdir . 'messages', 'Messages', $messageSentAllTime, $labelsAllTime, $messageSentLast2Month, $labelsLast2Month);
        $this->drawPieChart($statsdir . 'loginpie', $lastlogingroupedcnt, $lastlogingrouped);
        $this->drawPieChart($statsdir . 'countrypie', $countrycnt, $country);
        $this->drawBarChart($statsdir . 'languagepie', 'Members', $languages, array_keys($languages));
        $this->drawPieChart($statsdir . 'preferredlanguagepie', $preferredLanguages, array_keys($preferredLanguages));

        $stats = fopen( $statsdir . $statsfile, "w");
        fwrite($stats, date('Y-m-d'));
        fclose($stats);
    }

    public function updateStatistics()
    {
        // Number of member
        $query = "SELECT COUNT(*) AS cnt FROM members WHERE Status in ('Active','ChoiceInactive','OutOfRemind')";
        $row = $this->singleLookup($query);
        $NbActiveMembers = $row->cnt;

        // Number of member with at least one positive comment
        $query = "SELECT COUNT(DISTINCT(members.id)) AS cnt FROM members,comments WHERE Status in ('Active','ChoiceInactive','OutOfRemind') AND members.id=comments.IdToMember AND comments.Quality='Good'";
        $row = $this->singleLookup($query);
        $NbMemberWithOneTrust = $row->cnt;

        $d1 = strftime("%Y-%m-%d 00:00:00", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $d2 = strftime("%Y-%m-%d 00:00:00", mktime(0, 0, 0, date("m"), date("d"), date("Y")));

        // Number of member who have logged
        $NbMemberWhoLoggedToday = 0;
        $str = "SELECT COUNT(distinct(members.id)) as cnt from members right join " . PVars::getObj('syshcvol')->ARCH_DB . ".logs on  members.id=" . PVars::getObj('syshcvol')->ARCH_DB . ".logs.IdMember and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.type='Login' and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.created between '$d1' and '$d2' and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.Str like 'Successful login%' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMemberWhoLoggedToday = $row->cnt;
        }

        $NbMessageSent = 0;
        $str = "SELECT COUNT(*) as cnt from messages where DateSent between '$d1' and '$d2' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMessageSent = $row->cnt;
        }

        // Number of message read
        $NbMessageRead = 0;
        $str = "SELECT COUNT(*) as cnt from messages where WhenFirstRead between '$d1' and '$d2' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMessageRead = $row->cnt;
        }

        $str = "INSERT INTO stats ( id , created , NbActiveMembers , NbMessageSent , NbMessageRead , NbMemberWithOneTrust , NbMemberWhoLoggedToday )VALUES (NULL ,CURRENT_TIMESTAMP , $NbActiveMembers , $NbMessageSent , $NbMessageRead , $NbMemberWithOneTrust , $NbMemberWhoLoggedToday )";
        $this->dao->query($str);

        return true;
    }
}

