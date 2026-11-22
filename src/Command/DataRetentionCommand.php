<?php

namespace App\Command;

use App\Doctrine\AccommodationType;
use App\Doctrine\DeleteRequestType;
use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Donation;
use App\Entity\ForumPostNotification;
use App\Entity\Friend;
use App\Entity\GroupMembership;
use App\Entity\HostingRequest;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\LoginMessageAcknowledged;
use App\Entity\Member;
use App\Entity\MemberActivityNotificationSent;
use App\Entity\MemberLegHidden;
use App\Entity\MemberLegNotificationSent;
use App\Entity\MemberPhoto;
use App\Entity\MemberPreference;
use App\Entity\MemberThreadSubscription;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\NewsletterRecipient;
use App\Entity\PasswordReset;
use App\Entity\ProfileNote;
use App\Entity\ProfileVisit;
use App\Entity\Trip;
use App\Logger\Logger;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Hidehalo\Nanoid\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;

#[AsCommand(
    name: 'data:retention',
    description: 'Cleans the database of retired users (should be run at least once each day)',
    aliases: ['data:cleanup'],
    hidden: false,
)]
class DataRetentionCommand extends Command
{
    /**
     * The sizes of the profile pictures, as files <id>_<size>_<size> and <id>_<size>_<size>.webp (and <id>_<size> of
     * the old site): those the
     * pages, the mails and the scripts ask for, those of the old site, and the original.
     */
    private const array AVATAR_SIZES = [
        'xs', '24', '30', '32', '36', '44', '48', '50', '64', '72', '96', '100', '120', '128', '150', '160', '192', '200',
        '320', '480', '500', '800', 'original',
    ];

    private Member $bwAdmin;
    private MemberRepository $memberRepository;

    public function __construct(
        private readonly Logger $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $dataDirectory,
        #[Autowire('%gallery_directory%')]
        private readonly string $galleryDirectory,
        #[Autowire('%member_batch_size%')]
        private readonly int $batchSize,
        private readonly LockFactory $lockFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('batch-size', InputArgument::OPTIONAL, 'How many members to process in this run');
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $this->memberRepository = $memberRepository;

        /** @var Member $bwAdmin */
        $bwAdmin = $memberRepository->find(1);
        $this->bwAdmin = $bwAdmin;

        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) ($input->getArgument('batch-size') ?? $this->batchSize);
        if ($batchSize <= 0) {
            $io->error('The batch size must be a positive number.');

            return Command::FAILURE;
        }

        $io->title('Running Data Retention');

        // One run at a time: a run finishes its batch, however long that takes (no expiry), and a run that
        // starts meanwhile (the cron starts one every 30 minutes) leaves it to the one at work.
        $lock = $this->lockFactory->createLock('data:retention', null);
        if (!$lock->acquire()) {
            $io->note('The previous run is still at work; this one ends.');

            return Command::SUCCESS;
        }
        try {
            $retired = $this->removeMembers($io, $batchSize);
        } finally {
            $lock->release();
        }

        $io->success(\sprintf('Data of %d members has been deleted.', $retired));

