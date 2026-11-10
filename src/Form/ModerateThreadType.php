<?php

namespace App\Form;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Entity\ForumThread;
use App\Entity\Group;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A forum moderator's settings of a thread (the Topic part of the moderation panel): its group, who can see it,
 * its pin, the day it is hidden after and whether it is deleted. The data is an array.
 */
class ModerateThreadType extends AbstractType
{
    /** Who can see a thread or a post: the label's translation key => the value. */
    public const array VISIBILITIES = [
        'forum_edit_vis_norestriction' => ForumVisibilityType::NO_RESTRICTION,
        'forum_edit_vis_membersonly' => ForumVisibilityType::MEMBERS_ONLY,
        'forum_edit_vis_grouponly' => ForumVisibilityType::GROUP_ONLY,
        'forum_edit_vis_moderatoronly' => ForumVisibilityType::MODERATOR_ONLY,
    ];

    /**
     * @return array{group: ?Group, visibility: string, stickyValue: int, expiryDate: ?DateTime, deleted: bool}
     */
    public static function dataOf(ForumThread $thread): array
    {
        return [
            'group' => $thread->getGroup(),
            'visibility' => \in_array($thread->getVisibility(), self::VISIBILITIES, true) ? $thread->getVisibility() : ForumVisibilityType::MEMBERS_ONLY,
            'stickyValue' => (int) $thread->getStickyValue(),
            'expiryDate' => $thread->getExpiryDate(),
            'deleted' => ForumDeleteStatusType::DELETED === $thread->getDeleted(),
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'choice_translation_domain' => false,
                'query_builder' => static fn (EntityRepository $repository) => $repository->createQueryBuilder('g')
                    ->where('g.approved = :approved')
                    ->setParameter('approved', Group::APPROVED)
                    ->orderBy('g.name', 'ASC'),
                'label' => 'group',
            ])
            ->add('visibility', ChoiceType::class, ['choices' => self::VISIBILITIES, 'label' => 'forum.moderation.topic.visibility'])
            ->add('stickyValue', IntegerType::class, ['label' => 'forum.moderation.pin', 'required' => false, 'empty_data' => '0', 'attr' => ['min' => 0]])
            ->add('expiryDate', DateType::class, ['widget' => 'single_text', 'required' => false, 'label' => 'forum.moderation.hide.after', 'input' => 'datetime'])
            ->add('deleted', CheckboxType::class, ['required' => false, 'label' => 'forum.moderation.deleted']);
    }

    public function getBlockPrefix(): string
    {
        return 'moderate_thread';
    }
}
