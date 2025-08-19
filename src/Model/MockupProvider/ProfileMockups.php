<?php

namespace App\Model\MockupProvider;

use App\Entity\Member;
use App\Form\DeleteProfileFormType;
use App\Form\InvitationType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'delete profile' => [
            'type' => 'template',
            'template' => 'profile/delete.not.logged.in.html.twig',
            'description' => 'The delete profile page when not logged in',
        ],
        'delete profile (wrong credentials)' => [
            'type' => 'template',
            'template' => 'profile/delete.not.logged.in.html.twig',
            'description' => 'The delete profile page when not logged in',
        ],
    ];

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getFeature(): string
    {
        return 'delete_profile';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        return match ($parameters['name']) {
            'delete profile' => $this->getVariablesForDeleteProfile($parameters),
            'delete profile (wrong credentials)' => $this->getVariablesForDeleteProfileCredentialsError($parameters),
            default => [],
        };
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * Not all mockups need parameters.
     */
    private function getVariablesForDeleteProfile(array $parameters): array
    {
        $form = $this->formFactory->create(DeleteProfileFormType::class);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * Not all mockups need parameters.
     */
    private function getVariablesForDeleteProfileCredentialsError(array $parameters): array
    {
        $form = $this->formFactory->create(DeleteProfileFormType::class);
        $form->addError(new FormError($this->translator->trans('profile.delete.credentials')));

        return [
            'form' => $form->createView(),
        ];
    }
}
