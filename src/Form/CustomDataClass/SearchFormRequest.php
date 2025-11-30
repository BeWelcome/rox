<?php

namespace App\Form\CustomDataClass;

use AnthonyMartin\GeoLocation\GeoPoint;
use SearchModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class SearchFormRequest.
 *
 * @SuppressWarnings("PHPMD")
 */
class SearchFormRequest
{
    #[NotNull(message: 'search.location.invalid', groups: ['text-search'])]
    public string $location = '';

    #[NotNull(message: 'search.location.dropdown', groups: ['text-search'])]
    public string $location_fullname;

    public string $location_name;

    public int $location_geoname_id;

    public string $location_latitude;

    public string $location_longitude;

    public bool $location_admin_unit;

    public ?float $ne_latitude = null;

    public ?float $ne_longitude = null;

    public ?float $sw_latitude = null;

    public ?float $sw_longitude = null;

    public bool $accommodation_anytime = true;

    public bool $accommodation_neverask = true;

    public bool $no_smoking = false;

    public bool $no_alcohol = false;

    public bool $no_drugs = false;

    public bool $show_map = false;

    public bool $show_options = true;

    #[Choice(choices: [-1, 0, 5, 10, 15, 20, 50, 100, 200])]
    public int $distance = 20;

    public ?bool $showOnMap = false;

    public ?bool $showOptions = false;

    public int $can_host = 1;

    public array $groups = [];

    public array $languages = [];

    public int $min_age = 0;

    public int $max_age = 120;

    public ?string $gender = null;

    public bool $offers_dinner = false;

    public bool $offers_tour = false;

    public bool $accessible = false;

    public bool $has_profile_picture = false;

    public bool $has_about_me = false;

    public bool $has_comments = false;

    public ?string $keywords = null;

    public int $last_login = 24;

    public int $order = 6;

    public int $direction = SearchModel::DIRECTION_DESCENDING;

    public int $items = 20;

    public int $page = 1;

    #[IsTrue(message: 'search.location.invalid', groups: ['text-search'])]
    public function isLocationValid(): bool
    {
        // Check if the form was submitted through the map javascript
        $showOnMap = (bool) $this->showOnMap;
        if (true === $showOnMap) {
            return true;
        }

        if (empty($this->location)) {
            // Empty location is never correct
            return false;
        }
        // Check if $location_geoname_id, $location_latitude and $location_longitude are set
        if (0 !== $this->location_geoname_id && $this->location_latitude && $this->location_longitude) {
            // The searchpicker set all necessary information
            return true;
        }

        // Searchpicker didn't get a chance to set the location information
        // \todo Try to find one based on the entered information in the location field

        return false;
    }

    #[IsTrue(message: 'search.accommodation.invalid', groups: ['text-search'])]
    public function isAccommodationValid(): bool
    {
        return $this->accommodation_anytime || $this->accommodation_neverask;
    }

    public function overrideFromRequest(Request $request)
    {
        return self::fillObjectFromRequest($this, $request);
    }

    public static function fromRequest(Request $request)
    {
        $searchFormRequest = new self();

        return self::fillObjectFromRequest($searchFormRequest, $request);
    }

    public static function determineValidationGroups(FormInterface $form)
    {
        $data = $form->getData();
        $showOnMap = (bool) $data->showOnMap;
        if (true === $showOnMap) {
            return ['map-search'];
        }

        return ['text-search'];
    }

