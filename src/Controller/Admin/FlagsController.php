<?php

namespace App\Controller\Admin;

use App\Entity\Flag;
use App\Entity\Member;
use App\Form\Admin\FlagAssignmentType;
use App\Form\Admin\FlagDefinitionType;
use App\Model\Admin\FlagsModel;
use App\Repository\FlagRepository;
use App\Repository\MemberRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(Member::ROLE_ADMIN_FLAGS)]
class FlagsController extends AbstractController
{
    public function __construct(
        private readonly FlagsModel $model,
        private readonly MemberRepository $memberRepository,
        private readonly FlagRepository $flagRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/admin/flags', name: 'admin_flags', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->assignmentList($request, true);
    }

    #[Route(path: '/admin/flags/overview', name: 'admin_flags_overview', methods: ['GET'])]
    public function overview(): Response
    {
        $manager = $this->getManager();

        return $this->render('admin/flags/overview.html.twig', [
            'flags' => $this->model->getRelevantFlags(),
            'submenu' => $this->getSubmenu($manager, 'overview'),
        ]);
    }

    #[Route(path: '/admin/flags/list/members', name: 'admin_flags_members', methods: ['GET'])]
    public function listMembers(Request $request): Response
    {
        return $this->assignmentList($request, true);
    }

    #[Route(path: '/admin/flags/list/members/{username}', name: 'admin_flags_member', methods: ['GET'])]
    public function listMember(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        return $this->assignmentList($request, true, $member);
    }

    #[Route(path: '/admin/flags/list/flags', name: 'admin_flags_flags', methods: ['GET'])]
    public function listFlags(Request $request): Response
    {
        return $this->assignmentList($request, false);
    }

    #[Route(path: '/admin/flags/list/flags/{id}', name: 'admin_flags_flag', methods: ['GET'])]
    public function listFlag(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Flag $flag,
    ): Response {
        return $this->assignmentList($request, false, null, $flag);
    }

    #[Route(path: '/admin/flags/assign', name: 'admin_flags_assign', methods: ['GET', 'POST'])]
    public function assign(Request $request): Response
    {
        return $this->assignmentForm($request);
    }

    #[Route(path: '/admin/flags/assign/{username}', name: 'admin_flags_assign_user', methods: ['GET', 'POST'])]
    public function assignMember(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        return $this->assignmentForm($request, $member);
    }

    #[Route(path: '/admin/flags/create', name: 'admin_flags_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $manager = $this->getManager();
        if (!$this->model->canCreate($manager)) {
            throw $this->createAccessDeniedException('The Flags scope does not permit creating flags.');
        }

        $form = $this->createForm(FlagDefinitionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $flag = $this->model->create(trim($data['name']), trim($data['description']));
            if (null === $flag) {
                $form->get('name')->addError(new FormError('admin.flags.flag.exists'));
            } else {
                $this->addFlash('success', $this->translator->trans('admin.flags.created', [
                    '%flag%' => $this->escapeFlashValue($flag->getName()),
                ]));

                return $this->redirectToRoute('admin_flags_overview');
            }
        }

