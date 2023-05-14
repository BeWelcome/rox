<?php

namespace App\Form\CustomDataClass;

use AnthonyMartin\GeoLocation\GeoPoint;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use SearchModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchFormRequest.
 *
 * @SuppressWarnings(PHPMD)
 */
class SearchFormRequest
{
    /**
     * @var string
     * @Assert\NotNull(message="search.location.invalid", groups={"text-search"})
     */
    public $location;

    /**
     * @var int
     * @Assert\NotNull(message="search.location.dropdown", groups={"text-search"})
     */
    public $location_fullname;

    public $location_name;

    public $location_geoname_id;

    /**
     * @var float
     */
    public $location_latitude;

    /**
     * @var float
     */
    public $location_longitude;

    /**
     * @var bool
     */
    public $location_admin_unit;

    /**
     * @var float
     */
    public $ne_latitude;

    /**
     * @var float
     */
    public $ne_longitude;

    /**
     * @var float
     */
    public $sw_latitude;

    /**
     * @var float
     */
    public $sw_longitude;

    /** @var bool */
    public $accommodation_anytime = true;

    /** @var bool */
    public $accommodation_neverask = true;

    /** @var bool */
    public $no_smoking = false;

    /** @var bool */
    public $no_alcohol = false;

    /** @var bool */
    public $no_drugs = false;

    /** @var bool */
    public $show_map = false;

    /** @var bool */
    public $show_options = true;

    /**
     * @var int
     *
     * @Assert\Choice({ -1, 0, 5, 10, 15, 20, 50, 100, 200})
     */
    public $distance = 20;

    /**
     * @var int
     */
    public $showOnMap = false;

    /**
     * @var bool
     */
    public $showOptions = false;

    /** @var int */
    public $can_host = 1;

    /** @var PersistentCollection */
    public $groups;

    /** @var PersistentCollection */
    public $languages;

    /** @var int */
    public $min_age = 0;

    /** @var int */
    public $max_age = 120;

    /** @var string */
    public $gender;

    /** @var bool */
    public $offerdinner;

    /** @var bool */
    public $offertour;

    /** @var bool */
    public $accessible = false;

    /** @var bool */
    public $profile_picture = false;

    /** @var bool */
    public $about_me = false;

    /** @var bool */
    public $has_comments;

    /** @var string */
    public $keywords;

    /** @var int Last Login in months */
    public $last_login = 24;

    /** @var int */
    public $order = 6;

    /** @var int */
    public $direction = SearchModel::DIRECTION_DESCENDING;

    /** @var int */
    public $items = 20;

    /**
     * @Assert\IsTrue(message="search.location.invalid", groups={"text-search"})
     */
    public function isLocationValid()
    {
        // Check if the form was submitted through the map javascript
        $showOnMap = (bool) ($this->showOnMap);
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

    /**
     * @Assert\IsTrue(message="search.accommodation.invalid", groups={"text-search"})
     */
    public function isAccommodationValid()
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

    private static function fillObjectFromRequest(self $searchFormRequest, Request $request): self
    {
        $data = [];
        if ($request->query->has('tiny')) {
            $data = $request->query->get('tiny');
        }
        if ($request->query->has('home')) {
            $data = $request->query->get('home');
        }
        if ($request->query->has('search')) {
            $data = $request->query->get('search');
        }
        if ($request->query->has('map')) {
            $data = $request->query->get('map');
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
        $searchFormRequest->order = self::get($data, 'order', SearchModel::ORDER_ACCOM);
        $searchFormRequest->direction = self::get($data, 'direction', SearchModel::DIRECTION_ASCENDING);
        $searchFormRequest->items = self::get($data, 'items', 10);
        $searchFormRequest->show_map = self::get($data, 'show_map', '0') ? true : false;
        $searchFormRequest->showOnMap = self::get($data, 'showOnMap', false);
        $searchFormRequest->showOptions = self::get($data, 'show_options', false);
        $searchFormRequest->offerdinner = '1' === self::get($data, 'offerdinner', '0');
        $searchFormRequest->offertour = '1' === self::get($data, 'offertour', '0');
        $searchFormRequest->accessible = '1' === self::get($data, 'accessible', '0');
        $searchFormRequest->profile_picture = '1' === self::get($data, 'profile_picture', '0');
        $searchFormRequest->about_me = '1' === self::get($data, 'about_me', '0');
        $searchFormRequest->no_smoking = '1' === self::get($data, 'no_smoking', '0');
        $searchFormRequest->no_alcohol = '1' === self::get($data, 'no_alcohol', '0');
        $searchFormRequest->no_drugs = '1' === self::get($data, 'no_drugs', '0');
        $searchFormRequest->has_comments = '1' === self::get($data, 'has_comments', '0');

        if (
            null !== $searchFormRequest->location_geoname_id
            && 1 !== $searchFormRequest->location_admin_unit
            && -1 !== $searchFormRequest->distance
            && is_float($searchFormRequest->location_latitude)
            && is_float($searchFormRequest->location_longitude)
        ) {
            list($neLat, $neLng, $swLat, $swLng) = self::calculateBoundingBox(
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

    public static function determineValidationGroups(FormInterface $form)
    {
        $data = $form->getData();
        $showOnMap = (bool) ($data->showOnMap);
        if (true === $showOnMap) {
            return ['map-search'];
        }

        return ['text-search'];
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
        return (isset($data[$index])) ? $data[$index] : $default;
    }
}
