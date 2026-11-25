<?php

namespace App\Command;

use App\Service\Sitemap;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Prints the sitemap that /sitemap.xml serves, built fresh from the database, to look at it. The site needs no run of
 * it: the route builds the sitemap itself and keeps it until a FAQ category or a newsletter changes.
 */
#[AsCommand(
    name: 'sitemap:generate',
    description: 'Prints the sitemap that /sitemap.xml serves',
)]
class GenerateSitemapCommand extends Command
{
    public function __construct(
        private readonly Sitemap $sitemap,
        private readonly RouterInterface $router,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('base-url', 'b', InputOption::VALUE_REQUIRED, 'Scheme and host of the addresses (default: the router\'s)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseUrl = $input->getOption('base-url');
        if (null === $baseUrl) {
            $context = $this->router->getContext();
            $baseUrl = $context->getScheme() . '://' . $context->getHost();
        }

        $this->sitemap->invalidate();
        $output->write($this->sitemap->getXml($baseUrl));

        return Command::SUCCESS;
    }
}
