<?php

namespace App\Tests\Model;

use App\Model\BaseRequestModel;
use App\Model\InvitationModel;
use PHPUnit\Framework\TestCase;

class InvitationModelTest extends TestCase
{
    public function testIsInstanceOfBaseRequestModel(): void
    {
        // Behavioral test: verify inheritance contract.
        // If InvitationModel adds specific logic later, we will test that logic.
        // For now, ensuring it IS A BaseRequestModel is the specification.
        $model = new InvitationModel();
        $this->assertInstanceOf(BaseRequestModel::class, $model);
    }
}
