<?php

namespace Rox\Member\Service;

use DateTime;
use Rox\Core\Exception\NotFoundException;
use Rox\Member\Model\Member;
use Rox\Member\Model\Preference;
use Rox\Member\Model\Preference as PreferenceRepository;

/**
 * Rox defines a set of available member preferences. Members can have preferred
 * values for each of these preferences via a join/association table.
 */
class PreferenceService
{
    const PREF_LANG = 'PreferenceLanguage';

    /**
     * @var PreferenceRepository
     */
    protected $preferenceRepository;

    /**
     * @param PreferenceRepository $preferenceRepository
     */
    public function __construct(PreferenceRepository $preferenceRepository)
    {
        $this->preferenceRepository = $preferenceRepository;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     *
     * @return Preference
     */
    public function getDefinitionByCode($code)
    {
        // Get the preference definition.
        $preference = $this->preferenceRepository->newQuery()
            ->where('codeName', $code)->first();

        if (!$preference) {
            // If the preference definition doesn't exist then there is nothing
            // to link the preference to.
            throw new NotFoundException();
        }

        return $preference;
    }

    /**
     * @param Member               $member
     * @param PreferenceRepository $preferenceDefinition
     *
     * @throws NotFoundException
     *
     * @return Preference
     */
    public function getMemberPreference(Member $member, Preference $preferenceDefinition)
    {
        $preference = $member->preferences()
            ->wherePivot('IdPreference', $preferenceDefinition->id)
            ->get()->first();

        if (!$preference) {
            throw new NotFoundException();
        }

        return $preference;
    }

    /**
     * Helper function for getting a single member preference without a) having
     * to first get the preference definition, or, b) getting all preferences
     * and discarding those unwanted.
     *
     * @param Member $member
     * @param string $code
     *
     * @throws NotFoundException
     *
     * @return Preference
     */
    public function getMemberPreferenceByCode(Member $member, $code)
    {
        $relation = $member->preferences();

        $relation->getQuery()->where('codeName', $code);

        $preference = $relation->get()->first();

        if (!$preference) {
            throw new NotFoundException();
        }

        return $preference;
    }

    /**
     * Given a member and an available preference definition, set a preferred
     * value.
     *
     * If a Preference definition is already associated to a member, the value
     * will be updated, otherwise it is created for that user.
     *
     * @param Member               $member
     * @param PreferenceRepository $preferenceDefinition
     * @param string               $value
     */
    public function setMemberPreference(Member $member, Preference $preferenceDefinition, $value)
    {
        // Get the preference object which records the member language
        $preferencePivot = $member->preferences
            ->where('id', $preferenceDefinition->id)->first();

        // If it exists, just update it
        if ($preferencePivot) {
            // Use the ID from it to update
            $member->preferences()->updateExistingPivot($preferencePivot->id, [
                'Value' => $value,
            ]);

            return;
        }

        // Otherwise create a new preference object for the language
        $pivot = $member->preferences()->newPivot([
            'IdMember' => $member->id,
            'IdPreference' => $preferenceDefinition->id,
            'Value' => $value,
            'created' => new DateTime(),
            'updated' => new DateTime(),
        ]);

        // The 'pivot' record represents the new language preference object.
        // It is saved with the timestamps set manually because Eloquent
        // doesn't support the field names being other than 'created_at' and
        // 'updated_at'
        $pivot->save([
            'timestamps' => false,
        ]);
    }
}
