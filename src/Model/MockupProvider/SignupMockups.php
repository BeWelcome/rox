<?php

namespace App\Model\MockupProvider;

use App\Entity\Member;

class SignupMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'Confirm Email Address' => [
            'type' => 'email',
            'template' => 'emails/signup.html.twig',
            'description' => 'Email containing the link to confirm email address.',
            'setup' => 'getSignupParameters',
        ],
        'Confirm Email Address Resent' => [
            'type' => 'email',
            'template' => 'emails/resent.html.twig',
            'description' => 'Email containing the link to confirm email address with some extra text.',
            'setup' => 'getSignupParameters',
        ],
        'Finish' => [
            'type' => 'page',
            'url' => 'signup/finish',
            'template' => 'signup/finish.html.twig',
            'description' => 'Successful signup.',
        ],
        'Error' => [
            'type' => 'page',
            'url' => 'signup/finish',
            'template' => 'signup/error.html.twig',
            'description' => 'Error during signup.',
        ],
        'Signup Email Resent' => [
            'type' => 'page',
            'template' => 'signup/resent.html.twig',
            'description' => 'Email with confirmation links has been resent.',
        ],
    ];

    public function getFeature(): string
    {
        return 'signups';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        /** @var Member $user */
        $user = $parameters['user'];

        return [
            'username' => $user->getUsername(),
            'gender' => $user->getGender(),
            'key' => hash('sha256', $user->getUsername()),
            'email_address' => $user->getEmail(),
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
