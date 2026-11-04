<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Right;
use App\Form\Admin\RightAssignmentType;
use App\Model\Admin\RightsModel;
use App\Repository\MemberRepository;
use App\Repository\RightRepository;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The rights tool (templates/admin/rights): Assign, Overview, By member and By right, as tabs. A volunteer with the
 * Rights right manages the rights its scope names ("All" for every right). New rights come with a migration and the
 * code that uses them, not from here.
 *
 * Removing asks first in a dialog and posts the page it came from (return), where it leads back to.
 */
#[IsGranted(Member::ROLE_ADMIN_RIGHTS)]
class RightsController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The CSRF token id of removing an assignment. */
    public const string TOKEN = 'admin_rights';

    public function __construct(
        private readonly RightsModel $model,
        private readonly MemberRepository $memberRepository,
        private readonly RightRepository $rightRepository,
        // ItemsPerPageTraits reads the member's preference with it
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/admin/rights', name: 'admin_rights', methods: ['GET', 'POST'])]
    public function assign(Request $request): Response
    {
        return $this->assignmentForm($request);
    }

    #[Route(path: '/admin/rights/assign/{username}', name: 'admin_rights_assign', methods: ['GET', 'POST'])]
    public function assignMember(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        return $this->assignmentForm($request, $member);
    }

    #[Route(path: '/admin/rights/overview', name: 'admin_rights_overview', methods: ['GET'])]
    public function overview(): Response
    {
        $manager = $this->getManager();

        return $this->render('admin/rights/overview.html.twig', [
            'rights' => $this->model->getManagedRights($manager),
            'holders' => $this->model->countHoldersByRight($manager),
            'tab' => 'overview',
        ]);
    }

    #[Route(path: '/admin/rights/list/members', name: 'admin_rights_members', methods: ['GET'])]
    public function listMembers(Request $request): Response
    {
        return $this->byMember($request);
    }

    /**
     * The member's rights, from the Admin box of the profile: By member, searching and showing them.
     */
    #[Route(path: '/admin/rights/list/member/{username}', name: 'admin_rights_member', methods: ['GET'])]
    public function listMember(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        return $this->byMember($request, $member);
    }

    #[Route(path: '/admin/rights/list/rights', name: 'admin_rights_rights', methods: ['GET'])]
    public function listRights(Request $request): Response
    {
        return $this->byRight($request);
    }

    #[Route(path: '/admin/rights/list/rights/{id}', name: 'admin_rights_right', methods: ['GET'])]
    public function listRight(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Right $right,
    ): Response {
        return $this->byRight($request, $right);
    }

    #[Route(path: '/admin/rights/edit/{id}/{username}', name: 'admin_rights_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Right $right,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $manager = $this->getManager();
        $this->denyUnlessManaged($manager, $right);
        $assignment = $this->model->findAssignment($member, $right)
            ?? throw $this->createNotFoundException('The right is not assigned to this member.');

        $form = $this->createForm(RightAssignmentType::class, [
            'username' => $member->getUsername(),
            'right' => $right,
            'level' => $assignment->getLevel(),
            'scope' => $assignment->getScope(),
            'comment' => $assignment->getComment(),
        ], [
            'rights' => $this->model->getManagedRights($manager),
            'username_locked' => true,
            'right_locked' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($this->scopeIsValid($form, $data['scope'])) {
                $this->model->edit($assignment, (int) $data['level'], $this->scope($data['scope']), trim($data['comment']));
                $this->addTranslatedFlash('notice', 'admin.rights.edited.notice');

                return $this->back($request, $member);
            }
        }

        return $this->render('admin/rights/form.html.twig', [
            'form' => $form,
            'member' => $member,
            'right' => $right,
            'editing' => true,
            'return' => $this->returnPath($request, $member),
            'tab' => str_starts_with($this->returnPath($request, $member), '/admin/rights/list/rights') ? 'rights' : 'members',
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route(path: '/admin/rights/remove/{id}/{username}', name: 'admin_rights_remove', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function remove(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Right $right,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $manager = $this->getManager();
        $this->denyUnlessManaged($manager, $right);
        $assignment = $this->model->findAssignment($member, $right);
        if (null === $assignment || 0 === $assignment->getLevel()) {
            throw $this->createNotFoundException('The active right assignment was not found.');
        }

        $this->model->remove($assignment, $manager);
        $this->addFlash('notice', $this->getTranslator()->trans('admin.rights.removed.notice', [
            'right' => $right->getName(),
            'username' => $member->getUsername(),
        ]));

        return $this->back($request, $member);
    }

    private function assignmentForm(Request $request, ?Member $member = null): Response
    {
        $manager = $this->getManager();
        $form = $this->createForm(RightAssignmentType::class, [
            'username' => $member?->getUsername() ?? '',
            'right' => null,
            'level' => 1,
            'scope' => '',
            'comment' => '',
        ], [
            'rights' => $this->model->getManagedRights($manager),
            'username_locked' => null !== $member,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $target = $member ?? $this->memberRepository->findOneBy(['username' => trim((string) $data['username'])]);
            /** @var Right $right */
            $right = $data['right'];
            $this->denyUnlessManaged($manager, $right);
            if (null === $target) {
                $form->get('username')->addError(new FormError('admin.rights.error.member'));
            } elseif ($this->scopeIsValid($form, $data['scope'])) {
                $result = $this->model->assign($target, $right, (int) $data['level'], $this->scope($data['scope']), trim($data['comment']));
                if (RightsModel::ASSIGNMENT_DUPLICATE === $result) {
                    $form->get('right')->addError(new FormError('admin.rights.error.assigned'));
                } else {
                    $this->addFlash('notice', $this->getTranslator()->trans('admin.rights.assigned.notice', [
                        'right' => $right->getName(),
                        'username' => $target->getUsername(),
                    ]));

                    return $this->redirectToRoute('admin_rights_member', ['username' => $target->getUsername()]);
                }
            }
        }

        return $this->render('admin/rights/form.html.twig', [
            'form' => $form,
            'member' => $member,
            'right' => null,
            'editing' => false,
            'return' => null === $member ? null : $this->generateUrl('admin_rights_member', ['username' => $member->getUsername()]),
            'tab' => 'assign',
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    /**
     * By member: the members on the left (search, right), the chosen one with their assignments on the right.
     */
    private function byMember(Request $request, ?Member $member = null): Response
    {
        $manager = $this->getManager();
        $search = $member?->getUsername() ?? trim($request->query->getString('member'));
        $right = $this->filteredRight($manager, $request);
        [$members, $assignments] = $this->model->paginateMembers(
            $manager,
            '' === $search ? null : $search,
            $right?->getId(),
            $request->query->getInt('page', 1),
            $this->getItemsPerPage($manager),
        );

        // the chosen member: from the URL (also without rights: the empty state), else the first one of the list
        $selected = $member;
        if (null === $selected && '' !== $request->query->getString('selected')) {
            $selected = $this->memberRepository->findOneBy(['username' => $request->query->getString('selected')]);
        }
        $list = iterator_to_array($members->getCurrentPageResults(), false);
        if (null === $selected && [] !== $list) {
            $selected = $this->memberRepository->find((int) $list[0]['member_id']);
        }

        return $this->render('admin/rights/members.html.twig', [
            'members' => $members,
            'assignments' => $assignments,
            'selected' => $selected,
            'selected_assignments' => null === $selected ? [] : $this->model->findAssignmentsOfMember($manager, $selected),
            'rights' => $this->model->getManagedRights($manager),
            'filters' => ['member' => $search, 'right' => $right?->getId()],
            'tab' => 'members',
        ]);
    }

    /**
     * By right: a table of the assignments, grouped by right; with the removed ones unless history is turned off.
     */
    private function byRight(Request $request, ?Right $right = null): Response
    {
        $manager = $this->getManager();
        if (null !== $right) {
            $this->denyUnlessManaged($manager, $right);
        }
        $right ??= $this->filteredRight($manager, $request);
        $search = trim($request->query->getString('member'));
        $history = '0' !== $request->query->getString('history', '1');

        return $this->render('admin/rights/rights.html.twig', [
            'assignments' => $this->model->paginateAssignments(
                $manager,
                '' === $search ? null : $search,
                $right?->getId(),
                $history,
                $request->query->getInt('page', 1),
                $this->getItemsPerPage($manager),
            ),
            'holders' => $this->model->countHoldersByRight($manager),
            'rights' => $this->model->getManagedRights($manager),
            'filters' => ['member' => $search, 'right' => $right?->getId(), 'history' => $history],
            'tab' => 'rights',
        ]);
    }

    /**
     * The right of the ?right= filter; one outside the volunteer's scope is refused.
     */
    private function filteredRight(Member $manager, Request $request): ?Right
    {
        $id = $request->query->getInt('right');
        $right = 0 < $id ? $this->rightRepository->find($id) : null;
        if (null !== $right) {
            $this->denyUnlessManaged($manager, $right);
        }

        return $right;
    }

    private function scopeIsValid(FormInterface $form, ?string $scope): bool
    {
        if ('' === trim((string) $scope) || $this->model->isScopeWellFormed((string) $scope)) {
            return true;
        }
        $form->get('scope')->addError(new FormError('AdminRightsScopeNotWellFormed'));

        return false;
    }

    /**
     * The scope to store: an empty one is All.
     */
    private function scope(?string $scope): string
    {
        $scope = trim((string) $scope);

        return '' === $scope ? '"All"' : $scope;
    }

    /**
     * Back to the view an action came from (return), else to the member's rights.
     */
    private function back(Request $request, Member $member): RedirectResponse
    {
        return $this->redirect($this->returnPath($request, $member));
    }

    /**
     * The return of the request when it is a page of the rights tool, else the member's rights.
     */
    private function returnPath(Request $request, Member $member): string
    {
        $return = $request->request->getString('return', $request->query->getString('return'));
        if (preg_match('~^/admin/rights/list/[^#\\\\]*$~', $return)) {
            return $return;
        }

        return $this->generateUrl('admin_rights_member', ['username' => $member->getUsername()]);
    }

    private function getManager(): Member
    {
        $manager = $this->getUser();
        if (!$manager instanceof Member) {
            throw $this->createAccessDeniedException();
        }

        return $manager;
    }

    private function denyUnlessManaged(Member $manager, Right $right): void
    {
        if (!$this->model->canManage($manager, $right)) {
            throw $this->createAccessDeniedException('This right is outside your Rights scope.');
        }
    }
}
