<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecordedUsernamesOfLeftMembers
 *
 * @ORM\Table(name="recorded_usernames_of_left_members")
 * @ORM\Entity
 */
class RecordedUsernamesOfLeftMembers
{
    /**
     * @var string
     *
     * @ORM\Column(name="UsernameNotToUse", type="string", length=32)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $usernamenottouse;



    /**
     * Get usernamenottouse
     *
     * @return string
     */
    public function getUsernamenottouse()
    {
        return $this->usernamenottouse;
    }
}
