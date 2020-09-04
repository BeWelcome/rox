<?php

declare(strict_types=1);

namespace App\Command;

use App\Security\RefreshToken\RefreshTokenStorageInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class RefreshTokensExpireCommand extends Command
{
    private $storage;
    private $repository;

    public function __construct(RefreshTokenStorageInterface $storage, ObjectRepository $repository)
    {
        parent::__construct('app:refresh-tokens:expire');

        $this->storage = $storage;
        $this->repository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Expire all refresh-tokens or by user.')
            ->setDefinition(
                [
                    new InputArgument('username', InputArgument::OPTIONAL, 'The username'),
                    new InputOption('field', null, InputOption::VALUE_REQUIRED, 'The user field (username, email)', 'username'),
                ]
            )
            ->setHelp(
                <<<'EOT'
The <info>app:refresh-token:expire</info> command expires all refresh-tokens or by user:
<info>php %command.full_name%</info>
<info>php %command.full_name% username</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = (string) $input->getArgument('username');
        if (!empty($username)) {
            /** @var UserInterface|null $user */
            $user = $this->repository->findOneBy([(string) $input->getOption('field') => $username]);
            if (!$user) {
                throw new EntityNotFoundException(sprintf('User with username "%s" not found.', $username));
            }
            $this->storage->expireAll($user);
            $output->writeln(sprintf('RefreshTokens for user <comment>%s</comment> successfully expired.', $username));
        } else {
            $this->storage->expireAll();
            $output->writeln('RefreshTokens for all users successfully expired.');
        }

        return 0;
    }
}
