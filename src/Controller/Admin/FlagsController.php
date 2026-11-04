<?php

namespace App\Controller\Admin;

use App\Entity\Flag;
use App\Entity\Member;
use App\Model\Admin\FlagsModel;
use App\Utilities\TranslatorTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The one flag in use, NotAllowedToPostInForum (FlagsModel): blocked and unblocked from the Admin box of the member's
 * profile (templates/profile/_admin_tools.html.twig), every assignment ever made listed at /admin/flags.
 */
#[IsGranted(Member::ROLE_ADMIN_FLAGS)]
class FlagsController extends AbstractController
{
    use TranslatorTrait;

    /** The CSRF token id of blocking and unblocking. */
    public const string TOKEN = 'admin_flags';

    public function __construct(
        private readonly FlagsModel $model,
    ) {
    }

    #[Route(path: '/admin/flags', name: 'admin_flags', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $flag = $this->flag();
        $search = trim($request->query->getString('member'));
        $assignments = $this->model->findAllOfFlag($flag, '' === $search ? null : $search);

        return $this->render('admin/flags/list.html.twig', [
            'flag' => $flag,
            'assignments' => $assignments,
            'current' => \count(array_filter($assignments, static fn (array $row): bool => 1 === (int) $row['current'])),
            'search' => $search,
        ]);
    }

    /**
     * Blocks forum posting, with the comment why.
     */
    #[Route(path: '/admin/flags/{username}/block', name: 'admin_flags_block', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function block(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $flag = $this->flag();
        $comment = trim($request->request->getString('comment'));
        if ('' === $comment) {
            $this->addFlash('error', $this->getTranslator()->trans('admin.flags.block.comment.missing'));
        } elseif ($this->model->block($member, $flag, $comment)) {
            $this->addFlash('notice', $this->getTranslator()->trans('admin.flags.assigned.notice', [
                'flag' => $flag->getName(),
                'username' => $member->getUsername(),
            ]));
        }

        return $this->back($request, $member);
    }

    /**
     * Removes the block (from the profile or the flags list).
     */
    #[Route(path: '/admin/flags/{username}/unblock', name: 'admin_flags_unblock', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function unblock(
        Request $request,
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
    ): Response {
        $flag = $this->flag();
        $block = $this->model->findBlock($member, $flag);
        $manager = $this->getUser();
        if (null !== $block && $manager instanceof Member && $this->model->unblock($block, $manager)) {
            $this->addFlash('notice', $this->getTranslator()->trans('admin.flags.removed.notice', [
                'flag' => $flag->getName(),
                'username' => $member->getUsername(),
            ]));
        }

        return $this->back($request, $member);
    }

    private function flag(): Flag
    {
        return $this->model->getForumPostingFlag()
            ?? throw $this->createNotFoundException('There is no NotAllowedToPostInForum flag.');
    }

    /**
     * Back to the flags list or the profile the action came from (return), else the member's profile.
     */
    private function back(Request $request, Member $member): RedirectResponse
    {
        $return = $request->request->getString('return');
        if (preg_match('~^/(?:admin/flags|members/[^/?#\\\\]+)(?:\?[^#\\\\]*)?$~', $return)) {
            return $this->redirect($return);
        }

        return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
    }
}
