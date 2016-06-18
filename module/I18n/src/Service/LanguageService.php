<?php

namespace Rox\I18n\Service;

use Doctrine\Common\Cache\Cache;
use Illuminate\Database\Eloquent\Collection;
use Rox\I18n\Model\Language;

class LanguageService
{
    /**
     * @var Language
     */
    protected $language;

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct(Language $language, Cache $cache)
    {
        $this->language = $language;
        $this->cache = $cache;
    }

    /**
     * Returns languages available for website translation, which is indicated
     * by existence of the 'WelcomeToSignup' translation.
     *
     * This function doesn't necessarily reflect which languages members may
     * translate their profiles into, and may need to be renamed accordingly.
     *
     * Result is cached for 24 hours due to infrequent changes.
     *
     * @return Collection|Language[]
     */
    public function getAvailableLanguages()
    {
        if ($languages = $this->cache->fetch(__METHOD__)) {
            return $languages;
        }

        $q = $this->language->newQuery();

        $q->selectRaw('DISTINCT languages.*');

        $q->join('words', 'languages.id', '=', 'words.IdLanguage');

        $q->where('words.code', 'WelcomeToSignup');

        $q->orderBy('FlagSortCriteria');

        $languages = $q->get();

        $this->cache->save(__METHOD__, $languages, 60 * 60 * 24);

        return $languages;
    }
}
