<?php

namespace App\Model;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Utilities\ManagerTrait;
use DateTime;
use InvalidArgumentException;

/**
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
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
            $departure = (clone $today)->modify('+2days');
        }

        return !($today < $departure);
    }

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
        $originalRequest = $hostingRequest->getRequest();
        $currentRequest = $data->getRequest();
        $arrivalDiff = date_diff($originalRequest->getArrival(), $currentRequest->getArrival());
        $newArrival = !(0 === $arrivalDiff->y && 0 === $arrivalDiff->m && 0 === $arrivalDiff->d);

        $departureDiff = date_diff($originalRequest->getDeparture(), $currentRequest->getDeparture());
        $newDeparture = !(0 === $departureDiff->y && 0 === $departureDiff->m && 0 === $departureDiff->d);

        $newFlexible = ($originalRequest->getFlexible() !== $currentRequest->getFlexible());

        $newNumberOfTravellers =
            ($originalRequest->getNumberOfTravellers() !== $currentRequest->getNumberOfTravellers());

        $newHostingRequest = new HostingRequest();
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
     * The requestChanged parameter triggers a PHPMD warning which is out of place in this case.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param mixed $subject
     * @param mixed $template
     * @param mixed $requestChanged
     */
    public function sendRequestNotification(
        Member $sender,
        Member $receiver,
        Member $host,
        Message $request,
        $subject,
        $template,
        $requestChanged
    ) {
        // Send mail notification
        $this->mailer->sendMessageNotificationEmail($sender, $receiver, $template, [
            'host' => $host,
            'subject' => $subject,
            'message' => $request,
            'request' => $request->getRequest(),
            'changed' => $requestChanged,
        ]);

        return true;
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
}
