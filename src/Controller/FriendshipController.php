<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use App\Form\AddFriendType;
use App\Repository\FriendRepository;
use App\Service\Mailer;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route(path: '/members/{username:member}/friendship/add', name: 'add_friendship')]
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

        $friend = $this->findFriendshipBetween($loggedInMember, $member);
        if (null !== $friend) {
            return $this->redirectToRoute('edit_relation', ['username' => $member->getUsername()]);
        }

        $addFriendForm = $this->createForm(AddFriendType::class);
        $addFriendForm->handleRequest($request);

        if ($addFriendForm->isSubmitted() && $addFriendForm->isValid()) {
            var_dump($addFriendForm->getClickedButton());
            exit;
            if ('yes' === $addFriendForm->getClickedButton()) {
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
            'form' => $addFriendForm,
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'add_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/friendship/edit', name: 'edit_friendship')]
    public function edit(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        $friend = $this->findFriendshipBetween($loggedInMember, $member);
        if (null === $friend) {
            return $this->redirectToRoute('add_relation', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(AddFriendType::class, $friend);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $friend = $form->getData();
            $friend->setUpdated(new DateTime());

            $this->entityManager->merge($friend);
            $this->entityManager->flush();

            return $this->redirectToRoute('relations', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('friend/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'edit_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/friendship/delete', name: 'delete_friendship')]
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
            return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.removed');

        return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/friendship/confirm', name: 'confirm_friendship')]
    public function confirm(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);

        $friendship = $relationRepository->findUnconfirmedRelationBetween($member, $loggedInMember);
        if (null === $friendship) {
            return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
        }

        $friendship->setConfirmed('Yes');
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.confirmed');

        return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/friendship/dismiss', name: 'dismiss_friendship')]
    public function dismiss(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);
        $friendship = $relationRepository->findUnconfirmedRelationBetween($member, $loggedInMember);
        if (null === $friendship) {
            return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.friendship.dismissed');

        return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/friends/{page}', name: 'friends')]
    public function friends(Member $member, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();

        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);
        $friends = $relationRepository->getRelations($member, $page, $this->getItemsPerPage($member));

        return $this->render('friend/friends.html.twig', [
            'member' => $member,
            'friends' => $friends,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    private function findFriendshipBetween(Member $loggedInMember, Member $member): ?Friend
    {
        /** @var FriendRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Friend::class);

        return $relationRepository->findFriendshipBetween($loggedInMember, $member);
    }
}
