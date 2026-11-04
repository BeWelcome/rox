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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumsController extends AbstractController
{
    use ItemsPerPageTraits;

    public function __construct(private EntityManagerInterface $entityManager)
    {
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
        // what the logged in member may read in the forum; forum moderators and admins see all
        $moderator = $this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR) || $this->isGranted(Member::ROLE_ADMIN_ADMIN);
        $posts = $postsRepository->getForumPostsByMember($member, $loggedInMember, $moderator, $search, $page, $itemsPerPage);

        return $this->render('profile/forum.posts.html.twig', [
            'search_form' => $searchForm->createView(),
            'search' => $search,
            'member' => $member,
            'posts' => $posts,
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'forum_posts']),
        ]);
    }
}
