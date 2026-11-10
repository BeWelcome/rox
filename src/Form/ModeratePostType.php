<?php

namespace App\Form;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\PostCanStillEditType;
use App\Entity\ForumPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A forum moderator's settings of a post (the This post part of the moderation panel): who can see it, whether its
 * author can still edit it and whether it is deleted. The data is an array.
 */
class ModeratePostType extends AbstractType
{
    /**
     * @return array{visibility: string, ownerCanStillEdit: bool, deleted: bool}
     */
    public static function dataOf(ForumPost $post): array
    {
        return [
            'visibility' => \in_array($post->getPostvisibility(), ModerateThreadType::VISIBILITIES, true) ? $post->getPostvisibility() : ForumVisibilityType::MEMBERS_ONLY,
            'ownerCanStillEdit' => PostCanStillEditType::CAN_STILL_EDIT === $post->getOwnerCanStillEdit(),
            'deleted' => ForumDeleteStatusType::DELETED === $post->getDeleted(),
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('visibility', ChoiceType::class, ['choices' => ModerateThreadType::VISIBILITIES, 'label' => 'forum.moderation.post.visibility'])
            ->add('ownerCanStillEdit', CheckboxType::class, ['required' => false, 'label' => 'forum.moderation.owner.can.edit'])
            ->add('deleted', CheckboxType::class, ['required' => false, 'label' => 'forum.moderation.deleted']);
    }

    public function getBlockPrefix(): string
    {
        return 'moderate_post';
    }
}
