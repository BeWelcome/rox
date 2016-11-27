<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Member;
use DateTime;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMemberData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $now = new DateTime();
        $admin = new Member();
        $admin
            ->setCreated($now)
            ->setExUserId(0)
            ->setUsername('admin')
            ->setStatus('Active')
            ->setChangedId(0)
            ->setEmail(0)
            ->setIdCity(0)
            ->setLatitude(0.0)
            ->setLongitude(0.0)
            ->setNbRemindWithoutLogingIn(0)
            ->setHomePhoneNumber(0)
            ->setCellPhoneNumber(0)
            ->setWorkPhoneNumber(0)
            ->setSecEmail(0)
            ->setFirstName(0)
            ->setSecondName(0)
            ->setLastName(0)
            ->setAccomodation('dependonrequest')
            ->setAdditionalAccomodationInfo(0)
            ->setILiveWith(0)
            ->setIdentityCheckLevel(0)
            ->setInformationToGuest(0)
            ->setTypicOffer(0)
            ->setOffer(0)
            ->setMaxGuest(1)
            ->setMaxLenghtOfStay(2)
            ->setOrganizations(0)
            ->setRestrictions(0)
            ->setOtherRestrictions(0)
            ->setBday(1)
            ->setBmonth(1)
            ->setByear(1980)
            ->setBirthdate(new DateTime('1980-01-01'))
            ->setupdated($now)
            ->setLastLogin($now)
            ->setSecurityFlag(0)
            ->setQuality(0)
            ->setProfileSummary(0)
            ->setOccupation(0)
            ->setCounterGuests(0)
            ->setCounterHosts(0)
            ->setCounterTrusts(0)
            ->setGender('female')
            ->setHideGender(1)
            ->setGenderOfGuest('male')
            ->setMotivationForHospitality(0)
            ->setHideBirthDate(1)
            ->setAdressHidden(1)
            ->setWebSite(0)
            ->setChatSkype(0)
            ->setChatICQ(0)
            ->setChatAOL(0)
            ->setChatMSN(0)
            ->setChatYahoo(0)
            ->setChatOthers(0)
            ->setFutureTrips(0)
            ->setOldTrips(0)
            ->setLogCount(0)
            ->setHobbies(0)
            ->setBooks(0)
            ->setMusic(0)
            ->setPastTrips(0)
            ->setPlannedTrips(0)
            ->setPleaseBring(0)
            ->setOfferGuests(0)
            ->setOfferHosts(0)
            ->setPublicTransport(0)
            ->setMovies(0)
            ->setChatGoogle(0)
            ->setLastSwitchToActive($now)
            ->setbewelcomed(0);

        $manager->persist($admin);
        $manager->flush();
    }
}