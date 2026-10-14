<?php

namespace App\Command;

use App\Doctrine\AccommodationType;
use App\Doctrine\DeleteRequestType;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Entity\NewLocation;
use App\Logger\Logger;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Hidehalo\Nanoid\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;

class DataRetentionCommand extends Command
{
    public const int MAX_EXECUTION_TIME_SECONDS = 110;

    private const array SIZES = [
        'xs',
        '30',
        '36',
        '48',
        '50',
        '72',
        '96',
        '100',
        '120',
        '150',
        '160',
        '200',
        '320',
        '480',
        '500',
        'original',
    ];
    protected static $defaultName = 'data:retention';

    private Member $bwAdmin;

    private SymfonyStyle $io;

    public function __construct(
        private readonly Logger $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly MemberRepository $memberRepository,
        private readonly string $dataDirectory,
        private readonly int $mailerBatchSize
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Cleans the database of retired users (should be run at least once each day)')
            ->addArgument(
                'batch-size',
                InputArgument::OPTIONAL,
                sprintf('Number of members to process in this run (default: %d)', $this->mailerBatchSize),
                null
            )
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Member $bwAdmin */
        $bwAdmin = $this->memberRepository->find(1);
        $this->bwAdmin = $bwAdmin;

        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Running Data Retention');

        $batchSize = $input->getArgument('batch-size') ?? $this->mailerBatchSize;

        if ($batchSize <= 0) {
            $this->io->error('Batch size must be a positive integer.');

            return Command::FAILURE;
        }

        $this->io->note(sprintf('Processing a batch of up to %d members (max execution time ~120s).', $batchSize));

        $retired = $this->removeMembers($batchSize);

        $this->io->success(sprintf('Data of %d members has been deleted.', $retired));

        /*
         * Need to find a way to delete the full chain of messages. While the code sets purge on all items in a thread,
         * sometimes the tree is broken and a message suddenly doesn't have a parent anymore.
         *
         * @todo Find a way to delete all messages that both side have been marked as purged.
         *
            $connection = $this->entityManager->getConnection();
            $removedMessages = $connection->executeStatement(
                <<<SQL
                    DELETE FROM messages WHERE DeleteRequest IN (
                        'senderpurged,receiverpurged',
                        'senderdeleted,senderpurged,receiverpurged',
                        'receiverdeleted,senderpurged,receiverpurged',
                        'senderdeleted,receiverdeleted,senderpurged,receiverpurged'
                    )
                SQL
            );

            $this->io->note(sprintf('Removed messages where both members have been retired: %d.', $removedMessages));
        */

        return Command::SUCCESS;
    }

