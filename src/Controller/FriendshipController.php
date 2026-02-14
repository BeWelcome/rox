<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use App\Repository\FriendRepository;
use App\Service\Mailer;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FriendshipController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProfileSubmenu $profileSubmenu,
        private readonly ChangeProfilePictureGlobals $globals,
    ) {
    }

    #[Route(path: '/members/{username:member}/friendship/add', name: 'add_friendship', methods: ['GET', 'POST'])]
    public function add(Request $request, Member $member, Mailer $mailer): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $loggedInMember->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.friend.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        /** @var FriendRepository $friendRepository */
        $friendRepository = $this->entityManager->getRepository(Friend::class);
        $friend = $friendRepository->findFriendshipBetween($loggedInMember, $member);

        if (null !== $friend) {
            return $this->redirectToRoute('edit_friendship', ['username' => $member->getUsername()]);
        }

        if ('POST' === $request->getMethod()) {
            $add = $request->request->get('add_friendship', '');
            if ('add' === $add) {
                $left = $member->getId() < $loggedInMember->getId() ? $member : $loggedInMember;
                $right = $member->getId() < $loggedInMember->getId() ? $loggedInMember : $member;

                $friend = new Friend();
                $friend->setLeft($left);
                $friend->setRight($right);
                $friend->setConfirmed(false);

                $this->entityManager->persist($friend);
                $this->entityManager->flush();

                $mailer->sendFriendshipNotification($friend, $loggedInMember);
            }

            return $this->redirectToRoute('friends', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('friend/add.html.twig', [
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'add_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/friendship/edit', name: 'edit_friendship', methods: ['GET', 'POST'])]
    public function edit(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $loggedInMember->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.friend.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        /** @var FriendRepository $friendRepository */
        $friendRepository = $this->entityManager->getRepository(Friend::class);
        $friend = $friendRepository->findFriendshipBetween($loggedInMember, $member);
        if (null === $friend) {
            return $this->redirectToRoute('add_friendship', ['username' => $member->getUsername()]);
        }

        if ('POST' === $request->getMethod()) {
            $remove = $request->request->get('remove_friendship');
            if ('remove' === $remove) {
                $this->entityManager->remove($friend);
                $this->entityManager->flush();
            }

            return $this->redirectToRoute('friends', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('friend/edit.html.twig', [
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'add_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/friendship/remove', name: 'remove_friendship')]
    public function remove(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);

        $friendship = $relationRepository->findFriendshipBetween($loggedInMember, $member);
        if (null === $friendship) {
            return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.removed');

        return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{left}/friendship/{right}/confirm', name: 'confirm_friendship')]
    public function confirm(
        #[MapEntity(mapping: ['left' => 'username'])] Member $left,
        #[MapEntity(mapping: ['right' => 'username'])] Member $right,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($left !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);
        $friendship = $relationRepository->findUnconfirmedRelationBetween($left, $right);
        if (null === $friendship) {
            return $this->redirectToRoute('friends', ['username' => $right->getUsername()]);
        }

        $friendship->setConfirmed(true);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.confirmed');

        return $this->redirectToRoute('friends', ['username' => $left->getUsername()]);
    }

    #[Route(path: '/members/{left}/friendship/{right}/dismiss', name: 'dismiss_friendship')]
    public function dismiss(
        #[MapEntity(mapping: ['left' => 'username'])] Member $left,
        #[MapEntity(mapping: ['right' => 'username'])] Member $right,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($left !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);
        $friendship = $relationRepository->findUnconfirmedRelationBetween($left, $right);
        if (null === $friendship) {
            return $this->redirectToRoute('friends', ['username' => $left->getUsername()]);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.dismissed');

        return $this->redirectToRoute('friends', ['username' => $left->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/friends/{page}', name: 'friends')]
    public function friends(Member $member, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();
        $itemsPerPage = $this->getItemsPerPage($loggedInMember);

        /** @var FriendRepository $friendRepository */
        $friendRepository = $this->entityManager->getRepository(Friend::class);
        $friends = $friendRepository->getFriends($member, $page, $itemsPerPage);

        return $this->render('friend/friends.html.twig', [
            'member' => $member,
            'friends' => $friends,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    private function findFriendshipBetween(Member $loggedInMember, Member $member): ?Friend
    {
        return $relationRepository->findFriendshipBetween($loggedInMember, $member);
    }
}
