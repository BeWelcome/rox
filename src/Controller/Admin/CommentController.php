<?php

namespace App\Controller\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Model\Admin\CommentModel;
use App\Model\Admin\CommentQueue;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

/**
 * The comments tool of the volunteers (Comments right) and the Safety Team: a list of comments (the tabs, or all
 * comments about or by a member) and the chosen comment with everything that can be done to it. Every action posts
 * the page it came from (return), the list (queue) and the comment after it (next): back on the page, a comment
 * that left the list makes room for the next one.
 *
 * A volunteer with the Comments right hands a comment to the Safety Team; from then on it is theirs: the volunteers
 * without the Safety Team right don't see it in any list and can't open it, so nobody works on it twice.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class CommentController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The CSRF token id of the actions on a comment. */
    public const string TOKEN = 'admin_comment';

    public function __construct(
        private readonly CommentModel $commentModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/admin/comment', name: 'admin_comment_overview')]
    public function adminCommentOverview(Request $request): Response
    {
        return $this->queue($request, CommentQueue::All);
    }

    #[Route(path: '/admin/comment/safetyteam', name: 'admin_abuser_overview')]
    public function adminSafetyTeamOverview(Request $request): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have SafetyTeam right to access this.');
        }

        return $this->queue($request, CommentQueue::SafetyTeam);
    }

    #[Route(path: '/admin/comment/reported', name: 'admin_comment_reported_overview')]
    public function adminReportedOverview(Request $request): Response
    {
        return $this->queue($request, CommentQueue::Reported);
    }

    #[Route(path: '/admin/comment/negative', name: 'admin_negative_overview')]
    public function adminNegativeOverview(Request $request): Response
    {
        return $this->queue($request, CommentQueue::Negative);
    }

    #[Route(path: '/admin/comment/checked', name: 'admin_checked_overview')]
    public function adminCheckedOverview(Request $request): Response
    {
        return $this->queue($request, CommentQueue::Checked);
    }

    #[Route(path: '/admin/comment/for/{username:member}', name: 'admin_comments_for_member', priority: 10)]
    public function showAllCommentsForMember(Request $request, Member $member): Response
    {
        return $this->queue($request, CommentQueue::About, $member);
    }

    #[Route(path: '/admin/comment/from/{username:member}', name: 'admin_comments_from_member', priority: 10)]
    public function showAllCommentsFromMember(Request $request, Member $member): Response
    {
        return $this->queue($request, CommentQueue::By, $member);
    }

    /**
     * One comment, as the mails and the profiles link to it: the comments about the member with this one chosen.
     */
    #[Route(path: '/admin/comment/{to_member}/{from_member}', name: 'admin_comment', methods: ['GET'])]
    public function adminComment(
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        $this->denyUnlessVolunteer();
        $comment = $this->commentModel->findBetween($toMember, $fromMember)
            ?? throw $this->createNotFoundException('No such comment.');
        if (!$this->canSee($comment)) {
            // e.g. the link of the report mail, after another volunteer handed the comment over
            $this->addTranslatedFlash('notice', 'admin.comments.safetyteam.handed.over');

            return $this->redirectToRoute('admin_comment_reported_overview');
        }

        return $this->redirectToRoute('admin_comments_for_member', [
            'username' => $toMember->getUsername(),
            'selected' => $comment->getId(),
        ]);
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/hide', name: 'admin_comment_hide', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentHide(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.hidden', static function (Comment $comment) {
            $comment->setShowToOtherMembers(false);
        });
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/show', name: 'admin_comment_show', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentShow(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.visible', static function (Comment $comment) {
            $comment->setShowToOtherMembers(true);
        });
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/lock', name: 'admin_comment_lock', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentLock(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.locked', static function (Comment $comment) {
            $comment->setEditingAllowed(false);
        });
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/unlock', name: 'admin_comment_unlock', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentUnlock(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.editable', static function (Comment $comment) {
            $comment->setEditingAllowed(true);
        });
    }

    #[Route(
        path: '/admin/comment/{to_member}/{from_member}/safetyteam',
        name: 'admin_comment_assign_safetyteam',
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentAssignSafetyTeamAction(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.safetyteam', static function (Comment $comment) {
            $comment->setAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK);
        });
    }

    #[Route(
        path: '/admin/comment/{to_member}/{from_member}/checked',
        name: 'admin_comment_mark_checked',
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentMarkChecked(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.checked', static function (Comment $comment) {
            $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECKED);
        });
    }

    #[Route(
        path: '/admin/comment/{to_member}/{from_member}/recheck',
        name: 'admin_comment_check_again',
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentCheckAgain(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        return $this->act($request, $toMember, $fromMember, 'flash.admin.comment.recheck', static function (Comment $comment) {
            $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECK);
        });
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/delete', name: 'admin_comment_delete', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function adminCommentDelete(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember,
    ): Response {
        $this->denyUnlessVolunteer();
        $comment = $this->comment($toMember, $fromMember);
        // its reports go with it (comment_report: ON DELETE CASCADE)
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', 'flash.admin.comment.deleted');

        return $this->back($request, $toMember, null);
    }

    private function queue(Request $request, CommentQueue $queue, ?Member $member = null): Response
    {
        $this->denyUnlessVolunteer();

        $safetyTeam = $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM);
        $comments = $this->commentModel->getQueue($queue, $request->query->getInt('page', 1), $member, $safetyTeam);
        $list = iterator_to_array($comments->getCurrentPageResults(), false);

        // the chosen comment (also when it isn't on this page, e.g. from a mail), else the first one
        $selectedId = $request->query->getInt('selected');
        $selected = 0 < $selectedId ? $this->commentModel->find($selectedId) : null;
        if (null === $selected || !$this->canSee($selected)) {
            $selected = $list[0] ?? null;
        }

        return $this->render('admin/comment/comments.html.twig', [
            'queue' => $queue,
            'member' => $member,
            'comments' => $comments,
            'selected' => $selected,
            'next' => $this->nextAfter($selected, $list),
            'counts' => $this->commentModel->getCountsToCheck(),
            'tabs' => $this->getTabs(),
        ] + $this->commentModel->getDetails($selected, $list));
    }

    /**
     * The comment to show when the chosen one leaves the list: the one after it, at the end the one before it.
     *
     * @param list<Comment> $list
     */
    private function nextAfter(?Comment $selected, array $list): ?Comment
    {
        $index = array_search($selected, $list, true);
        if (false === $index) {
            return null;
        }

        return $list[$index + 1] ?? $list[$index - 1] ?? null;
    }

    /**
     * @param callable(Comment): void $change
     */
    private function act(Request $request, Member $toMember, Member $fromMember, string $flash, callable $change): Response
    {
        $this->denyUnlessVolunteer();
        $comment = $this->comment($toMember, $fromMember);
        $change($comment);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', $flash);

        return $this->back($request, $toMember, $comment);
    }

    /**
     * Back to the page the action came from, with the comment still chosen; with the next one if it left the list
     * (or was deleted). Only pages of the comments tool are allowed as return.
     */
    private function back(Request $request, Member $toMember, ?Comment $comment): RedirectResponse
    {
        $return = $request->request->getString('return');
        if (!preg_match('~^/admin/comment(?:/[^?#]*)?(?:\?[^#]*)?$~', $return) || str_contains($return, '\\')) {
            $return = $this->generateUrl('admin_comments_for_member', ['username' => $toMember->getUsername()]);
        }

        $path = (string) parse_url($return, \PHP_URL_PATH);
        parse_str((string) parse_url($return, \PHP_URL_QUERY), $query);

        $queue = CommentQueue::tryFrom($request->request->getString('queue'));
        $stays = null !== $comment && $this->canSee($comment) && (null === $queue || $queue->contains($comment));
        $next = $request->request->getInt('next');
        unset($query['selected']);
        if ($stays) {
            $query['selected'] = $comment->getId();
        } elseif (0 < $next) {
            $query['selected'] = $next;
        }

        return $this->redirect($path . ([] === $query ? '' : '?' . http_build_query($query)));
    }

    /**
     * The comment to act on; a comment handed to the Safety Team only for them.
     */
    private function comment(Member $toMember, Member $fromMember): Comment
    {
        $comment = $this->commentModel->findBetween($toMember, $fromMember)
            ?? throw $this->createNotFoundException('No such comment.');
        if (!$this->canSee($comment)) {
            throw $this->createAccessDeniedException('The comment is with the Safety Team.');
        }

        return $comment;
    }

    /**
     * Whether the volunteer may see the comment: one handed to the Safety Team only its members.
     */
    private function canSee(Comment $comment): bool
    {
        return CommentAdminActionType::SAFETY_TEAM_CHECK !== $comment->getAdminAction()
            || $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM);
    }

    private function denyUnlessVolunteer(): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_COMMENTS) && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have the Comments right or be a member of the Safety Team to access this.');
        }
    }

    /**
     * The tabs, by right: the Safety Team's first.
     *
     * @return array<string, array{key: string, url: string}> by CommentQueue value
     */
    private function getTabs(): array
    {
        $tabs = [];
        if ($this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            $tabs[CommentQueue::SafetyTeam->value] = $this->tab('admin.comments.tab.safetyteam', 'admin_abuser_overview');
        }
        $tabs[CommentQueue::Reported->value] = $this->tab('admin.comments.tab.reported', 'admin_comment_reported_overview');
        if ($this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            $tabs[CommentQueue::Negative->value] = $this->tab('admin.comments.tab.negative', 'admin_negative_overview');
        }
        $tabs[CommentQueue::Checked->value] = $this->tab('admin.comments.tab.checked', 'admin_checked_overview');
        $tabs[CommentQueue::All->value] = $this->tab('admin.comments.tab.all', 'admin_comment_overview');

        return $tabs;
    }

    /**
     * @return array{key: string, url: string}
     */
    private function tab(string $key, string $route): array
    {
        return ['key' => $key, 'url' => $this->generateUrl($route)];
    }
}
