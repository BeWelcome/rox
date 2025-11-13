<?php

namespace App\Model;

use App\Doctrine\LanguageLevelType;
use App\Doctrine\MemberStatusType;
use App\Entity\Address;
use App\Entity\Language;
use App\Entity\MemberPreference;
use App\Entity\MembersLanguagesLevel;
use App\Entity\Message;
use App\Entity\NewAddress;
use App\Entity\NewLocation;
use App\Entity\NewMember as Member;
use App\Entity\NewMemberTranslation as MemberTranslation;
use App\Entity\Preference;
use App\Entity\Subject;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Hidehalo\Nanoid\Client;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly TranslatorInterface $translator,
        private readonly Mailer $mailer,
    ) {
    }

    public function createAccount(array $signupData): Member
    {
        $locale = $signupData['locale'];
        $member = new Member();
        $member->setStatus(MemberStatusType::AWAITING_MAIL_CONFIRMATION);
        $member->setUsername($signupData['username']);
        $member->setEmail($signupData['email']);
        $member->setLocale($locale);

        $nanoClient = new Client();
        $member->setRegistrationKey($nanoClient->generateId(16, Client::MODE_DYNAMIC));

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($member);
        $hashedPassword = $passwordHasher->hash($signupData['password']);

        $member->setPassword($hashedPassword);
        $member->addTranslation(
            new MemberTranslation(
                'en',
                'profileLanguage',
                'en'
            )
        );

        if ('en' !== $locale) {
            $member->addTranslation(
                new MemberTranslation(
                    $locale,
                    'profileLanguage',
                    $locale
                )
            );
        }

        $this->entityManager->persist($member);

        $memberRepository = $this->entityManager->getRepository(Member::class);
        $admin = $memberRepository->findOneBy(['username' => 'bwadmin']);
        if (null !== $admin) {
            $firstMessage = new Message();
            $subject = new Subject();
            $subjectText = $this->translator->trans('signup.welcome.subject', ['username' => $member->getUsername()]);
            $subject->setSubject($subjectText);
            $messageText = $this->translator->trans('signup.welcome.body', ['username' => $member->getUsername()]);
            $firstMessage
                ->setSubject($subject)
                ->setMessage($messageText)
                ->setReceiver($member)
                ->setSender($admin)
            ;

            $this->entityManager->persist($subject);
            $this->entityManager->persist($firstMessage);
        }

        $this->entityManager->flush();

        $parameters = [
            'subject' => 'signup.confirm.email',
            'username' => $signupData['username'],
            'email_address' => $signupData['email'],
            'key' => $member->getRegistrationKey(),
        ];

        $this->mailer->sendSignupEmail($member, 'signup', $parameters);

        return $member;
    }

    public function updateMember(Member $member, array $data): void
    {
        $location = $this->entityManager->getRepository(NewLocation::class)->findOneBy([
            'geonameId' => $data['location']['geoname_id'],
        ]);

        $address = new NewAddress();
        $address
            ->setCity($location)
            ->setActive(true)
            ->setLatitude($data['location']['latitude'])
            ->setLongitude($data['location']['longitude']);

        $member
            ->setName($data['name'])
            ->setShortName($data['short_name'])
            ->setBirthdate($data['birthdate'])
            ->setGender($data['gender'])
            ->setAccommodation($data['accommodation'])
            ->addAddress($address)
            ->setHostingInterest($data['hosting_interest'] ?? null)
        ;

        if ($data['registration_key'] === $member->getRegistrationKey()) {
            $member->setRegistrationKey(null);
            $member->setStatus(MemberStatusType::ACTIVE);
        } else {
            $member->setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
        }

        $translations = $member->getTranslations();
        $languageRepository = $this->entityManager->getRepository(Language::class);

        foreach ($data['mother_tongue'] as $motherTongue) {
            $language = $languageRepository->findOneBy(['shortCode' => $motherTongue]);
            if (null !== $language) {
                // for each mother tongue create a profile version if the language is a written language
                if ($language->getIsWrittenlanguage()) {
                    if (!isset($translations[$motherTongue]['ProfileLanguage'])) {
                        $member->addTranslation(new MemberTranslation($motherTongue, 'ProfileLanguage', $member));
                    }
                }

                // Also add entry for language level in database
                $languageLevel = new MembersLanguagesLevel();
                $languageLevel->setLanguage($language);
                $languageLevel->setLevel(LanguageLevelType::MOTHER_TONGUE);
                $languageLevel->setMember($member);

                $this->entityManager->persist($languageLevel);
            }
        }

        $this->entityManager->persist($member);

        $address = new Address();
        $address
            ->setMember($member)
            ->setLocation($location)
            // Set next ones to 0 as the are not set to have a default NULL value in the current database
            ->setHouseNumber(0)
            ->setExplanation(0)
            ->setGettingThere(0)
            ->setStreetName(0)
            ->setRank(0)
            ->setZip(0)
        ;

        $this->entityManager->persist($address);

        $newsletterValue = $data['newsletters'] ? 'Yes' : 'No';
        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy([
            'codename' => Preference::NEWSLETTERS_VIA_EMAIL,
        ]);

        $newsletterPreference = new MemberPreference();
        $newsletterPreference
            ->setMember($member)
            ->setPreference($preference)
            ->setValue($newsletterValue)
        ;
        $this->entityManager->persist($newsletterPreference);

        $localeEventsValue = $data['local_events'] ? 'Yes' : 'No';
        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy([
            'codename' => Preference::LOCAL_EVENT_NOTIFICATIONS,
        ]);

        $localEventsPreference = new MemberPreference();
        $localEventsPreference
            ->setMember($member)
            ->setPreference($preference)
            ->setValue($localeEventsValue)
        ;
        $this->entityManager->persist($localEventsPreference);

        $this->entityManager->flush();
    }

    public function checkUsername(string $username): bool
    {
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(['username' => $username]);

        if (null !== $member) {
            return false;
        }

        return true;
    }

    public function checkEmailAddress(string $email): bool
    {
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(['email' => $email]);

        if (null !== $member) {
            return false;
        }

        return true;
    }
}
