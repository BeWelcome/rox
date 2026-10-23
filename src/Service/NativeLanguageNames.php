<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides the name of each supported language in that language itself (e.g. 'de' => 'Deutsch').
 *
 * Translating 'lang_xx' into locale 'xx' for every locale forces the translator to load all locale
 * catalogues on each request. The resulting map is therefore cached and only rebuilt when translations
 * are refreshed (see TranslationModel) or the cache expires.
 */
class NativeLanguageNames
{
    private const string CACHE_KEY_PREFIX = 'native_language_names.';
    private const int CACHE_LIFETIME = 3600;

    /** @var array<string, string>|null */
    private ?array $names = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CacheInterface $cache,
        private readonly array $locales,
    ) {
    }

    public function get(string $locale): string
    {
        $names = $this->all();

        return $names[$locale] ?? $this->translate($locale);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->names ??= $this->cache->get($this->cacheKey(), function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_LIFETIME);

            $names = [];
            foreach ($this->locales as $locale) {
                $names[$locale] = $this->translate($locale);
            }

            return $names;
        });
    }

    public function clear(): void
    {
        $this->names = null;
        $this->cache->delete($this->cacheKey());
    }

    private function translate(string $locale): string
    {
        return $this->translator->trans(strtolower('lang_' . $locale), [], null, $locale);
    }

    private function cacheKey(): string
    {
        // Include the locale list so changing LOCALES never serves an outdated map.
        return self::CACHE_KEY_PREFIX . md5(implode(',', $this->locales));
    }
}
