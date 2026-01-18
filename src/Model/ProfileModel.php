<?php

namespace App\Model;

use App\Doctrine\LanguageLevelType;
use App\Doctrine\MemberStatusType;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberLanguageLevel;
use App\Entity\MemberTranslation;
use App\Form\ProfileStatusFormType;
use App\Service\Mailer;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Hidehalo\Nanoid\Client;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

readonly class ProfileModel
{
    private EntityRepository $memberTranslationRepository;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private Mailer $mailer,
    ) {
        $this->memberTranslationRepository = $this->entityManager->getRepository(MemberTranslation::class);
    }

    public function getStatusForm(Member $loggedInMember, Member $member): ?FormInterface
    {
        $statusForm = null;
        $admin = $this->security->isGranted(Member::ROLE_ADMIN_ADMIN, $loggedInMember)
            || $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM, $loggedInMember)
            || $this->security->isGranted(Member::ROLE_ADMIN_PROFILE, $loggedInMember);

        if ($admin) {
            $statusFormBuilder = $this->formFactory->createBuilder(ProfileStatusFormType::class, [
                'status' => $member->getStatus(),
                'member' => $member->getId(),
            ]);
            $statusForm = $statusFormBuilder->getForm();
        }

        return $statusForm;
    }

    public function retireProfile(Member $member, array $data): bool
    {
        $feedback = $data['feedback'];
        if (!empty($feedback)) {
            $this->mailer->sendProfileDeletionFeedback($member, $feedback);
        }

        $member->setStatus(MemberStatusType::ASKED_TO_LEAVE);

        $dataRetention = $data['data_retention'] ?? false;
        if ($dataRetention) {
            $retentionDate = new Carbon()->subYears(1);
            $member->setLastActive($retentionDate);
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return true;
    }

    public function checkForDuplicates(Member $member, ArrayCollection $submittedLanguageLevels): bool
    {
        $duplicates = false;
        $languages = [];
        foreach ($submittedLanguageLevels as $languageLevel) {
            $isoCode = $languageLevel->getLanguage()->getShortCode();
            if (!isset($languages[$isoCode])) {
                $languages[$isoCode] = true;
            } else {
                $duplicates = true;
            }
        }

        return $duplicates;
    }

    public function checkForMotherTongue(ArrayCollection $submittedLanguageLevels): bool
    {
        $motherTongue = false;
        foreach ($submittedLanguageLevels as $languageLevel) {
            $level = $languageLevel->getLevel();
            if (LanguageLevelType::MOTHER_TONGUE === $level) {
                $motherTongue = true;
            }
        }

        return $motherTongue;
    }

    public function addProfileLanguagesForExpertOrBetter(Member $member, ArrayCollection $submittedLanguageLevels): int
    {
        $count = 0;
        $translations = $member->getTranslations();
        $languages = array_keys($translations);
        foreach ($submittedLanguageLevels as $languageLevel) {
            if (
                /* @var MemberLanguageLevel $languageLevel */
                $languageLevel->getLanguage()->isWrittenLanguage()
                && \in_array($languageLevel->getLevel(), [
                    LanguageLevelType::EXPERT,
                    LanguageLevelType::FLUENT,
                    LanguageLevelType::MOTHER_TONGUE,
                ], true)
            ) {
                $shortCode = $languageLevel->getLanguage()->getShortCode();
                if (!\in_array($shortCode, $languages, true)) {
                    $member->addTranslation(new MemberTranslation($shortCode, 'ProfileLanguage', $shortCode));
                    ++$count;
                }
            }
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return $count;
    }

    public function addProfileLanguage(Member $member, Language $language): void
    {
        $shortCode = $language->getShortCode();
        $translations = $member->getTranslations();
        $languages = array_keys($translations);

        if (!\in_array($shortCode, $languages, true)) {
            $member->addTranslation(new MemberTranslation($shortCode, 'ProfileLanguage', $shortCode));
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    public function deleteProfileLanguage(Member $member, string $language): void
    {
        $translations = $member->getRawTranslations();
        foreach ($translations as $translation) {
            if ($translation->getLocale() === $language) {
                $translations->removeElement($translation);
                $this->entityManager->remove($translation);
            }
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    public function handleProfileEdit(?string $section, Member $member, array $data): array
    {
        $errors = [];
        switch ($section) {
            case 'aboutme':
                $errors = $this->handleAboutMe($member, $data);
                break;
            case 'interests':
                $errors = $this->handleMyInterests($member, $data);
                break;
            case 'travels':
                $errors = $this->handleTravelExperiences($member, $data);
                break;
            case 'accommodation':
                $errors = $this->handleAccommodation($member, $data);
                break;
        }

        if (empty($errors)) {
            $member->setUpdated(new DateTime());

            $this->entityManager->persist($member);
            $this->entityManager->flush();
        }

        return $errors;
    }

    public function handleField(Member $member, string $language, string $field, ?string $fieldContent): void
    {
        $fieldTranslation = $this->memberTranslationRepository->findOneBy([
            'object' => $member,
            'locale' => $language,
            'field' => $field,
        ]);

        if (empty($fieldContent)) {
            if (null !== $fieldTranslation) {
                $member->getRawTranslations()->removeElement($fieldTranslation);
                $this->entityManager->remove($fieldTranslation);
            }
        } else {
            if (null === $fieldTranslation) {
                $fieldTranslation = new MemberTranslation($language, $field, $fieldContent);
            }
            $fieldTranslation->setContent($fieldContent);

            $member->addTranslation($fieldTranslation);
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    private function handleAboutMe(Member $member, array $data): array
    {
        $language = $data['language'];

        $this->handleField($member, $language, 'AboutMe', $data['about_me']);
        $this->handleField($member, $language, 'Occupation', $data['occupation']);
        $this->handleField($member, $language, 'OfferHosts', $data['offer_hosts']);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return [];
    }

    private function handleMyInterests(Member $member, array $data): array
    {
        $language = $data['language'];

        $this->handleField($member, $language, 'Hobbies', $data['hobbies']);
        $this->handleField($member, $language, 'Books', $data['books']);
        $this->handleField($member, $language, 'Music', $data['music']);
        $this->handleField($member, $language, 'Movies', $data['movies']);
        $this->handleField($member, $language, 'Organizations', $data['organizations']);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return [];
    }

    private function handleTravelExperiences(Member $member, array $data): array
    {
        $language = $data['language'];

        $this->handleField($member, $language, 'PastTrips', $data['past']);
        $this->handleField($member, $language, 'PlannedTrips', $data['planned']);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return [];
    }

    private function handleAccommodation(Member $member, array $data): array
    {
        $language = $data['language'];

        $member->setAccommodation($data['accommodation']);
        $member->setMaxGuests($data['max_guests']);
        if ('yes' === $data['accommodation']) {
            $member->setHostingInterest($data['hosting_interest']);
        }
        $member->setStandardOffers($data['offers']);
        $member->setRestrictions($data['restrictions']);

        $address = $member->getActiveAddress();
        if (null !== $address) {
            $address->setIsWheelChairAccessible($data['wheelchair_accessible'] ?? false);

            $this->entityManager->persist($address);
        }

        $this->handleField($member, $language, 'MaxLengthOfStay', $data['length_of_stay']);
        $this->handleField($member, $language, 'ILiveWith', $data['i_live_with']);
        $this->handleField($member, $language, 'PleaseBring', $data['please_bring']);
        $this->handleField($member, $language, 'OfferGuests', $data['offer_guests']);
        $this->handleField($member, $language, 'WhereYouSleep', $data['where_you_sleep']);
        $this->handleField($member, $language, 'AdditionalInfo', $data['additional_info']);
        $this->handleField($member, $language, 'HouseRules', $data['house_rules']);
        $this->handleField($member, $language, 'GettingThere', $data['getting_there']);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return [];
    }

    public function sendEmailConfirmationEmail(Member $member, string $email): void
    {
        $nanoClient = new Client();
        $member->setRegistrationKey($nanoClient->generateId(16, Client::MODE_DYNAMIC));

        $parameters = [
            'subject' => 'signup.confirm.email',
            'username' => $member->getUsername(),
            'email_address' => $email,
            'key' => $member->getRegistrationKey(),
        ];

        $this->mailer->sendSignupEmail($member, 'newemail', $parameters);
    }
}
