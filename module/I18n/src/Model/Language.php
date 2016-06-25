<?php

namespace Rox\I18n\Model;

use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;

/**
 * @property int $id
 * @property string $ShortCode
 */
class Language extends AbstractModel
{
    /**
     * @var string
     */
    protected $table = 'languages';

    /**
     * @param string $shortCode
     *
     * @throws NotFoundException
     *
     * @return Language
     */
    public function getByShortCode($shortCode)
    {
        $q = $this->newQuery();

        $q->where('ShortCode', $shortCode);

        $language = $q->get()->first();

        if (!$language) {
            throw new NotFoundException();
        }

        return $language;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     *
     * @return Language
     */
    public function getById($id)
    {
        $q = $this->newQuery();

        $q->where('id', $id);

        $language = $q->get()->first();

        if (!$language) {
            throw new NotFoundException();
        }

        return $language;
    }
}
