<?php

namespace AppBundle\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchFormRequest
 * @package AppBundle\Form\CustomDataClass
 * @SuppressWarnings(PHPMD)
 */
class SearchFormRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $location;

    /** @var integer */
    public $geoname_id;

    /** @var double */
    public $latitude;

    /** @var double */
    public $longitude;

    /** @var boolean */
    public $accommodation_anytime = true;

    /** @var boolean */
    public $accommodation_dependonrequest = true;

    /** @var boolean */
    public $accommodation_neverask = true;

    /** @var integer */
    public $distance = 5;

    /** @var integer */
    public $can_host = 1;

    /** @var integer */
    public $page = 1;
}