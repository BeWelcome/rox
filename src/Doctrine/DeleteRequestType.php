<?php

namespace App\Doctrine;

class DeleteRequestType extends SetType
{
    public const string SENDER_DELETED = 'senderdeleted';
    public const string RECEIVER_DELETED = 'receiverdeleted';
    public const string SENDER_PURGED = 'senderpurged';
    public const string RECEIVER_PURGED = 'receiverpurged';

    protected string $name = 'delete_request';

    protected array $values = [
        self::SENDER_DELETED,
        self::RECEIVER_DELETED,
        self::SENDER_PURGED,
        self::RECEIVER_PURGED,
    ];

    public static function addSenderDeleted($deleteRequest): string
    {
        return self::addRequest($deleteRequest, self::SENDER_DELETED);
    }

    public static function addReceiverDeleted($deleteRequest): string
    {
        return self::addRequest($deleteRequest, self::RECEIVER_DELETED);
    }

    public static function removeSenderDeleted($deleteRequest): string
    {
        return self::removeRequest($deleteRequest, self::SENDER_DELETED);
    }

    public static function removeReceiverDeleted($deleteRequest): string
    {
        return self::removeRequest($deleteRequest, self::RECEIVER_DELETED);
    }

    public static function addSenderPurged($deleteRequest): string
    {
        return self::addRequest($deleteRequest, self::SENDER_PURGED);
    }

    public static function addReceiverPurged($deleteRequest): string
    {
        return self::addRequest($deleteRequest, self::RECEIVER_PURGED);
    }

    public static function removeSenderPurged($deleteRequest): string
    {
        return self::removeRequest($deleteRequest, self::SENDER_PURGED);
    }

    public static function removeReceiverPurged($deleteRequest): string
    {
        return self::removeRequest($deleteRequest, self::RECEIVER_PURGED);
    }

    private static function addRequest($deleteRequest, $request): string
    {
        $requests = array_filter(explode(',', (string) $deleteRequest));
        $key = array_search($request, $requests, true);
        if (false === $key) {
            $requests[] = $request;
        }

        return implode(',', $requests);
    }

    private static function removeRequest($deleteRequest, $request): string
    {
        $requests = array_filter(explode(',', (string) $deleteRequest));
        $key = array_search($request, $requests, true);
        if (false !== $key) {
            unset($requests[$key]);
        }

        return implode(',', $requests);
    }
}
