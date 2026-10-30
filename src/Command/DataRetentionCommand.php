<?php

namespace App\Command;

use App\Doctrine\AccommodationType;
use App\Doctrine\DeleteRequestType;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Logger\Logger;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
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
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'data:retention',
    description: 'Cleans the database of retired users (should be run at least once each day)',
    aliases: ['data:cleanup'],
    hidden: false,
)]
class DataRetentionCommand extends Command
{
    /** A run stops after this, so that it ends before the next one starts. */
    public const int MAX_SECONDS = 110;

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

        $retired = $this->removeMembers($io, $batchSize);

        $io->success(\sprintf('Data of %d members has been deleted.', $retired));

        return Command::SUCCESS;
    }

    private function removeMembers(SymfonyStyle $io, int $batchSize): int
    {
        $entityManager = $this->entityManager;
        $start = microtime(true);
        $removed = 0;

        $members = $this->memberRepository->loadDataRetentionMembers($batchSize);

        if (!empty($members)) {
            $msg = 'Removing private data for ' . \count($members) . ' members.';
            $io->info($msg);
            $this->logger->write($msg, 'Data Retention', $this->bwAdmin);

            /** @var Member $member */
            foreach ($members as $member) {
                if (microtime(true) - $start >= self::MAX_SECONDS) {
                    $msg = \sprintf(
                        'Stopped after %d seconds, %d of %d members done.',
                        self::MAX_SECONDS,
                        $removed,
                        \count($members),
                    );
                    $io->warning($msg);
                    $this->logger->write($msg, 'Data Retention', $this->bwAdmin);
                    break;
                }

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
                $this->markConversationsAsPurged($member);

                $member->setUsername('retired_' . $member->getId());
                $entityManager->persist($member);
                $entityManager->flush();
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
        $memberPath = $this->dataDirectory . '/user/avatars/';

        $filesystem = new Filesystem();
        $finder = new Finder();
        try {
            // {id}_{size}: '{id}*' would match the avatars of members whose id starts with this one
            $files = $finder->files()->name($member->getId() . '_*')->in($memberPath);

            foreach ($files as $file) {
                $filesystem->remove($file);
            }
        } catch (Exception $e) {
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
