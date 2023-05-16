<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\ProfileNote;
use App\Form\ProfileNoteFilterType;
use App\Form\ProfileNoteType;
use App\Repository\ProfileNoteRepository;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NoteController extends AbstractController
{
    use ItemsPerPageTraits;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/members/{username}/note/add", name="add_note")
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
     * @Route("/members/{username}/note/edit", name="edit_note")
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
            $note->setUpdated(new DateTime());
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
     * @Route("/members/{username}/note/delete", name="delete_note")
     */
    public function delete(Member $member): Response
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
            return $this->redirectToRoute('notes', ['username' => $member->getUsername()]);
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();

        return $this->redirectToRoute('notes', ['username' => $loggedInMember->getUsername()]);
    }

    /**
     * @Route("/members/{username}/notes/{page}", name="notes")
     */
    public function notes(Request $request, Member $member, ProfileSubmenu $profileSubmenu, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();
        if ($member !== $loggedInMember) {
            throw new AccessDeniedException();
        }

        $order = ProfileNoteFilterType::ORDER_UPDATED;
        $categories = [];
        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);
        $selectableCategories = $noteRepository->getCategories($loggedInMember);

        $filterForm = $this->createForm(
            ProfileNoteFilterType::class,
            [
                'choices' => $categories,
                'order' => $order
            ],
            [
                'categories' => $selectableCategories,
            ]
        );
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $categories = $data['categories'];
            $order = (int) $data['order'];
        }
        $notes = $noteRepository->getProfileNotes($member, $categories, $order, $page, $this->getItemsPerPage($member));

        return $this->render('note/notes.html.twig', [
            'member' => $member,
            'form' => $filterForm->createView(),
            'notes' => $notes,
            'filtered' => !empty($categories),
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'notes']),
        ]);
    }
}
