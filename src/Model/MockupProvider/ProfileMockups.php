<?php

namespace App\Model\MockupProvider;

use App\Form\DeleteProfileFormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileMockups extends AbstractMockups
{
    protected const string FEATURE = 'delete_profile';

    protected const array MOCKUPS = [
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
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'delete profile':
                return $this->getVariablesForDeleteProfile($parameters);
            case 'delete profile (wrong credentials)':
                return $this->getVariablesForDeleteProfileCredentialsError($parameters);
            default:
                return [];
        }
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    private function getVariablesForDeleteProfile(array $parameters): array
    {
        $form = $this->formFactory->create(DeleteProfileFormType::class);

        return [
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForDeleteProfileCredentialsError(array $parameters): array
    {
        $form = $this->formFactory->create(DeleteProfileFormType::class);
        $form->addError(new FormError($this->translator->trans('profile.delete.credentials')));

        return [
            'form' => $form->createView(),
        ];
    }
}
