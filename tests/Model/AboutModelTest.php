<?php

namespace App\Tests\Model;

use App\Entity\Feedback;
use App\Entity\FeedbackCategory;
use App\Entity\Language;
use App\Entity\Member;
use App\Model\AboutModel;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class AboutModelTest extends TestCase
{
    private $entityManager;
    private $mailer;
    private $aboutModel;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(Mailer::class);
        $this->aboutModel = new AboutModel($this->entityManager, $this->mailer);
    }

    public function testGetFeedbackCategoriesReturnsResult(): void
    {
        // Stubbing the chain to return a specific result.
        // We don't care about the exact method calls on the query builder, mostly just that it returns the expected data.
        $expectedCategories = [new FeedbackCategory(), new FeedbackCategory()];

        $query = $this->createStub(Query::class);
        $query->method('getResult')->willReturn($expectedCategories);

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('indexBy')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager->method('createQueryBuilder')->willReturn($qb);

        $result = $this->aboutModel->getFeedbackCategories();

        $this->assertSame($expectedCategories, $result);
    }

    public function testSendFeedbackEmailTriggersMailer(): void
    {
        // Side-effect test: verify mailer is called.
        $this->mailer->expects($this->once())->method('sendFeedbackEmail');

        $category = new FeedbackCategory();
        $category->setEmailtonotify('admin@example.com');

        $data = [
            'IdCategory' => $category,
            'FeedbackEmail' => 'test@test.com',
            'message' => 'hello',
        ];

        $this->aboutModel->sendFeedbackEmail($data);
    }

    public function testAddFeedbackPersistsData(): void
    {
        // Side-effect test: verify persistence.
        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Feedback::class));
        $this->entityManager->expects($this->once())->method('flush');

        // Stub repository to return a dummy language
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('find')->willReturn(new Language());
        $this->entityManager->method('getRepository')->willReturn($repo);

        $data = [
            'member' => new Member(),
            'FeedbackQuestion' => 'Question',
            'IdCategory' => new FeedbackCategory(),
        ];

        $this->aboutModel->addFeedback($data);
    }
}
