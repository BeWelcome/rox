<?php


namespace App\Tests\Model;


use App\Entity\HostingRequest;
use App\Model\HostingRequestModel;
use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;

class HostingRequestModelTest extends TestCase
{
    public function testRequestExpiredYesterday()
    {
        $arrival = (clone(new DateTime()))->sub(new DateInterval('P2D'));
        $departure = (clone($arrival))->sub(new DateInterval('P1D'));

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->checkRequestExpired($request);

        $this->assertEquals(true, $expired);
    }

    public function testRequestExpiresTomorrow()
    {
        $arrival = (clone(new DateTime()))->sub(new DateInterval('P2D'));
        $departure = (clone($arrival))->add(new DateInterval('P1D'));

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture($departure);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->checkRequestExpired($request);

        $this->assertEquals(false, $expired);
    }

    public function testRequestExpiresNoDeparture()
    {
        $arrival = (clone(new DateTime()))->sub(new DateInterval('P2D'));

        $request = new HostingRequest();
        $request->setArrival($arrival);
        $request->setDeparture(null);

        $requestModel = new HostingRequestModel();
        $expired = $requestModel->checkRequestExpired($request);

        $this->assertEquals(false, $expired);
    }
}
