<?php

namespace App\Controller\Admin;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Model\Admin\NewMembersModel;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The members who signed up in the last 90 days: the greeting team sends them a local and a global greeting,
 * the safety team and the checkers see the unfinished signups too and change a member's status.
 */
class NewMembersController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;

    public const string TOKEN = 'newmembers';

    /**
     * The statuses the safety team sets here.
     */
    public const array STATUSES = [
        MemberStatusType::ACTIVE,
        MemberStatusType::AWAITING_MAIL_CONFIRMATION,
        MemberStatusType::PASSED_AWAY,
        MemberStatusType::PENDING,
        MemberStatusType::BANNED,
        MemberStatusType::CHOICE_INACTIVE,
        MemberStatusType::OUT_OF_REMIND,
        MemberStatusType::ACTIVE_HIDDEN,
        MemberStatusType::SUSPENDED,
        MemberStatusType::ASKED_TO_LEAVE,
    ];

    private const array GREETINGS = [
        'local' => NewMembersModel::LOCAL_GREETING,
        'global' => NewMembersModel::GLOBAL_GREETING,
    ];

    public function __construct(
        private readonly NewMembersModel $model,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/admin/newmembers/{page}', name: 'newmembers', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function list(Request $request, int $page = 1): Response
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        $all = $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM) || $this->isGranted(Member::ROLE_ADMIN_CHECKER);

        /** @var Member $volunteer */
        $volunteer = $this->getUser();

        return $this->render('admin/newmembers/list.html.twig', [
            'members' => $this->model->getPager($all, $request->getLocale(), $page, $this->getItemsPerPage($volunteer)),
            'all' => $all,
            'can_change_status' => $this->canChangeStatus(),
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * The old URL of a page of the list.
     */
    #[Route(path: '/admin/newmembers/page/{page}', name: 'newmembers_pages_old', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function oldPage(int $page): RedirectResponse
    {
        return $this->redirectToRoute('newmembers', ['page' => $page], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Marks the greeting as sent and opens the message to the member.
     */
    #[Route(
        path: '/admin/newmembers/{username:member}/greeting/{greeting}',
        name: 'newmembers_greeting',
        requirements: ['greeting' => 'local|global'],
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function greeting(Member $member, string $greeting): RedirectResponse
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        $this->model->markGreetingSent($member, self::GREETINGS[$greeting]);

        return $this->redirectToRoute('message_new', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/admin/newmembers/{username:member}/status', name: 'newmembers_status', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function status(Request $request, Member $member): RedirectResponse
    {
        if (!$this->canChangeStatus()) {
            throw $this->createAccessDeniedException();
        }

        $status = $request->request->getString('status');
        if (!\in_array($status, self::STATUSES, true)) {
            throw $this->createNotFoundException();
        }

        $member->setStatus($status);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', 'admin.newmembers.status.changed', [
            '%username%' => $member->getUsername(),
            '%status%' => $this->translator->trans('memberstatus' . strtolower($status)),
        ]);

        return $this->redirectToRoute('newmembers', ['page' => max(1, $request->request->getInt('page', 1))]);
    }

    private function canChangeStatus(): bool
    {
        foreach ([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ACCEPTER, Member::ROLE_ADMIN_PROFILE] as $role) {
            if ($this->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $roles
     */
    private function denyUnlessAnyGranted(array $roles): void
    {
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                return;
            }
        }

        throw $this->createAccessDeniedException();
    }
}
