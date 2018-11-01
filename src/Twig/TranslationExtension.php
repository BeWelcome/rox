<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use Symfony\Bridge\Twig\NodeVisitor\TranslationDefaultDomainNodeVisitor;
use Symfony\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFilter;

/**
 * Provides integration of the Translation component with Twig.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslationExtension extends AbstractExtension
{
    private $translator;
    private $translationNodeVisitor;

    public function __construct(TranslatorInterface $translator = null, NodeVisitorInterface $translationNodeVisitor = null)
    {
        $this->translator = $translator;
        $this->translationNodeVisitor = $translationNodeVisitor;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('silent', [$this, 'silent'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('trans', [$this, 'trans'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('transchoice', [$this, 'transchoice'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return AbstractTokenParser[]
     */
    public function getTokenParsers()
    {
        return [
            // {% trans %}Symfony is great!{% endtrans %}
            new TransTokenParser(),

            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),

            // {% trans_default_domain "foobar" %}
            new TransDefaultDomainTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [$this->getTranslationNodeVisitor(), new TranslationDefaultDomainNodeVisitor()];
    }

    public function getTranslationNodeVisitor()
    {
        return $this->translationNodeVisitor ?: $this->translationNodeVisitor = new TranslationNodeVisitor();
    }

    public function silent($message, array $arguments = [], $domain = null, $locale = null)
    {
        $arguments = array_merge($arguments, ['silent' => 'silent']);

        return $this->translator->trans($message, $arguments, $domain, $locale);
    }

    public function silentchoice($message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        $arguments = array_merge($arguments, ['silent' => 'silent', '%count%' => $count]);

        return $this->translator->transChoice($message, $count, $arguments, $domain, $locale);
    }

    public function trans($message, array $arguments = [], $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        if (array_key_exists('silent', $arguments)) {
            return $this->translator->trans($message, $arguments, $domain, $locale);
        }

        return '<trans data-locale="'.$locale.'" data-key="'.$message.'">'.$this->translator->trans($message, $arguments, $domain, $locale).'</trans>';
    }

    public function transchoice($message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        if (array_key_exists('silent', $arguments)) {
            return $this->translator->transChoice($message, $count, array_merge(['%count%' => $count], $arguments), $domain, $locale);
        }

        return '<trans data-locale="'.$locale.'" data-key="'.$message.'">'.$this->translator->transChoice($message, $count, array_merge(['%count%' => $count], $arguments), $domain, $locale).'</trans>';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translator';
    }
}
