<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\ProfileNote;
use App\Entity\Relation;
use App\Form\ProfileNoteType;
use App\Repository\ProfileNoteRepository;
use App\Repository\RelationRepository;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RelationController extends AbstractController
{
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

        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);

        $note = $noteRepository->getNoteForMemberPair($loggedInMember, $member);
        if (null !== $note) {
            return $this->redirectToRoute('edit_note', ['username' => $member->getUsername()]);
        }

        $categories = $noteRepository->getCategories($loggedInMember);

        $form = $this->createForm(ProfileNoteType::class, $note, [
            'categories' => $categories,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();
            $note->setOwner($loggedInMember);
            $note->setMember($member);
            $this->entityManager->persist($note);
            $this->entityManager->flush();

            return $this->redirectToRoute('notes', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('note/add.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'add_note']),
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

        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);

        $note = $noteRepository->getNoteForMemberPair($loggedInMember, $member);
        if (null === $note) {
            return $this->redirectToRoute('add_note', ['username' => $member->getUsername()]);
        }

        $categories = $noteRepository->getCategories($loggedInMember);

        $form = $this->createForm(ProfileNoteType::class, $note, [
            'categories' => $categories,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();
            $this->entityManager->persist($note);
            $this->entityManager->flush();

            return $this->redirectToRoute('notes', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('note/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'edit_note']),
        ]);
    }

    /**
     * @Route("/members/{username}/relation/remove", name="remove_relation")
     */
    public function remove(): Response
    {
        return $this->render('note/index.html.twig', [
            'controller_name' => 'NoteController',
        ]);
    }

    /**
     * @Route("/members/{username}/relations", name="relations")
     */
    public function relations(Member $member, ProfileSubmenu $profileSubmenu): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();
        if ($member !== $loggedInMember) {
            throw new AccessDeniedException();
        }

        /** @var RelationRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(Relation::class);
        $notes = $noteRepository->getRelations($member);

        return $this->render('relation/relations.html.twig', [
            'member' => $member,
            'notes' => $notes,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'relations']),
        ]);
    }
}
