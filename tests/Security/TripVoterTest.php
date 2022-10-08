<?php

namespace App\Tests\Security;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Member;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Security\TripVoter;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TripVoterTest extends TestCase
{
    public function testTripCreatorCanEditIfNotExpired()
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $member,
        ]);
        $tomorrow = new DateTime('+1day');
        $leg = new Subtrip();
        $leg->setDeparture($tomorrow);

        $trip = new Trip();
        $trip->setCreator($member);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testTripCreatorCanNotEditIfExpired()
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $member,
        ]);
        $yesterday = new DateTime('-1day');
        $leg = new Subtrip();
        $leg->setDeparture($yesterday);

        $trip = new Trip();
        $trip->setCreator($member);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    public function testTripCreatorCanViewIfExpired()
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $member,
        ]);
        $yesterday = new DateTime('-1day');
        $leg = new Subtrip();
        $leg->setDeparture($yesterday);

        $trip = new Trip();
        $trip->setCreator($member);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testOnlyTripCreatorCanViewIfExpired()
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $member,
        ]);
        $yesterday = new DateTime('-1day');
        $leg = new Subtrip();
        $leg->setDeparture($yesterday);

        $creator = Mockery::mock(Member::class);
        $trip = new Trip();
        $trip->setCreator($creator);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    public function testCreatorCanViewCompletelyPrivateTrip()
    {
        $creator = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $creator,
        ]);
        $tomorrow = new DateTime('+1day');
        $leg = new Subtrip();
        $leg->setDeparture($tomorrow);
        $leg->setOptions([SubtripOptionsType::PRIVATE]);

        $trip = new Trip();
        $trip->setCreator($creator);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testOnlyTripCreatorCanViewCompletelyPrivateTrip()
    {
        $member = Mockery::mock(Member::class);
        $token = Mockery::mock(TokenInterface::class, [
            'getUser' => $member,
        ]);
        $yesterday = new DateTime('+1day');
        $leg = new Subtrip();
        $leg->setDeparture($yesterday);
        $leg->setOptions([SubtripOptionsType::PRIVATE]);

        $creator = Mockery::mock(Member::class);
        $trip = new Trip();
        $trip->setCreator($creator);
        $trip->addSubtrip($leg);

        $tripVoter = new TripVoter();
        $vote = $tripVoter->vote($token, $trip, [TripVoter::TRIP_VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }
}
