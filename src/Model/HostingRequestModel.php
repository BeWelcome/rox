<?php

namespace App\Model;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Utilities\ManagerTrait;
use DateInterval;
use DateTime;
use InvalidArgumentException;

class HostingRequestModel
{
    use ManagerTrait;

    public function getFilteredRequests($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findLatestRequests($member, 'requests_' . $folder, $sort, $sortDir, $page, $limit);
    }

    public function isRequestExpired(HostingRequest $request): bool
    {
        $today = new DateTime('today');
        $departure = $request->getDeparture();
        if (null === $departure) {
            // No departure date given assume an interval of two days max
            $departure = (clone($today))->modify('+2days');
        }

        return ($today < $departure) ? false : true;
    }

    /**
     * @param $clickedButton
     * @param mixed $sender
     * @param mixed $receiver
     *
     * @return Message
     * @throws InvalidArgumentException
     *
     */
    public function getFinalRequest(Member $sender, Member $receiver, Message $hostingRequest, Message $data, $clickedButton)
    {
        if (null === $hostingRequest->getRequest()->getDeparture() || null === $data->getRequest()->getDeparture()) {
            throw new InvalidArgumentException();
        }

        $finalRequest = new Message();
        $finalRequest->setSender($hostingRequest->getSender());
        $finalRequest->setReceiver($hostingRequest->getSender());
        $finalRequest->setParent($hostingRequest);
        $finalRequest->setMessage($data->getMessage());
        $finalRequest->setSubject($hostingRequest->getSubject());
        $finalRequest->setStatus(MessageStatusType::SENT);

        $oldState = $hostingRequest->getRequest()->getStatus();
        $newState = $oldState;
        switch ($clickedButton) {
            case 'cancel':
                $newState = HostingRequest::REQUEST_CANCELLED;
                break;
            case 'decline':
                $newState = HostingRequest::REQUEST_DECLINED;
                break;
            case 'tentatively':
                $newState = HostingRequest::REQUEST_TENTATIVELY_ACCEPTED;
                break;
            case 'accept':
                $newState = HostingRequest::REQUEST_ACCEPTED;
                break;
        }

        $newStateSet = ($oldState !== $newState);

        // check if request was altered
        $arrivalDiff = date_diff($data->getRequest()->getArrival(), $hostingRequest->getRequest()->getArrival());
        $newArrival = !(0 === $arrivalDiff->y && 0 === $arrivalDiff->m && 0 === $arrivalDiff->d);

        $departureDiff = date_diff($data->getRequest()->getDeparture(), $hostingRequest->getRequest()->getDeparture());
        $newDeparture = !(0 === $departureDiff->y && 0 === $departureDiff->m && 0 === $departureDiff->d);

        $newFlexible = ($data->getRequest()->getFlexible() !== $hostingRequest->getRequest()->getFlexible());

        $newNumberOfTravellers = ($data->getRequest()->getNumberOfTravellers()
            !== $hostingRequest->getRequest()->getNumberOfTravellers());

        if ($newArrival || $newDeparture || $newFlexible || $newNumberOfTravellers) {
            $newHostingRequest = new HostingRequest();
            $newHostingRequest->setArrival($data->getRequest()->getArrival());
            $newHostingRequest->setDeparture($data->getRequest()->getDeparture());
            $newHostingRequest->setFlexible($data->getRequest()->getFlexible());
            $newHostingRequest->setNumberOfTravellers($data->getRequest()->getNumberOfTravellers());
            $finalRequest->setRequest($newHostingRequest);
        } else {
            $finalRequest->setRequest($hostingRequest->getRequest());
        }
        if ($newStateSet) {
            $finalRequest->getRequest()->setStatus($newState);
        }

        return $finalRequest;
    }
}
