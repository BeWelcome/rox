<?php

namespace App\Service;

use App\Doctrine\DomainType;
use App\Entity\Member;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\Formatter\IntlFormatterInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * Translation mode (translators with the Words right): the texts the page translated, listed at the bottom of the
 * page, each with what the translator may do with it in the side panel: edit the translation, add a missing one or
 * create the English original. Once saved, render() gives the new texts so the page can show them in place.
 */
final class InPlaceTranslations
{
    public const string EDIT = 'edit';
    public const string ADD = 'add';
    public const string CREATE = 'create';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'translator.formatter')]
        private readonly MessageFormatterInterface&IntlFormatterInterface $formatter,
    ) {
    }

    /**
     * The texts translated so far, once per code and locale, in the order they were first used.
     *
     * @return list<array{code: string, domain: string, locale: string, state: int, text: string,
     *     action: ?string, occurrences: list<array{text: string, parameters: ?array<string, scalar|null>}>}>
     *     text: the text as it is shown (the first use); action: EDIT, ADD, CREATE or null (no rights);
     *     occurrences: each different text shown, with the parameters it was rendered with (null when they can't be
     *     sent back to render it again: objects, dates)
     */
    #[AsTwigFunction('in_place_translations')]
    public function getUsedTexts(Member $translator): array
    {
        if (!$this->translator instanceof DataCollectorTranslator) {
            return [];
        }

        $texts = [];
        foreach ($this->translator->getCollectedMessages() as $message) {
            $key = $message['locale'] . '|' . $message['id'];
            if (!isset($texts[$key])) {
                $texts[$key] = [
                    'code' => (string) $message['id'],
                    'domain' => (string) $message['domain'],
                    'locale' => (string) $message['locale'],
                    'state' => $message['state'],
                    'text' => $message['translation'],
                    'action' => $this->getAction($translator, $message['state'], (string) $message['locale']),
                    'occurrences' => [],
                ];
            }
            $occurrence = [
                'text' => $message['translation'],
                'parameters' => $this->getSendableParameters($message['parameters'] ?? []),
            ];
            if (!\in_array($occurrence, $texts[$key]['occurrences'], true)) {
                $texts[$key]['occurrences'][] = $occurrence;
            }
        }

        return array_values($texts);
    }

    /**
     * The text stored for the code in the locale (the English one if there is none), rendered once for each set of
     * parameters; null if there is no text for the code.
     *
     * @param list<array<string, scalar|null>> $parameterSets
     *
     * @return list<string>|null
     */
    public function render(string $code, string $locale, array $parameterSets): ?array
    {
        $repository = $this->entityManager->getRepository(Word::class);
        $word = $repository->findOneBy(['code' => $code, 'language' => $locale])
            ?? $repository->findOneBy(['code' => $code, 'language' => 'en']);
        if (null === $word) {
            return null;
        }

        $texts = [];
        foreach ($parameterSets as $parameters) {
            $texts[] = DomainType::ICU_MESSAGES === $word->getDomain()
                ? $this->formatter->formatIntl($word->getSentence(), $locale, $parameters)
                : $this->formatter->format($word->getSentence(), $locale, $parameters);
        }

        return $texts;
    }

    /**
     * Mirrors the rights the edit, add and create pages check.
     */
    private function getAction(Member $translator, int $state, string $locale): ?string
    {
        return match ($state) {
            DataCollectorTranslator::MESSAGE_DEFINED => $translator->hasRightsForLocale($locale) ? self::EDIT : null,
            DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK => $translator->hasRightsForLocale($locale) && 'en' !== $locale ? self::ADD : null,
            default => $translator->hasRightsForLocale('en') ? self::CREATE : null,
        };
    }

    /**
     * @param array<mixed> $parameters
     *
     * @return array<string, scalar|null>|null
     */
    private function getSendableParameters(array $parameters): ?array
    {
        foreach ($parameters as $value) {
            if (null !== $value && !\is_scalar($value)) {
                return null;
            }
        }

        return $parameters;
    }
}
