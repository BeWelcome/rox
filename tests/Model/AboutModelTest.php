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
    public function testGetFeedbackCategoriesReturnsResult(): void
    {
        // Stubbing the chain to return a specific result.
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

        $mailer = $this->createStub(Mailer::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($qb);

        $aboutModel = new AboutModel($entityManager, $mailer);
        $result = $aboutModel->getFeedbackCategories();

        $this->assertSame($expectedCategories, $result);
    }

    public function testSendFeedbackEmailTriggersMailer(): void
    {
        // Side-effect test: verify mailer is called.
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())->method('sendFeedbackEmail');
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $category = new FeedbackCategory();
        $category->setEmailtonotify('admin@example.com');

        $data = [
            'IdCategory' => $category,
            'FeedbackEmail' => 'test@test.com',
            'message' => 'hello',
        ];

        $aboutModel = new AboutModel($entityManager, $mailer);
        $aboutModel->sendFeedbackEmail($data);
    }

    public function testAddFeedbackPersistsData(): void
    {
        // Side-effect test: verify persistence.
        $mailer = $this->createStub(Mailer::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Feedback::class));
        $entityManager->expects($this->once())->method('flush');

        // Stub repository to return a dummy language
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('find')->willReturn(new Language());
        $entityManager->method('getRepository')->willReturn($repository);

        $data = [
            'member' => new Member(),
            'FeedbackQuestion' => 'Question',
            'IdCategory' => new FeedbackCategory(),
        ];

        $aboutModel = new AboutModel($entityManager, $mailer);
        $aboutModel->addFeedback($data);
    }
}
