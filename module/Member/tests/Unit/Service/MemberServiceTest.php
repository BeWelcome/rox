<?php

namespace Rox\Member\Service;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Rox\Member\Model\Member;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class MemberServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EncoderFactoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $encoderFactory;

    /**
     * @var MemberService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $memberService;

    public function setUp()
    {
        $this->encoderFactory = $this
            ->getMockBuilder(EncoderFactoryInterface::class)
            ->getMock();

        $this->memberService = new MemberService($this->encoderFactory);
    }

    public function testChangePassword()
    {
        /** @var Member|PHPUnit_Framework_MockObject_MockObject $member */
        $member = $this->getMockBuilder(Member::class)->getMock();

        $password = base64_encode(random_bytes(32));

        $encoder = $this->createMock(PasswordEncoderInterface::class);

        $passwordHash = 'fakeencoded';

        $encoder
            ->expects($this->once())
            ->method('encodePassword')
            ->with($password, $this->callback(function ($value) {
                $value = bin2hex($value);

                return preg_match('/^[0-9a-z]{32}$/', $value);
            }))
            ->willReturn($passwordHash);

        $this->encoderFactory->expects($this->once())->method('getEncoder')
            ->with($member)->willReturn($encoder);

        $member->expects($this->once())->method('__set')
            ->with('PassWord', $passwordHash);

        $member->expects($this->once())->method('save');

        $this->memberService->changePassword($member, $password);
    }
}