        return Command::SUCCESS;
    }

    private function removeMembers(SymfonyStyle $io, int $batchSize): int
    {
        $entityManager = $this->entityManager;
        $removed = 0;

        $members = $this->memberRepository->loadDataRetentionMembers($batchSize);

        if (!empty($members)) {
            $msg = 'Removing private data for ' . \count($members) . ' members.';
            $io->info($msg);
            $this->logger->write($msg, 'Data Retention', $this->bwAdmin);

            /** @var Member $member */
            foreach ($members as $member) {
                $username = $member->getUsername();
                $this->removeBrowserPushNotificationsFromSender($username);

                $languageLevels = $member->getLanguageLevels();
                foreach ($languageLevels as $languageLevel) {
                    $entityManager->remove($languageLevel);
                }

                $addresses = $member->getAddresses();
                foreach ($addresses as $address) {
                    $entityManager->remove($address);
                }

                $this->removeBrowserPushData($member);
                $member = $this->removeMemberInfo($member);
                $this->removeUserInfo($member);
                $this->removeProfilePictures($member);
                $this->removeGallery($member);
                $this->removeNotes($member);
                $this->removeRelations($member);
                $this->removeActivityAnswers($member);
                $this->anonymizeDonations($member);
                $this->markConversationsAsPurged($member);

                $member->setUsername('retired_' . $member->getId());
                $entityManager->persist($member);
                $entityManager->flush();
                // after the rename: the conversations with members retired before, then the trips, whose stops may
                // have lost their invitations with those conversations
                $this->removeConversationsWithRetiredMembers($member);
                $this->removeTrips($member);
                // the profile's texts in all languages (after the flush, which stores the emptied ones)
                $entityManager->createQuery('DELETE ' . MemberTranslation::class . ' t WHERE t.object = :member')
                    ->setParameter('member', $member)
                    ->execute();
                ++$removed;

                $msg = 'Removed private data for ' . $username . ' (retired_' . $member->getId() . ').';
                $io->info($msg);
                $this->logger->write($msg, 'Data Retention', $this->bwAdmin);
            }

            $msg = 'Removed private data for ' . $removed . ' members.';
            $io->info($msg);

            $this->logger->write($msg, 'Data Retention', $this->bwAdmin);
        }

        return $removed;
    }

    private function removeBrowserPushNotificationsFromSender(string $username): void
    {
        /** @var BrowserPushNotificationRepository $notificationRepository */
        $notificationRepository = $this->entityManager->getRepository(BrowserPushNotification::class);
        $notificationRepository->deleteNotificationsFromSender($username);
    }

    private function removeBrowserPushData(Member $member): void
    {
        $subscriptions = $this->entityManager->getRepository(BrowserPushSubscription::class)->findBy([
            'member' => $member,
        ]);
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }

        $notifications = $this->entityManager->getRepository(BrowserPushNotification::class)->findBy([
            'receiver' => $member,
        ]);
        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }
    }

    private function removeMemberInfo(Member $member): Member
    {
        // Used to set a random password (and forget it directly)
        $client = new Client();
        $longAgo = new DateTime('1900-01-01');

        /** @var EntityRepository $locationRepository */
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $location = $locationRepository->findOneBy([]);

        $member
            ->setAccommodation(AccommodationType::NO)
            ->setAdditionalAccommodationinfo('')
            ->setBewelcomed(0)
            ->setBirthdate($longAgo)
            ->setBooks(null)
            ->setEmail($client->generateId() . '@example.com')
            ->setName('')
            ->setShortName('')
            ->setGender('other')
            ->setHideAttribute(255)
            ->setHobbies(null)
            ->setHostingInterest(null)
            ->setIlivewith(null)
            ->setLastswitchtoactive($longAgo)
            ->setMaxGuests(0)
            ->setMaxLengthOfStay(null)
            ->setMovies(null)
            ->setMusic(null)
            ->setRemindersWithOutLogin(0)
            ->setOccupation(null)
            ->setStandardOffers([])
            ->setOfferguests(null)
            ->setOfferhosts(null)
            ->setOrganizations(null)
            ->setRestrictions([])
            ->setPasttrips(null)
            ->setPlannedtrips(null)
            ->setPleasebring(null)
            ->setAboutMe(null)
            ->setRegistrationKey(null)
            ->setStatus('AskToLeave')
            ->hideAge()
            ->hideGender()
            ->hideName()
            ->setWhereYouSleep(null)
            ->setProfileLanguage('none')
            ->setAdditionalAccommodationInfo(null)
            ->setCreated($longAgo)
            ->setUpdated($longAgo)
            ->setPleaseBring(null)
            ->setHouseRules(null)
            ->setGettingThere(null)
            ->setNewEmail(null)
            ->setPassword($client->generateId())
        ;

        return $member;
    }

    private function removeUserInfo(Member $member): void
    {
        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare('
            UPDATE
                user
            SET
                handle = :newHandle,
                pw = PASSWORD(:password),
                email = :email
            WHERE
                handle = :handle
            ');
        $statement->bindValue(':handle', $member->getUsername());
        $statement->bindValue(':newHandle', 'retired_' . $member->getId());
        $statement->bindValue(':password', 'password');
        $statement->bindValue(':email', 'noemail@example.com');
        try {
            $statement->executeQuery();
        } catch (TableNotFoundException) {
            // the legacy user table is gone (new schema, test database)
        }
    }

    private function removeProfilePictures(Member $member): void
    {
        $memberPath = $this->dataDirectory . '/user/avatars/' . $member->getId() . '_';

        // the known names only: no scan of the folder (over a million files on the live site, once per member). Files
        // of other sizes (any size can be asked for) are left to a clean-up of their own.
        $files = [];
        foreach (self::AVATAR_SIZES as $size) {
            $files[] = $memberPath . $size;
            if ('original' !== $size) {
                $files[] = $memberPath . $size . '_' . $size;
                $files[] = $memberPath . $size . '_' . $size . '.webp';
            }
        }

        try {
            new Filesystem()->remove(array_filter($files, 'file_exists'));
        } catch (IOException $e) {
            $this->logger->write(
                'Problem during retention run: ' . $e->getMessage(),
                'Data Retention',
                $this->bwAdmin
            );
        }
    }

    /**
     * The member's albums and pictures, in the database and on disk.
     */
    /**
     * The private notes the member wrote about others, with their categories (note_category goes with its note). Notes
     * others wrote about the member are theirs and stay.
     */
    private function removeNotes(Member $member): void
    {
        $this->entityManager->createQuery('DELETE ' . ProfileNote::class . ' n WHERE n.owner = :member')
            ->setParameter('member', $member)
            ->execute();
    }

    /**
     * What links the member to others or only served the member: friendships, group memberships (with the reason
     * given for joining), profile visits both ways, profile photos, a password reset, preferences, forum
     * subscriptions and notifications, hidden trip stops, sent trip and activity notifications, acknowledged login
     * messages, newsletter and mass mail recipient rows.
     */
    private function removeRelations(Member $member): void
    {
        $deletes = [
            Friend::class => 'e.left = :member OR e.right = :member OR e.requester = :member',
            GroupMembership::class => 'e.member = :member',
            ProfileVisit::class => 'e.member = :member OR e.visitor = :member',
            MemberPhoto::class => 'e.member = :member',
            PasswordReset::class => 'e.member = :member',
            MemberPreference::class => 'e.member = :member',
            MemberThreadSubscription::class => 'e.subscriber = :member',
            ForumPostNotification::class => 'e.receiver = :member',
            MemberLegHidden::class => 'e.member = :member',
            MemberLegNotificationSent::class => 'e.member = :member',
            MemberActivityNotificationSent::class => 'e.member = :member',
            LoginMessageAcknowledged::class => 'e.member = :member',
            NewsletterRecipient::class => 'e.receiver = :member',
        ];
        foreach ($deletes as $entity => $where) {
            $this->entityManager->createQuery('DELETE ' . $entity . ' e WHERE ' . $where)
                ->setParameter('member', $member)
                ->execute();
        }
    }

    /**
     * The member's answers to activities: for activities that are over the answer stays (the number of attendees
     * stays right), without the comment why; for activities still to come it goes completely.
     */
    private function removeActivityAnswers(Member $member): void
    {
        $now = new DateTime();
        $this->entityManager->createQuery(
            'DELETE ' . ActivityAttendee::class . ' a WHERE a.attendee = :member AND a.activity IN'
            . ' (SELECT act.id FROM ' . Activity::class . ' act WHERE act.ends >= :now)'
        )
            ->setParameter('member', $member)
            ->setParameter('now', $now)
            ->execute();
        $this->entityManager->createQuery(
            'UPDATE ' . ActivityAttendee::class . " a SET a.comment = '' WHERE a.attendee = :member"
        )
            ->setParameter('member', $member)
            ->execute();
    }

    /**
     * The donations stay (amount and date) without who gave them: no member, address, name or comment of the donor.
     */
    private function anonymizeDonations(Member $member): void
    {
        $this->entityManager->createQuery(
            'UPDATE ' . Donation::class . " d SET d.donor = NULL, d.email = '', d.nameGiven = '', d.memberComment = ''"
            . ' WHERE d.donor = :member'
        )
            ->setParameter('member', $member)
            ->execute();
    }

    /**
     * Conversations of two retired members belong to nobody any more: their messages go, with the hosting requests
     * and invitations they were about. Nothing else references a message (a reply's parent becomes null).
     */
    private function removeConversationsWithRetiredMembers(Member $member): void
    {
        $retired = 'o.username = CONCAT(\'retired_\', o.id)';
        $messages = $this->entityManager->createQuery(
            'SELECT m.id, IDENTITY(m.request) AS request FROM ' . Message::class . ' m JOIN ' . Member::class . ' o'
            . ' WITH (m.sender = :member AND m.receiver = o) OR (m.receiver = :member AND m.sender = o)'
            . ' WHERE ' . $retired
        )
            ->setParameter('member', $member)
            ->getArrayResult();
        if ([] === $messages) {
            return;
        }

        $this->entityManager->createQuery('DELETE ' . Message::class . ' m WHERE m.id IN (:ids)')
            ->setParameter('ids', array_column($messages, 'id'))
            ->execute();
        $requests = array_values(array_unique(array_filter(array_column($messages, 'request'))));
        if ([] === $requests) {
            return;
        }
        // a request still referenced by another message stays (never between other members, but to be safe)
        $stillUsed = $this->entityManager->createQuery(
            'SELECT DISTINCT IDENTITY(m.request) FROM ' . Message::class . ' m WHERE m.request IN (:ids)'
        )
            ->setParameter('ids', $requests)
            ->getSingleColumnResult();
        $unused = array_values(array_diff($requests, $stillUsed));
        if ([] !== $unused) {
            $this->entityManager->createQuery('DELETE ' . HostingRequest::class . ' r WHERE r.id IN (:ids)')
                ->setParameter('ids', $unused)
                ->execute();
        }
    }

    /**
     * The member's trips go, except the stops other members still have an invitation for: those stay with their
     * trip, whose summary and description are emptied (the place, dates and number of travellers tell nobody who it
     * was).
     */
    private function removeTrips(Member $member): void
    {
        $legs = $this->entityManager->createQuery(
            'SELECT l.id, IDENTITY(l.trip) AS trip FROM ' . Leg::class . ' l JOIN l.trip t WHERE t.creator = :member'
        )
            ->setParameter('member', $member)
            ->getArrayResult();
        if ([] === $legs) {
            return;
        }

        $invited = $this->entityManager->createQuery(
            'SELECT DISTINCT IDENTITY(r.inviteForLeg) FROM ' . HostingRequest::class
            . ' r WHERE r.inviteForLeg IN (:legs)'
        )
            ->setParameter('legs', array_column($legs, 'id'))
            ->getSingleColumnResult();
        $notInvited = array_values(array_diff(array_column($legs, 'id'), $invited));
        if ([] !== $notInvited) {
            $this->entityManager->createQuery('DELETE ' . Leg::class . ' l WHERE l.id IN (:ids)')
                ->setParameter('ids', $notInvited)
                ->execute();
        }

        // the trips left without stops go, the others stay without their texts
        $keptTrips = [];
        foreach ($legs as $leg) {
            if (\in_array($leg['id'], $invited, false)) {
                $keptTrips[$leg['trip']] = true;
            }
        }
        $trips = array_values(array_unique(array_column($legs, 'trip')));
        $gone = array_values(array_diff($trips, array_keys($keptTrips)));
        if ([] !== $gone) {
            $this->entityManager->createQuery('DELETE ' . Trip::class . ' t WHERE t.id IN (:ids)')
                ->setParameter('ids', $gone)
                ->execute();
        }
        if ([] !== $keptTrips) {
            $this->entityManager->createQuery(
                'UPDATE ' . Trip::class . " t SET t.summary = '', t.description = '' WHERE t.id IN (:ids)"
            )
                ->setParameter('ids', array_keys($keptTrips))
                ->execute();
        }
    }

    private function removeGallery(Member $member): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            'DELETE gig FROM gallery_items_to_gallery gig
             JOIN gallery g ON g.id = gig.gallery_id_foreign WHERE g.user_id_foreign = ?',
            [$member->getId()],
        );
        $connection->executeStatement(
            'DELETE gig FROM gallery_items_to_gallery gig
             JOIN gallery_items i ON i.id = gig.item_id_foreign WHERE i.user_id_foreign = ?',
            [$member->getId()],
        );
        $connection->executeStatement('DELETE FROM gallery_items WHERE user_id_foreign = ?', [$member->getId()]);
        $connection->executeStatement('DELETE FROM gallery WHERE user_id_foreign = ?', [$member->getId()]);

        $directory = $this->galleryDirectory . '/member' . $member->getId();
        try {
            new Filesystem()->remove($directory);
        } catch (IOException $e) {
            $this->logger->write(
                'Problem during retention run: ' . $e->getMessage(),
                'Data Retention',
                $this->bwAdmin
            );
        }
    }

    /**
     * The member's side of every conversation is purged.
     */
    private function markConversationsAsPurged(Member $member): void
    {
        $connection = $this->entityManager->getConnection();
        $sides = ['receiver_id' => DeleteRequestType::RECEIVER_PURGED, 'sender_id' => DeleteRequestType::SENDER_PURGED];
        foreach ($sides as $column => $purged) {
            $connection->executeStatement(
                "UPDATE message SET delete_request = CONCAT_WS(',', NULLIF(delete_request, ''), :purged)
                 WHERE {$column} = :member AND FIND_IN_SET(:purged, IFNULL(delete_request, '')) = 0",
                ['purged' => $purged, 'member' => $member->getId()],
            );
        }
    }
}
