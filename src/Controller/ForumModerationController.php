<?php

namespace App\Controller;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\PostCanStillEditType;
use App\Doctrine\ReportStatusType;
use App\Entity\ForumPost;
use App\Entity\Group;
use App\Entity\Member;
use App\Logger\Logger;
use App\Model\ForumModerationModel;
use App\Security\ForumVoter;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Forum moderation, at the legacy forum's URLs: the moderator's page of a post (its thread's group, pin, visibility
 * and deletion; the post's visibility, deletion and whether its author can still edit it), members' reports about
 * posts and the moderators' answers, and the forum rules.
 */
class ForumModerationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly ForumModerationModel $moderationModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly Logger $logger,
    ) {
    }

    #[Route(path: '/forums/rules', name: 'forum_rules', methods: ['GET'])]
    public function rules(): Response
    {
        return $this->render('forum/rules.html.twig');
    }

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

        $visibilities = [
            'forum_edit_vis_norestriction' => ForumVisibilityType::NO_RESTRICTION,
            'forum_edit_vis_membersonly' => ForumVisibilityType::MEMBERS_ONLY,
            'forum_edit_vis_grouponly' => ForumVisibilityType::GROUP_ONLY,
            'forum_edit_vis_moderatoronly' => ForumVisibilityType::MODERATOR_ONLY,
        ];

        $threadForm = $this->container->get('form.factory')->createNamedBuilder('moderate_thread', FormType::class, [
            'group' => $thread->getGroup(),
            'stickyValue' => (int) $thread->getStickyValue(),
            'visibility' => \in_array($thread->getVisibility(), $visibilities, true) ? $thread->getVisibility() : ForumVisibilityType::MEMBERS_ONLY,
            'deleted' => ForumDeleteStatusType::DELETED === $thread->getDeleted(),
            'expiryDate' => $thread->getExpiryDate(),
        ])
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'choice_translation_domain' => false,
                'query_builder' => static fn (EntityRepository $repository) => $repository->createQueryBuilder('g')
                    ->where('g.approved = :approved')
                    ->setParameter('approved', Group::APPROVED)
                    ->orderBy('g.name', 'ASC'),
                'label' => 'group',
                'attr' => ['class' => 'js-tom-select'],
            ])
            ->add('stickyValue', IntegerType::class, ['label' => 'forum.moderation.sticky', 'required' => false, 'empty_data' => '0'])
            ->add('visibility', ChoiceType::class, ['choices' => $visibilities, 'label' => 'forum_label_visibility'])
            ->add('expiryDate', DateType::class, ['widget' => 'single_text', 'required' => false, 'label' => 'forum.moderation.expiry', 'input' => 'datetime'])
            ->add('deleted', CheckboxType::class, ['required' => false, 'label' => 'deleted'])
            ->add('save', SubmitType::class, ['label' => 'forum.moderation.save.thread'])
            ->getForm();

        $postForm = $this->container->get('form.factory')->createNamedBuilder('moderate_post', FormType::class, [
            'ownerCanStillEdit' => PostCanStillEditType::CAN_STILL_EDIT === $post->getOwnerCanStillEdit(),
            'visibility' => \in_array($post->getPostvisibility(), $visibilities, true) ? $post->getPostvisibility() : ForumVisibilityType::MEMBERS_ONLY,
            'deleted' => ForumDeleteStatusType::DELETED === $post->getDeleted(),
        ])
            ->add('ownerCanStillEdit', CheckboxType::class, ['required' => false, 'label' => 'forum.moderation.owner.can.edit'])
            ->add('visibility', ChoiceType::class, ['choices' => $visibilities, 'label' => 'forum_label_visibility'])
            ->add('deleted', CheckboxType::class, ['required' => false, 'label' => 'deleted'])
            ->add('save', SubmitType::class, ['label' => 'forum.moderation.save.post'])
            ->getForm();

        $threadForm->handleRequest($request);
        if ($threadForm->isSubmitted() && $threadForm->isValid()) {
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
            $this->addTranslatedFlash('notice', 'forum.moderation.saved');

            return $this->redirectToRoute('forum_admin', ['postId' => $post->getId()]);
        }

        $postForm->handleRequest($request);
        if ($postForm->isSubmitted() && $postForm->isValid()) {
            $data = $postForm->getData();
            $this->moderationModel->updatePost($post, (bool) $data['ownerCanStillEdit'], $data['visibility'], (bool) $data['deleted']);
            $this->logger->write("Post #{$post->getId()} moderated", 'ForumModerator');
            $this->addTranslatedFlash('notice', 'forum.moderation.saved');

            return $this->redirectToRoute('forum_admin', ['postId' => $post->getId()]);
        }

        return $this->render('forum/moderate.html.twig', [
            'post' => $post,
            'thread' => $thread,
            'threadForm' => $threadForm,
            'postForm' => $postForm,
            'reports' => $this->moderationModel->findReportsOfPosts([(int) $post->getId()])[(int) $post->getId()] ?? [],
        ]);
    }

    /**
     * A member reports a post to the moderators, or adds to their report.
     */
    #[Route(path: '/forums/reporttomod/{postId}', name: 'forum_report', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    public function report(Request $request, #[MapEntity(id: 'postId')] ForumPost $post): Response
    {
        $this->denyAccessUnlessGranted(ForumVoter::POST_VIEW, $post);
        $member = $this->getMember();
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
            'report' => $report,
            'form' => $form,
        ]);
    }

    /**
     * A moderator reads a member's report and answers it.
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

        $form = $this->createCommentForm('report_answer', true, $report->getStatus());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->moderationModel->answerReport($report, $this->getMember(), (string) $form->get('comment')->getData(), $form->get('status')->getData());
            $this->logger->write("Report of member #{$reporter->getId()} about post #{$post->getId()} answered", 'ForumModerator');
            $this->addTranslatedFlash('notice', 'forum.moderation.saved');

            return $this->redirectToRoute('forum_report_member', ['postId' => $post->getId(), 'memberId' => $reporter->getId()]);
        }

        return $this->render('forum/report.html.twig', [
            'post' => $post,
            'report' => $report,
            'form' => $form,
        ]);
    }

    /**
     * The moderators' lists of reports: all open ones, or the ones a moderator answered (all, or the open ones).
     */
    #[Route(path: '/forums/reporttomod/{list}', name: 'forum_reports', requirements: ['list' => 'AllActiveReports|AllMyReport|MyReportActive'], methods: ['GET'])]
    public function reports(string $list): Response
    {
        $this->denyAccessUnlessModerator();
        $open = [ReportStatusType::OPEN, ReportStatusType::IN_DISCUSSION];

        $reports = match ($list) {
            'AllMyReport' => $this->moderationModel->findReports([], $this->getMember()),
            'MyReportActive' => $this->moderationModel->findReports($open, $this->getMember()),
            default => $this->moderationModel->findReports($open),
        };

        return $this->render('forum/reports.html.twig', ['reports' => $reports, 'list' => $list]);
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
