<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\Address;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\NewLocation;
use App\Entity\Preference;
use App\Entity\Subject;
use App\Service\Mailer;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hidehalo\Nanoid\Client;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupModel
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly PasswordHasherFactoryInterface $passwordHasherFactory, private readonly TranslatorInterface $translator, private readonly Mailer $mailer)
    {
    }

    public function createAccount(array $signupData, ?string $locale): Member
    {
        $member = new Member();
        $member->setStatus(MemberStatusType::AWAITING_MAIL_CONFIRMATION);
        $member->setUsername($signupData['username']);
        $member->setEmail($signupData['email']);

        $nanoClient = new Client();
        $member->setRegistrationKey($nanoClient->generateId(16, Client::MODE_DYNAMIC));

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($member);
        $hashedPassword = $passwordHasher->hash($signupData['password']);

        $member->setPassword($hashedPassword);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        // Matches English in the odd setup of the database
        $localeId = 0;
        $english = $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => 'en']);
        $language = $english;
        if (null !== $locale) {
            $language = $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => $locale]);
            if (null !== $language) {
                $localeId = $language->getId();
            } else {
                $language = $english;
            }
        }

        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy([
            'codename' => Preference::LOCALE
        ]);

        $memberPreference = new MemberPreference();
        $memberPreference
            ->setMember($member)
            ->setPreference($preference)
            ->setValue($localeId)
        ;

        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        $member->initializePreferredLanguage($this->entityManager);

        $this->addProfileTranslations($member, $language);

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
            'geonameId' => $data['location']['geoname_id']
        ]);

        $member
            ->setCity($location)
            ->setLatitude($data['location']['latitude'])
            ->setLongitude($data['location']['longitude'])
            ->setFirstName($data['name'])
            ->setBirthdate($data['birthdate'])
            ->setGender($data['gender'])
            ->setAccommodation($data['accommodation'])
            ->setHostingInterest($data['hosting_interest'] ?? null)
            ->setShowGender(false)
            ->setShowAge(false)
        ;

        if (MemberStatusType::MAIL_CONFIRMED === $member->getStatus()) {
            $member->setStatus(MemberStatusType::ACTIVE);
        } else {
            $member->setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
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

        $this->entityManager->persist($address);

        $newsletterValue = $data['newsletters'] ? 'Yes' : 'No';
        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy([
            'codename' => Preference::NEWSLETTERS_VIA_EMAIL
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
            'codename' => Preference::LOCAL_EVENT_NOTIFICATIONS
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

    private function addProfileTranslations(Member $member, ?Language $language): void
    {
        // First create profile language for English
        $this->addProfileTranslation($member, 'en');

        if (null !== $language && 'en' !== $language->getShortCode()) {
            $this->addProfileTranslation($member, $language->getShortCode());
        }

        $this->entityManager->flush();
    }

    private function addProfileTranslation(Member $member, string $locale): void
    {
        $languageRepository = $this->entityManager->getRepository(Language::class);
        $language = $languageRepository->findOneBy(['shortCode' => $locale]);

        if (null !== $language) {
            $profileTranslation = new MemberTranslation();
            $profileTranslation
                ->setTranslator($member)
                ->setOwner($member)
                ->setTableColumn('members.language')
                ->setSentence($language->getName())
                ->setLanguage($language)
            ;

            $this->entityManager->persist($profileTranslation);
            $this->entityManager->flush();

            $profileTranslation->setTranslation($profileTranslation->getId());
            $this->entityManager->persist($profileTranslation);
        }
    }
}