        return $this->render('admin/flags/form.html.twig', [
            'headline' => 'admin.flags.create',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'create'),
        ]);
    }

    #[Route(path: '/admin/flags/edit/{id}/{username}', name: 'admin_flags_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Flag $flag,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $manager = $this->getManager();
        $this->denyUnlessRelevant($flag);
        $assignment = $this->model->findCurrentAssignment($member, $flag);
        if (null === $assignment || 0 === $assignment->getLevel()) {
            throw $this->createNotFoundException('The current active flag assignment was not found.');
        }

        $form = $this->createForm(FlagAssignmentType::class, [
            'username' => $member->getUsername(),
            'flag' => $flag,
            'level' => $assignment->getLevel(),
            'scope' => $assignment->getScope(),
            'comment' => $assignment->getComment(),
        ], [
            'flags' => $this->model->getRelevantFlags(),
            'username_readonly' => true,
            'flag_disabled' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $edited = $this->model->edit(
                $assignment,
                (int) $data['level'],
                trim((string) $data['scope']),
                trim($data['comment']),
            );
            if (!$edited) {
                throw $this->createNotFoundException('The flag assignment is no longer current.');
            }
            $this->addFlash('success', $this->translator->trans('admin.flags.flag.edited'));

            return $this->redirectToRoute('admin_flags_member', [
                'username' => $member->getUsername(),
            ]);
        }

        return $this->render('admin/flags/form.html.twig', [
            'headline' => 'admin.flags.edit',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'members'),
        ]);
    }

    #[Route(path: '/admin/flags/remove/{id}/{username}', name: 'admin_flags_remove', methods: ['GET', 'POST'])]
    public function remove(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Flag $flag,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $manager = $this->getManager();
        $this->denyUnlessRelevant($flag);
        $assignment = $this->model->findCurrentAssignment($member, $flag);
        if (null === $assignment || 0 === $assignment->getLevel()) {
            throw $this->createNotFoundException('The current active flag assignment was not found.');
        }

        $form = $this->createFormBuilder(null, [
            'csrf_token_id' => 'remove-flag-' . $assignment->getId(),
        ])->add('remove', SubmitType::class, [
            'label' => 'admin.flags.remove',
            'attr' => ['class' => 'btn-danger'],
        ])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->model->remove($assignment, $manager)) {
                throw $this->createNotFoundException('The flag assignment is no longer current.');
            }
            $this->addFlash('success', $this->translator->trans('admin.flags.removed', [
                '%flag%' => $this->escapeFlashValue($flag->getName()),
                '%username%' => $this->escapeFlashValue($member->getUsername()),
            ]));

            return $this->redirectToRoute('admin_flags_member', [
                'username' => $member->getUsername(),
                'history' => 1,
            ]);
        }

        return $this->render('admin/flags/remove.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'members'),
        ]);
    }

    private function assignmentForm(Request $request, ?Member $member = null): Response
    {
        $manager = $this->getManager();
        $form = $this->createForm(FlagAssignmentType::class, [
            'username' => $member?->getUsername() ?? '',
            'flag' => null,
            'level' => null,
            'scope' => '',
            'comment' => '',
        ], [
            'flags' => $this->model->getRelevantFlags(),
            'username_readonly' => null !== $member,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $targetMember = $member ?? $this->memberRepository->findOneBy(['username' => trim($data['username'])]);
            if (null === $targetMember) {
                $form->get('username')->addError(new FormError('admin.flags.username.not.existing'));
            } else {
                /** @var Flag $flag */
                $flag = $data['flag'];
                $this->denyUnlessRelevant($flag);
                $result = $this->model->assign(
                    $targetMember,
                    $flag,
                    (int) $data['level'],
                    trim((string) $data['scope']),
                    trim($data['comment']),
                );
                if (FlagsModel::ASSIGNMENT_DUPLICATE === $result) {
                    $form->get('flag')->addError(new FormError('admin.flags.already.assigned'));
                } else {
                    $this->addFlash('success', $this->translator->trans('admin.flags.assigned', [
                        '%flag%' => $this->escapeFlashValue($flag->getName()),
                        '%username%' => $this->escapeFlashValue($targetMember->getUsername()),
                    ]));

                    return $this->redirectToRoute('admin_flags_member', [
                        'username' => $targetMember->getUsername(),
                    ]);
                }
            }
        }

        return $this->render('admin/flags/form.html.twig', [
            'headline' => 'admin.flags.assign',
            'form' => $form,
            'submenu' => $this->getSubmenu($manager, 'assign'),
            'autocomplete' => null === $member,
        ]);
    }

    private function assignmentList(
        Request $request,
        bool $memberFirst,
        ?Member $member = null,
        ?Flag $flag = null,
    ): Response {
        $manager = $this->getManager();
        if (null !== $flag) {
            $this->denyUnlessRelevant($flag);
        }
        $username = $member?->getUsername() ?? trim((string) $request->query->get('member', ''));
        $flagId = $flag?->getId() ?? $this->getFilterId($request, 'flag');
        if (null !== $flagId && null === $flag) {
            $filteredFlag = $this->flagRepository->find($flagId);
            if (null !== $filteredFlag) {
                $this->denyUnlessRelevant($filteredFlag);
            }
        }
        $includeHistory = $request->query->getBoolean('history');
        $assignments = $this->model->paginateAssignments(
            '' === $username ? null : $username,
            $flagId,
            $includeHistory,
            $memberFirst,
            $request->query->getInt('page', 1),
        );

        if (null !== $member && 0 === $assignments->getNbResults() && !$includeHistory) {
            return $this->redirectToRoute('admin_flags_assign_user', [
                'username' => $member->getUsername(),
            ]);
        }

        return $this->render('admin/flags/list.html.twig', [
            'assignments' => $assignments,
            'flags' => $this->model->getRelevantFlags(),
            'member_first' => $memberFirst,
            'filters' => [
                'member' => $username,
                'flag' => $flagId,
                'history' => $includeHistory,
            ],
            'submenu' => $this->getSubmenu($manager, $memberFirst ? 'members' : 'flags'),
        ]);
    }

    private function getManager(): Member
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FLAGS)) {
            throw $this->createAccessDeniedException('You need the Flags right to access this page.');
        }

        $manager = $this->getUser();
        if (!$manager instanceof Member) {
            throw $this->createAccessDeniedException();
        }

        return $manager;
    }

    private function denyUnlessRelevant(Flag $flag): void
    {
        if ($flag->getRelevance() <= 0) {
            throw $this->createNotFoundException('The flag is not available.');
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
                'key' => 'admin.flags.assign',
                'icon' => 'plus',
                'url' => $this->generateUrl('admin_flags_assign'),
            ],
            'overview' => [
                'key' => 'admin.flags.overview',
                'icon' => 'list',
                'url' => $this->generateUrl('admin_flags_overview'),
            ],
            'members' => [
                'key' => 'admin.flags.list.members',
                'icon' => 'users',
                'url' => $this->generateUrl('admin_flags_members'),
            ],
            'flags' => [
                'key' => 'admin.flags.list.flags',
                'icon' => 'flag',
                'url' => $this->generateUrl('admin_flags_flags'),
            ],
        ];
        if ($this->model->canCreate($manager)) {
            $items['create'] = [
                'key' => 'admin.flags.create',
                'icon' => 'plus-square',
                'url' => $this->generateUrl('admin_flags_create'),
            ];
        }

        return [
            'items' => $items,
            'active' => $active,
        ];
    }
}
