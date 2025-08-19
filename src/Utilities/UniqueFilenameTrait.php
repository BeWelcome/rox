<?php

namespace App\Utilities;

trait UniqueFilenameTrait
{
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return sha1(uniqid(random_int(0, mt_getrandmax()), true));
    }
}
