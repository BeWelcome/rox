<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\ProfileNote;
use App\Entity\Relation;
use App\Form\ProfileNoteType;
use App\Form\RelationType;
use App\Repository\ProfileNoteRepository;
use App\Repository\RelationRepository;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RelationController extends AbstractController
{
    use ItemsPerPageTraits;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/members/{username}/relation/add", name="add_relation")
     */
    public function add(Request $request, Member $member, ProfileSubmenu $profileSubmenu): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        /** @var RelationRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(Relation::class);

        $relation = $noteRepository->findRelationBetween($loggedInMember, $member);
        if (null !== $relation) {
            return $this->redirectToRoute('edit_relation', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(RelationType::class, $relation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();
            $relation->setOwner($loggedInMember);
            $relation->setMember($member);
            $this->entityManager->persist($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('relations', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('relation/add.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'add_relation']),
        ]);
    }

    /**
     * @Route("/members/{username}/relation/edit", name="edit_relation")
     */
    public function edit(Request $request, Member $member, ProfileSubmenu $profileSubmenu): Response
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
            return $this->redirectToRoute('add_relation', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(RelationType::class, $relation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();

            $this->entityManager->persist($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('relations', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('relation/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'edit_relation']),
        ]);
    }

    /**
     * @Route("/members/{username}/relation/remove", name="remove_relation")
     */
    public function remove(): Response
    {
        return $this->render('relation/index.html.twig', [
        ]);
    }

    /**
     * @Route("/members/{username}/relations/{page}", name="relations")
     */
    public function relations(Member $member, ProfileSubmenu $profileSubmenu, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);
        $relations = $relationRepository->getRelations($member, $page, $this->getItemsPerPage($member));

        return $this->render('relation/relations.html.twig', [
            'member' => $member,
            'relations' => $relations,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'relations']),
        ]);
    }
}
