<?php

namespace App\Controller;

use App\Entity\MemberPreference;
use App\Entity\Preference;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ForumsController extends AbstractController
{
    private const POSTS_DIFF = 3;
    private const POSTS_MAX = 10;
    private const POSTS_MIN = 1;

    /**
     * @Route("/forums/more/group", name="forums_more_group_posts")
     *
     * @return RedirectResponse
     */
    public function showMoreGroupPostsAction()
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
     *
     * @return RedirectResponse
     */
    public function showLessGroupPostsAction()
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
     *
     * @return RedirectResponse
     */
    public function showMoreAgoraPostsAction()
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
     *
     * @return RedirectResponse
     */
    public function showLessAgoraPostsAction()
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
     *
     * @return RedirectResponse
     */
    public function showOnlyPostsInMyGroupsAction()
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

        return $this->redirectToRoute('forums');
    }

    /**
     * @Route("/forums/show/groups/all", name="forums_groups_all")
     *
     * @return RedirectResponse
     */
    public function showPostsInAllGroupsAction()
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

        return $this->redirectToRoute('forums');
    }
}
