<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Member;
use App\Entity\Preference;
use App\Repository\ForumPostRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumsController extends AbstractController
{
    use ItemsPerPageTraits;

    private const int POSTS_DIFF = 3;
    private const int POSTS_MAX = 10;
    private const int POSTS_MIN = 1;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/forums/more/group', name: 'forums_more_group_posts')]
    public function showMoreGroupPostsAction(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_GROUPS_POSTS]);

        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) $memberPreference->getValue();
        $value = min($value + self::POSTS_DIFF, self::POSTS_MAX);
        $memberPreference->setValue($value);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        return $this->redirectToRoute('forums');
    }

    #[Route(path: '/forums/less/group', name: 'forums_less_group_posts')]
    public function showLessGroupPostsAction(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_GROUPS_POSTS]);

        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) $memberPreference->getValue();
        $value = min($value - self::POSTS_DIFF, self::POSTS_MIN);
        $memberPreference->setValue($value);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        return $this->redirectToRoute('forums');
    }

    #[Route(path: '/forums/more/agora', name: 'forums_more_agora_posts')]
    public function showMoreAgoraPostsAction(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_FORUM_POSTS]);

        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) $memberPreference->getValue();
        $value = min($value + self::POSTS_DIFF, self::POSTS_MAX);
        $memberPreference->setValue($value);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        return $this->redirectToRoute('forums');
    }

    #[Route(path: '/forums/less/agora', name: 'forums_less_agora_posts')]
    public function showLessAgoraPostsAction(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_FORUM_POSTS]);

        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) $memberPreference->getValue();
        $value = min($value - self::POSTS_DIFF, self::POSTS_MIN);
        $memberPreference->setValue($value);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        return $this->redirectToRoute('forums');
    }

    #[Route(path: '/forums/show/groups/only-mine', name: 'forums_groups_only_mine')]
    public function showOnlyPostsInMyGroups(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MY_GROUP_POSTS_ONLY]);

        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('Yes');
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    #[Route(path: '/forums/show/groups/all', name: 'forums_groups_all')]
    public function showPostsInAllGroups(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MY_GROUP_POSTS_ONLY]);

        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('No');
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    #[Route(path: '/members/{username:member}/posts/{page}/{search}', name: 'profile_forum_posts_search')]
    #[Route(path: '/members/{username:member}/posts/{page}', name: 'profile_forum_posts', requirements: ['page' => '\d+'])]
    public function showPostsByMember(
        Request $request,
        ProfileSubmenu $profileSubmenu,
        Member $member,
        EntityManagerInterface $entityManager,
        ChangeProfilePictureGlobals $globals,
        int $page = 1,
        string $search = '',
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        $roles = $loggedInMember->getRoles();
        $adminShowForumPosts = (\in_array(Member::ROLE_ADMIN_SAFETYTEAM, $roles, true)
            || \in_array(Member::ROLE_ADMIN_ADMIN, $roles, true)
            || \in_array(Member::ROLE_ADMIN_FORUMMODERATOR, $roles, true)
        );

        $preferenceRepository = $entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_FORUMS_POSTS]);
        $memberPreference = $member->getMemberPreference($preference);

        if ('No' === $memberPreference->getValue() && $member !== $loggedInMember && !$adminShowForumPosts) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $searchForm = $this->createFormBuilder()
            ->add('q', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'forum.search.term',
                ],
                'required' => false,
            ])
            ->setMethod('POST')
            ->setData(['q' => $search])
            ->getForm()
        ;
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();

            return $this->redirectToRoute('profile_forum_posts_search', [
                'username' => $member->getUsername(),
                'search' => $data['q'],
            ]);
        }

        /** @var ForumPostRepository $postsRepository */
        $postsRepository = $this->entityManager->getRepository(ForumPost::class);

        $itemsPerPage = $this->getItemsPerPage($member);
        $posts = $postsRepository->getForumPostsByMember($member, $search, $page, $itemsPerPage);

        return $this->render('profile/forum.posts.html.twig', [
            'search_form' => $searchForm->createView(),
            'search' => $search,
            'member' => $member,
            'posts' => $posts,
            'globals_js_json' => $globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'forum_posts']),
        ]);
    }
}
