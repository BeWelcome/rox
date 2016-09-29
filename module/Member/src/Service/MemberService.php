<?php

namespace Rox\Member\Service;

use Rox\Member\Model\Member;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class MemberService
{
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function changePassword(Member $member, $password)
    {
        // Use the decorated encoder factory to get the ideal encoder for
        // this user.
        $encoder = $this->encoderFactory->getEncoder($member);

        $salt = '';

        // Bcrypt creates its own salt, so we only create one for other
        // encoders, should they be used in the future.
        if (!$encoder instanceof BCryptPasswordEncoder) {
            $salt = random_bytes(16);
        }

        $member->PassWord = $encoder->encodePassword($password, $salt);

        $member->save();
    }
}
