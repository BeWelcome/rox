<?php
// Error shortcuts
if (in_array('SignupErrorInvalidBirthDate', $vars['errors'])) {
    $errorBirthDate = true;
}
if (in_array('SignupErrorInvalidFirstName', $vars['errors'])) {
    $errorFirstName = true;
}
if (in_array('SignupErrorInvalidLastName', $vars['errors'])) {
    $errorLastName = true;
}
if (in_array('SignupErrorInvalidStreet', $vars['errors'])) {
    $errorStreet = true;
}
if (in_array('SignupErrorInvalidHouseNumber', $vars['errors'])) {
    $errorHouseNumber = true;
}
if (in_array('SignupErrorInvalidZip', $vars['errors'])) {
    $errorZip = true;
}
if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
    $errorEmail = true;
}

?>
<div class="col-12">
    <?php if ($this->adminedit) : ?>
        <?= $words->get('ProfileStatus') ?>:
        <select id="Status" name="Status">
            <?php echo $statusOptions; ?>
        </select>
    <?php endif; ?>
</div>

<div class="col-12 mt-3" id="editProfileTab">
    <ul class="nav nav-tabs flex-column flex-md-row" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="basics-tab" data-toggle="tab"
               href="#basics" role="tab" aria-controls="basics"
               aria-selected="false"><?= $words->get('Home') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="aboutme-tab" data-toggle="tab"
               href="#aboutme" role="tab" aria-controls="aboutme"
               aria-selected="false"><?= $words->get('ProfileSummary') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="accommodation-tab" data-toggle="tab"
               href="#accommodation" role="tab"
               aria-controls="contact" aria-selected="false"><?= $words->get('ProfileAccommodation') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="myinterests-tab" data-toggle="tab"
               href="#myinterests" role="tab"
               aria-controls="contact" aria-selected="false"><?= $words->get('ProfileInterests') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="languages-tab" data-toggle="tab"
               href="#languages" role="tab"
               aria-controls="contact" aria-selected="true"><?= $words->get('Languages') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="contactinfo-tab" data-toggle="tab"
               href="#contactinfo" role="tab"
               aria-controls="contact" aria-selected="false"><?= $words->get('ContactInfo') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="travel-tab" data-toggle="tab" href="#travel"
               role="tab" aria-controls="contact"
               aria-selected="false"><?= $words->get('ProfileTravelExperience') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="family-tab" data-toggle="tab" href="#family"
               role="tab" aria-controls="contact"
               aria-selected="false"><?= $words->get('MyRelations') ?></a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <?php
        include_once 'editprofile_form_basics.php';
         include_once 'editprofile_form_aboutme.php';
         include_once 'editprofile_form_accommodation.php';
         include_once 'editprofile_form_myinterests.php';
         include_once 'editprofile_form_languages.php';
         include_once 'editprofile_form_contactinfo.php';
         include_once 'editprofile_form_travel.php';
         include_once 'editprofile_form_family.php';
         ?>
    </div>
</div>
