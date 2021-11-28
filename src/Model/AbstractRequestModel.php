<?php

namespace App\Model;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use Exception;
use InvalidArgumentException;

class AbstractRequestModel
{

    /**
     * @throws InvalidArgumentException|\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function getFinalRequest(
        Member $sender,
        Member $receiver,
        Message $hostingRequest,
        Message $data,
        string $clickedButton
    ): Message {
        if (null === $hostingRequest->getRequest()->getDeparture() || null === $data->getRequest()->getDeparture()) {
            throw new InvalidArgumentException();
        }

        $finalRequest = new Message();
        $finalRequest->setSender($sender);
        $finalRequest->setReceiver($receiver);
        $finalRequest->setParent($hostingRequest);
        $finalRequest->setMessage($data->getMessage());
        $finalRequest->setSubject($hostingRequest->getSubject());
        $finalRequest->setStatus(MessageStatusType::SENT);

        $oldState = $hostingRequest->getRequest()->getStatus();
        $newState = $this->getNewState($clickedButton, $oldState);

        $newStateSet = ($oldState !== $newState);

        // check if request was altered
        $originalRequest = $hostingRequest->getRequest();
        $currentRequest = $data->getRequest();

        $newArrival = $this->hasArrivalChanged($originalRequest, $currentRequest);

        $newDeparture = $this->hasDepartureChanged($originalRequest, $currentRequest);

        $newFlexible = ($originalRequest->getFlexible() !== $currentRequest->getFlexible());

        $newNumberOfTravellers =
            ($originalRequest->getNumberOfTravellers() !== $currentRequest->getNumberOfTravellers());

        $newHostingRequest = new HostingRequest();
        $newHostingRequest->setInviteForLeg($hostingRequest->getRequest()->getInviteForLeg());
        $newHostingRequest->setArrival(
            $this->getFinal($originalRequest->getArrival(), $currentRequest->getArrival())
        );
        $newHostingRequest->setDeparture(
            $this->getFinal($originalRequest->getDeparture(), $currentRequest->getDeparture())
        );
        $newHostingRequest->setFlexible(
            $this->getFinal($originalRequest->getFlexible(), $currentRequest->getFlexible())
        );
        $newHostingRequest->setNumberOfTravellers(
            $this->getFinal($originalRequest->getNumberOfTravellers(), $currentRequest->getNumberOfTravellers())
        );
        if ($newArrival || $newDeparture || $newFlexible || $newNumberOfTravellers) {
            $finalRequest->setRequest($newHostingRequest);
        } else {
            $finalRequest->setRequest($hostingRequest->getRequest());
        }

        if ($newStateSet) {
            $finalRequest->getRequest()->setStatus($newState);
        }

        return $finalRequest;
    }

    /**
     * @param $original
     * @param $current
     *
     * @return mixed
     */
    private function getFinal($original, $current)
    {
        return ($original !== $current) ? $current : $original;
    }

    private function hasArrivalChanged(HostingRequest $original, HostingRequest $current): bool
    {
        $arrivalDiff = date_diff($original->getArrival(), $current->getArrival());

        return !(0 === $arrivalDiff->y && 0 === $arrivalDiff->m && 0 === $arrivalDiff->d);
    }

    private function hasDepartureChanged(HostingRequest $original, HostingRequest $current): bool
    {
        $departureDiff = date_diff($original->getDeparture(), $current->getDeparture());

        return !(0 === $departureDiff->y && 0 === $departureDiff->m && 0 === $departureDiff->d);
    }

    private function getNewState(string $clickedButton, int $oldState): int
    {
        $newState = $oldState;
        if ('cancel' === $clickedButton) {
            $newState = HostingRequest::REQUEST_CANCELLED;
        } elseif ('decline' === $clickedButton) {
            $newState = HostingRequest::REQUEST_DECLINED;
        } elseif ('tentatively' === $clickedButton) {
            $newState = HostingRequest::REQUEST_TENTATIVELY_ACCEPTED;
        } elseif ('accept' === $clickedButton) {
            $newState = HostingRequest::REQUEST_ACCEPTED;
        }

        return $newState;
    }

    public function hasExpired(Message $message): bool
    {
        throw new Exception("What just happened?");
    }
}
