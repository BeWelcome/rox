<?php

namespace AppBundle\Form\CustomDataClass;

use Doctrine\ORM\PersistentCollection;
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
     * @Assert\NotBlank()
     */
    public $location;

    /** @var int */
    public $location_geoname_id;

    /** @var float */
    public $location_latitude;

    /** @var float */
    public $location_longitude;

    /** @var bool */
    public $accommodation_anytime = true;

    /** @var bool */
    public $accommodation_dependonrequest = true;

    /** @var bool */
    public $accommodation_neverask = false;

    /** @var int */
    public $distance = 5;

    /** @var int */
    public $can_host = 1;

    /** @var int */
    public $page = 1;

    /** @var PersistentCollection */
    public $groups;

    /** @var PersistentCollection */
    public $languages;

    /** @var int */
    public $min_age;

    /** @var int */
    public $max_age;

    /** @var string */
    public $gender;

    /** @var bool */
    public $inactive;

    /** @var bool */
    public $offerdinner;

    /** @var bool */
    public $offertour;

    /** @var bool */
    public $accessible;

    /** @var string */
    public $keywords;

    /** @var int */
    public $order = 6;

    /** @var int */
    public $items = 20;

    public static function fromRequest(Request $request)
    {
        $searchFormRequest = new self();
        $searchFormRequest->location = $request->query->get('location');
        $searchFormRequest->accommodation_anytime = $request->query->get('accommodation_anytim');
        $searchFormRequest->accommodation_dependonrequest = $request->query->get('accommodation_dependonrequest');
        $searchFormRequest->accommodation_neverask = $request->query->get('accommodation_neverask');
        $searchFormRequest->can_host = $request->query->get('can_host');
        $searchFormRequest->distance = $request->query->get('distance');
        $searchFormRequest->keywords = $request->query->get('keywords');
        $searchFormRequest->page = $request->query->get('page');
        $searchFormRequest->groups = $request->query->get('groups');
        $searchFormRequest->languages = $request->query->get('languages');
        $searchFormRequest->inactive = $request->query->get('inactive');
        $searchFormRequest->location_geoname_id = $request->query->get('location_genoname_id');
        $searchFormRequest->location_latitude = $request->query->get('location_latitude');
        $searchFormRequest->location_longitude = $request->query->get('location_longitude');
        $searchFormRequest->min_age = $request->query->get('min_age');
        $searchFormRequest->max_age = $request->query->get('max_age');
        $searchFormRequest->gender = $request->query->get('gender');
        $searchFormRequest->order = $request->query->get('order');
        $searchFormRequest->items = $request->query->get('items');
        $searchFormRequest->offerdinner = $request->query->get('dinner');
        $searchFormRequest->offertour = $request->query->get('tour');
        $searchFormRequest->accessible = $request->query->get('accessible');

        return $searchFormRequest;
    }
}
