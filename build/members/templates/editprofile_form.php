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
<div class="col-12 mb-3">
    <?php if ($this->adminedit) : ?>
        <?= $words->get('ProfileStatus') ?>:
        <select id="Status" name="Status">
            <?php echo $statusOptions; ?>
        </select>
    <?php endif; ?>
</div>

<div class="col-12 mb-3" id="editProfile" data-children=".item">
    <div class="item"><?php include_once 'editprofile_form_basics.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_aboutme.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_accommodation.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_myinterests.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_languages.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_contactinfo.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_travel.php'; ?></div>
    <div class="item"><?php include_once 'editprofile_form_family.php'; ?></div>
</div>
<div class="col-12">
    <input type="submit" class="btn btn-primary float-right" name="submit"
           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
</div>
