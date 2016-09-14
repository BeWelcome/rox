<?php

namespace Rox\I18\Loader;

use Illuminate\Database\Connection;
use PDO;
use PDOStatement;
use PHPUnit_Framework_TestCase;
use Rox\I18n\Loader\DatabaseLoader;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    public function setUp()
    {
        $this->connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function test()
    {
        $pdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stmt = $this->getMockBuilder(PDOStatement::class)
            ->getMock();

        $stmt->expects($this->once())->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn([
            [
                'Sentence' => 'fake sentence',
                'code' => 'fake_sent',
            ],
        ]);

        $this->connection->expects($this->once())->method('getPdo')->willReturn($pdo);

        $pdo->expects($this->once())->method('prepare')->willReturn($stmt);

        $loader = new DatabaseLoader($this->connection);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
    }
}
