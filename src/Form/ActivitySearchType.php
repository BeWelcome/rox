<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActivitySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyword', SearchType::class, [
                'label' => 'activitiessearchbutton',
                'constraints' => [
                    new NotBlank(message: 'activity.keyword.empty'),
                    new Length(max: 100),
                ],
            ])
        ;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'search';
    }
}
