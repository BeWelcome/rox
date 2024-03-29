<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Member;
use App\Entity\NewLocation;
use App\Entity\Preference;
use App\Entity\ProfileVisit;
use App\Form\ProfileStatusFormType;
use App\Form\SearchLocationType;
use App\Form\SetLocationType;
use App\Repository\ProfileVisitRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private ProfileSubmenu $profileSubmenu;
    private ChangeProfilePictureGlobals $globals;

    public function __construct(ChangeProfilePictureGlobals $globals, ProfileSubmenu $profileSubmenu)
    {
        $this->globals = $globals;
        $this->profileSubmenu = $profileSubmenu;
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
        ProfileSubmenu $profileSubmenu,
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
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'visitors']),
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
        ProfileSubmenu $profileSubmenu,
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

    private function renderProfile(bool $ownProfile, Member $member, Member $loggedInMember): Response
    {
        return $this->render('profile/show.html.twig', [
            'member' => $member,
            'own' => $ownProfile,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $loggedInMember),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $loggedInMember),
        ]);
    }
}
