<?php

namespace App\Utilities;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Trait TranslatedFlashTrait
 *
 * Expects that TranslatorTrait is used as well
 *
 * @package App\Utilities
 */
trait TranslatedFlashTrait
{
    /**
     * @param $type
     * @param $message
     * @param mixed ...$params
     */
    protected function addTranslatedFlash($type, $message, ...$params)
    {
        $this->addFlash($type, $this->getTranslator()->trans($message, ...$params));
    }
}
