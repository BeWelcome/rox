<?php

namespace App\Doctrine;

class DeleteRequestType extends SetType
{
    const SENDER_DELETED = 'senderdeleted';
    const RECEIVER_DELETED = 'receiverdeleted';
    const SENDER_PURGED = 'senderpurged';
    const RECEIVER_PURGED = 'receiverpurged';

    /** @var string */
    protected $name = 'delete_request';

    /** @var array */
    protected $values = [
        self::SENDER_DELETED,
        self::RECEIVER_DELETED,
        self::SENDER_PURGED,
        self::RECEIVER_PURGED,
    ];

    public static function addSenderDeleted($deleteRequest)
    {
        return self::addRequest($deleteRequest, self::SENDER_DELETED);
    }

    public static function addReceiverDeleted($deleteRequest)
    {
        return self::addRequest($deleteRequest, self::RECEIVER_DELETED);
    }

    public static function removeSenderDeleted($deleteRequest)
    {
        return self::removeRequest($deleteRequest, self::SENDER_DELETED);
    }

    public static function removeReceiverDeleted($deleteRequest)
    {
        return self::removeRequest($deleteRequest, self::RECEIVER_DELETED);
    }

    public static function addSenderPurged($deleteRequest)
    {
        return self::addRequest($deleteRequest, self::SENDER_PURGED);
    }

    public static function addReceiverPurged($deleteRequest)
    {
        return self::addRequest($deleteRequest, self::RECEIVER_PURGED);
    }

    public static function removeSenderPurged($deleteRequest)
    {
        return self::removeRequest($deleteRequest, self::SENDER_PURGED);
    }

    public static function removeReceiverPurged($deleteRequest)
    {
        return self::removeRequest($deleteRequest, self::RECEIVER_PURGED);
    }

    private static function addRequest($deleteRequest, $request)
    {
        $requests = array_filter(explode(',', $deleteRequest));
        $key = array_search($request, $requests, true);
        if (false === $key) {
            $requests[] = $request;
        }

        return implode(',', $requests);
    }

    private static function removeRequest($deleteRequest, $request)
    {
        $requests = array_filter(explode(',', $deleteRequest));
        $key = array_search($request, $requests, true);
        if (false !== $key) {
            unset($requests[$key]);
        }
        $requests = implode(',', $requests);

        return ('' === $requests) ? null : $requests;
    }
}
