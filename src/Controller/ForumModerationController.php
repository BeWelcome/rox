<?php

namespace App\Controller;

use App\Doctrine\ReportStatusType;
use App\Entity\ForumPost;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\ReportToModerator;
use App\Form\ModeratePostType;
use App\Form\ModerateThreadType;
use App\Logger\Logger;
use App\Model\ForumModerationModel;
use App\Security\ForumVoter;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Forum moderation, mostly at the legacy forum's URLs: the moderation panel's forms (a thread's group, pin,
 * visibility and deletion; a post's visibility, deletion and whether its author can still edit it), members' reports
 * about posts, the moderators' answers and the Reports tab of the groups.
 */
class ForumModerationController extends AbstractController
{
    use GroupSubmenuTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private const string LISTS = 'AllActiveReports|AllMyReport|MyReportActive';

    public function __construct(
        private readonly ForumModerationModel $moderationModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly Logger $logger,
    ) {
    }

    /**
     * The rules are a tab of every group now; the agora's has them for the old URL.
     */
    #[Route(path: '/forums/rules', name: 'forum_rules', methods: ['GET'])]
    public function rules(): RedirectResponse
    {
        return $this->redirectToRoute('group_rules', ['group_id' => Group::BEWELCOME], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The moderators' settings of a post and its thread. They are in the moderation panel of the thread's page now:
     * the old URL opens the panel; the panel's forms are sent here and lead back to it.
     */
    #[Route(path: '/forums/modfulleditpost/{postId}', name: 'forum_admin', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    #[Route(path: '/forums/modeditpost/{postId}', name: 'forum_admin_edit', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    #[Route(path: '/forums/modeditpost/{postId}/', name: 'forum_admin_edit_trailing', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    public function moderate(Request $request, #[MapEntity(id: 'postId')] ForumPost $post): Response
    {
        $this->denyAccessUnlessModerator();
        $thread = $post->getThread();
        if (null === $thread) {
            throw $this->createNotFoundException();
        }
        $panel = ['postId' => $post->getId(), 'moderate' => $post->getId()];
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute('forum_post', $panel);
        }

        $threadForm = $this->createForm(ModerateThreadType::class, ModerateThreadType::dataOf($thread));
        $threadForm->handleRequest($request);
        $postForm = $this->createForm(ModeratePostType::class, ModeratePostType::dataOf($post));
        $postForm->handleRequest($request);

        if ($threadForm->isSubmitted()) {
            $panel['section'] = 'topic';
            if (!$threadForm->isValid()) {
                return $this->notSaved($panel);
            }
            $data = $threadForm->getData();
            $this->moderationModel->updateThread(
                $thread,
                $data['group'],
                (int) $data['stickyValue'],
                $data['visibility'],
                (bool) $data['deleted'],
                $data['expiryDate'],
            );
            $this->logger->write("Thread #{$thread->getId()} moderated", 'ForumModerator');
        } elseif ($postForm->isSubmitted()) {
            $panel['section'] = 'post';
            if (!$postForm->isValid()) {
                return $this->notSaved($panel);
            }
            $data = $postForm->getData();
            $this->moderationModel->updatePost($post, (bool) $data['ownerCanStillEdit'], $data['visibility'], (bool) $data['deleted']);
            $this->logger->write("Post #{$post->getId()} moderated", 'ForumModerator');
        } else {
            return $this->redirectToRoute('forum_post', $panel);
        }

        return $this->redirectToRoute('forum_post', $panel + ['saved' => 1]);
    }

    /**
     * A member reports a post to the moderators, or adds to their report: a page of the post's group.
     */
    #[Route(path: '/forums/reporttomod/{postId}', name: 'forum_report', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    public function report(Request $request, #[MapEntity(id: 'postId')] ForumPost $post): Response
    {
        $this->denyAccessUnlessGranted(ForumVoter::POST_VIEW, $post);
        $member = $this->getMember();
        $group = $this->getGroupOf($post);
        $report = $this->moderationModel->findReport($post, $member);

        $form = $this->createCommentForm('report', false, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->moderationModel->report($post, $member, $form->get('comment')->getData());
            $this->logger->write("Post #{$post->getId()} reported", 'Forum');
            $this->addTranslatedFlash('notice', 'forum.report.sent');

            return $this->redirectToRoute('forum_report', ['postId' => $post->getId()]);
        }

        return $this->render('forum/report.html.twig', [
            'post' => $post,
            'group' => $group,
            'report' => $report,
            'form' => $form,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'],
        ]);
    }

    /**
     * A moderator reads a member's report and answers it: the Reports tab of the post's group.
     */
    #[Route(path: '/forums/reporttomod/{postId}/{memberId}', name: 'forum_report_member', requirements: ['postId' => '\d+', 'memberId' => '\d+'], methods: ['GET', 'POST'])]
    public function reportOfMember(
        Request $request,
        #[MapEntity(id: 'postId')] ForumPost $post,
        #[MapEntity(id: 'memberId')] Member $reporter,
    ): Response {
        $this->denyAccessUnlessModerator();
        $report = $this->moderationModel->findReport($post, $reporter);
        if (null === $report) {
            throw $this->createNotFoundException();
        }
        $group = $this->getGroupOf($post);

        $form = $this->createCommentForm('report_answer', true, $report->getStatus());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->moderationModel->answerReport($report, $this->getMember(), (string) $form->get('comment')->getData(), $form->get('status')->getData());
            $this->logger->write("Report of member #{$reporter->getId()} about post #{$post->getId()} answered", 'ForumModerator');
            $this->addTranslatedFlash('notice', 'forum.moderation.saved');

            return $this->redirectToRoute('forum_report_member', ['postId' => $post->getId(), 'memberId' => $reporter->getId()]);
        }

        $others = array_values(array_filter(
            $this->moderationModel->findReportsOfPosts([(int) $post->getId()])[(int) $post->getId()] ?? [],
            static fn (ReportToModerator $other): bool => $other->getId() !== $report->getId(),
        ));

        return $this->render('forum/report_moderator.html.twig', [
            'post' => $post,
            'group' => $group,
            'report' => $report,
            'others' => $others,
            'form' => $form,
            'submenu' => ['items' => $this->getSubmenuItems($this->getMember(), $group), 'active' => 'reports'],
        ]);
    }

    /**
     * The moderators' lists of reports are the Reports tab of the groups now; the old URL is the one of all groups.
     */
    #[Route(path: '/forums/reporttomod/{list}', name: 'forum_reports', requirements: ['list' => self::LISTS], methods: ['GET'])]
    public function reports(string $list): RedirectResponse
    {
        $this->denyAccessUnlessModerator();

        return $this->redirectToRoute('group_reports', ['group_id' => Group::BEWELCOME, 'list' => $list, 'scope' => 'all'], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The Reports tab of a group, for the forum moderators: all open reports about posts of the group, or the ones
     * the moderator answered (all, or the open ones); ?scope=all: of all groups.
     */
    #[Route(path: '/group/{group_id}/reports/{list}', name: 'group_reports', requirements: ['group_id' => '\d+', 'list' => self::LISTS], defaults: ['list' => 'AllActiveReports'], methods: ['GET'])]
    public function groupReports(Request $request, #[MapEntity(id: 'group_id')] Group $group, string $list): Response
    {
        $this->denyAccessUnlessModerator();
        $all = 'all' === $request->query->get('scope');
        $scope = $all ? null : $group;
        $member = $this->getMember();

        $lists = [
            'AllActiveReports' => [ForumModerationModel::OPEN_STATUSES, null],
            'MyReportActive' => [ForumModerationModel::OPEN_STATUSES, $member],
            'AllMyReport' => [[], $member],
        ];
        $counts = array_map(fn (array $criteria): int => \count($this->moderationModel->findReports($criteria[0], $criteria[1], $scope)), $lists);
        $reports = $this->moderationModel->findReports($lists[$list][0], $lists[$list][1], $scope);

        $postIds = array_values(array_unique(array_map(static fn (ReportToModerator $report): int => $report->getPostId(), $reports)));
        $posts = [];
        if ([] !== $postIds) {
            foreach ($this->entityManager->getRepository(ForumPost::class)->findBy(['id' => $postIds]) as $post) {
                $posts[(int) $post->getId()] = $post;
            }
        }

        return $this->render('forum/reports.html.twig', [
            'group' => $group,
            'reports' => $reports,
            'posts' => $posts,
            'list' => $list,
            'counts' => $counts,
            'all' => $all,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'reports'],
        ]);
    }

    /**
     * @param array<string, int|string> $panel
     */
    private function notSaved(array $panel): RedirectResponse
    {
        $this->addTranslatedFlash('error', 'forum.moderation.not.saved');

        return $this->redirectToRoute('forum_post', $panel);
    }

    private function getGroupOf(ForumPost $post): Group
    {
        $group = $post->getThread()?->getGroup();
        if (null === $group) {
            throw $this->createNotFoundException();
        }

        return $group;
    }

    private function createCommentForm(string $name, bool $withStatus, ?string $status): FormInterface
    {
        $builder = $this->container->get('form.factory')->createNamedBuilder($name, FormType::class, ['status' => $status])
            ->add('comment', TextareaType::class, [
                'label' => 'forum.report.comment',
                'required' => !$withStatus,
                'constraints' => $withStatus ? [new Length(max: 5000)] : [new NotBlank(), new Length(max: 5000)],
            ]);
        if ($withStatus) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'forum.report.status',
                'expanded' => true,
                'choices' => [
                    'forum.report.status.open' => ReportStatusType::OPEN,
                    'forum.report.status.discussion' => ReportStatusType::IN_DISCUSSION,
                    'forum.report.status.closed' => ReportStatusType::CLOSED,
                ],
            ]);
        }

        return $builder->add('send', SubmitType::class, ['label' => 'forum.report.send'])->getForm();
    }

    private function denyAccessUnlessModerator(): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR) && !$this->isGranted(Member::ROLE_ADMIN_ADMIN)) {
            throw $this->createAccessDeniedException();
        }
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
