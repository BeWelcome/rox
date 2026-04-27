<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\Form\FormInterface;
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

    public string $location_geoname_id;

    public string $location_latitude;

    public string $location_longitude;

    public bool $location_admin_unit;

    public ?float $min_latitude = null;

    public ?float $min_longitude = null;

    public ?float $max_latitude = null;

    public ?float $max_longitude = null;

    public bool $accommodation_yes = true;

    public bool $accommodation_no = true;

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

    public ?int $min_age = null;

    public ?int $max_age = null;

    public array $gender = [];

    public bool $offers_dinner = false;

    public bool $offers_tour = false;

    public bool $accessible = false;

    public bool $has_profile_picture = false;

    public bool $has_about_me = false;

    public bool $has_comments = false;

    public ?string $keywords = null;

    public int $last_active = 24;

    public int $order = 6;

    public int $direction = \App\Repository\MemberRepository::DIRECTION_DESCENDING;

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
        return $this->accommodation_yes || $this->accommodation_no;
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
}
