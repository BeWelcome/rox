<?php

namespace App\Form;

use App\Entity\FeedbackCategory;
use App\Entity\SubTrip;
use Laminas\Validator\NotEmpty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FeedbackFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->setMethod('GET')
            ->add('IdCategory', ChoiceType::class, [
                'label' => 'feedbackchooseyourcategory',
                'placeholder' => 'feedbackchooseyourcategory',
                'choices' => $options['categories'],
                'choice_translation_domain' => 'messages',
                'choice_value' => function (?FeedbackCategory $entity) {
                    return $entity ? $entity->getId() : '';
                },
                'choice_label' => function (?FeedbackCategory $entity) {
                    return $entity ? strtolower('FeedBackName_' . $entity->getName()) : '';
                },
                'required' => false,
                'attr' => [
                    'class' => 'select2',
                ],
                'constraints' => [
                    new NotNull(['message' => 'feedback.select.category'])
                ],

            ])
            ->add('FeedbackQuestion', CkEditorType::class, [
                'label' => 'feedbackenteryourquestion',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
        $member = $options['member'];
        if (null === $member) {
            $formBuilder->add('FeedbackEmail', TextType::class, [
                'label' => 'feedbackemail',
                'required' => false,
                'constraints' => [
                    new Email(),
                ],
            ]);
        } else {
            $formBuilder->add('FeedbackEmail', HiddenType::class, [
                'data' => $member->getEmail(),
            ]);
        }
        $formBuilder
            ->add('no_reply_needed', CheckboxType::class, [
                'label' => 'feedbackidonotwantananswer',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categories' => [],
            'member' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
