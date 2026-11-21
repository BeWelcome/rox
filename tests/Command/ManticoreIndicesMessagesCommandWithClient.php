<?php

namespace App\Tests\Command;

use App\Command\ManticoreIndicesMessagesCommand;
use Doctrine\ORM\EntityManagerInterface;
use Manticoresearch\Client;

/**
 * The command with a given Manticore client instead of one for the configured host.
 */
final class ManticoreIndicesMessagesCommandWithClient extends ManticoreIndicesMessagesCommand
{
    public function __construct(EntityManagerInterface $entityManager, private readonly Client $client)
    {
        parent::__construct($entityManager, 'manticore', 9308);
    }

    protected function createClient(): Client
    {
        return $this->client;
    }
}
