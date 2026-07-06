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

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())->method('select')->with('c')->willReturnSelf();
        $qb->expects($this->once())->method('from')->with(FeedbackCategory::class, 'c')->willReturnSelf();
        $qb->expects($this->once())->method('where')->with('c.visible = 1')->willReturnSelf();
        $qb->expects($this->once())->method('orderBy')->with('c.sortorder', 'ASC')->willReturnSelf();
        $qb->expects($this->once())->method('indexBy')->with('c', 'c.id')->willReturnSelf();
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
        $category = new FeedbackCategory();
        $category->setEmailtonotify('admin@example.com');

        $data = [
            'IdCategory' => $category,
            'FeedbackEmail' => 'test@test.com',
            'message' => 'hello',
        ];

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendFeedbackEmail')
            ->with(
                'test@test.com',
                $this->callback(static fn ($address) => 'admin@example.com' === $address->getAddress()),
                $data
            )
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $aboutModel = new AboutModel($entityManager, $mailer);
        $aboutModel->sendFeedbackEmail($data);
    }

    public function testSendFeedbackEmailWithNoEnailTriggersMailerWithDefaultEmail(): void
    {
        // Side-effect test: verify mailer is called.
        $category = new FeedbackCategory();
        $category->setEmailtonotify('admin@example.com');

        $data = [
            'IdCategory' => $category,
            'FeedbackEmail' => null,
            'message' => 'hello',
        ];

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendFeedbackEmail')
            ->with(
                'feedback@bewelcome.org',
                $this->callback(static fn ($address) => 'admin@example.com' === $address->getAddress()),
                $data
            )
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $aboutModel = new AboutModel($entityManager, $mailer);
        $aboutModel->sendFeedbackEmail($data);
    }

    public function testAddFeedbackPersistsData(): void
    {
        // Side-effect test: verify persistence.
        $mailer = $this->createStub(Mailer::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        // Stub repository to return a dummy language
        $language = new Language();
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())->method('find')->with(0)->willReturn($language);
        $entityManager->method('getRepository')->willReturn($repository);

        $member = new Member();
        $category = new FeedbackCategory();
        $data = [
            'member' => $member,
            'FeedbackQuestion' => 'Question',
            'IdCategory' => $category,
        ];

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Feedback $feedback) use ($member, $category, $language): bool {
                return $member === $feedback->getAuthor()
                    && 'Question' === $feedback->getDiscussion()
                    && $category === $feedback->getCategory()
                    && $language === $feedback->getLanguage();
            }))
        ;
        $entityManager->expects($this->once())->method('flush');

        $aboutModel = new AboutModel($entityManager, $mailer);
        $aboutModel->addFeedback($data);
    }
}
