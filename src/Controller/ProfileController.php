<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Member;
use App\Entity\NewLocation;
use App\Entity\Preference;
use App\Entity\ProfileVisit;
use App\Form\DeleteProfileFormType;
use App\Form\ProfileStatusFormType;
use App\Form\SearchLocationType;
use App\Form\SetLocationType;
use App\Model\ProfileModel;
use App\Repository\ProfileVisitRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private ProfileSubmenu $profileSubmenu;
    private ChangeProfilePictureGlobals $globals;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ChangeProfilePictureGlobals $globals,
        ProfileSubmenu $profileSubmenu,
        EntityManagerInterface $entityManager
    ) {
        $this->globals = $globals;
        $this->profileSubmenu = $profileSubmenu;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/members/{username}/new", name="members_profile_new")
     *
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    public function show(Member $member): Response
    {
        if (!$member->isBrowsable()) {
            throw new AccessDeniedException();
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($loggedInMember === $member) {
            return $this->showOwnProfile($member);
        }

        return $this->renderProfile(false, $member, $loggedInMember);
    }

    public function showOwnProfile(Member $member): Response
    {
        return $this->renderProfile(true, $member, $member);
    }

    /**
     * @Route("/members/status/set", name="profile_set_status",
     *     methods={"POST"}
     * )
     */
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

    /**
     * @Route("/members/{username}/visitors/{page}", name="profile_visitors")
     */
    public function showMyVisitors(
        Member $member,
        EntityManagerInterface $entityManager,
        int $page = 1
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

    /**
     * @Route("/setlocation", name="profile_set_location_redirect")
     */
    public function redirectToSetLocation(): RedirectResponse
    {
        return $this->redirectToRoute('profile_set_location', ['username' => $this->getUser()->getUsername()]);
    }

    /**
     * @Route("/members/{username}/location", name="profile_set_location")
     */
    public function setLocation(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager
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
            $locationRepository = $entityManager->getRepository(NewLocation::class);
            /** @var NewLocation $location */
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

    /**
     * @Route("/deleteprofile", name="profile_delete_redirect")
     */
    public function deleteProfileNotLoggedIn(
        Request $request,
        ProfileModel $profileModel,
        TranslatorInterface $translator,
        PasswordHasherFactoryInterface $passwordHasherFactory
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
            if (null === $member) {
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
                $success = $profileModel->retireProfile($member, $data);
            }

            if ($success) {
                return $this->redirectToRoute('security_logout');
            }
        }

        return $this->render('profile/delete.not.logged.in.html.twig', [
            'form' => $deleteProfileForm->createView()
        ]);
    }

    /**
     * @Route("/members/{username}/delete", name="profile_delete")
     */
    public function deleteProfile(Request $request, Member $member, ProfileModel $profileModel): Response
    {
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $deleteProfileForm = $this->createForm(DeleteProfileFormType::class, null, [
            'loggedIn' => true,
        ]);
        $deleteProfileForm->handleRequest($request);

        if ($deleteProfileForm->isSubmitted() && $deleteProfileForm->isValid()) {
            $success = $profileModel->retireProfile($member, $deleteProfileForm->getData());

            if ($success) {
                return $this->redirectToRoute('security_logout');
            }
        }

        return $this->render('profile/delete.html.twig', [
            'form' => $deleteProfileForm->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $member, ['active' => 'profile']),
        ]);
    }

    private function renderProfile(bool $ownProfile, Member $member, Member $loggedInMember): Response
    {
        return $this->render('profile/show.html.twig', [
            'member' => $member,
            'own' => $ownProfile,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember),
        ]);
    }

    private function deleteProfileProcess(Request $request, bool $loggedIn): Response
    {
        $deleteProfileForm = $this->createForm(DeleteProfileFormType::class, null, [
            'loggedIn' => $loggedIn,
        ]);
        $deleteProfileForm->handleRequest($request);

        if ($deleteProfileForm->isSubmitted() && $deleteProfileForm->isValid()) {
            $data = $deleteProfileForm->getData();
            if (false === $loggedIn) {
                // Check credentials
            }

            // handle delete profile form.

            return $this->redirectToRoute('logout');
        }

        return $this->render('profile/delete.html.twig', [
            'form' => $deleteProfileForm->createView(),
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $profileSubmenu->getSubmenu($member, $member, ['active' => 'profile']),
        ]);

    }
}
