<?php

namespace App\Model\MockupProvider;

use App\Entity\NewMember as Member;
use App\Form\SignupFormFinalizeType;
use App\Form\SignupFormType;
use Symfony\Component\Form\FormFactoryInterface;

class SignupMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
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
        'Signup' => [
            'type' => 'page',
            'url' => 'signup/',
            'template' => 'signup/first.step.html.twig',
            'description' => 'Successful signup.',
        ],
        'Finalize' => [
            'type' => 'page',
            'url' => 'signup/finalize',
            'template' => 'signup/finalize.html.twig',
            'description' => 'Successful signup.',
        ],
        'Signup Email Resent' => [
            'type' => 'page',
            'template' => 'signup/resent.html.twig',
            'description' => 'Email with confirmation links has been resent.',
        ],
    ];

    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

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

        $signupForm = $this->formFactory->create(SignupFormType::class);
        $finalizeForm = $this->formFactory->create(SignupFormFinalizeType::class);

        return [
            'username' => $user->getUsername(),
            'gender' => $user->getGender(),
            'key' => hash('sha256', $user->getUsername()),
            'email_address' => $user->getEmail(),
            'signup' => $signupForm->createView(),
            'finalize' => $finalizeForm->createView(),
        ];
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