    private function removeMembers(int $batchSize): int
    {
        $entityManager = $this->entityManager;
        $startTime = microtime(true);

        $members = $this->memberRepository->loadDataRetentionMembers($batchSize);

        if (!empty($members)) {
            $totalFound = \count($members);
            $msg = 'Removing private data for ' . $totalFound . ' members.';
            $this->io->info($msg);
            $this->logger->write($msg, 'Data Retention', $this->bwAdmin);

            $processedCount = 0;
            /** @var Member $member */
            foreach ($members as $member) {
                // Check if approaching maximum execution time limit (120s)
                if ((microtime(true) - $startTime) >= self::MAX_EXECUTION_TIME_SECONDS) {
                    $warnMsg = sprintf(
                        'Time limit approaching (%ds). Stopping batch early after processing %d of %d members.',
                        self::MAX_EXECUTION_TIME_SECONDS,
                        $processedCount,
                        $totalFound
                    );
                    $this->io->warning($warnMsg);
                    $this->logger->write($warnMsg, 'Data Retention', $this->bwAdmin);
                    break;
                }

                $username = $member->getUsername();
                $cryptedFields = $member->getCryptedFields();
                foreach ($cryptedFields as $cryptedField) {
                    $entityManager->remove($cryptedField);
                }

                $memberTranslationRepository = $entityManager->getRepository(MemberTranslation::class);
                /** @var MemberTranslation[] $memberTranslations */
                $memberTranslations = $memberTranslationRepository->findBy(['owner' => $member]);
                foreach ($memberTranslations as $memberTranslation) {
                    $entityManager->remove($memberTranslation);
                }

                $languageLevels = $member->getLanguageLevels();
                foreach ($languageLevels as $languageLevel) {
                    $entityManager->remove($languageLevel);
                }

                $addresses = $member->getAddresses();
                foreach ($addresses as $address) {
                    $entityManager->remove($address);
                }

                $member = $this->removeMemberInfo($member);
                $this->removeUserInfo($member);
                $this->removeProfilePictures($member);
                $this->removeGalleryItems($member);
                $this->markConversationsAsPurged($member);

                $member->setUsername('retired_' . $member->getId());
                $entityManager->persist($member);
                $entityManager->flush();

                ++$processedCount;

                $msg = 'Removed private data for ' . $username . ' (retired_' . $member->getId() . ').';
                $this->io->info($msg);
                $this->logger->write($msg, 'Data Retention', $this->bwAdmin);
            }

            $summaryMsg = sprintf('Removed private data for %d members.', $processedCount);
            $this->io->info($summaryMsg);
            $this->logger->write($summaryMsg, 'Data Retention', $this->bwAdmin);

            return $processedCount;
        }

        $this->io->info('No members found for data retention.');

        return 0;
    }

