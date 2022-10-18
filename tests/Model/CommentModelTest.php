<?php

namespace App\Tests\Model;

use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Model\CommentModel;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class CommentModelTest extends TestCase
{
    private CommentModel $commentModel;

    public function setUp(): void
    {
        $em = $this->createStub(EntityManager::class);
        $this->commentModel = new CommentModel($em);
    }

    public function testNewExperienceOneNewRelation()
    {
        $original = new Comment();
        $original
            ->setRelations($this->buildRelations([
                CommentRelationsType::WAS_GUEST,
            ]))
            ->setTextFree('Lorem ipsum.')
        ;
        $updated = clone $original;
        $updated
            ->setRelations(
                $this->addRelations($updated->getRelations(), [
                    CommentRelationsType::WAS_HOST,
                ])
            )
            ->setTextFree('Lorem ipsum.')
        ;

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNewExperienceDisjunctRelations()
    {
        $original = new Comment();
        $original->setRelations($this->buildRelations([
            CommentRelationsType::WAS_GUEST,
        ]))->setTextFree('Lorem ipsum.');
        $updated = clone $original;
        $updated->setRelations($this->buildRelations([
            CommentRelationsType::WAS_HOST,
        ]))->setTextFree('Lorem ipsum.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceRelation()
    {
        $original = new Comment();
        $original->setRelations($this->buildRelations([
            CommentRelationsType::WAS_GUEST,
        ]))->setTextFree('Lorem ipsum.');
        $updated = clone $original;

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNewExperienceTextAdded()
    {
        $original = new Comment();
        $original->setTextFree('Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum. Lorem ipsum.');

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceNoTextChanged()
    {
        $original = new Comment();
        $original->setTextFree('Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceTypoFixed()
    {
        $original = new Comment();
        $original->setTextFree('Lorem pisum. Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum. Lorem ipsum.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceNegationFixed()
    {
        $original = new Comment();
        $original->setTextFree('I can stand the rain.');
        $updated = new Comment();
        $updated->setTextFree('I can\'t stand the rain.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceRemovedLineBreaks()
    {
        $original = new Comment();
        $original->setTextFree(
            'First line.' .
            PHP_EOL .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.' .
            PHP_EOL
        );
        $updated = new Comment();
        $updated->setTextFree(
            'First line.' .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.'
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceAddedLineBreaks()
    {
        $original = new Comment();
        $original->setTextFree(
            'First line.' .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.'
        );
        $updated = new Comment();
        $updated->setTextFree(
            'First line.' .
            PHP_EOL .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.' .
            PHP_EOL
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceSmallTextChanges()
    {
        $original = new Comment();
        $original->setTextFree(
            'Olli is a very charming and friendly guest, who is eager to discover the world!' .
            PHP_EOL .
            PHP_EOL .
            'All the best for your journey(s)! Take care and stay healthy!'
        );
        $updated = new Comment();
        $updated->setTextFree(
            'Methusalem is a very charming and friendly guest, who is eager to discover the world!' .
            PHP_EOL .
            'All the best for your journey! Take care and stay healthy!'
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNewExperienceTextChanges()
    {
        $original = new Comment();
        $original->setTextFree('I can stand the rain but not the snow.');
        $updated = new Comment();
        $updated->setTextFree('I like the snow can stand the rain.');

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    private function buildRelations(array $relations): string
    {
        return implode(',', $relations);
    }

    private function addRelations(string $relations, array $additionalRelations): string
    {
        // turn relations into array.
        $relations = explode(',', $relations);

        return implode(',', array_merge($relations, $additionalRelations));
    }
}