    private static function fillObjectFromRequest(self $searchFormRequest, Request $request): self
    {
        $data = [];
        if ($request->query->has('tiny')) {
            $data = $request->query->all('tiny');
        }
        if ($request->query->has('home')) {
            $data = $request->query->all('home');
        }
        if ($request->query->has('search')) {
            $data = $request->query->all('search');
        }
        if ($request->query->has('map')) {
            $data = $request->query->all('map');
        }
        if (empty($data)) {
            // if no data was given return a default object
            return $searchFormRequest;
        }

        $searchFormRequest->location = self::get($data, 'location', '');
        $searchFormRequest->accommodation_anytime = self::get($data, 'accommodation_anytime', '1') ? true : false;
        $searchFormRequest->accommodation_neverask = self::get($data, 'accommodation_neverask', '0') ? true : false;
        $searchFormRequest->can_host = self::get($data, 'can_host', 1);
        $searchFormRequest->distance = self::get($data, 'distance', 20);
        $searchFormRequest->keywords = self::get($data, 'keywords', '');
        $searchFormRequest->page = $request->query->get('page', 1);
        $searchFormRequest->groups = self::get($data, 'groups', []);
        $searchFormRequest->languages = self::get($data, 'languages', []);
        $searchFormRequest->last_login = self::get($data, 'last_login', 12);
        $searchFormRequest->location_geoname_id = self::get($data, 'location_geoname_id', null);
        $searchFormRequest->location_latitude = self::get($data, 'location_latitude', null);
        $searchFormRequest->location_longitude = self::get($data, 'location_longitude', null);
        $searchFormRequest->location_admin_unit = self::get($data, 'location_admin_unit', false);
        $searchFormRequest->min_age = self::get($data, 'min_age', null);
        $searchFormRequest->max_age = self::get($data, 'max_age', null);
        $searchFormRequest->gender = self::get($data, 'gender', null);
        $searchFormRequest->order = self::get($data, 'order', SearchModel::ORDER_ACCOMMODATION);
        $searchFormRequest->direction = self::get($data, 'direction', SearchModel::DIRECTION_ASCENDING);
        $searchFormRequest->items = self::get($data, 'items', 10);
        $searchFormRequest->show_map = self::get($data, 'show_map', '0') ? true : false;
        $searchFormRequest->showOnMap = self::get($data, 'showOnMap', false);
        $searchFormRequest->showOptions = self::get($data, 'show_options', false);
        $searchFormRequest->offers_dinner = '1' === self::get($data, 'offers_dinner', '0');
        $searchFormRequest->offers_tour = '1' === self::get($data, 'offers_tour', '0');
        $searchFormRequest->accessible = '1' === self::get($data, 'accessible', '0');
        $searchFormRequest->has_profile_picture = '1' === self::get($data, 'has_profile_picture', '0');
        $searchFormRequest->has_about_me = '1' === self::get($data, 'has_about_me', '0');
        $searchFormRequest->no_smoking = '1' === self::get($data, 'no_smoking', '0');
        $searchFormRequest->no_alcohol = '1' === self::get($data, 'no_alcohol', '0');
        $searchFormRequest->no_drugs = '1' === self::get($data, 'no_drugs', '0');
        $searchFormRequest->has_comments = '1' === self::get($data, 'has_comments', '0');

        if (
            null !== $searchFormRequest->location_geoname_id
            && 1 !== $searchFormRequest->location_admin_unit
            && -1 !== $searchFormRequest->distance
        ) {
            [$neLat, $neLng, $swLat, $swLng] = self::calculateBoundingBox(
                $searchFormRequest->location_latitude,
                $searchFormRequest->location_longitude,
                $searchFormRequest->distance
            );
            $searchFormRequest->ne_latitude = $neLat;
            $searchFormRequest->ne_longitude = $neLng;
            $searchFormRequest->sw_latitude = $swLat;
            $searchFormRequest->sw_longitude = $swLng;
        } else {
            $searchFormRequest->ne_latitude = self::get($data, 'ne_latitude', null);
            $searchFormRequest->ne_longitude = self::get($data, 'ne_longitude', null);
            $searchFormRequest->sw_latitude = self::get($data, 'sw_latitude', null);
            $searchFormRequest->sw_longitude = self::get($data, 'sw_longitude', null);
        }

        return $searchFormRequest;
    }

    private static function calculateBoundingBox($latitude, $longitude, $distance): array
    {
        $distance = (int) $distance;
        if (-1 === $distance) {
            $distance = 100;
        }

        $center = new GeoPoint($latitude, $longitude);
        $boundingBox = $center->boundingBox($distance, 'km');

        return [
            $boundingBox->getMinLatitude(),
            $boundingBox->getMinLongitude(),
            $boundingBox->getMaxLatitude(),
            $boundingBox->getMaxLongitude(),
        ];
    }

    private static function get($data, $index, $default)
    {
        return $data[$index] ?? $default;
    }
}
