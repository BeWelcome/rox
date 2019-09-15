<?php

namespace App\Utilities;

trait UniqueFilenameTrait
{
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return sha1(uniqid(rand(), true));
    }
}
