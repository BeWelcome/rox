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
     * @var EntityManager
     */
    private $em;

    /**
     * SearchFormRequest constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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

    public static function fromRequest(Request $request, EntityManagerInterface $em)
    {
        $formRequest = new self($em);
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
            return $formRequest;
        }

        $formRequest->location = self::get($data, 'location', '');
        $formRequest->accommodation_anytime = self::get($data, 'accommodation_anytime', '1') ? true : false;
        $formRequest->accommodation_neverask = self::get($data, 'accommodation_neverask', '0') ? true : false;
        $formRequest->can_host = self::get($data, 'can_host', 1);
        $formRequest->distance = self::get($data, 'distance', 20);
        $formRequest->keywords = self::get($data, 'keywords', '');
        $formRequest->page = $request->query->get('page', 1);
        $formRequest->groups = self::get($data, 'groups', []);
        $formRequest->languages = self::get($data, 'languages', []);
        $formRequest->last_login = self::get($data, 'last_login', 12);
        $formRequest->location_geoname_id = self::get($data, 'location_geoname_id', null);
        $formRequest->location_latitude = self::get($data, 'location_latitude', null);
        $formRequest->location_longitude = self::get($data, 'location_longitude', null);
        $formRequest->location_admin_unit = self::get($data, 'location_admin_unit', false);
        $formRequest->min_age = self::get($data, 'min_age', null);
        $formRequest->max_age = self::get($data, 'max_age', null);
        $formRequest->gender = self::get($data, 'gender', null);
        $formRequest->order = self::get($data, 'order', SearchModel::ORDER_ACCOM);
        $formRequest->direction = self::get($data, 'direction', SearchModel::DIRECTION_ASCENDING);
        $formRequest->items = self::get($data, 'items', 10);
        $formRequest->show_map = self::get($data, 'show_map', '0') ? true : false;
        $formRequest->showOnMap = self::get($data, 'showOnMap', false);
        $formRequest->showOptions = self::get($data, 'show_options', false);
        $formRequest->offerdinner = '1' === self::get($data, 'offerdinner', '0');
        $formRequest->offertour = '1' === self::get($data, 'offertour', '0');
        $formRequest->accessible = '1' === self::get($data, 'accessible', '0');
        $formRequest->profile_picture = '1' === self::get($data, 'profile_picture', '0');
        $formRequest->about_me = '1' === self::get($data, 'about_me', '0');
        $formRequest->no_smoking = '1' === self::get($data, 'no_smoking', '0');
        $formRequest->no_alcohol = '1' === self::get($data, 'no_alcohol', '0');
        $formRequest->no_drugs = '1' === self::get($data, 'no_drugs', '0');
        $formRequest->has_comments = '1' === self::get($data, 'has_comments', '0');

        if (
            null !== $formRequest->location_geoname_id
            && 1 !== $formRequest->location_admin_unit
            && -1 !== $formRequest->distance
        ) {
            list($neLat, $neLng, $swLat, $swLng) = self::calculateBoundingBox(
                $formRequest->location_latitude,
                $formRequest->location_longitude,
                $formRequest->distance
            );
            $formRequest->ne_latitude = $neLat;
            $formRequest->ne_longitude = $neLng;
            $formRequest->sw_latitude = $swLat;
            $formRequest->sw_longitude = $swLng;
        } else {
            $formRequest->ne_latitude = self::get($data, 'ne_latitude', null);
            $formRequest->ne_longitude = self::get($data, 'ne_longitude', null);
            $formRequest->sw_latitude = self::get($data, 'sw_latitude', null);
            $formRequest->sw_longitude = self::get($data, 'sw_longitude', null);
        }

        return $formRequest;
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
