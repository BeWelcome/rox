<?php

namespace App\Controller;

use App\Doctrine\ForumVisibilityType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use App\Form\ForumPostType;
use App\Logger\Logger;
use App\Model\ForumModel;
use App\Security\ForumVoter;
use App\Utilities\GroupSubmenuTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Writing in the forums: a new thread (in the agora or a group's forum), a reply, an edit or translation of a post.
 * The URLs are the ones the legacy forum had.
 */
class ForumWritingController extends AbstractController
{
    use GroupSubmenuTrait;

    public function __construct(
        private readonly ForumModel $forumModel,
        private readonly Logger $logger,
    ) {
    }

    #[Route(path: '/forums/new', name: 'forums_new', methods: ['GET', 'POST'])]
    public function newAgoraThread(
        Request $request,
        #[MapEntity(expr: 'repository.find(' . Group::BEWELCOME . ')')] Group $group,
    ): Response {
        return $this->newThread($request, $group);
    }

    #[Route(path: '/group/{group_id}/new', name: 'group_new_post', requirements: ['group_id' => '\d+'], methods: ['GET', 'POST'])]
    #[Route(path: '/group/{group_id}/forum/new', name: 'group_new_topic', requirements: ['group_id' => '\d+'], methods: ['GET', 'POST'])]
    public function newGroupThread(Request $request, #[MapEntity(id: 'group_id')] Group $group): Response
    {
        return $this->newThread($request, $group);
    }

    #[Route(path: '/forums/s{threadId}/reply', name: 'forum_reply', requirements: ['threadId' => '\d+'], methods: ['GET', 'POST'])]
    public function agoraReply(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): Response
    {
        return $this->reply($request, $thread);
    }

    #[Route(path: '/group/{group_id}/forum/s{thread}/reply', name: 'group_forum_reply', requirements: ['group_id' => '\d+', 'thread' => '\d+'], methods: ['GET', 'POST'])]
    public function groupReply(Request $request, #[MapEntity(id: 'thread')] ForumThread $thread): Response
    {
        return $this->reply($request, $thread);
    }

    #[Route(path: '/forums/edit/m{postId}', name: 'forum_edit', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'postId')] ForumPost $post,
        #[Autowire('%locales%')] array $locales,
    ): Response {
        return $this->editPost($request, $post, $locales, $post->getLanguage()->getShortCode());
    }

    #[Route(path: '/forums/translate/m{postId}', name: 'forum_translate', requirements: ['postId' => '\d+'], methods: ['GET', 'POST'])]
    public function translate(
        Request $request,
        #[MapEntity(id: 'postId')] ForumPost $post,
        #[Autowire('%locales%')] array $locales,
    ): Response {
        return $this->editPost($request, $post, $locales, $request->getLocale());
    }

    private function newThread(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted(ForumVoter::GROUP_POST, $group);
        $member = $this->getMember();
        $visibilities = $this->getVisibilities($group);

        $form = $this->createForm(ForumPostType::class, [
            'subscribe' => true,
            'visibility' => $visibilities[0] ?? null,
        ], ['with_title' => true, 'visibilities' => $visibilities, 'submit_label' => 'forum_label_create_topic']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $thread = $this->forumModel->createThread(
                $group,
                $member,
                $data['title'],
                $data['text'],
                $request->getLocale(),
                $data['visibility'] ?? $this->getDefaultVisibility($group),
                (bool) $data['subscribe'],
            );
            $this->logger->write("New thread #{$thread->getId()} in group #{$group->getId()}", 'Forum');

            return $this->redirectToThread($thread);
        }

        return $this->renderForm('forum/new.html.twig', $form, ['group' => $group], $group);
    }

    private function reply(Request $request, ForumThread $thread): Response
    {
        $this->denyAccessUnlessGranted(ForumVoter::THREAD_REPLY, $thread);
        $member = $this->getMember();
        $group = $thread->getGroup();
        $visibilities = $this->getVisibilities($group);

        $form = $this->createForm(ForumPostType::class, [
            'subscribe' => $this->forumModel->isSubscribed($thread, $member),
            'visibility' => \in_array($thread->getVisibility(), $visibilities, true) ? $thread->getVisibility() : ($visibilities[0] ?? null),
        ], ['visibilities' => $visibilities]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $post = $this->forumModel->reply(
                $thread,
                $member,
                $data['text'],
                $request->getLocale(),
                $data['visibility'] ?? $this->getDefaultVisibility($group),
                (bool) $data['subscribe'],
            );
            $this->logger->write("Reply #{$post->getId()} in thread #{$thread->getId()}", 'Forum');

            return $this->redirectToThread($thread, $post);
        }

        return $this->renderForm('forum/reply.html.twig', $form, ['thread' => $thread], $group);
    }

    /**
     * @param list<string> $locales
     */
    private function editPost(Request $request, ForumPost $post, array $locales, string $locale): Response
    {
        $this->denyAccessUnlessGranted(ForumVoter::POST_EDIT, $post);
        $member = $this->getMember();
        $thread = $post->getThread();
        $isFirstPost = null !== $thread && $thread->getFirstPost() === $post;
        // the language picked on the page, else the post's own (edit) or the member's (translation)
        $locale = (string) $request->query->get('locale', $locale);
        $locale = \in_array($locale, $locales, true) ? $locale : $post->getLanguage()->getShortCode();
        $translations = $post->getMessageTranslations();
        $visibilities = null === $thread ? [] : $this->getVisibilities($thread->getGroup());

        $form = $this->createForm(ForumPostType::class, [
            'locale' => $locale,
            'text' => $translations[$locale]['message'] ?? '',
            'title' => $isFirstPost ? ($thread->getTitles()[$locale] ?? '') : null,
            'visibility' => \in_array($post->getPostvisibility(), $visibilities, true) ? $post->getPostvisibility() : null,
        ], [
            'with_title' => $isFirstPost,
            'with_subscribe' => false,
            'visibilities' => $visibilities,
            'locales' => array_values(array_unique([...$locales, $post->getLanguage()->getShortCode()])),
            'display_locale' => $request->getLocale(),
            'submit_label' => $isFirstPost ? 'forum_label_update_topic' : 'forum_label_update_post',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->forumModel->savePost($post, $member, $data['locale'], $data['text'], $data['title'] ?? null, $data['visibility'] ?? null);
            $this->logger->write("Post #{$post->getId()} edited in {$data['locale']}", 'Forum');

            return null === $thread ? $this->redirectToRoute('forums') : $this->redirectToThread($thread, $post);
        }

        return $this->renderForm('forum/edit.html.twig', $form, ['post' => $post, 'thread' => $thread], $thread?->getGroup());
    }

    /**
     * Who a post can be for: the agora's posts are for all members; in a group's forum for all members or the group's
     * members only, in a members-only group for its members only.
     *
     * @return list<string>
     */
    private function getVisibilities(?Group $group): array
    {
        if (null === $group || $group->isBeWelcome() || $group->isMembersOnly()) {
            return [];
        }

        return [ForumVisibilityType::MEMBERS_ONLY, ForumVisibilityType::GROUP_ONLY];
    }

    private function getDefaultVisibility(?Group $group): string
    {
        return null !== $group && !$group->isBeWelcome() && $group->isMembersOnly()
            ? ForumVisibilityType::GROUP_ONLY
            : ForumVisibilityType::MEMBERS_ONLY;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function renderForm(string $template, FormInterface $form, array $parameters, ?Group $group): Response
    {
        $parameters['form'] = $form;
        $parameters['group'] = $group;
        if (null !== $group && !$group->isBeWelcome()) {
            $parameters['submenu'] = ['items' => $this->getSubmenuItems($this->getMember(), $group), 'active' => 'forum'];
        }

        return $this->render($template, $parameters);
    }

    /**
     * To the thread, at the post (on the last page, where a new post is when the thread is read oldest first).
     */
    private function redirectToThread(ForumThread $thread, ?ForumPost $post = null): RedirectResponse
    {
        $fragment = null === $post ? [] : ['_fragment' => 'post' . $post->getId()];
        $page = null === $post ? 1 : intdiv((int) $thread->getReplies(), ForumPagesController::POSTS_PER_PAGE) + 1;
        if ($thread->isAgora()) {
            return 1 === $page
                ? $this->redirectToRoute('forum_thread', ['threadId' => $thread->getId()] + $fragment)
                : $this->redirectToRoute('forum_thread_pages', ['threadId' => $thread->getId(), 'pageId' => $page] + $fragment);
        }

        $parameters = ['group_id' => $thread->getGroup()->getId(), 'thread' => $thread->getId()];

        return 1 === $page
            ? $this->redirectToRoute('group_forum_thread', $parameters + $fragment)
            : $this->redirectToRoute('group_forum_thread_pages', $parameters + ['page_id' => $page] + $fragment);
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
