<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\RelatedGroup;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Links between groups ("related groups"). A member of a group links it to another group they're in, and any member
 * of the group removes a link; the log shows who did what.
 */
class RelatedGroupController extends AbstractController
{
    use GroupSubmenuTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/group/{group_id}/selectrelatedgroup', name: 'relatedgroup_select', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function select(#[MapEntity(id: 'group_id')] Group $group): Response
    {
        $member = $this->getMember();
        if (!$this->isMember($group, $member)) {
            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        $related = $this->getRelatedGroups($group);
        $candidates = array_filter(
            $this->getMemberGroups($member),
            static fn (Group $other): bool => $other !== $group && !\in_array($other, $related, true),
        );

        return $this->render('group/related_select.html.twig', [
            'group' => $group,
            'candidates' => $candidates,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'relatedgroupsettings'],
        ]);
    }

    #[Route(path: '/group/{group_id}/addrelatedgroup/{related_id}', name: 'relatedgroup_add', requirements: ['group_id' => '\d+', 'related_id' => '\d+'], methods: ['POST'])]
    public function add(
        Request $request,
        #[MapEntity(id: 'group_id')] Group $group,
        #[MapEntity(id: 'related_id')] Group $related,
    ): RedirectResponse {
        $this->checkCsrfToken($request, 'relatedgroup-add-' . $group->getId() . '-' . $related->getId());
        $member = $this->getMember();

        $allowed = $group !== $related
            && $this->isMember($group, $member)
            && $this->isMember($related, $member)
            && !\in_array($related, $this->getRelatedGroups($group), true);
        if ($allowed) {
            $this->entityManager->persist(new RelatedGroup($group, $related, $member));
            $this->entityManager->flush();
            $this->addFlash('notice', \sprintf($this->getTranslator()->trans('SuccessfullyAddedRelatedGroup'), $related->getName()));
        } else {
            $this->addFlash('error', \sprintf($this->getTranslator()->trans('ErrorWhileAddingRelatedGroup'), $related->getName()));
        }

        return $this->redirectToRoute('relatedgroup_log', ['group_id' => $group->getId()]);
    }

    #[Route(path: '/group/{group_id}/selectdeleterelatedgroup', name: 'relatedgroup_selectdelete', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function selectRemoval(#[MapEntity(id: 'group_id')] Group $group): Response
    {
        $member = $this->getMember();
        if (!$this->isMember($group, $member)) {
            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        return $this->render('group/related_remove.html.twig', [
            'group' => $group,
            'related' => $this->getRelatedGroups($group),
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'relatedgroupsettings'],
        ]);
    }

    #[Route(path: '/group/{group_id}/deleterelatedgroup/{related_id}', name: 'relatedgroup_delete', requirements: ['group_id' => '\d+', 'related_id' => '\d+'], methods: ['POST'])]
    public function remove(
        Request $request,
        #[MapEntity(id: 'group_id')] Group $group,
        #[MapEntity(id: 'related_id')] Group $related,
    ): RedirectResponse {
        $this->checkCsrfToken($request, 'relatedgroup-delete-' . $group->getId() . '-' . $related->getId());
        $member = $this->getMember();

        $link = $this->entityManager->getRepository(RelatedGroup::class)->findOneBy([
            'group' => $group,
            'relatedGroup' => $related,
            'deletedBy' => null,
        ]);
        if (null !== $link && $this->isMember($group, $member)) {
            $link->remove($member);
            $this->entityManager->flush();
            $this->addFlash('notice', \sprintf($this->getTranslator()->trans('SuccessfullyRemovedRelatedGroup'), $related->getName()));
        } else {
            $this->addFlash('error', \sprintf($this->getTranslator()->trans('ErrorWhileRemoveRelatedGroup'), $related->getName()));
        }

        return $this->redirectToRoute('relatedgroup_log', ['group_id' => $group->getId()]);
    }

    #[Route(path: '/group/{group_id}/relatedgroupsettings', name: 'relatedgroup_log', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function log(#[MapEntity(id: 'group_id')] Group $group): Response
    {
        $member = $this->getMember();

        return $this->render('group/related_log.html.twig', [
            'group' => $group,
            'isMember' => $this->isMember($group, $member),
            'links' => $this->entityManager->getRepository(RelatedGroup::class)->findBy(['group' => $group], ['updated' => 'DESC']),
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'relatedgroupsettings'],
        ]);
    }

    /**
     * @return list<Group> the groups the group links to now
     */
    private function getRelatedGroups(Group $group): array
    {
        $links = $this->entityManager->getRepository(RelatedGroup::class)->findBy(['group' => $group, 'deletedBy' => null]);

        return array_map(static fn (RelatedGroup $link): Group => $link->getRelatedGroup(), $links);
    }

    /**
     * @return list<Group>
     */
    private function getMemberGroups(Member $member): array
    {
        $memberships = $this->entityManager->getRepository(GroupMembership::class)->findBy([
            'member' => $member,
            'status' => GroupMembershipStatusType::CURRENT_MEMBER,
        ]);
        $groups = array_map(static fn (GroupMembership $membership): Group => $membership->getGroup(), $memberships);
        usort($groups, static fn (Group $a, Group $b): int => strcasecmp($a->getName(), $b->getName()));

        return $groups;
    }

    private function isMember(Group $group, Member $member): bool
    {
        $membership = $this->entityManager->getRepository(GroupMembership::class)->findOneBy(['group' => $group, 'member' => $member]);

        return null !== $membership && GroupMembershipStatusType::CURRENT_MEMBER === $membership->getStatus();
    }

    private function checkCsrfToken(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
