<?php

namespace App\Twig;

use App\Model\LanguageModel;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Registry;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MockupExtension
{
    public function image($image)
    {
        return $image;
    }
}
