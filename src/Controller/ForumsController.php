<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Repository\ForumPostRepository;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ForumsController extends AbstractController
{
    private const POSTS_DIFF = 3;
    private const POSTS_MAX = 10;
    private const POSTS_MIN = 1;

    /**
     * @Route("/forums/more/group", name="forums_more_group_posts")
     */
    public function showMoreGroupPostsAction(): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_GROUPS_POSTS]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) ($memberPreference->getValue());
        $value = min($value + self::POSTS_DIFF, self::POSTS_MAX);
        $memberPreference->setValue($value);
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        return $this->redirectToRoute('forums');
    }

    /**
     * @Route("/forums/less/group", name="forums_less_group_posts")
     */
    public function showLessGroupPostsAction(): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_GROUPS_POSTS]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) ($memberPreference->getValue());
        $value = min($value - self::POSTS_DIFF, self::POSTS_MIN);
        $memberPreference->setValue($value);
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        return $this->redirectToRoute('forums');
    }

    /**
     * @Route("/forums/more/agora", name="forums_more_agora_posts")
     */
    public function showMoreAgoraPostsAction(): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_FORUM_POSTS]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) ($memberPreference->getValue());
        $value = min($value + self::POSTS_DIFF, self::POSTS_MAX);
        $memberPreference->setValue($value);
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        return $this->redirectToRoute('forums');
    }

    /**
     * @Route("/forums/less/agora", name="forums_less_agora_posts")
     */
    public function showLessAgoraPostsAction(): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::NUMBER_FORUM_POSTS]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $value = (int) ($memberPreference->getValue());
        $value = min($value - self::POSTS_DIFF, self::POSTS_MIN);
        $memberPreference->setValue($value);
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        return $this->redirectToRoute('forums');
    }

    /**
     * @Route("/forums/show/groups/only-mine", name="forums_groups_only_mine")
     */
    public function showOnlyPostsInMyGroups(Request $request): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MY_GROUP_POSTS_ONLY]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('Yes');
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/forums/show/groups/all", name="forums_groups_all")
     */
    public function showPostsInAllGroups(Request $request): RedirectResponse
    {
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MY_GROUP_POSTS_ONLY]);
        /** @var MemberPreference $memberPreference */
        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('No');
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/members/{username}/posts/{page}/{search}", name="profile_forum_posts_search")
     * @Route("/members/{username}/posts/{page}", name="profile_forum_posts",
     *     requirements={"page"="\d+"}
     * )
     *
     * @return Response
     */
    public function showPostsByMember(
        Request $request,
        ProfileSubmenu $profileSubmenu,
        Member $member,
        EntityManagerInterface $entityManager,
        int $page = 1,
        string $search = ""
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
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
                'search' => $data['q']
            ]);
        }

        /** @var ForumPostRepository $postsRepository */
        $postsRepository = $entityManager->getRepository(ForumPost::class);
        $posts = $postsRepository->getForumPostsByMember($member, $search, $page);

        return $this->render('profile/forum.posts.html.twig', [
            'search_form' => $searchForm->createView(),
            'search' => $search,
            'member' => $member,
            'posts' => $posts,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'forum_posts']),
        ]);
    }
}
