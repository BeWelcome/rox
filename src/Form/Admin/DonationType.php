<?php

namespace App\Form\Admin;

use App\Entity\Donation;
use App\Entity\Location;
use App\Form\DataTransformer\UsernameToMemberTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationType extends AbstractType
{
    public function __construct(
        private readonly UsernameToMemberTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('donor', TextType::class, [
                'required' => false,
                'label' => 'Donor Username',
                'invalid_message' => 'That is not a valid username',
                'help' => 'Leave empty if not a member',
            ])
            ->add('nameGiven', TextType::class, [
                'required' => false,
                'label' => 'Donor Name',
                'help' => 'Use this if the donor is not a member',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Amount',
            ])
            ->add('created', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Donated On',
            ])
            ->add('systemComment', TextareaType::class, [
                'required' => false,
                'label' => 'Comment',
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                'choices' => $this->getCountryChoices(),
                'label' => 'Country',
                'placeholder' => 'Select a country',
            ])
        ;

        $builder->get('donor')
            ->addModelTransformer($this->transformer);

        $builder->get('country')
            ->addModelTransformer(new CallbackTransformer(
                function (?Location $location) {
                    return $location?->getGeonameId();
                },
                function (?int $geonameId) {
                    if (!$geonameId) {
                        return null;
                    }
                    return $this->entityManager->getRepository(Location::class)->find($geonameId);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }

    private function getCountryChoices(): array
    {
        $query = $this->entityManager->createQuery(
            'SELECT l.name, l.geonameId 
             FROM App\Entity\Location l 
             WHERE l.featureCode LIKE :fcode 
             AND l.featureCode != :notfcode 
             ORDER BY l.name ASC'
        )
            ->setParameter('fcode', 'PCL%')
            ->setParameter('notfcode', 'PCLH');

        $query->setHint(TranslatableListener::HINT_FALLBACK, 'en');
        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'de');

        $results = $query->getArrayResult();
        $choices = [];
        foreach ($results as $row) {
            $choices[$row['name']] = $row['geonameId'];
        }
        return $choices;
    }
}
