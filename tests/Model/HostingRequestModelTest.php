<?php

namespace App\Tests\Model;

use App\Controller\HostingRequestController;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Model\HostingRequestModel;
use App\Model\MessageModel;
use App\Service\Mailer;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class HostingRequestModelTest extends TestCase
{
    /** @var Member */
    private $sender;
    /** @var Member */
    private $receiver;
    /** @var Message */
    private $parent;
    /** @var Subject */
    private $subject;

    public function setUp(): void
    {
        $this->sender = new Member();
        $this->receiver = new Member();
        $this->parent = new Message();
        $this->subject = new Subject();
        $this->subject->setSubject('subject');
        $this->parent->setReceiver($this->sender);
        $this->parent->setSender($this->receiver);
    }

    /**
     * Call getFinalRequest on HostingRequestModel
     */
    private function invokeGetFinalRequest(
        Member $sender,
        Member $receiver,
        Message $hostingRequest,
        Message $data,
        string $clickedButton
    ): Message
    {
        $requestModel = new HostingRequestModel();
        return $requestModel->getFinalRequest($sender, $receiver, $hostingRequest, $data, $clickedButton);
    }

    public function testRequestExpiredYesterday()
    {
        $arrival = new DateTime('yesterday');
        $departure = (new DateTime('yesterday'))->setTime(23, 59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->isRequestExpired($request);

        $this->assertSame(true, $expired);
    }

    public function testRequestExpiresToday()
    {
        $arrival = new DateTime('yesterday');
        $departure = (new DateTime('today'))->setTime(23,59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->isRequestExpired($request);

        $this->assertSame(false, $expired);
    }

    public function testRequestExpiresTomorrow()
    {
        $arrival = new DateTime('yesterday');
        $departure = (new DateTime('tomorrow'))->setTime(23,59);

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->isRequestExpired($request);

        $this->assertSame(false, $expired);
    }

    public function testRequestExpiresNoDeparture()
    {
        $arrival = new DateTime('yesterday');

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture(null);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->isRequestExpired($request);

        $this->assertSame(false, $expired);
    }

    /**
     * @param $arrival
     * @param $departure
     * @param $flexible
     * @param $numberOfTravellers
     * @param $state
     * @return Message
     * @throws InvalidArgumentException|\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function setupRequestMessage($arrival, $departure, $flexible, $numberOfTravellers, $state, $messageText = '')
    {
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

        $message->setRequest($request);

        return $message;
    }

    public function stateDataProvider()
    {
        return [
            ['cancel', HostingRequest::REQUEST_CANCELLED],
            ['accept', HostingRequest::REQUEST_ACCEPTED],
            ['tentatively', HostingRequest::REQUEST_TENTATIVELY_ACCEPTED],
            ['decline', HostingRequest::REQUEST_DECLINED],
        ];
    }

    /**
     * @dataProvider stateDataProvider
     */
    public function testGetFinalRequestStateChange($clickedButton, $expected)
    {
        $arrival = new DateTime();
        $departure = $arrival->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
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

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestFlexibleChanged()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, true, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the status changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }


    public function arrivalDataProvider()
    {
        return [
            ['2020-10-10', '2019-10-10'],
            ['2020-10-10', '2020-09-10'],
            ['2020-10-10', '2019-10-09'],
            ['2020-10-10', '2021-10-10'],
            ['2020-10-10', '2020-11-10'],
            ['2020-10-10', '2010-10-11'],
        ];
    }

    /**
     * @dataProvider arrivalDataProvider
     */
    public function testGetFinalRequestArrivalChanged($old, $new)
    {
        $arrival = new DateTime($old);
        $departure = new DateTime($new);

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, true, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($departure, $departure, true, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
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
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    /**
     * @dataProvider arrivalDataProvider
     */
    public function testGetFinalRequestDepartureChanged($old, $new)
    {
        $arrival = new DateTime();
        $departure = new DateTime($old);
        $newDeparture = new DateTime($new);

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $newDeparture, false, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
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
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestArrivalAndDepartureChanged()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));
        $newArrival = (clone($arrival))->add(new DateInterval('P1M'));
        $newDeparture = (clone($arrival))->add(new DateInterval('P1M1D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($newArrival, $newDeparture, false, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
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
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestNumberOfTravellersChanged()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 5, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), 5);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
    }

    public function testGetFinalRequestMessage()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'No Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $finalRequestMessage = $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertEquals($finalRequestMessage->getMessage(), 'Message');

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestParent()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $finalRequestMessage = $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertEquals($finalRequestMessage->getParent(), $hostingRequestMessage);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestSubject()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $finalRequestMessage = $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        );

        // Check that the message was set correctly
        $this->assertEquals($finalRequestMessage->getSubject(), $this->subject);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testDepartureNotNullOriginal()
    {
        $this->expectException(InvalidArgumentException::class);

        $arrival = new DateTime();
        $departure = null;

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'new'
        );
    }

    public function testDepartureNotNullForm()
    {
        $this->expectException(InvalidArgumentException::class);

        $arrival = new DateTime();
        $departure = new DateTime();

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN, 'Message');
        $formRequestMessage = $this->setupRequestMessage($arrival, null, false, 1, HostingRequest::REQUEST_OPEN, 'Message');

        $this->invokeGetFinalRequest(
            $this->sender,
            $this->receiver,
            $hostingRequestMessage,
            $formRequestMessage,
            'new'
        );
    }
}
