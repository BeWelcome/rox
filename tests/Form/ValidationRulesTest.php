<?php

namespace App\Tests\Form;

use App\Form\CustomDataClass\CommunityNewsCommentRequest;
use App\Form\CustomDataClass\FaqCategoryRequest;
use App\Form\CustomDataClass\FaqRequest;
use App\Form\CustomDataClass\GalleryImageEditRequest;
use App\Form\CustomDataClass\GroupRequest;
use App\Form\CustomDataClass\ReportCommentRequest;
use App\Form\CustomDataClass\Tools\ChangeUsernameRequest;
use App\Form\CustomDataClass\Translation\TranslationRequest as EditTranslationBase;
use App\Form\CustomDataClass\TranslationRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validation;

/**
 * These rules were docblock annotations, which Symfony 7 no longer reads: the forms accepted anything (group without
 * a name, a translation without text, ...). As attributes they are checked again, as on prod (v3.8.5).
 */
class ValidationRulesTest extends TestCase
{
    private const array RULES = [
        CommunityNewsCommentRequest::class => ['title' => 2, 'text' => 1],
        FaqCategoryRequest::class => ['wordCode' => 2],
        FaqRequest::class => ['wordCode' => 1, 'question' => 1, 'answer' => 1],
        GalleryImageEditRequest::class => ['title' => 1, 'description' => 1],
        GroupRequest::class => ['name' => 1, 'description' => 1, 'type' => 1, 'picture' => 2],
        ReportCommentRequest::class => ['feedback' => 1],
        ChangeUsernameRequest::class => ['oldUsername' => 1, 'newUsername' => 2],
        EditTranslationBase::class => ['wordCode' => 1, 'locale' => 1, 'domain' => 1, 'englishText' => 1, 'description' => 1],
        TranslationRequest::class => ['translation' => 1],
    ];

    public function testAllTwentyFiveRulesAreRead(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $total = 0;
        foreach (self::RULES as $class => $properties) {
            /** @var ClassMetadata $metadata */
            $metadata = $validator->getMetadataFor($class);
            foreach ($properties as $property => $expected) {
                $constraints = [];
                foreach ($metadata->getPropertyMetadata($property) as $propertyMetadata) {
                    $constraints = [...$constraints, ...$propertyMetadata->getConstraints()];
                }
                $this->assertCount($expected, $constraints, "$class::\$$property");
                $total += \count($constraints);
            }
        }
        $this->assertSame(25, $total);
    }

    public function testAGroupNeedsANameAndADescription(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate(new GroupRequest());
        $paths = array_map(fn ($violation) => $violation->getPropertyPath(), iterator_to_array($violations));

        $this->assertContains('name', $paths);
        $this->assertContains('description', $paths);
        $this->assertContains('picture', $paths);
    }

    public function testANewUsernameMustDiffer(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $request = new ChangeUsernameRequest();
        $request->oldUsername = 'someone';
        $request->newUsername = 'someone';

        $this->assertCount(1, $validator->validate($request));
    }
}
