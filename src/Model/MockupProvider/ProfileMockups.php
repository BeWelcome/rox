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
    private const MOCKUPS = [
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
    private FormFactoryInterface $formFactory;
    private TranslatorInterface $translator;

    public function __construct(FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->translator = $translator;
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
