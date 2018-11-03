<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Members
 *
 * @ORM\Table(name="members", uniqueConstraints={@ORM\UniqueConstraint(name="i_Username", columns={"Username"})}, indexes={@ORM\Index(name="index_by_created", columns={"created"}), @ORM\Index(name="Id2City", columns={"IdCity"}), @ORM\Index(name="ex_user_id", columns={"ex_user_id"}), @ORM\Index(name="Status", columns={"Status"})})
 * @ORM\Entity
 */
class Members
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ex_user_id", type="integer", nullable=false)
     */
    private $exUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="Username", type="string", length=32, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'MailToConfirm';

    /**
     * @var integer
     *
     * @ORM\Column(name="ChangedId", type="integer", nullable=false)
     */
    private $changedid = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="Email", type="integer", nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdCity", type="integer", nullable=false)
     */
    private $idcity;

    /**
     * @var string
     *
     * @ORM\Column(name="Latitude", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="Longitude", type="decimal", precision=10, scale=7, nullable=true)
     */
    private $longitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="NbRemindWithoutLogingIn", type="integer", nullable=false)
     */
    private $nbremindwithoutlogingin;

    /**
     * @var integer
     *
     * @ORM\Column(name="HomePhoneNumber", type="integer", nullable=false)
     */
    private $homephonenumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="CellPhoneNumber", type="integer", nullable=false)
     */
    private $cellphonenumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="WorkPhoneNumber", type="integer", nullable=false)
     */
    private $workphonenumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="SecEmail", type="integer", nullable=false)
     */
    private $secemail;

    /**
     * @var integer
     *
     * @ORM\Column(name="FirstName", type="integer", nullable=false)
     */
    private $firstname = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="SecondName", type="integer", nullable=false)
     */
    private $secondname = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="LastName", type="integer", nullable=false)
     */
    private $lastname = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Accomodation", type="string", nullable=false)
     */
    private $accomodation = 'dependonrequest';

    /**
     * @var integer
     *
     * @ORM\Column(name="AdditionalAccomodationInfo", type="integer", nullable=false)
     */
    private $additionalaccomodationinfo;

    /**
     * @var integer
     *
     * @ORM\Column(name="ILiveWith", type="integer", nullable=false)
     */
    private $ilivewith;

    /**
     * @var boolean
     *
     * @ORM\Column(name="IdentityCheckLevel", type="boolean", nullable=false)
     */
    private $identitychecklevel = '000';

    /**
     * @var integer
     *
     * @ORM\Column(name="InformationToGuest", type="integer", nullable=false)
     */
    private $informationtoguest;

    /**
     * @var string
     *
     * @ORM\Column(name="TypicOffer", type="string", nullable=false)
     */
    private $typicoffer;

    /**
     * @var integer
     *
     * @ORM\Column(name="Offer", type="integer", nullable=false)
     */
    private $offer;

    /**
     * @var integer
     *
     * @ORM\Column(name="MaxGuest", type="integer", nullable=false)
     */
    private $maxguest = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="MaxLenghtOfStay", type="integer", nullable=false)
     */
    private $maxlenghtofstay = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="Organizations", type="integer", nullable=false)
     */
    private $organizations;

    /**
     * @var string
     *
     * @ORM\Column(name="Restrictions", type="string", nullable=false)
     */
    private $restrictions;

    /**
     * @var integer
     *
     * @ORM\Column(name="OtherRestrictions", type="integer", nullable=false)
     */
    private $otherrestrictions;

    /**
     * @var integer
     *
     * @ORM\Column(name="bday", type="integer", nullable=false)
     */
    private $bday;

    /**
     * @var integer
     *
     * @ORM\Column(name="bmonth", type="integer", nullable=false)
     */
    private $bmonth;

    /**
     * @var integer
     *
     * @ORM\Column(name="byear", type="integer", nullable=false)
     */
    private $byear;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LastLogin", type="datetime", nullable=false)
     */
    private $lastlogin = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="SecurityFlag", type="integer", nullable=false)
     */
    private $securityflag = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Quality", type="string", nullable=false)
     */
    private $quality = 'NeverLog';

    /**
     * @var integer
     *
     * @ORM\Column(name="ProfileSummary", type="integer", nullable=false)
     */
    private $profilesummary;

    /**
     * @var integer
     *
     * @ORM\Column(name="Occupation", type="integer", nullable=false)
     */
    private $occupation;

    /**
     * @var integer
     *
     * @ORM\Column(name="CounterGuests", type="integer", nullable=false)
     */
    private $counterguests = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="CounterHosts", type="integer", nullable=false)
     */
    private $counterhosts = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="CounterTrusts", type="integer", nullable=false)
     */
    private $countertrusts = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="PassWord", type="string", length=100, nullable=true)
     */
    private $password = 'ENCRYPT(\'password\')';

    /**
     * @var string
     *
     * @ORM\Column(name="Gender", type="string", nullable=false)
     */
    private $gender = 'IDontTell';

    /**
     * @var string
     *
     * @ORM\Column(name="HideGender", type="string", nullable=false)
     */
    private $hidegender = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="GenderOfGuest", type="string", nullable=false)
     */
    private $genderofguest = 'any';

    /**
     * @var integer
     *
     * @ORM\Column(name="MotivationForHospitality", type="integer", nullable=true)
     */
    private $motivationforhospitality;

    /**
     * @var string
     *
     * @ORM\Column(name="HideBirthDate", type="string", nullable=false)
     */
    private $hidebirthdate = 'No';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="BirthDate", type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="AdressHidden", type="string", nullable=false)
     */
    private $adresshidden = 'Yes';

    /**
     * @var string
     *
     * @ORM\Column(name="WebSite", type="text", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_SKYPE", type="text", length=255, nullable=true)
     */
    private $chatSkype;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_ICQ", type="text", length=255, nullable=true)
     */
    private $chatIcq;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_AOL", type="text", length=255, nullable=true)
     */
    private $chatAol;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_MSN", type="text", length=255, nullable=true)
     */
    private $chatMsn;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_YAHOO", type="text", length=255, nullable=true)
     */
    private $chatYahoo;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_Others", type="text", length=255, nullable=true)
     */
    private $chatOthers;

    /**
     * @var integer
     *
     * @ORM\Column(name="Id4City", type="integer", nullable=false)
     */
    private $id4city;

    /**
     * @var integer
     *
     * @ORM\Column(name="FutureTrips", type="integer", nullable=false)
     */
    private $futuretrips = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="OldTrips", type="integer", nullable=false)
     */
    private $oldtrips = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="LogCount", type="integer", nullable=false)
     */
    private $logcount = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="Hobbies", type="integer", nullable=false)
     */
    private $hobbies;

    /**
     * @var integer
     *
     * @ORM\Column(name="Books", type="integer", nullable=false)
     */
    private $books;

    /**
     * @var integer
     *
     * @ORM\Column(name="Music", type="integer", nullable=false)
     */
    private $music;

    /**
     * @var integer
     *
     * @ORM\Column(name="PastTrips", type="integer", nullable=false)
     */
    private $pasttrips;

    /**
     * @var integer
     *
     * @ORM\Column(name="PlannedTrips", type="integer", nullable=false)
     */
    private $plannedtrips;

    /**
     * @var integer
     *
     * @ORM\Column(name="PleaseBring", type="integer", nullable=false)
     */
    private $pleasebring;

    /**
     * @var integer
     *
     * @ORM\Column(name="OfferGuests", type="integer", nullable=false)
     */
    private $offerguests;

    /**
     * @var integer
     *
     * @ORM\Column(name="OfferHosts", type="integer", nullable=false)
     */
    private $offerhosts;

    /**
     * @var integer
     *
     * @ORM\Column(name="PublicTransport", type="integer", nullable=false)
     */
    private $publictransport;

    /**
     * @var integer
     *
     * @ORM\Column(name="Movies", type="integer", nullable=false)
     */
    private $movies;

    /**
     * @var integer
     *
     * @ORM\Column(name="chat_GOOGLE", type="integer", nullable=false)
     */
    private $chatGoogle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LastSwitchToActive", type="datetime", nullable=true)
     */
    private $lastswitchtoactive;

    /**
     * @var integer
     *
     * @ORM\Column(name="bewelcomed", type="integer", nullable=false)
     */
    private $bewelcomed;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set exUserId
     *
     * @param integer $exUserId
     *
     * @return Members
     */
    public function setExUserId($exUserId)
    {
        $this->exUserId = $exUserId;

        return $this;
    }

    /**
     * Get exUserId
     *
     * @return integer
     */
    public function getExUserId()
    {
        return $this->exUserId;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Members
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Members
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set changedid
     *
     * @param integer $changedid
     *
     * @return Members
     */
    public function setChangedid($changedid)
    {
        $this->changedid = $changedid;

        return $this;
    }

    /**
     * Get changedid
     *
     * @return integer
     */
    public function getChangedid()
    {
        return $this->changedid;
    }

    /**
     * Set email
     *
     * @param integer $email
     *
     * @return Members
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return integer
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set idcity
     *
     * @param integer $idcity
     *
     * @return Members
     */
    public function setIdcity($idcity)
    {
        $this->idcity = $idcity;

        return $this;
    }

    /**
     * Get idcity
     *
     * @return integer
     */
    public function getIdcity()
    {
        return $this->idcity;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Members
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Members
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set nbremindwithoutlogingin
     *
     * @param integer $nbremindwithoutlogingin
     *
     * @return Members
     */
    public function setNbremindwithoutlogingin($nbremindwithoutlogingin)
    {
        $this->nbremindwithoutlogingin = $nbremindwithoutlogingin;

        return $this;
    }

    /**
     * Get nbremindwithoutlogingin
     *
     * @return integer
     */
    public function getNbremindwithoutlogingin()
    {
        return $this->nbremindwithoutlogingin;
    }

    /**
     * Set homephonenumber
     *
     * @param integer $homephonenumber
     *
     * @return Members
     */
    public function setHomephonenumber($homephonenumber)
    {
        $this->homephonenumber = $homephonenumber;

        return $this;
    }

    /**
     * Get homephonenumber
     *
     * @return integer
     */
    public function getHomephonenumber()
    {
        return $this->homephonenumber;
    }

    /**
     * Set cellphonenumber
     *
     * @param integer $cellphonenumber
     *
     * @return Members
     */
    public function setCellphonenumber($cellphonenumber)
    {
        $this->cellphonenumber = $cellphonenumber;

        return $this;
    }

    /**
     * Get cellphonenumber
     *
     * @return integer
     */
    public function getCellphonenumber()
    {
        return $this->cellphonenumber;
    }

    /**
     * Set workphonenumber
     *
     * @param integer $workphonenumber
     *
     * @return Members
     */
    public function setWorkphonenumber($workphonenumber)
    {
        $this->workphonenumber = $workphonenumber;

        return $this;
    }

    /**
     * Get workphonenumber
     *
     * @return integer
     */
    public function getWorkphonenumber()
    {
        return $this->workphonenumber;
    }

    /**
     * Set secemail
     *
     * @param integer $secemail
     *
     * @return Members
     */
    public function setSecemail($secemail)
    {
        $this->secemail = $secemail;

        return $this;
    }

    /**
     * Get secemail
     *
     * @return integer
     */
    public function getSecemail()
    {
        return $this->secemail;
    }

    /**
     * Set firstname
     *
     * @param integer $firstname
     *
     * @return Members
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return integer
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set secondname
     *
     * @param integer $secondname
     *
     * @return Members
     */
    public function setSecondname($secondname)
    {
        $this->secondname = $secondname;

        return $this;
    }

    /**
     * Get secondname
     *
     * @return integer
     */
    public function getSecondname()
    {
        return $this->secondname;
    }

    /**
     * Set lastname
     *
     * @param integer $lastname
     *
     * @return Members
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return integer
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set accomodation
     *
     * @param string $accomodation
     *
     * @return Members
     */
    public function setAccomodation($accomodation)
    {
        $this->accomodation = $accomodation;

        return $this;
    }

    /**
     * Get accomodation
     *
     * @return string
     */
    public function getAccomodation()
    {
        return $this->accomodation;
    }

    /**
     * Set additionalaccomodationinfo
     *
     * @param integer $additionalaccomodationinfo
     *
     * @return Members
     */
    public function setAdditionalaccomodationinfo($additionalaccomodationinfo)
    {
        $this->additionalaccomodationinfo = $additionalaccomodationinfo;

        return $this;
    }

    /**
     * Get additionalaccomodationinfo
     *
     * @return integer
     */
    public function getAdditionalaccomodationinfo()
    {
        return $this->additionalaccomodationinfo;
    }

    /**
     * Set ilivewith
     *
     * @param integer $ilivewith
     *
     * @return Members
     */
    public function setIlivewith($ilivewith)
    {
        $this->ilivewith = $ilivewith;

        return $this;
    }

    /**
     * Get ilivewith
     *
     * @return integer
     */
    public function getIlivewith()
    {
        return $this->ilivewith;
    }

    /**
     * Set identitychecklevel
     *
     * @param boolean $identitychecklevel
     *
     * @return Members
     */
    public function setIdentitychecklevel($identitychecklevel)
    {
        $this->identitychecklevel = $identitychecklevel;

        return $this;
    }

    /**
     * Get identitychecklevel
     *
     * @return boolean
     */
    public function getIdentitychecklevel()
    {
        return $this->identitychecklevel;
    }

    /**
     * Set informationtoguest
     *
     * @param integer $informationtoguest
     *
     * @return Members
     */
    public function setInformationtoguest($informationtoguest)
    {
        $this->informationtoguest = $informationtoguest;

        return $this;
    }

    /**
     * Get informationtoguest
     *
     * @return integer
     */
    public function getInformationtoguest()
    {
        return $this->informationtoguest;
    }

    /**
     * Set typicoffer
     *
     * @param string $typicoffer
     *
     * @return Members
     */
    public function setTypicoffer($typicoffer)
    {
        $this->typicoffer = $typicoffer;

        return $this;
    }

    /**
     * Get typicoffer
     *
     * @return string
     */
    public function getTypicoffer()
    {
        return $this->typicoffer;
    }

    /**
     * Set offer
     *
     * @param integer $offer
     *
     * @return Members
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get offer
     *
     * @return integer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set maxguest
     *
     * @param integer $maxguest
     *
     * @return Members
     */
    public function setMaxguest($maxguest)
    {
        $this->maxguest = $maxguest;

        return $this;
    }

    /**
     * Get maxguest
     *
     * @return integer
     */
    public function getMaxguest()
    {
        return $this->maxguest;
    }

    /**
     * Set maxlenghtofstay
     *
     * @param integer $maxlenghtofstay
     *
     * @return Members
     */
    public function setMaxlenghtofstay($maxlenghtofstay)
    {
        $this->maxlenghtofstay = $maxlenghtofstay;

        return $this;
    }

    /**
     * Get maxlenghtofstay
     *
     * @return integer
     */
    public function getMaxlenghtofstay()
    {
        return $this->maxlenghtofstay;
    }

    /**
     * Set organizations
     *
     * @param integer $organizations
     *
     * @return Members
     */
    public function setOrganizations($organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Get organizations
     *
     * @return integer
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Set restrictions
     *
     * @param string $restrictions
     *
     * @return Members
     */
    public function setRestrictions($restrictions)
    {
        $this->restrictions = $restrictions;

        return $this;
    }

    /**
     * Get restrictions
     *
     * @return string
     */
    public function getRestrictions()
    {
        return $this->restrictions;
    }

    /**
     * Set otherrestrictions
     *
     * @param integer $otherrestrictions
     *
     * @return Members
     */
    public function setOtherrestrictions($otherrestrictions)
    {
        $this->otherrestrictions = $otherrestrictions;

        return $this;
    }

    /**
     * Get otherrestrictions
     *
     * @return integer
     */
    public function getOtherrestrictions()
    {
        return $this->otherrestrictions;
    }

    /**
     * Set bday
     *
     * @param integer $bday
     *
     * @return Members
     */
    public function setBday($bday)
    {
        $this->bday = $bday;

        return $this;
    }

    /**
     * Get bday
     *
     * @return integer
     */
    public function getBday()
    {
        return $this->bday;
    }

    /**
     * Set bmonth
     *
     * @param integer $bmonth
     *
     * @return Members
     */
    public function setBmonth($bmonth)
    {
        $this->bmonth = $bmonth;

        return $this;
    }

    /**
     * Get bmonth
     *
     * @return integer
     */
    public function getBmonth()
    {
        return $this->bmonth;
    }

    /**
     * Set byear
     *
     * @param integer $byear
     *
     * @return Members
     */
    public function setByear($byear)
    {
        $this->byear = $byear;

        return $this;
    }

    /**
     * Get byear
     *
     * @return integer
     */
    public function getByear()
    {
        return $this->byear;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Members
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Members
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastlogin
     *
     * @param \DateTime $lastlogin
     *
     * @return Members
     */
    public function setLastlogin($lastlogin)
    {
        $this->lastlogin = $lastlogin;

        return $this;
    }

    /**
     * Get lastlogin
     *
     * @return \DateTime
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * Set securityflag
     *
     * @param integer $securityflag
     *
     * @return Members
     */
    public function setSecurityflag($securityflag)
    {
        $this->securityflag = $securityflag;

        return $this;
    }

    /**
     * Get securityflag
     *
     * @return integer
     */
    public function getSecurityflag()
    {
        return $this->securityflag;
    }

    /**
     * Set quality
     *
     * @param string $quality
     *
     * @return Members
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality
     *
     * @return string
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set profilesummary
     *
     * @param integer $profilesummary
     *
     * @return Members
     */
    public function setProfilesummary($profilesummary)
    {
        $this->profilesummary = $profilesummary;

        return $this;
    }

    /**
     * Get profilesummary
     *
     * @return integer
     */
    public function getProfilesummary()
    {
        return $this->profilesummary;
    }

    /**
     * Set occupation
     *
     * @param integer $occupation
     *
     * @return Members
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation
     *
     * @return integer
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set counterguests
     *
     * @param integer $counterguests
     *
     * @return Members
     */
    public function setCounterguests($counterguests)
    {
        $this->counterguests = $counterguests;

        return $this;
    }

    /**
     * Get counterguests
     *
     * @return integer
     */
    public function getCounterguests()
    {
        return $this->counterguests;
    }

    /**
     * Set counterhosts
     *
     * @param integer $counterhosts
     *
     * @return Members
     */
    public function setCounterhosts($counterhosts)
    {
        $this->counterhosts = $counterhosts;

        return $this;
    }

    /**
     * Get counterhosts
     *
     * @return integer
     */
    public function getCounterhosts()
    {
        return $this->counterhosts;
    }

    /**
     * Set countertrusts
     *
     * @param integer $countertrusts
     *
     * @return Members
     */
    public function setCountertrusts($countertrusts)
    {
        $this->countertrusts = $countertrusts;

        return $this;
    }

    /**
     * Get countertrusts
     *
     * @return integer
     */
    public function getCountertrusts()
    {
        return $this->countertrusts;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Members
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return Members
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set hidegender
     *
     * @param string $hidegender
     *
     * @return Members
     */
    public function setHidegender($hidegender)
    {
        $this->hidegender = $hidegender;

        return $this;
    }

    /**
     * Get hidegender
     *
     * @return string
     */
    public function getHidegender()
    {
        return $this->hidegender;
    }

    /**
     * Set genderofguest
     *
     * @param string $genderofguest
     *
     * @return Members
     */
    public function setGenderofguest($genderofguest)
    {
        $this->genderofguest = $genderofguest;

        return $this;
    }

    /**
     * Get genderofguest
     *
     * @return string
     */
    public function getGenderofguest()
    {
        return $this->genderofguest;
    }

    /**
     * Set motivationforhospitality
     *
     * @param integer $motivationforhospitality
     *
     * @return Members
     */
    public function setMotivationforhospitality($motivationforhospitality)
    {
        $this->motivationforhospitality = $motivationforhospitality;

        return $this;
    }

    /**
     * Get motivationforhospitality
     *
     * @return integer
     */
    public function getMotivationforhospitality()
    {
        return $this->motivationforhospitality;
    }

    /**
     * Set hidebirthdate
     *
     * @param string $hidebirthdate
     *
     * @return Members
     */
    public function setHidebirthdate($hidebirthdate)
    {
        $this->hidebirthdate = $hidebirthdate;

        return $this;
    }

    /**
     * Get hidebirthdate
     *
     * @return string
     */
    public function getHidebirthdate()
    {
        return $this->hidebirthdate;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     *
     * @return Members
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set adresshidden
     *
     * @param string $adresshidden
     *
     * @return Members
     */
    public function setAdresshidden($adresshidden)
    {
        $this->adresshidden = $adresshidden;

        return $this;
    }

    /**
     * Get adresshidden
     *
     * @return string
     */
    public function getAdresshidden()
    {
        return $this->adresshidden;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Members
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set chatSkype
     *
     * @param string $chatSkype
     *
     * @return Members
     */
    public function setChatSkype($chatSkype)
    {
        $this->chatSkype = $chatSkype;

        return $this;
    }

    /**
     * Get chatSkype
     *
     * @return string
     */
    public function getChatSkype()
    {
        return $this->chatSkype;
    }

    /**
     * Set chatIcq
     *
     * @param string $chatIcq
     *
     * @return Members
     */
    public function setChatIcq($chatIcq)
    {
        $this->chatIcq = $chatIcq;

        return $this;
    }

    /**
     * Get chatIcq
     *
     * @return string
     */
    public function getChatIcq()
    {
        return $this->chatIcq;
    }

    /**
     * Set chatAol
     *
     * @param string $chatAol
     *
     * @return Members
     */
    public function setChatAol($chatAol)
    {
        $this->chatAol = $chatAol;

        return $this;
    }

    /**
     * Get chatAol
     *
     * @return string
     */
    public function getChatAol()
    {
        return $this->chatAol;
    }

    /**
     * Set chatMsn
     *
     * @param string $chatMsn
     *
     * @return Members
     */
    public function setChatMsn($chatMsn)
    {
        $this->chatMsn = $chatMsn;

        return $this;
    }

    /**
     * Get chatMsn
     *
     * @return string
     */
    public function getChatMsn()
    {
        return $this->chatMsn;
    }

    /**
     * Set chatYahoo
     *
     * @param string $chatYahoo
     *
     * @return Members
     */
    public function setChatYahoo($chatYahoo)
    {
        $this->chatYahoo = $chatYahoo;

        return $this;
    }

    /**
     * Get chatYahoo
     *
     * @return string
     */
    public function getChatYahoo()
    {
        return $this->chatYahoo;
    }

    /**
     * Set chatOthers
     *
     * @param string $chatOthers
     *
     * @return Members
     */
    public function setChatOthers($chatOthers)
    {
        $this->chatOthers = $chatOthers;

        return $this;
    }

    /**
     * Get chatOthers
     *
     * @return string
     */
    public function getChatOthers()
    {
        return $this->chatOthers;
    }

    /**
     * Set id4city
     *
     * @param integer $id4city
     *
     * @return Members
     */
    public function setId4city($id4city)
    {
        $this->id4city = $id4city;

        return $this;
    }

    /**
     * Get id4city
     *
     * @return integer
     */
    public function getId4city()
    {
        return $this->id4city;
    }

    /**
     * Set futuretrips
     *
     * @param integer $futuretrips
     *
     * @return Members
     */
    public function setFuturetrips($futuretrips)
    {
        $this->futuretrips = $futuretrips;

        return $this;
    }

    /**
     * Get futuretrips
     *
     * @return integer
     */
    public function getFuturetrips()
    {
        return $this->futuretrips;
    }

    /**
     * Set oldtrips
     *
     * @param integer $oldtrips
     *
     * @return Members
     */
    public function setOldtrips($oldtrips)
    {
        $this->oldtrips = $oldtrips;

        return $this;
    }

    /**
     * Get oldtrips
     *
     * @return integer
     */
    public function getOldtrips()
    {
        return $this->oldtrips;
    }

    /**
     * Set logcount
     *
     * @param integer $logcount
     *
     * @return Members
     */
    public function setLogcount($logcount)
    {
        $this->logcount = $logcount;

        return $this;
    }

    /**
     * Get logcount
     *
     * @return integer
     */
    public function getLogcount()
    {
        return $this->logcount;
    }

    /**
     * Set hobbies
     *
     * @param integer $hobbies
     *
     * @return Members
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
     * Get hobbies
     *
     * @return integer
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * Set books
     *
     * @param integer $books
     *
     * @return Members
     */
    public function setBooks($books)
    {
        $this->books = $books;

        return $this;
    }

    /**
     * Get books
     *
     * @return integer
     */
    public function getBooks()
    {
        return $this->books;
    }

    /**
     * Set music
     *
     * @param integer $music
     *
     * @return Members
     */
    public function setMusic($music)
    {
        $this->music = $music;

        return $this;
    }

    /**
     * Get music
     *
     * @return integer
     */
    public function getMusic()
    {
        return $this->music;
    }

    /**
     * Set pasttrips
     *
     * @param integer $pasttrips
     *
     * @return Members
     */
    public function setPasttrips($pasttrips)
    {
        $this->pasttrips = $pasttrips;

        return $this;
    }

    /**
     * Get pasttrips
     *
     * @return integer
     */
    public function getPasttrips()
    {
        return $this->pasttrips;
    }

    /**
     * Set plannedtrips
     *
     * @param integer $plannedtrips
     *
     * @return Members
     */
    public function setPlannedtrips($plannedtrips)
    {
        $this->plannedtrips = $plannedtrips;

        return $this;
    }

    /**
     * Get plannedtrips
     *
     * @return integer
     */
    public function getPlannedtrips()
    {
        return $this->plannedtrips;
    }

    /**
     * Set pleasebring
     *
     * @param integer $pleasebring
     *
     * @return Members
     */
    public function setPleasebring($pleasebring)
    {
        $this->pleasebring = $pleasebring;

        return $this;
    }

    /**
     * Get pleasebring
     *
     * @return integer
     */
    public function getPleasebring()
    {
        return $this->pleasebring;
    }

    /**
     * Set offerguests
     *
     * @param integer $offerguests
     *
     * @return Members
     */
    public function setOfferguests($offerguests)
    {
        $this->offerguests = $offerguests;

        return $this;
    }

    /**
     * Get offerguests
     *
     * @return integer
     */
    public function getOfferguests()
    {
        return $this->offerguests;
    }

    /**
     * Set offerhosts
     *
     * @param integer $offerhosts
     *
     * @return Members
     */
    public function setOfferhosts($offerhosts)
    {
        $this->offerhosts = $offerhosts;

        return $this;
    }

    /**
     * Get offerhosts
     *
     * @return integer
     */
    public function getOfferhosts()
    {
        return $this->offerhosts;
    }

    /**
     * Set publictransport
     *
     * @param integer $publictransport
     *
     * @return Members
     */
    public function setPublictransport($publictransport)
    {
        $this->publictransport = $publictransport;

        return $this;
    }

    /**
     * Get publictransport
     *
     * @return integer
     */
    public function getPublictransport()
    {
        return $this->publictransport;
    }

    /**
     * Set movies
     *
     * @param integer $movies
     *
     * @return Members
     */
    public function setMovies($movies)
    {
        $this->movies = $movies;

        return $this;
    }

    /**
     * Get movies
     *
     * @return integer
     */
    public function getMovies()
    {
        return $this->movies;
    }

    /**
     * Set chatGoogle
     *
     * @param integer $chatGoogle
     *
     * @return Members
     */
    public function setChatGoogle($chatGoogle)
    {
        $this->chatGoogle = $chatGoogle;

        return $this;
    }

    /**
     * Get chatGoogle
     *
     * @return integer
     */
    public function getChatGoogle()
    {
        return $this->chatGoogle;
    }

    /**
     * Set lastswitchtoactive
     *
     * @param \DateTime $lastswitchtoactive
     *
     * @return Members
     */
    public function setLastswitchtoactive($lastswitchtoactive)
    {
        $this->lastswitchtoactive = $lastswitchtoactive;

        return $this;
    }

    /**
     * Get lastswitchtoactive
     *
     * @return \DateTime
     */
    public function getLastswitchtoactive()
    {
        return $this->lastswitchtoactive;
    }

    /**
     * Set bewelcomed
     *
     * @param integer $bewelcomed
     *
     * @return Members
     */
    public function setBewelcomed($bewelcomed)
    {
        $this->bewelcomed = $bewelcomed;

        return $this;
    }

    /**
     * Get bewelcomed
     *
     * @return integer
     */
    public function getBewelcomed()
    {
        return $this->bewelcomed;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
