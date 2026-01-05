<?php

namespace App\Form;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddLanguageFormType extends AbstractType
{
    private mixed $languages;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $languageRepository = $entityManager->getRepository(Language::class);
        $languages = $languageRepository->getAllLanguages();

        $this->languages = [
            'languages.written' => [],
        ];

        foreach ($languages as $language) {
            $shortCode = $language->getShortCode();
            if ($language->getIsWrittenLanguage()) {
                $this->languages['languages.written'][$shortCode] = $language;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', LanguageType::class, [
                'written' => true,
            ])
        ;
    }
}
