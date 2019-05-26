<?php


namespace App\Tests;

use App\Controller\HostingRequestController;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\MessageModel;
use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;

class HostingRequestControllerTest extends TestCase
{
    /** @var Member  */
    private $sender;
    /** @var Member  */
    private $receiver;
    /** @var Message  */
    private $parent;

    public function setUp()
    {
        $this->sender = new Member();
        $this->receiver = new Member();
        $this->parent = new Message();
        $this->parent->setReceiver($this->sender);
        $this->parent->setSender($this->receiver);
    }

    /**
     * Call getFinalRequest on HostingRequestController
     *
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    private function invokeGetFinalRequest(array $parameters = array())
    {
        $reflection = new \ReflectionClass(HostingRequestController::class);
        $method = $reflection->getMethod('getFinalRequest');
        $method->setAccessible(true);

        return $method->invokeArgs(new HostingRequestController(new MessageModel()), $parameters);
    }

    /**
     * @param $arrival
     * @param $departure
     * @param $flexible
     * @param $numberOfTravellers
     * @param $state
     * @return Message
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function setupRequestMessage($arrival, $departure, $flexible, $numberOfTravellers, $state)
    {
        $message = new Message();
        $message->setParent($this->parent);
        $message->setSender($this->sender);
        $message->setReceiver($this->receiver);

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

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            $clickedButton
        ]);
        // Check that the status changed correctly
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $expected);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
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

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        ]);
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

    public function testGetFinalRequestArrivalChanged()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, true, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($departure, $departure, true, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        ]);
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $departure);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
    }

    public function testGetFinalRequestDepartureChanged()
    {
        $arrival = new DateTime();
        $departure = (clone($arrival))->add(new DateInterval('P2D'));
        $newDeparture = (clone($arrival))->add(new DateInterval('P4D'));

        $hostingRequestMessage = $this->setupRequestMessage($arrival, $departure, false, 1, HostingRequest::REQUEST_OPEN);
        $formRequestMessage = $this->setupRequestMessage($arrival, $newDeparture, false, 1, HostingRequest::REQUEST_OPEN);

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        ]);
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $newDeparture);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
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

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        ]);
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertNotEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $newArrival);
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $newDeparture);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
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

        $finalRequestMessage = $this->invokeGetFinalRequest([
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage->getSender(),
            $hostingRequestMessage,
            $formRequestMessage,
            'reply'
        ]);
        // Check that the arrival date changed correctly
        $this->assertNotEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), $hostingRequestMessage->getRequest()->getNumberOfTravellers());
        $this->assertEquals($finalRequestMessage->getRequest()->getNumberOfTravellers(), 5);

        // Check that all other properties didn't change
        $this->assertEquals($finalRequestMessage->getSender(), $hostingRequestMessage->getSender());
        $this->assertEquals($finalRequestMessage->getReceiver(), $hostingRequestMessage->getReceiver());
        $this->assertEquals($finalRequestMessage->getRequest()->getStatus(), $hostingRequestMessage->getRequest()->getStatus());
        $this->assertEquals($finalRequestMessage->getRequest()->getFlexible(), $hostingRequestMessage->getRequest()->getFlexible());
        $this->assertEquals($finalRequestMessage->getRequest()->getDeparture(), $hostingRequestMessage->getRequest()->getDeparture());
        $this->assertEquals($finalRequestMessage->getRequest()->getArrival(), $hostingRequestMessage->getRequest()->getArrival());
    }
}
