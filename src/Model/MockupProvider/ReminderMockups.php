<?php

namespace App\Model\MockupProvider;

use App\Model\MockupProvider\AbstractMockups;
use DateTime;

class ReminderMockups extends AbstractMockups
{
    protected const string FEATURE = 'reminders';

    protected const array MOCKUPS = [
        'Login reminder' => [
            'type' => 'email',
            'template' => 'emails/login.reminder.html.twig',
            'description' => 'Email send automatically once a year to make sure members keep there profile updated',
        ],
        'Suspension notice' => [
            'type' => 'email',
            'template' => 'emails/suspension.notice.html.twig',
            'description' => 'The page doesn\'t exists.',
        ],
    ];

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    public function getMockupVariables(array $parameters): array
    {
        return [
            'deletion_date' => new DateTime('+2months'),
            'receiver' => $parameters['user'],
        ];
    }
}
