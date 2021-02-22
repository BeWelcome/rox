<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Member;
use App\Entity\Trip;
use App\Validator\Constraints\TripOwner;
use App\Validator\Constraints\TripOwnerValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TripOwnerValidatorTest extends TestCase
{
    private MockObject $securityMock;
    private MockObject $objectMock;
    private MockObject $contextMock;
    private MockObject $userMock;
    private MockObject $violationMock;
    private TripOwner $constraint;
    private TripOwnerValidator $validator;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->objectMock = $this->createMock(Trip::class);
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->userMock = $this->createMock(Member::class);
        $this->violationMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->constraint = new TripOwner();
        $this->validator = new TripOwnerValidator($this->securityMock);
        $this->validator->initialize($this->contextMock);
    }

    public function testItThrowsAnExceptionOnInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate($this->objectMock, new NotBlank());
    }

    public function testItIgnoresInvalidUser(): void
    {
        $this->securityMock->expects($this->once())->method('getUser')->willReturn(null);
        $this->contextMock->expects($this->never())->method('buildViolation');

        $this->validator->validate($this->objectMock, $this->constraint);
    }

    /**
     * @dataProvider getInvalidValue
     */
    public function testItIgnoresInvalidValue($value): void
    {
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->contextMock->expects($this->never())->method('buildViolation');

        $this->validator->validate($value, $this->constraint);
    }

    public function getInvalidValue(): array
    {
        return [
            ['invalid'],
            [new stdClass()],
            [1],
            [true],
        ];
    }

    public function testItIgnoresValidCreator(): void
    {
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->objectMock->expects($this->once())->method('getCreator')->willReturn($this->userMock);
        $this->contextMock->expects($this->never())->method('buildViolation');

        $this->validator->validate($this->objectMock, $this->constraint);
    }

    public function testItBuildsAndAddAViolationOnInvalidCreator(): void
    {
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->objectMock->expects($this->once())->method('getCreator')->willReturn($this->createMock(Member::class));
        $this->contextMock->expects($this->once())->method('buildViolation')->with('This value is not valid.')->willReturn($this->violationMock);
        $this->violationMock->expects($this->once())->method('addViolation');

        $this->validator->validate($this->objectMock, $this->constraint);
    }
}
