<?php

namespace App\Form\Admin;

use App\Entity\Donation;
use App\Entity\Location;
use App\Form\DataTransformer\UsernameToMemberTransformer;
use Collator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationType extends AbstractType
{
    public const string DONOR_MEMBER = 'member';
    public const string DONOR_OTHER = 'other';
    public const string DONOR_UNKNOWN = 'unknown';

    public function __construct(
        private readonly UsernameToMemberTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Only decides which donor field is used; the other one is cleared on submit.
            ->add('donorType', ChoiceType::class, [
                'mapped' => false,
                'expanded' => true,
                'label' => 'admin.treasurer.donor.type',
                'choices' => [
                    'admin.treasurer.donor.type.member' => self::DONOR_MEMBER,
                    'admin.treasurer.donor.type.other' => self::DONOR_OTHER,
                    'admin.treasurer.donor.type.unknown' => self::DONOR_UNKNOWN,
                ],
            ])
            ->add('donor', TextType::class, [
                'required' => false,
                'label' => 'admin.treasurer.donor',
                'invalid_message' => 'That is not a valid username',
                'help' => 'admin.treasurer.donor.help',
            ])
            ->add('nameGiven', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'admin.treasurer.name.given',
                'help' => 'admin.treasurer.name.given.help',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => false,
                'html5' => true,
                'label' => 'admin.treasurer.amount',
            ])
            ->add('created', DateType::class, [
                'widget' => 'single_text',
                'label' => 'admin.treasurer.donated.on',
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                // the names in the language of the page, not translated again
                'choices' => $this->getCountryChoices($this->requestStack->getCurrentRequest()?->getLocale() ?? 'en'),
                'choice_translation_domain' => false,
                'label' => 'admin.treasurer.country',
                'help' => 'admin.treasurer.country.help',
                'placeholder' => 'admin.treasurer.country.placeholder',
            ])
            ->add('systemComment', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'admin.treasurer.comment',
                'help' => 'admin.treasurer.comment.help',
                'attr' => ['rows' => 3],
            ])
        ;

        $builder->get('donor')
            ->addModelTransformer($this->transformer);

        $builder->get('country')
            ->addModelTransformer(new CallbackTransformer(
                static function (?Location $location) {
                    return $location?->getGeonameId();
                },
                function (?int $geonameId) {
                    if (!$geonameId) {
                        return null;
                    }

                    return $this->entityManager->getRepository(Location::class)->find($geonameId);
                }
            ));

        // The donor type of a donation: a member, else a name, else unknown. A new donation starts with a member.
        $builder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event): void {
            $donation = $event->getData();
            $type = self::DONOR_MEMBER;
            if ($donation instanceof Donation && null !== $donation->getId() && null === $donation->getDonor()) {
                $type = '' !== $donation->getNameGiven() ? self::DONOR_OTHER : self::DONOR_UNKNOWN;
            }
            $event->getForm()->get('donorType')->setData($type);
        });

        // The fields the donor type hides are cleared, so a value left in them isn't saved (or checked).
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }
            $type = $data['donorType'] ?? self::DONOR_MEMBER;
            if (self::DONOR_MEMBER !== $type) {
                $data['donor'] = '';
            }
            if (self::DONOR_OTHER !== $type) {
                $data['nameGiven'] = '';
            }
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }

    /**
     * The countries with their names in the locale (Gedmo's translations, else the name of the location), sorted as
     * in that language.
     *
     * @return array<string, int> name => geoname id
     */
    private function getCountryChoices(string $locale): array
    {
        $query = $this->entityManager->createQuery(
            "SELECT l.name, l.geonameId FROM App\\Entity\\Location l WHERE l.featureCode LIKE 'PCL%' AND l.featureCode != 'PCLH'"
        );
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);
        $query->setHint(TranslatableListener::HINT_FALLBACK, 1);

        $choices = [];
        foreach ($query->getArrayResult() as $row) {
            $choices[$row['name']] = $row['geonameId'];
        }
        $collator = new Collator($locale);
        uksort($choices, static fn (string $a, string $b): int => (int) $collator->compare($a, $b));

        return $choices;
    }
}
