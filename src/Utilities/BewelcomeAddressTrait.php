<?php

namespace App\Utilities;

use App\Entity\NewMember as Member;
use Symfony\Component\Mime\Address;

trait BewelcomeAddressTrait
{
    public function beWelcomeAddress(Member $member, $email = null): Address
    {
        if (null === $email) {
            $email = $member->getEmail();
        }

        return new Address($email, 'BeWelcome - ' . $member->getUsername());
    }
}
