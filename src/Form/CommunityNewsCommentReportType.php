<?php

namespace App\Form;

use App\Entity\CommunityNewsCommentReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A member's report of a community news comment: why (a reason) and what else the checkers should know (a note,
 * needed for "Something else").
 */
class CommunityNewsCommentReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'communitynews.report.reason',
                'expanded' => true,
                'choices' => array_combine(
                    array_map(static fn (string $reason): string => 'communitynews.report.reason.' . $reason, CommunityNewsCommentReport::REASONS),
                    CommunityNewsCommentReport::REASONS,
                ),
                'constraints' => [new NotBlank(message: 'communitynews.report.error.reason')],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'communitynews.report.note',
                'required' => false,
                'attr' => ['rows' => 2, 'placeholder' => 'communitynews.report.note.placeholder'],
                'constraints' => [new Length(max: 2000)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new Callback(static function (array $data, ExecutionContextInterface $context): void {
                if (CommunityNewsCommentReport::REASON_OTHER === ($data['reason'] ?? null) && '' === trim((string) ($data['note'] ?? ''))) {
                    $context->buildViolation('communitynews.report.error.note')->atPath('[note]')->addViolation();
                }
            })],
        ]);
    }
}
