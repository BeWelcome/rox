<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Params.
 *
 * @ORM\Table(name="params")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Params
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="IsRealProductionDatabase", type="string", nullable=false)
     */
    private $isrealproductiondatabase = 'No';

    /**
     * @var int
     *
     * @ORM\Column(name="recordonline", type="integer", nullable=false)
     */
    private $recordonline = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="ToggleDonateBar", type="integer", nullable=false)
     */
    private $toggledonatebar = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="neededperyear", type="integer", nullable=false)
     */
    private $neededperyear = 1260;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="campaignstartdate", type="date", nullable=false)
     */
    private $campaignstartdate = '2012-10-11';

    /**
     * @var string
     *
     * @ORM\Column(name="MailToNotifyWhenNewMemberSignup", type="text", length=65535, nullable=false)
     */
    private $mailtonotifywhennewmembersignup;

    /**
     * @var string
     *
     * @ORM\Column(name="FeatureForumClosed", type="string", nullable=false)
     */
    private $featureforumclosed = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="FeatureAjaxChatClosed", type="string", nullable=false)
     */
    private $featureajaxchatclosed = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="FeatureSignupClose", type="string", nullable=false)
     */
    private $featuresignupclose = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="FeatureSearchPageIsClosed", type="string", nullable=false)
     */
    private $featuresearchpageisclosed = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="FeatureQuickSearchIsClosed", type="string", nullable=false)
     */
    private $featurequicksearchisclosed = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="RssFeedIsClosed", type="string", nullable=false)
     */
    private $rssfeedisclosed = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="AjaxChatSpecialAllowedList", type="text", length=65535, nullable=false)
     */
    private $ajaxchatspecialallowedlist;

    /**
     * @var int
     *
     * @ORM\Column(name="AjaxChatDebuLevel", type="integer", nullable=false)
     */
    private $ajaxchatdebulevel = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="ReloadRightsAndFlags", type="string", nullable=false)
     */
    private $reloadrightsandflags = 'No';

    /**
     * @var int
     *
     * @ORM\Column(name="logs_id_midnight", type="integer", nullable=false)
     */
    private $logsIdMidnight;

    /**
     * @var int
     *
     * @ORM\Column(name="previous_logs_id_midnight", type="integer", nullable=false)
     */
    private $previousLogsIdMidnight;

    /**
     * @var string
     *
     * @ORM\Column(name="memcache", type="string", nullable=false)
     */
    private $memcache = 'False';

    /**
     * @var int
     *
     * @ORM\Column(name="DayLightOffset", type="integer", nullable=false)
     */
    private $daylightoffset = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="NbCommentsInLastComments", type="integer", nullable=false)
     */
    private $nbcommentsinlastcomments = 20;

    /**
     * @var int
     *
     * @ORM\Column(name="IdCommentOfTheMoment", type="integer", nullable=false)
     */
    private $idcommentofthemoment = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="MailBotMode", type="string", nullable=false)
     */
    private $mailbotmode = 'Manual';

    /**
     * @var string
     *
     * @ORM\Column(name="ToggleStatsForWordsUsage", type="string", nullable=false)
     */
    private $togglestatsforwordsusage = 'No';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Params
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set isrealproductiondatabase.
     *
     * @param string $isrealproductiondatabase
     *
     * @return Params
     */
    public function setIsrealproductiondatabase($isrealproductiondatabase)
    {
        $this->isrealproductiondatabase = $isrealproductiondatabase;

        return $this;
    }

    /**
     * Get isrealproductiondatabase.
     *
     * @return string
     */
    public function getIsrealproductiondatabase()
    {
        return $this->isrealproductiondatabase;
    }

    /**
     * Set recordonline.
     *
     * @param int $recordonline
     *
     * @return Params
     */
    public function setRecordonline($recordonline)
    {
        $this->recordonline = $recordonline;

        return $this;
    }

    /**
     * Get recordonline.
     *
     * @return int
     */
    public function getRecordonline()
    {
        return $this->recordonline;
    }

    /**
     * Set toggledonatebar.
     *
     * @param int $toggledonatebar
     *
     * @return Params
     */
    public function setToggledonatebar($toggledonatebar)
    {
        $this->toggledonatebar = $toggledonatebar;

        return $this;
    }

    /**
     * Get toggledonatebar.
     *
     * @return int
     */
    public function getToggledonatebar()
    {
        return $this->toggledonatebar;
    }

    /**
     * Set neededperyear.
     *
     * @param int $neededperyear
     *
     * @return Params
     */
    public function setNeededperyear($neededperyear)
    {
        $this->neededperyear = $neededperyear;

        return $this;
    }

    /**
     * Get neededperyear.
     *
     * @return int
     */
    public function getNeededperyear()
    {
        return $this->neededperyear;
    }

    /**
     * Set campaignstartdate.
     *
     * @param \DateTime $campaignstartdate
     *
     * @return Params
     */
    public function setCampaignstartdate($campaignstartdate)
    {
        $this->campaignstartdate = $campaignstartdate;

        return $this;
    }

    /**
     * Get campaignstartdate.
     *
     * @return \DateTime
     */
    public function getCampaignstartdate()
    {
        return $this->campaignstartdate;
    }

    /**
     * Set mailtonotifywhennewmembersignup.
     *
     * @param string $mailtonotifywhennewmembersignup
     *
     * @return Params
     */
    public function setMailtonotifywhennewmembersignup($mailtonotifywhennewmembersignup)
    {
        $this->mailtonotifywhennewmembersignup = $mailtonotifywhennewmembersignup;

        return $this;
    }

    /**
     * Get mailtonotifywhennewmembersignup.
     *
     * @return string
     */
    public function getMailtonotifywhennewmembersignup()
    {
        return $this->mailtonotifywhennewmembersignup;
    }

    /**
     * Set featureforumclosed.
     *
     * @param string $featureforumclosed
     *
     * @return Params
     */
    public function setFeatureforumclosed($featureforumclosed)
    {
        $this->featureforumclosed = $featureforumclosed;

        return $this;
    }

    /**
     * Get featureforumclosed.
     *
     * @return string
     */
    public function getFeatureforumclosed()
    {
        return $this->featureforumclosed;
    }

    /**
     * Set featureajaxchatclosed.
     *
     * @param string $featureajaxchatclosed
     *
     * @return Params
     */
    public function setFeatureajaxchatclosed($featureajaxchatclosed)
    {
        $this->featureajaxchatclosed = $featureajaxchatclosed;

        return $this;
    }

    /**
     * Get featureajaxchatclosed.
     *
     * @return string
     */
    public function getFeatureajaxchatclosed()
    {
        return $this->featureajaxchatclosed;
    }

    /**
     * Set featuresignupclose.
     *
     * @param string $featuresignupclose
     *
     * @return Params
     */
    public function setFeaturesignupclose($featuresignupclose)
    {
        $this->featuresignupclose = $featuresignupclose;

        return $this;
    }

    /**
     * Get featuresignupclose.
     *
     * @return string
     */
    public function getFeaturesignupclose()
    {
        return $this->featuresignupclose;
    }

    /**
     * Set featuresearchpageisclosed.
     *
     * @param string $featuresearchpageisclosed
     *
     * @return Params
     */
    public function setFeaturesearchpageisclosed($featuresearchpageisclosed)
    {
        $this->featuresearchpageisclosed = $featuresearchpageisclosed;

        return $this;
    }

    /**
     * Get featuresearchpageisclosed.
     *
     * @return string
     */
    public function getFeaturesearchpageisclosed()
    {
        return $this->featuresearchpageisclosed;
    }

    /**
     * Set featurequicksearchisclosed.
     *
     * @param string $featurequicksearchisclosed
     *
     * @return Params
     */
    public function setFeaturequicksearchisclosed($featurequicksearchisclosed)
    {
        $this->featurequicksearchisclosed = $featurequicksearchisclosed;

        return $this;
    }

    /**
     * Get featurequicksearchisclosed.
     *
     * @return string
     */
    public function getFeaturequicksearchisclosed()
    {
        return $this->featurequicksearchisclosed;
    }

    /**
     * Set rssfeedisclosed.
     *
     * @param string $rssfeedisclosed
     *
     * @return Params
     */
    public function setRssfeedisclosed($rssfeedisclosed)
    {
        $this->rssfeedisclosed = $rssfeedisclosed;

        return $this;
    }

    /**
     * Get rssfeedisclosed.
     *
     * @return string
     */
    public function getRssfeedisclosed()
    {
        return $this->rssfeedisclosed;
    }

    /**
     * Set ajaxchatspecialallowedlist.
     *
     * @param string $ajaxchatspecialallowedlist
     *
     * @return Params
     */
    public function setAjaxchatspecialallowedlist($ajaxchatspecialallowedlist)
    {
        $this->ajaxchatspecialallowedlist = $ajaxchatspecialallowedlist;

        return $this;
    }

    /**
     * Get ajaxchatspecialallowedlist.
     *
     * @return string
     */
    public function getAjaxchatspecialallowedlist()
    {
        return $this->ajaxchatspecialallowedlist;
    }

    /**
     * Set ajaxchatdebulevel.
     *
     * @param int $ajaxchatdebulevel
     *
     * @return Params
     */
    public function setAjaxchatdebulevel($ajaxchatdebulevel)
    {
        $this->ajaxchatdebulevel = $ajaxchatdebulevel;

        return $this;
    }

    /**
     * Get ajaxchatdebulevel.
     *
     * @return int
     */
    public function getAjaxchatdebulevel()
    {
        return $this->ajaxchatdebulevel;
    }

    /**
     * Set reloadrightsandflags.
     *
     * @param string $reloadrightsandflags
     *
     * @return Params
     */
    public function setReloadrightsandflags($reloadrightsandflags)
    {
        $this->reloadrightsandflags = $reloadrightsandflags;

        return $this;
    }

    /**
     * Get reloadrightsandflags.
     *
     * @return string
     */
    public function getReloadrightsandflags()
    {
        return $this->reloadrightsandflags;
    }

    /**
     * Set logsIdMidnight.
     *
     * @param int $logsIdMidnight
     *
     * @return Params
     */
    public function setLogsIdMidnight($logsIdMidnight)
    {
        $this->logsIdMidnight = $logsIdMidnight;

        return $this;
    }

    /**
     * Get logsIdMidnight.
     *
     * @return int
     */
    public function getLogsIdMidnight()
    {
        return $this->logsIdMidnight;
    }

    /**
     * Set previousLogsIdMidnight.
     *
     * @param int $previousLogsIdMidnight
     *
     * @return Params
     */
    public function setPreviousLogsIdMidnight($previousLogsIdMidnight)
    {
        $this->previousLogsIdMidnight = $previousLogsIdMidnight;

        return $this;
    }

    /**
     * Get previousLogsIdMidnight.
     *
     * @return int
     */
    public function getPreviousLogsIdMidnight()
    {
        return $this->previousLogsIdMidnight;
    }

    /**
     * Set memcache.
     *
     * @param string $memcache
     *
     * @return Params
     */
    public function setMemcache($memcache)
    {
        $this->memcache = $memcache;

        return $this;
    }

    /**
     * Get memcache.
     *
     * @return string
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * Set daylightoffset.
     *
     * @param int $daylightoffset
     *
     * @return Params
     */
    public function setDaylightoffset($daylightoffset)
    {
        $this->daylightoffset = $daylightoffset;

        return $this;
    }

    /**
     * Get daylightoffset.
     *
     * @return int
     */
    public function getDaylightoffset()
    {
        return $this->daylightoffset;
    }

    /**
     * Set nbcommentsinlastcomments.
     *
     * @param int $nbcommentsinlastcomments
     *
     * @return Params
     */
    public function setNbcommentsinlastcomments($nbcommentsinlastcomments)
    {
        $this->nbcommentsinlastcomments = $nbcommentsinlastcomments;

        return $this;
    }

    /**
     * Get nbcommentsinlastcomments.
     *
     * @return int
     */
    public function getNbcommentsinlastcomments()
    {
        return $this->nbcommentsinlastcomments;
    }

    /**
     * Set idcommentofthemoment.
     *
     * @param int $idcommentofthemoment
     *
     * @return Params
     */
    public function setIdcommentofthemoment($idcommentofthemoment)
    {
        $this->idcommentofthemoment = $idcommentofthemoment;

        return $this;
    }

    /**
     * Get idcommentofthemoment.
     *
     * @return int
     */
    public function getIdcommentofthemoment()
    {
        return $this->idcommentofthemoment;
    }

    /**
     * Set mailbotmode.
     *
     * @param string $mailbotmode
     *
     * @return Params
     */
    public function setMailbotmode($mailbotmode)
    {
        $this->mailbotmode = $mailbotmode;

        return $this;
    }

    /**
     * Get mailbotmode.
     *
     * @return string
     */
    public function getMailbotmode()
    {
        return $this->mailbotmode;
    }

    /**
     * Set togglestatsforwordsusage.
     *
     * @param string $togglestatsforwordsusage
     *
     * @return Params
     */
    public function setTogglestatsforwordsusage($togglestatsforwordsusage)
    {
        $this->togglestatsforwordsusage = $togglestatsforwordsusage;

        return $this;
    }

    /**
     * Get togglestatsforwordsusage.
     *
     * @return string
     */
    public function getTogglestatsforwordsusage()
    {
        return $this->togglestatsforwordsusage;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }
}
