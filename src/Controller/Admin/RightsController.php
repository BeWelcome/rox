<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Right;
use App\Form\Admin\RightAssignmentType;
use App\Form\Admin\RightDefinitionType;
use App\Model\Admin\RightsModel;
use App\Repository\MemberRepository;
use App\Repository\RightRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(Member::ROLE_ADMIN_RIGHTS)]
class RightsController extends AbstractController
{
    public function __construct(
        private readonly RightsModel $model,
        private readonly MemberRepository $memberRepository,
        private readonly RightRepository $rightRepository,
        private readonly TranslatorInterface $translator,
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
            'submenu' => $this->getSubmenu($manager, 'overview'),
        ]);
    }

    #[Route(path: '/admin/rights/list/members', name: 'admin_rights_members', methods: ['GET'])]
    public function listMembers(Request $request): Response
    {
        return $this->assignmentList($request, true);
    }

    #[Route(path: '/admin/rights/list/member/{username}', name: 'admin_rights_member', methods: ['GET'])]
    public function listMember(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        return $this->assignmentList($request, true, $member);
    }

    #[Route(path: '/admin/rights/list/rights', name: 'admin_rights_rights', methods: ['GET'])]
    public function listRights(Request $request): Response
    {
        return $this->assignmentList($request, false);
    }

    #[Route(path: '/admin/rights/list/rights/{id}', name: 'admin_rights_right', methods: ['GET'])]
    public function listRight(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Right $right,
    ): Response {
        return $this->assignmentList($request, false, null, $right);
    }

    #[Route(path: '/admin/rights/create', name: 'admin_rights_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $manager = $this->getManager();
        if (!$this->model->canCreate($manager)) {
            throw $this->createAccessDeniedException('The Rights scope does not permit creating rights.');
        }

        $form = $this->createForm(RightDefinitionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $right = $this->model->create(trim($data['name']), trim($data['description']));
            if (null === $right) {
                $form->get('name')->addError(new FormError('AdminRightsRightExists'));
            } else {
                $this->addFlash('success', $this->translator->trans('admin.rights.created', [
                    '%right%' => $this->escapeFlashValue($right->getName()),
                ]));

                return $this->redirectToRoute('admin_rights_overview');
            }
        }

        return $this->render('admin/rights/form.html.twig', [
            'headline' => 'AdminRightsCreate',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'create'),
        ]);
    }

    #[Route(path: '/admin/rights/edit/{id}/{username}', name: 'admin_rights_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Right $right,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $manager = $this->getManager();
        $this->denyUnlessManaged($manager, $right);
        $assignment = $this->model->findAssignment($member, $right);
        if (null === $assignment) {
            throw $this->createNotFoundException('The right is not assigned to this member.');
        }

        $form = $this->createForm(RightAssignmentType::class, [
            'username' => $member->getUsername(),
            'right' => $right,
            'level' => $assignment->getLevel(),
            'scope' => $assignment->getScope(),
            'comment' => $assignment->getComment(),
        ], [
            'rights' => $this->model->getManagedRights($manager),
            'username_readonly' => true,
            'right_disabled' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!$this->model->isScopeWellFormed($data['scope'])) {
                $form->get('scope')->addError(new FormError('AdminRightsScopeNotWellFormed'));
            } else {
                $this->model->edit(
                    $assignment,
                    (int) $data['level'],
                    trim($data['scope']),
                    trim($data['comment']),
                );
                $this->addFlash('success', $this->translator->trans('AdminRightsRightEdited'));

                return $this->redirectToRoute('admin_rights_member', [
                    'username' => $member->getUsername(),
                ]);
            }
        }

        return $this->render('admin/rights/form.html.twig', [
            'headline' => 'admin.rights.edit',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'members'),
        ]);
    }

    #[Route(path: '/admin/rights/remove/{id}/{username}', name: 'admin_rights_remove', methods: ['GET', 'POST'])]
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

        $form = $this->createFormBuilder(null, [
            'csrf_token_id' => 'remove-right-' . $assignment->getId(),
        ])->add('remove', SubmitType::class, [
            'label' => 'AdminRightsRemove',
            'attr' => ['class' => 'btn-danger'],
        ])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->model->remove($assignment, $manager);
            $this->addFlash('success', $this->translator->trans('admin.rights.removed', [
                '%right%' => $this->escapeFlashValue($right->getName()),
                '%username%' => $this->escapeFlashValue($member->getUsername()),
            ]));

            return $this->redirectToRoute('admin_rights_member', [
                'username' => $member->getUsername(),
            ]);
        }

        return $this->render('admin/rights/remove.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'members'),
        ]);
    }

    private function assignmentForm(Request $request, ?Member $member = null): Response
    {
        $manager = $this->getManager();
        $form = $this->createForm(RightAssignmentType::class, [
            'username' => $member?->getUsername() ?? '',
            'right' => null,
            'level' => null,
            'scope' => '',
            'comment' => '',
        ], [
            'rights' => $this->model->getManagedRights($manager),
            'username_readonly' => null !== $member,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $targetMember = $member ?? $this->memberRepository->findOneBy(['username' => trim($data['username'])]);
            if (null === $targetMember) {
                $form->get('username')->addError(new FormError('AdminRightsUsernameNotExisting'));
            } elseif (!$this->model->isScopeWellFormed($data['scope'])) {
                $form->get('scope')->addError(new FormError('AdminRightsScopeNotWellFormed'));
            } else {
                /** @var Right $right */
                $right = $data['right'];
                $this->denyUnlessManaged($manager, $right);
                $result = $this->model->assign(
                    $targetMember,
                    $right,
                    (int) $data['level'],
                    trim($data['scope']),
                    trim($data['comment']),
                );
                if (RightsModel::ASSIGNMENT_DUPLICATE === $result) {
                    $form->get('right')->addError(new FormError('AdminRightsAlreadyAssigned'));
                } else {
                    $this->addFlash('success', $this->translator->trans('admin.rights.assigned', [
                        '%right%' => $this->escapeFlashValue($right->getName()),
                        '%username%' => $this->escapeFlashValue($targetMember->getUsername()),
                    ]));

                    return $this->redirectToRoute('admin_rights_member', [
                        'username' => $targetMember->getUsername(),
                    ]);
                }
            }
        }

        return $this->render('admin/rights/form.html.twig', [
            'headline' => 'AdminRightsAssign',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'assign'),
            'autocomplete' => null === $member,
        ]);
    }

    private function assignmentList(
        Request $request,
        bool $memberFirst,
        ?Member $member = null,
        ?Right $right = null,
    ): Response {
        $manager = $this->getManager();
        if (null !== $right) {
            $this->denyUnlessManaged($manager, $right);
        }

        $username = $member?->getUsername() ?? trim((string) $request->query->get('member', ''));
        $rightId = $right?->getId() ?? $this->getFilterId($request, 'right');
        if (null !== $rightId && null === $right) {
            $filteredRight = $this->rightRepository->find($rightId);
            if (null !== $filteredRight) {
                $this->denyUnlessManaged($manager, $filteredRight);
            }
        }
        $includeHistory = $memberFirst || $request->query->getBoolean('history');
        $assignments = $this->model->paginateAssignments(
            $manager,
            '' === $username ? null : $username,
            $rightId,
            $includeHistory,
            $memberFirst,
            $request->query->getInt('page', 1),
        );

        if (null !== $member && 0 === $assignments->getNbResults()) {
            return $this->redirectToRoute('admin_rights_assign', [
                'username' => $member->getUsername(),
            ]);
        }

        return $this->render('admin/rights/list.html.twig', [
            'assignments' => $assignments,
            'rights' => $this->model->getManagedRights($manager),
            'member_first' => $memberFirst,
            'filters' => [
                'member' => $username,
                'right' => $rightId,
                'history' => $includeHistory,
            ],
            'submenu' => $this->getSubmenu($manager, $memberFirst ? 'members' : 'rights'),
        ]);
    }

    private function getManager(): Member
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_RIGHTS)) {
            throw $this->createAccessDeniedException('You need the Rights right to access this page.');
        }

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

    private function getFilterId(Request $request, string $name): ?int
    {
        $id = $request->query->getInt($name);

        return $id > 0 ? $id : null;
    }

    private function escapeFlashValue(string $value): string
    {
        return htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
    }

    private function getSubmenu(Member $manager, string $active): array
    {
        $items = [
            'assign' => [
                'key' => 'admin.rights.assign',
                'icon' => 'plus',
                'url' => $this->generateUrl('admin_rights'),
            ],
            'overview' => [
                'key' => 'admin.rights.overview',
                'icon' => 'list',
                'url' => $this->generateUrl('admin_rights_overview'),
            ],
            'members' => [
                'key' => 'admin.rights.list.members',
                'icon' => 'users',
                'url' => $this->generateUrl('admin_rights_members'),
            ],
            'rights' => [
                'key' => 'admin.rights.list.rights',
                'icon' => 'key',
                'url' => $this->generateUrl('admin_rights_rights'),
            ],
        ];
        if ($this->model->canCreate($manager)) {
            $items['create'] = [
                'key' => 'admin.rights.create',
                'icon' => 'plus-square',
                'url' => $this->generateUrl('admin_rights_create'),
            ];
        }

        return [
            'items' => $items,
            'active' => $active,
        ];
    }
}
