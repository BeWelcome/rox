<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Comment;
use App\Entity\GalleryImage;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\ProfileVisit;
use App\Entity\Relation;
use App\Form\DeleteProfileFormType;
use App\Form\ProfileStatusFormType;
use App\Form\SetLocationType;
use App\Model\ProfileModel;
use App\Repository\CommentRepository;
use App\Repository\GalleryImageRepository;
use App\Repository\ProfileVisitRepository;
use App\Repository\RelationRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 *
 * \todo Split into more focused controllers (e.g. one for viewing one for actions on profile)
 */
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ChangeProfilePictureGlobals $globals,
        private readonly ProfileSubmenu $profileSubmenu,
        private readonly ProfileModel $profileModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/members/{username:member}', name: 'members_profile')]
    public function show(Member $member): Response
    {
        if (
            !($member->isBrowsable()
                || $this->isGranted(Member::ROLE_ADMIN_ADMIN, $member)
                || $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM, $member)
                || $this->isGranted(Member::ROLE_ADMIN_PROFILE, $member)
            )
        ) {
            throw $this->createAccessDeniedException();
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        return $this->renderProfile($member, $loggedInMember);
    }

    #[Route(path: '/members/status/set', name: 'profile_set_status', methods: ['POST'])]
    public function setMemberStatus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $statusForm = $this->createForm(ProfileStatusFormType::class);
        $statusForm->handleRequest($request);

        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            $data = $statusForm->getData();
            $memberId = $data['member'];
            $status = $data['status'];
            $memberRepository = $entityManager->getRepository(Member::class);
            $member = $memberRepository->find($memberId);
            if (null !== $member) {
                $member->setStatus($status);
                $entityManager->persist($member);
                $entityManager->flush();
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    #[Route(path: '/members/{username:member}/visitors/{page}', name: 'profile_visitors')]
    public function showMyVisitors(
        Member $member,
        EntityManagerInterface $entityManager,
        int $page = 1,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($loggedInMember !== $member) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getusername()]);
        }

        $preferenceRepository = $entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_PROFILE_VISITORS]);
        $memberPreference = $loggedInMember->getMemberPreference($preference);

        if ('No' === $memberPreference->getValue()) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        /** @var ProfileVisitRepository $visitorRepository */
        $visitorRepository = $entityManager->getRepository(ProfileVisit::class);
        $visits = $visitorRepository->getProfileVisitorsMember($member, $page);

        return $this->render('profile/visits.html.twig', [
            'member' => $member,
            'visits' => $visits,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'visitors']),
        ]);
    }

    #[Route(path: '/setlocation', name: 'profile_set_location_redirect')]
    public function redirectToSetLocation(): RedirectResponse
    {
        return $this->redirectToRoute('profile_set_location', ['username' => $this->getUser()->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/location', name: 'profile_set_location')]
    public function setLocation(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($loggedInMember !== $member) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getusername()]);
        }

        $setLocationForm = $this->createForm(SetLocationType::class, [
            'fullname' => $member->getCity()->getFullname(),
            'name' => $member->getCity()->getName(),
            'geoname_id' => $member->getCity()->getGeonameId(),
            'latitude' => $member->getLatitude(),
            'longitude' => $member->getLongitude(),
        ]);
        $setLocationForm->handleRequest($request);

        if ($setLocationForm->isSubmitted() && $setLocationForm->isValid()) {
            $data = $setLocationForm->getData();
            $locationRepository = $entityManager->getRepository(Location::class);
            /** @var Location $location */
            $location = $locationRepository->find($data['geoname_id']);
            if (null !== $location) {
                $member->setCity($location);
                $member->setLatitude($data['latitude']);
                $member->setLongitude($data['longitude']);

                $addressRepository = $entityManager->getRepository(Address::class);
                /** @var Address $address */
                $address = $addressRepository->findOneBy(['member' => $member, 'rank' => '0']);
                $address->setLocation($location);

                $entityManager->persist($address);
                $entityManager->persist($member);
                $entityManager->flush();

                return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
            }
            // Some data was wrong (attack?)
        }

        return $this->render('profile/set.location.html.twig', [
            'member' => $member,
            'form' => $setLocationForm->createView(),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember),
        ]);
    }

    #[Route(path: '/deleteprofile', name: 'profile_delete_redirect')]
    public function deleteProfileNotLoggedIn(
        Request $request,
        TranslatorInterface $translator,
        PasswordHasherFactoryInterface $passwordHasherFactory,
    ): Response {
        /** @var Member $member */
        $member = $this->getUser();

        if (null !== $member) {
            return $this->redirectToRoute('profile_delete', ['username' => $member->getUsername()]);
        }

        $deleteProfileForm = $this->createForm(DeleteProfileFormType::class, null, [
            'loggedIn' => false,
        ]);
        $deleteProfileForm->handleRequest($request);

        if ($deleteProfileForm->isSubmitted() && $deleteProfileForm->isValid()) {
            $data = $deleteProfileForm->getData();
            $memberRepository = $this->entityManager->getRepository(Member::class);
            $member = $memberRepository->findOneBy(['username' => $data['username']]);

            $verified = false;
            if (null === $member || !$member->isBrowsable()) {
                $deleteProfileForm->addError(new FormError($translator->trans('profile.delete.credentials')));
            } else {
                $passwordHasher = $passwordHasherFactory->getPasswordHasher($member);
                $verified = $passwordHasher->verify($member->getPassword(), $data['password']);

                if (!$verified) {
                    $deleteProfileForm->addError(new FormError($translator->trans('profile.delete.credentials')));
                }
            }

            $success = false;
            if ($verified) {
                $success = $this->profileModel->retireProfile($member, $data);
            }

            if ($success) {
                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('profile/delete.not.logged.in.html.twig', [
            'form' => $deleteProfileForm->createView(),
        ]);
    }

    #[Route(path: '/members/{username:member}/delete', name: 'profile_delete')]
    public function deleteProfile(
        Request $request,
        TokenStorageInterface $tokenStorage,
        Member $member,
    ): Response {
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $deleteProfileForm = $this->createForm(DeleteProfileFormType::class, null, [
            'loggedIn' => true,
        ]);
        $deleteProfileForm->handleRequest($request);

        if ($deleteProfileForm->isSubmitted() && $deleteProfileForm->isValid()) {
            $success = $this->profileModel->retireProfile($member, $deleteProfileForm->getData());

            if ($success) {
                // force logout
                $tokenStorage->setToken(null); // Force logout
                $request->getSession()->invalidate();

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('profile/delete.html.twig', [
            'form' => $deleteProfileForm->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $member, ['active' => 'profile']),
        ]);
    }

    #[Route(path: '/members/{username:member}/edit/{language}/{section}', name: 'profile_edit')]
    public function editProfileInLanguage(
        Request $request,
        Member $member,
        ?string $language = null,
        ?string $section = null,
    ): Response {
        return $this->renderProfile($member, $member);
    }

    private function renderProfile(Member $member, Member $loggedInMember): Response
    {
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comments = $commentRepository->getLatestCommentsMember($member, 5);
        $visibleComments = $commentRepository->getVisibleCommentsForMemberCount($member);

        /** @var RelationRepository $relationsRepository */
        $relationsRepository = $this->entityManager->getRepository(Relation::class);
        $relations = $relationsRepository->findBy(['receiver' => $member, 'confirmed' => 'Yes']);

        /** @var GalleryImageRepository $galleryRepository */
        $galleryRepository = $this->entityManager->getRepository(GalleryImage::class);
        $pictures = $galleryRepository->getLatestImagesFor($member);

        return $this->render('profile/show.html.twig', [
            'member' => $member,
            'comments' => $comments,
            'visibleComments' => $visibleComments,
            'relations' => $relations,
            'pictures' => $pictures,
            'status_form' => $this->profileModel->getStatusForm($member, $loggedInMember),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember),
        ]);
    }
}
