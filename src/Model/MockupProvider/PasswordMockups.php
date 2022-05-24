<?php

namespace App\Model\MockupProvider;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\Form\FormFactoryInterface;

class PasswordMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'Reset Password Request' => [
            'type' => 'page',
            'url' => '/resetpassword',
            'template' => 'member/request.password.reset.html.twig',
            'description' => 'The page that is shown when a member asks for a new password',
        ],
        'Reset Password' => [
            'type' => 'page',
            'url' => '/resetpassword/{username}/{token}',
            'template' => 'member/reset.password.html.twig',
            'description' => 'The page that is shown when the member followed the link in the password reset email.',
        ],
        'Reset Password Email' => [
            'type' => 'email',
            'template' => 'emails/reset.password.html.twig',
            'description' => 'Mail send to the user when a password reset request was done',
        ],
    ];

    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFeature(): string
    {
        return 'passwords';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'Reset Password Email':
                return [
                    'sender' => $parameters['admin'],
                    'receiver' => $parameters['user'],
                    'token' => '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7',
                ];
            case 'Reset Password Request':
                return [
                    'form' => $this->formFactory->create(ResetPasswordRequestFormType::class)->createView(),
                ];
            case 'Reset Password':
                return [
                    'form' => $this->formFactory->create(ResetPasswordFormType::class)->createView(),
                ];
            default:
                return [];
        }
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }
}
