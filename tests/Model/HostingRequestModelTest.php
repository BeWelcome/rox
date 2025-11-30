<?php

namespace App\Tests\Model;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Model\HostingRequestModel;
use DateInterval;
use DateTime;
use Generator;
use PHPMD\Rule\Design\TooManyPublicMethods;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class HostingRequestModelTest extends TestCase
{
    private Member $sender;
    private Member $receiver;
    private Message $parent;
    private Subject $subject;

    protected function setUp(): void
    {
        $this->sender = new Member();
        $this->receiver = new Member();
        $this->parent = new Message();
        $this->subject = new Subject();
        $this->subject->setSubject('subject');
        $this->parent->setReceiver($this->sender);
        $this->parent->setSender($this->receiver);
    }

    public function testRequestExpiredYesterday(): void
    {
        $arrival = new DateTime('yesterday');
        $departure = new DateTime('yesterday')->setTime(23, 59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $message = new Message();
        $message->setRequest($request);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->hasExpired($message);

        $this->assertTrue($expired);
    }

    public function testRequestDoesNotExpireToday(): void
    {
        $arrival = new DateTime('today');
        $departure = new DateTime('today')->setTime(23, 59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $message = new Message();
        $message->setRequest($request);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->hasExpired($message);

        $this->assertFalse($expired);
    }

    public function testRequestForTomorrowHasntExpiredYet(): void
    {
        $arrival = new DateTime('tomorrow');
        $departure = new DateTime('tomorrow')->setTime(23, 59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $message = new Message();
        $message->setRequest($request);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->hasExpired($message);

        $this->assertFalse($expired);
    }

    public static function stateDataProvider(): Generator
    {
        yield ['cancel', HostingRequest::REQUEST_CANCELLED];
        yield ['accept', HostingRequest::REQUEST_ACCEPTED];
        yield ['tentatively', HostingRequest::REQUEST_TENTATIVELY_ACCEPTED];
        yield ['decline', HostingRequest::REQUEST_DECLINED];
    }

    #[DataProvider('stateDataProvider')]
    public function testGetFinalRequestStateChange(string $clickedButton, int $expected): void
    {
        $arrival = new DateTime();
        $departure = $arrival->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage(
            $arrival,
            $departure,
            false,
            1,
            HostingRequest::REQUEST_OPEN
        );
        $formRequestMessage = $this->setupRequestMessage(
            $arrival,
            $departure,
            false,
            1,
            HostingRequest::REQUEST_OPEN
        );

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            $clickedButton
        );
        // Check that the status changed correctly
        $this->assertSame($finalRequestMessage->getRequest(), $hostingRequestMessage->getRequest());
        $this->assertSame($finalRequestMessage->getStatus(), MessageStatusType::SENT);
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $expected);

        // Check that all other properties didn't change. Arrival and departure checked for equality only as getArrival
        // and getDeparture return a new Carbon instance
        $this->assertEquals(
            $finalRequestMessage->getRequest()->getArrival(),
            $hostingRequestMessage->getRequest()->getArrival()
        );
        $this->assertEquals(
            $finalRequestMessage->getRequest()->getDeparture(),
            $hostingRequestMessage->getRequest()->getDeparture()
        );
        $this->assertSame(
            $finalRequestMessage->getRequest()->getFlexible(),
            $hostingRequestMessage->getRequest()->getFlexible()
        );
        $this->assertSame(
            $finalRequestMessage->getRequest()->getNumberOfTravellers(),
            $hostingRequestMessage->getRequest()->getNumberOfTravellers()
        );
    }

    public function testGetFinalRequestFlexibleChanged(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage(
            $arrival,
            $departure,
            false,
            1,
            HostingRequest::REQUEST_OPEN
        );
        $formRequestMessage = $this->setupRequestMessage(
            $arrival,
            $departure,
            true,
            1,
            HostingRequest::REQUEST_OPEN
        );

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the flexible value changed
        $this->assertNotSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertSame($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public static function arrivalDataProvider(): Generator
    {
        yield [new DateTime('2020-10-10'), new DateTime('2019-10-10')];
        yield [new DateTime('2020-10-10'), new DateTime('2020-09-10')];
        yield [new DateTime('2020-10-10'), new DateTime('2019-10-09')];
        yield [new DateTime('2020-10-10'), new DateTime('2021-10-10')];
        yield [new DateTime('2020-10-10'), new DateTime('2020-11-10')];
        yield [new DateTime('2020-10-10'), new DateTime('2010-10-11')];
    }

    #[DataProvider('arrivalDataProvider')]
    public function testGetFinalRequestArrivalChanged(DateTime $arrival, DateTime $departure): void
    {
        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, true, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($departure, $departure, true, 1, HostingRequest::REQUEST_OPEN);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $departure);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    #[DataProvider('arrivalDataProvider')]
    public function testGetFinalRequestDepartureChanged(DateTime $departure, DateTime $newDeparture): void
    {
        $arrival = new DateTime();

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $newDeparture, false, 1, HostingRequest::REQUEST_OPEN);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $newDeparture);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestArrivalAndDepartureChanged(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));
        $newArrival = (clone $arrival)->add(new DateInterval('P1M'));
        $newDeparture = (clone $arrival)->add(new DateInterval('P1M1D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($newArrival, $newDeparture, false, 1, HostingRequest::REQUEST_OPEN);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertNotEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $newArrival);
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $newDeparture);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestNumberOfTravellersChanged(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 5, HostingRequest::REQUEST_OPEN);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the arrival date changed correctly
        $this->assertNotSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), 5);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
    }

    public function testGetFinalRequestMessage(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'No Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertSame($finalRequestMessage->getMessage(), 'Message');

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertSame($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestParent(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertSame($finalRequestMessage->getParent(), $hostingRequestMessage);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertSame($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestSubject(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertSame($finalRequestMessage->getSubject(), $this->subject);

        // Check that all other properties didn't change
        $this->assertSame($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertSame($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertSame($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertSame($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertSame($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testFinalRequestContainsCorrectInviteForLegAsNull(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message', null);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message', null);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that inviteForLeg was set correctly
        $this->assertNull($finalRequestMessage->getRequest()->getInviteForLeg());
    }

    public function testFinalRequestContainsCorrectInviteForLeg(): void
    {
        $arrival = new DateTime();
        $departure = (clone $arrival)->add(new DateInterval('P2D'));

        $inviteForLeg = new Subtrip()->setInvitedBy($this->sender);

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message', null);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message', $inviteForLeg);

        $requestModel = new HostingRequestModel();
        $finalRequestMessage = $requestModel->getFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that inviteForLeg was set correctly
        $this->assertSame($this->sender, $finalRequestMessage->getRequest()->getInviteForLeg()->getInvitedBy());
    }

    private function setupRequestMessage(
        DateTime $arrival,
        DateTime $departure,
        bool $flexible,
        int $numberOfTravellers,
        int $state,
        string $messageText = '',
        ?Subtrip $inviteForLeg = null,
    ): Message {
        $message = new Message();
        $message->setParent($this->parent);
        $message->setSender($this->sender);
        $message->setReceiver($this->receiver);
        $message->setMessage($messageText);
        $message->setSubject($this->subject);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);
        $request->setFlexible($flexible);
        $request->setNumberOfTravellers($numberOfTravellers);
        $request->setStatus($state);
        $request->setInviteForLeg($inviteForLeg);

        $message->setRequest($request);

        return $message;
    }
}
