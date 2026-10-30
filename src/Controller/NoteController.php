<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\ProfileNote;
use App\Form\ProfileNoteFilterType;
use App\Form\ProfileNoteType;
use App\Repository\ProfileNoteRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NoteController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private EntityManagerInterface $entityManager, private ChangeProfilePictureGlobals $globals)
    {
    }

    #[Route(path: '/members/{username:member}/note/add', name: 'add_note')]
    public function add(Request $request, Member $member, ProfileSubmenu $profileSubmenu): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getusername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $loggedInMember->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.note.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
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

            return $this->redirectAfterSave($request, $loggedInMember, $member);
        }

        return $this->render('note/add.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'add_note']),
        ]);
    }

    /**
     * My note about the member, in full (side panel of the profile, ?embed=1), with a button
     * to edit it. Without the panel, the profile (where the note is shown).
     */
    #[Route(path: '/members/{username:member}/note', name: 'view_note', priority: 11)]
    public function view(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);
        $note = $noteRepository->getNoteForMemberPair($loggedInMember, $member);
        if (null === $note) {
            return $this->redirectToRoute('add_note', ['username' => $member->getUsername()] + ($request->query->getBoolean('embed') ? ['embed' => 1] : []));
        }
        if (!$request->query->getBoolean('embed')) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return $this->render('note/view.html.twig', [
            'note' => $note,
            'member' => $member,
        ]);
    }

    #[Route(path: '/members/{username:member}/note/edit', name: 'edit_note')]
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

            return $this->redirectAfterSave($request, $loggedInMember, $member);
        }

        return $this->render('note/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'edit_note']),
        ]);
    }

    #[Route(path: '/members/{username:member}/note/delete', name: 'delete_note')]
    public function delete(Request $request, Member $member): Response
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

        return $this->redirectAfterSave($request, $loggedInMember, $member);
    }

    /**
     * The legacy URL of my notes.
     */
    #[Route(path: '/mynotes', name: 'members_show_all_notes')]
    public function myNotes(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->redirectToRoute('notes', ['username' => $member->getUsername()], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * My notes: one pill per category (with its count) to filter, a search on the member's
     * username or the note, then the notes as cards (edit in the side panel, delete).
     */
    #[Route(path: '/members/{username:member}/notes/{page}', name: 'notes')]
    public function notes(Request $request, Member $member, ProfileSubmenu $profileSubmenu, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getuser();
        if ($member !== $loggedInMember) {
            throw new AccessDeniedException();
        }

        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);
        $categoryCounts = $noteRepository->getCategoryCounts($loggedInMember);

        $category = (string) $request->query->get('category', '');
        if (!isset($categoryCounts[$category])) {
            $category = '';
        }
        $search = trim((string) $request->query->get('q', ''));

        $notes = $noteRepository->getProfileNotes(
            $member,
            '' === $category ? [] : [$category],
            ProfileNoteFilterType::ORDER_UPDATED,
            $page,
            $this->getItemsPerPage($member),
            $search
        );

        return $this->render('note/notes.html.twig', [
            'member' => $member,
            'notes' => $notes,
            'notes_total' => $noteRepository->getProfileNotesCount($loggedInMember),
            'category_counts' => $categoryCounts,
            'category' => $category,
            'search' => $search,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'notes']),
        ]);
    }

    /**
     * From the side panel of the profile (?embed=1): back to the profile, which shows the
     * note in its own block. From the full page: the list of notes, as before.
     */
    private function redirectAfterSave(Request $request, Member $loggedInMember, Member $member): Response
    {
        // Opened from my notes: back to the list (the panel then reloads the page)
        if ('notes' === $request->query->get('back')) {
            return $this->redirectToRoute('notes', ['username' => $loggedInMember->getUsername()]);
        }
        if ($request->query->getBoolean('embed')) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return $this->redirectToRoute('notes', ['username' => $loggedInMember->getUsername()]);
    }
}
