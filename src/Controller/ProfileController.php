<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\GalleryImage;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\ProfileVisit;
use App\Entity\Relation;
use App\Form\AboutMeFormType;
use App\Form\AccommodationFormType;
use App\Form\AddLanguageFormType;
use App\Form\DeleteProfileFormType;
use App\Form\LanguageLevelsFormType;
use App\Form\MyInterestsFormType;
use App\Form\ProfileStatusFormType;
use App\Form\SetLocationType;
use App\Form\TravelExperiencesFormType;
use App\Model\ProfileModel;
use App\Repository\CommentRepository;
use App\Repository\GalleryImageRepository;
use App\Repository\ProfileVisitRepository;
use App\Repository\RelationRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
    use TranslatedFlashTrait;

    public function __construct(
        private readonly ChangeProfilePictureGlobals $globals,
        private readonly ProfileSubmenu $profileSubmenu,
        private readonly ProfileModel $profileModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/members/{username:member}/{language}', name: 'members_profile')]
    public function show(Member $member, ?string $language = null): Response
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

        return $this->renderProfile($member, $loggedInMember, $language, false);
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
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'visitors']),
        ]);
    }

    #[Route(path: '/setlocation', name: 'profile_set_location_redirect')]
    public function redirectToSetLocation(): RedirectResponse
    {
        return $this->redirectToRoute('profile_set_location', ['username' => $this->getUser()->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/location', name: 'profile_set_location', priority: 11)]
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

        $address = $member->getActiveAddress();

        $setLocationForm = $this->createForm(SetLocationType::class, [
            'fullname' => $address->getLocation()->getFullname(),
            'name' => $address->getLocation()->getName(),
            'geoname_id' => $address->getLocation()->getGeonameId(),
            'latitude' => $address->getLatitude(),
            'longitude' => $address->getLongitude(),
        ]);
        $setLocationForm->handleRequest($request);

        if ($setLocationForm->isSubmitted() && $setLocationForm->isValid()) {
            $data = $setLocationForm->getData();
            $locationRepository = $entityManager->getRepository(Location::class);

            /** @var Location $location */
            $location = $locationRepository->find($data['geoname_id']);
            if (null !== $location) {
                $address->setLocation($location);
                $address->setLatitude($data['latitude']);
                $address->setLongitude($data['longitude']);

                $entityManager->persist($address);
                $entityManager->flush();

                return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
            }
            // Some data was wrong (attack?)
        }

        return $this->render('profile/set.location.html.twig', [
            'member' => $member,
            'form' => $setLocationForm->createView(),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member),
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
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'profile']),
        ]);
    }

    #[Route(path: '/members/{username:member}/edit/{language}', name: 'profile_edit', priority: 1)]
    public function enableEditModeForProfile(
        Member $member,
        ?string $language = null,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('profile_edit', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->renderProfile($member, $loggedInMember, $language, true);
    }

    #[Route(path: '/members/{username:member}/delete/language/{language}', name: 'profile_delete_language')]
    public function deleteLanguage(Member $member, string $language): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('profile_edit', ['username' => $loggedInMember->getUsername()]);
        }

        if ('en' === $language) {
            $this->addTranslatedFlash('notice', 'profile.delete.english');
        } else {
            $this->profileModel->deleteProfileLanguage($member, $language);
            $this->addTranslatedFlash(
                'notice',
                'language.deleted.flash',
                [
                    'language' => $this->translator->trans(strtolower('lang_' . $language), locale: $language),
                ]
            );
        }

        return $this->redirectToRoute('profile_edit', [
            'username' => $loggedInMember->getUsername(),
        ]);
    }

    #[Route(path: '/members/{username:member}/add/language', name: 'profile_add_language')]
    public function addLanguage(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('profile_edit', ['username' => $loggedInMember->getUsername()]);
        }

        $addLanguageForm = $this->createForm(AddLanguageFormType::class);
        $addLanguageForm->handleRequest($request);

        if ($addLanguageForm->isSubmitted() && $addLanguageForm->isValid()) {
            $language = $addLanguageForm->getData()['language'];
            $this->profileModel->addProfileLanguage($member, $language);

            return $this->redirectToRoute('profile_edit', [
                'username' => $loggedInMember->getUsername(),
                'language' => $language->getShortCode(),
            ]);
        }

        return $this->render('profile/add.language.html.twig', [
            'form' => $addLanguageForm,
            'member' => $member,
            'status_form' => $this->profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    #[Route(path: '/members/{username:member}/edit/{language}/languages', name: 'profile_edit_languages', priority: 2)]
    public function editLanguages(
        Request $request,
        Member $member,
        string $language,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('profile_edit_languages', ['username' => $loggedInMember->getUsername()]);
        }

        $languageLevels = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($member->getLanguageLevels() as $languageLevel) {
            $languageLevels->add($languageLevel);
        }

        $languageLevelsForm = $this->createForm(LanguageLevelsFormType::class, [
            'language' => $language,
            'language_levels' => $member->getLanguageLevels(),
        ]);
        $languageLevelsForm->handleRequest($request);

        if ($languageLevelsForm->isSubmitted() && $languageLevelsForm->isValid()) {
            $submittedLanguageLevels = new ArrayCollection($languageLevelsForm->getData()['language_levels']);
            $duplicates = $this->profileModel->checkForDuplicates($member, $submittedLanguageLevels);
            $motherTongue = $this->profileModel->checkForMotherTongue($submittedLanguageLevels);
            if (!$duplicates && $motherTongue) {
                foreach ($languageLevels as $languageLevel) {
                    if (false === $submittedLanguageLevels->contains($languageLevel)) {
                        $member->removeLanguageLevel($languageLevel);
                        $this->entityManager->remove($languageLevel);
                    }
                }

                foreach ($submittedLanguageLevels as $submittedLanguageLevel) {
                    $member->addLanguageLevel($submittedLanguageLevel);
                }

                $this->entityManager->persist($member);
                $this->entityManager->flush();

                $added = $this->profileModel->addProfileLanguagesForExpertOrBetter($member, $submittedLanguageLevels);
                if ($added > 0) {
                    $this->addTranslatedFlash('notice', 'profile.added.new.languages');
                }

                return $this->redirectToRoute('profile_edit', [
                    'username' => $member->getUsername(),
                    'language' => $languageLevelsForm->getData()['language'],
                ]);
            }

            if ($duplicates) {
                $languageLevelsForm->addError(new FormError('languages.error.duplicates'));
            }

            if (!$motherTongue) {
                $languageLevelsForm->addError(new FormError('languages.error.no.mother.tongue'));
            }
        }

        return $this->render('profile/edit/languages.html.twig', [
            'form' => $languageLevelsForm,
            'member' => $member,
            'status_form' => $this->profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    #[Route(path: '/members/{username:member}/edit/{language}/{section}', name: 'profile_edit_section')]
    public function editProfileInLanguage(Request $request, Member $member, string $language, string $section): Response
    {
        $section = strtolower($section);

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        $template = $this->getSectionTemplate($section);
        if (null === $template) {
            $section = 'AboutMe';
            $template = $this->getSectionTemplate($section);
        }

        $form = $this->getSectionForm($section, $member, $language);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $this->profileModel->handleProfileEdit($section, $member, $data);
            if (empty($errors)) {
                return $this->redirectToRoute('profile_edit', [
                    'username' => $member->getUsername(),
                    'language' => $language,
                ]);
            }
        }

        return $this->render($template, [
            'form' => $form,
            'member' => $member,
            'language' => $language,
            'status_form' => $this->profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    private function renderProfile(Member $member, Member $loggedInMember, ?string $language, bool $editMode): Response
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

        $template = $editMode ? 'profile/edit.html.twig' : 'profile/show.html.twig';

        return $this->render($template, [
            'member' => $member,
            'comments' => $comments,
            'visibleComments' => $visibleComments,
            'relations' => $relations,
            'pictures' => $pictures,
            'language' => $language,
            'status_form' => $this->profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    private function getSectionForm(?string $section, Member $member, string $language): ?FormInterface
    {
        $translations = $member->getTranslations();

        return match ($section) {
            'aboutme' => $this->createForm(AboutMeFormType::class, [
                'about_me' => $translations[$language]['AboutMe'] ?? '',
                'occupation' => $translations[$language]['Occupation'] ?? '',
                'offer_hosts' => $translations[$language]['OfferHosts'] ?? '',
                'language' => $language,
            ]),
            'interests' => $this->createForm(MyInterestsFormType::class, [
                'hobbies' => $translations[$language]['Hobbies'] ?? '',
                'books' => $translations[$language]['Books'] ?? '',
                'music' => $translations[$language]['Music'] ?? '',
                'movies' => $translations[$language]['Movies'] ?? '',
                'organizations' => $translations[$language]['Organizations'] ?? '',
                'language' => $language,
            ]),
            'travels' => $this->createForm(TravelExperiencesFormType::class, [
                'past' => $translations[$language]['PastTrips'] ?? '',
                'planned' => $translations[$language]['PlannedTrips'] ?? '',
                'language' => $language,
            ]),
            'accommodation' => $this->createForm(AccommodationFormType::class, [
                'max_guests' => $member->getMaxGuests(),
                'length_of_stay' => $translations[$language]['MaxLengthOfStay'] ?? '',
                'i_live_with' => $translations[$language]['ILiveWith'] ?? '',
                'please_bring' => $translations[$language]['PleaseBring'] ?? '',
                'where_you_sleep' => $translations[$language]['WhereYouSleep'] ?? '',
                'offers' => $translations[$language]['OfferGuests'] ?? '',
                'additional_info' => $translations[$language]['AdditionalInfo'] ?? '',
                'transport' => $translations[$language]['PublicTransport'] ?? '',
                'house_rules' => $translations[$language]['Restrictions'] ?? '',
                'language' => $language,
            ]),
            'languages' => $this->createForm(LanguageLevelsFormType::class, [
            ]),
            default => null,
        };
    }

    private function getSectionTemplate(?string $section): ?string
    {
        $template = match ($section) {
            'aboutme' => 'about_me',
            'interests' => 'my_interests',
            'travels' => 'travel_experiences',
            default => $section,
        };

        return 'profile/edit/' . $template . '.html.twig';
    }
}
