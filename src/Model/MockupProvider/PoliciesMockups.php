<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class PoliciesMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'Terms of Use' => [
            'type' => 'page',
            'url' => 'terms',
            'template' => 'policies/tou_translated.html.twig',
            'description' => 'The terms of use. Make sure to translate them fully before asking for publication.',
        ],
        'Privacy Policy' => [
            'type' => 'page',
            'url' => 'privacy_policy',
            'template' => 'policies/pp_translated.html.twig',
            'description' => 'The privacy policy. Make sure to translate them fully before asking for publication.',
        ],
        'Data Privacy' => [
            'type' => 'page',
            'url' => 'datarights/',
            'template' => 'policies/dp_translated.html.twig',
            'description' => 'The data privacy policy. Make sure to translate them fully before asking for publication.',
        ],
    ];

    public function getFeature(): string
    {
        return 'policies';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'Terms of Use':
                $policyEnglish = 'terms';
                break;
            case 'Privacy Policy':
                $policyEnglish = 'privacy';
                break;
            case 'Data Privacy':
                $policyEnglish = 'datarights';
                break;
            default:
                $policyEnglish = '';
        }
        return [
            'policy_english' => $policyEnglish,
        ];
    }

    public function getMockupParameter(): array
    {
        return [];
    }
}
