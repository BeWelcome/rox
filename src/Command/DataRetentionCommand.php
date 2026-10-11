<?php

namespace App\Command;

use App\Doctrine\AccommodationType;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Entity\NewLocation;
use App\Logger\Logger;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Hidehalo\Nanoid\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DataRetentionCommand extends Command
{
    protected static $defaultName = 'data:retention';

    public const MAX_EXECUTION_TIME_SECONDS = 110;

    private Member $bwAdmin;

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

        $io = new SymfonyStyle($input, $output);

        $io->title('Running Data Retention');

        $batchSize = $input->getArgument('batchSize') ?? $this->mailerBatchSize;

        if ($batchSize <= 0) {
            $io->error('Batch size must be a positive integer.');

            return Command::FAILURE;
        }

        $io->note(sprintf('Processing a batch of up to %d members (max execution time ~120s).', $batchSize));

        $retired = $this->removeMembers($io, $batchSize);

        $io->success(sprintf('Data of %d members has been deleted.', $retired));

        return Command::SUCCESS;
    }

    private function removeMembers(SymfonyStyle $io, int $batchSize): int
    {
        $entityManager = $this->entityManager;
        $startTime = microtime(true);

        $members = $this->memberRepository->loadDataRetentionMembers($batchSize);

        if (!empty($members)) {
            $totalFound = \count($members);
            $msg = 'Removing private data for ' . $totalFound . ' members.';
            $io->info($msg);
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
                    $io->warning($warnMsg);
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

                $member->setUsername('retired_' . $member->getId());
                $entityManager->persist($member);
                $entityManager->flush();

                ++$processedCount;

                $msg = 'Removed private data for ' . $username . ' (retired_' . $member->getId() . ').';
                $io->info($msg);
                $this->logger->write($msg, 'Data Retention', $this->bwAdmin);
            }

            $summaryMsg = sprintf('Removed private data for %d members.', $processedCount);
            $io->info($summaryMsg);
            $this->logger->write($summaryMsg, 'Data Retention', $this->bwAdmin);

            return $processedCount;
        }

        $io->info('No members found for data retention.');

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

    private function removeUserInfo(Member $member)
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
        $statement->executeQuery();
    }

    private function removeProfilePictures(Member $member)
    {
        $memberPath = $this->dataDirectory . '/user/avatars/';

        $filesystem = new Filesystem();
        $finder = new Finder();
        try {
            $files = $finder->files()->name($member->getId() . '*')->in($memberPath);

            foreach ($files as $file) {
                $filesystem->remove($file);
            }
        } catch (\Exception $e) {
            $this->logger->write(
                'Problem during retention run: ' . $e->getMessage(),
                'Data Retention',
                $this->bwAdmin
            );
        }
    }
}
