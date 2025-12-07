<?php

namespace App\Tests\Model;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Model\BaseRequestModel;
use DateTime;
use PHPUnit\Framework\TestCase;

class BaseRequestModelTest extends TestCase
{
    private BaseRequestModel $baseRequestModel;

    protected function setUp(): void
    {
        $this->baseRequestModel = new BaseRequestModel();
    }

    public function testGetFinalRequestUpdatesStatusOnAccept(): void
    {
        // Scenario: Accepting a pending request
        // Input: Pending Request, "Accept" action
        // Output: Accepted Request, Sent Message status

        $context = $this->createRequestContext(HostingRequest::REQUEST_OPEN);
        $dataMessage = new Message();
        $dataMessage->setMessage('Sure, welcome!');
        $dataRequest = new HostingRequest();
        // Initialize required fields to avoid uninitialized property access
        $dataRequest->setArrival(new DateTime('tomorrow'));
        $dataRequest->setDeparture(new DateTime('+2 days'));
        $dataMessage->setRequest($dataRequest);

        $finalRequest = $this->baseRequestModel->getFinalRequest(
            $context['sender'],
            $context['receiver'],
            $context['message'],
            $dataMessage,
            'accept'
        );

        // State Verification
        $this->assertEquals(
            HostingRequest::REQUEST_ACCEPTED,
            $finalRequest->getRequest()->getStatus(),
            'Status should be updated to ACCEPTED'
        );
        $this->assertEquals(
            MessageStatusType::SENT,
            $finalRequest->getStatus(),
            'Message status should be sent'
        );
        $this->assertSame(
            $context['sender'],
            $finalRequest->getSender(),
            'Sender should be preserved'
        );
    }

    public function testGetFinalRequestUpdatesDetails(): void
    {
        // Scenario: Modifying request details (e.g., number of travelers)
        // Input: Open Request with 1 traveler. Action "send" (just update/reply). New data has 2 travelers.
        // Output: Request details updated. Status unchanged.

        $context = $this->createRequestContext(HostingRequest::REQUEST_OPEN);
        $originalRequest = $context['message']->getRequest();
        $originalRequest->setNumberOfTravellers(1);

        $dataMessage = new Message();
        $dataMessage->setMessage('Actually calling with 2 people');
        $newData = new HostingRequest();
        $newData->setNumberOfTravellers(2); // Modification
        // Important: in the actual app form handling, these would be populated.
        // We replicate the "form submission" state here.
        $newData->setArrival($originalRequest->getArrival()->toDateTime());
        $newData->setDeparture($originalRequest->getDeparture()->toDateTime());

        $dataMessage->setRequest($newData);

        $finalRequest = $this->baseRequestModel->getFinalRequest(
            $context['sender'],
            $context['receiver'],
            $context['message'],
            $dataMessage,
            'send'
        );

        $this->assertEquals(
            2,
            $finalRequest->getRequest()->getNumberOfTravellers(),
            'Number of travellers should be updated'
        );
        $this->assertEquals(
            HostingRequest::REQUEST_OPEN,
            $finalRequest->getRequest()->getStatus(),
            'Status should remain OPEN'
        );
    }

    public function testHasExpired(): void
    {
        $context = $this->createRequestContext(HostingRequest::REQUEST_OPEN);
        $request = $context['message']->getRequest();

        // Case 1: Arrival in future
        $request->setArrival(new DateTime('+1 day'));
        $this->assertFalse($this->baseRequestModel->hasExpired($context['message']), 'Future request should not be expired');

        // Case 2: Arrival in past
        $request->setArrival(new DateTime('-1 day'));
        $this->assertTrue($this->baseRequestModel->hasExpired($context['message']), 'Past request should be expired');
    }

    /**
     * Helper to create a consistent valid request state.
     * This decouples the test methods from the specific setup requirements of the entities.
     */
    private function createRequestContext(int $status): array
    {
        $sender = new Member();
        $receiver = new Member();

        $request = new HostingRequest();
        $request->setStatus($status);
        $request->setArrival(new DateTime('tomorrow'));
        $request->setDeparture(new DateTime('+2 days'));
        $request->setNumberOfTravellers(1);

        $message = new Message();
        $message->setSubject(new Subject());
        $message->setRequest($request);
        $message->setSender($sender);
        $message->setReceiver($receiver);

        return [
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $message,
        ];
    }
}
