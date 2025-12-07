<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\Relation;
use App\Form\RelationType;
use App\Repository\RelationRepository;
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

class RelationController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private EntityManagerInterface $entityManager, private ProfileSubmenu $profileSubmenu, private ChangeProfilePictureGlobals $globals)
    {
    }

    #[Route(path: '/members/{username:nenber}/relation/add', name: 'add_relation')]
    public function add(Request $request, Member $member, Mailer $mailer): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $loggedInMember->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.relation.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        $relation = $this->findRelationBetween($loggedInMember, $member);
        if (null !== $relation) {
            return $this->redirectToRoute('edit_relation', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(RelationType::class, $relation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Relation $relation */
            $relation = $form->getData();
            if (!$this->checkForEmailAddress($relation) && !$this->checkForPhoneNumber($relation)) {
                $relation->setOwner($loggedInMember);
                $relation->setReceiver($member);

                $this->entityManager->persist($relation);
                $this->entityManager->flush();

                $mailer->sendRelationNotification($relation);

                return $this->redirectToRoute('relations', ['username' => $loggedInMember->getUsername()]);
            }
        }

        return $this->render('relation/add.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'add_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/relation/edit', name: 'edit_relation')]
    public function edit(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        $relation = $this->findRelationBetween($loggedInMember, $member);
        if (null === $relation) {
            return $this->redirectToRoute('add_relation', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(RelationType::class, $relation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();
            $relation->setUpdated(new DateTime());

            $this->entityManager->merge($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('relations', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('relation/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'edit_relation']),
        ]);
    }

    #[Route(path: '/members/{username:member}/relation/delete', name: 'delete_relation')]
    public function remove(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);

        $relation = $relationRepository->findRelationBetween($loggedInMember, $member);
        if (null === $relation) {
            return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
        }

        $entityManager->remove($relation);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.relation.removed');

        return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/relation/confirm', name: 'confirm_relation')]
    public function confirm(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);

        $relation = $relationRepository->findUnconfirmedRelationBetween($member, $loggedInMember);
        if (null === $relation) {
            return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
        }

        $relation->setConfirmed('Yes');
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.relation.confirmed');

        return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/relation/dismiss', name: 'dismiss_relation')]
    public function dismiss(Member $member, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);
        $relation = $relationRepository->findUnconfirmedRelationBetween($member, $loggedInMember);
        if (null === $relation) {
            return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
        }

        $entityManager->remove($relation);
        $entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.relation.dismissed');

        return $this->redirectToRoute('relations', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/relations/{page}', name: 'relations')]
    public function relations(Member $member, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);
        $relations = $relationRepository->getRelations($member, $page, $this->getItemsPerPage($member));

        return $this->render('relation/relations.html.twig', [
            'member' => $member,
            'relations' => $relations,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'relations']),
        ]);
    }

    private function findRelationBetween(Member $loggedInMember, Member $member): ?Relation
    {
        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);

        return $relationRepository->findRelationBetween($loggedInMember, $member);
    }

    private function checkForEmailAddress(Relation $relation): bool
    {
        $relationText = $relation->getCommentText();
        $found = preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", (string) $relationText);

        return $found > 0;
    }

    private function checkForPhoneNumber(Relation $relation): bool
    {
        $relationText = $relation->getCommentText();
        $found = preg_match("/([0-9][\. \)-]*){9,}/", (string) $relationText);

        return $found > 0;
    }
}