    private function removeMemberInfo(Member $member): Member
    {
        // Used to set a random password (and forget it directly)
        $client = new Client();
        $longAgo = new DateTime('1900-01-01');

        /** @var EntityRepository<NewLocation> $locationRepository */
        $locationRepository = $this->entityManager->getRepository(NewLocation::class);
        $location = $locationRepository->findOneBy([]);

        $member
            ->setAccommodation(AccommodationType::NO)
            ->setAdditionalAccommodationinfo(0)
            ->setAdresshidden('Yes')
            ->setBday(0)
            ->setBewelcomed(0)
            ->setBirthdate($longAgo)
            ->setBmonth(0)
            ->setByear(0)
            ->setBooks(0)
            ->setCellphonenumber(0)
            ->setChangedid(0)
            ->setChatAol($client->generateId())
            ->setChatGoogle($client->generateId())
            ->setChatIcq($client->generateId())
            ->setChatMsn($client->generateId())
            ->setChatOthers($client->generateId())
            ->setChatSkype($client->generateId())
            ->setChatYahoo($client->generateId())
            ->setCounterguests(0)
            ->setCounterhosts(0)
            ->setCountertrusts(0)
            ->setEmail($client->generateId() . '@example.com')
            ->setExUserId(0)
            ->setFirstName('')
            ->setSecondName(0)
            ->setLastName('')
            ->setFuturetrips(0)
            ->setGender('other')
            ->setGenderofguest('any')
            ->setHideAttribute(255)
            ->setHideBirthDate('Yes')
            ->setHidegender('Yes')
            ->setHobbies(0)
            ->setHomephonenumber(0)
            ->setHostingInterest(0)
            ->setIdentitychecklevel(false)
            ->setIlivewith(0)
            ->setInformationtoguest(0)
            ->setLastswitchtoactive($longAgo)
            ->setLatitude(null)
            ->setLongitude(null)
            ->setLogcount(0)
            ->setMaxguest(0)
            ->setMaxlenghtofstay(0)
            ->setMotivationforhospitality(0)
            ->setMovies(0)
            ->setMusic(0)
            ->setRemindersWithOutLogin(0)
            ->setOccupation(0)
            ->setOffer(0)
            ->setOfferguests(0)
            ->setOfferhosts(0)
            ->setOldtrips(0)
            ->setOrganizations(0)
            ->setOtherrestrictions(0)
            ->setPasttrips(0)
            ->setPlannedtrips(0)
            ->setPleasebring(0)
            ->setProfileSummary(0)
            ->setPublictransport(0)
            ->setQuality('')
            ->setRegistrationKey('')
            ->setRestrictions('')
            ->setSecurityflag(0)
            ->setStatus('AskToLeave')
            ->setTypicoffer('')
            ->setWebsite('')
            ->setWorkphonenumber(0)
            ->setPassword($client->generateId())
            ->setCity($location)
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
        } catch (Throwable $t) {
            // Ignore if user table doesn't exist (new schema or test database)
        }
    }

    private function removeProfilePictures(Member $member): void
    {
        $memberPath = $this->dataDirectory . '/user/avatars/' . $member->getId();

        // Sizes used on bewelcome

        $filesystem = new Filesystem();
        foreach (self::SIZES as $size) {
            // Old format
            $avatarFile = $memberPath . '_' . $size;
            if (file_exists($avatarFile)) {
                $filesystem->remove($avatarFile);
            }

            // Current format
            $avatarFile .= '_' . $size;
            if (file_exists($avatarFile)) {
                $filesystem->remove($avatarFile);
            }
        }
    }

    private function removeGalleryItems(Member $member): void
    {
        // \todo: use DQL as soon as database schema matches Doctrine entities
        // Remove albums created by member from database
        $connection = $this->entityManager->getConnection();
        $query = $connection->prepare('
            SELECT id FROM gallery WHERE user_id_foreign = :memberId
            ');
        $result = $query->executeQuery(['memberId' => $member->getId()]);
        $galleries = $result->fetchAllAssociative();

        $galleryIds = [];
        foreach ($galleries as $gallery) {
            $galleryIds[] = $gallery['id'];
        }

        $connection->executeStatement(
            'DELETE FROM gallery_items_to_gallery WHERE gallery_id_foreign IN (:galleryIds)',
            ['galleryIds' => $galleryIds],
            ['galleryIds' => Connection::PARAM_INT_ARRAY]
        );

        // Remove gallery items from database
        $statement = $connection->prepare('
            DELETE FROM gallery_items WHERE user_id_foreign = :memberId
        ');
        $statement->executeStatement(['memberId' => $member->getId()]);

        // Remove gallery (albums) from database
        $statement = $connection->prepare('
            DELETE FROM gallery WHERE user_id_foreign = :memberId
        ');
        $statement->executeStatement(['memberId' => $member->getId()]);

        $memberPath = $this->dataDirectory . '/gallery/member' . $member->getId();

        if (file_exists($memberPath)) {
            $filesystem = new Filesystem();
            $finder = new Finder();
            try {
                $files = $finder->files()->name('*')->in($memberPath);

                foreach ($files as $file) {
                    $filesystem->remove($file);
                }

                $filesystem->remove($memberPath);
            } catch (\Exception $e) {
                $this->io->error('Couldn\'t remove gallery for id ' . $member->getId() . ': ' . $e->getMessage());
                $this->logger->write(
                    sprintf('Couldn\'t remove gallery for id %d: %s', $member->getId(), $e->getMessage()),
                    'Data Retention',
                    $this->bwAdmin
                );
            }
        }
    }

    private function markConversationsAsPurged(Member $member): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            <<<'SQL'
                UPDATE messages
                SET DeleteRequest = CONCAT_WS(',', NULLIF(DeleteRequest, ''), :receiverPurged)
                WHERE messages.idReceiver = :memberId
                    AND FIND_IN_SET(:receiverPurged, DeleteRequest) = 0
            SQL,
            [
                'memberId' => $member->getId(),
                'receiverPurged' => DeleteRequestType::RECEIVER_PURGED,
            ]
        );

        $connection->executeStatement(
            <<<'SQL'
                UPDATE messages
                SET DeleteRequest = CONCAT_WS(',', NULLIF(DeleteRequest, ''), :senderPurged)
                WHERE messages.idSender = :memberId
                    AND FIND_IN_SET(:senderPurged, DeleteRequest) = 0
            SQL,
            [
                'memberId' => $member->getId(),
                'senderPurged' => DeleteRequestType::SENDER_PURGED,
            ]
        );
    }
}
