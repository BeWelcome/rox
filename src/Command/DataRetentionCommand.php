<?php

namespace App\Command;

use App\Utilities\SessionSingleton;
use App\Utilities\TranslatorSingleton;
use Doctrine\ORM\EntityManagerInterface;
use EnvironmentExplorer;
use MembersModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataRetentionCommand extends Command
{
    protected static $defaultName = 'data:retention';

    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var ParameterBagInterface
     */
    private $params;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->params = $params;
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Cleans the database of retired users (should be run at least once each day)')
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Setup old environment to be able to use the old code
        $session = $this->session;
        $session->start();

        // Make sure the Rox classes find this session and the translator
        SessionSingleton::createInstance($session);
        TranslatorSingleton::createInstance($this->translator);

        $environmentExplorer = new EnvironmentExplorer($this->urlGenerator);
        $environmentExplorer->initializeGlobalState(
            $this->params->get('database_host'),
            $this->params->get('database_name'),
            $this->params->get('database_user'),
            $this->params->get('database_password')
        );

        $io = new SymfonyStyle($input, $output);

        $membersModel = new MembersModel();
        $connection = $this->entityManager->getConnection();
        $membersModel->set_pdo($connection->getWrappedConnection());

        $retired = $membersModel->removeMembers();

        $io->success(sprintf('Data of %d members has been deleted.', $retired));

        return Command::SUCCESS;
    }
}
