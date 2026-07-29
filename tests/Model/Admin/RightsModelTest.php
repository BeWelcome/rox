<?php

namespace App\Tests\Model\Admin;

use App\Model\Admin\RightsModel;
use App\Repository\RightRepository;
use App\Repository\RightVolunteerRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class RightsModelTest extends TestCase
{
    public function testScopeValidationAcceptsDelimitedTokensAndRejectsMalformedLists(): void
    {
        $model = new RightsModel(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(RightRepository::class),
            $this->createStub(RightVolunteerRepository::class),
        );

        foreach (['"All"', '"Words";"Group";', 'Words, Group', '"New Zealand",All'] as $scope) {
            self::assertTrue($model->isScopeWellFormed($scope), $scope);
        }

        foreach (['', '  ', '"Words""Group"', 'Words"Group', '""', 'Words,,Group', '"Words', ',Words'] as $scope) {
            self::assertFalse($model->isScopeWellFormed($scope), $scope);
        }
    }
}
