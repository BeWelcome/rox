<?php
$activefieldset = "";
$defaultfieldset = "members";
$formkit = $this->layoutkit->formkit;
$callback_tags = $formkit->setPostCallback('AdminMassmailController', 'massmailEnqueueCallback');

$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    echo '<div class="error">';
    foreach ($errors as $error) {
        echo $words->get($error) . '<br />';
    }
    echo '</div>';
}

$vars = $this->getRedirectedMem('vars');
$action = $this->getRedirectedMem('action');
if (!empty($action)) {
    switch ($action) {
        case 'enqueueMembers' :
            $activefieldset = 'members';
            break;
        case 'enqueueLocation' :
            $activefieldset = 'location';
            break;
        case 'enqueueGroup' :
            $activefieldset = 'group';
            break;
        case 'enqueueLoginReminder' :
            $activefieldset = 'reminder';
            break;
        case 'enqueueSuggestionReminder' :
            $activefieldset = 'suggestionsreminder';
            break;
        case 'enqueueMailToConfirmReminder' :
            $activefieldset = 'mailtoconfirm';
            break;
        case 'enqueueTermsOfUse' :
            $activefieldset = 'termsofuse';
            break;
    }
} else {
    if ($this->canEnqueueMembers) {
        $defaultfieldset = 'members';
    } elseif ($this->canEnqueueLocation) {
        $defaultfieldset = 'location';
    } elseif ($this->canEnqueueGroup) {
        $defaultfieldset = 'group';
    } elseif ($this->canEnqueueReminder) {
        $defaultfieldset = 'reminder';
    } elseif ($this->canEnqueueTermsOfUse) {
        $defaultfieldset = 'termsofuse';
    }
}

$words = new MOD_words();
?>
<div id="adminmassmailenqueue" class="w-100">
    <form method="post" name="mass-mail-enqueue-form" id="mass-mail-enqueue-form" class="w-100"
          enctype="multipart/form-data">
        <?php echo $callback_tags; ?>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>"/>
        <ul class="nav nav-tabs" id="enqueueTabs" role="tablist">
            <?php if ($this->canEnqueueMembers) { ?>
            <li class="nav-item">
                <a class="nav-link active" id="members-tab" data-toggle="tab" href="#members" aria-controls="members"
                   aria-selected="true">
                    <?php echo $words->getBuffered('AdminMassMailEnqueueMembers'); ?>
                </a>
            </li>
            <?php } ?>
            <?php if ($this->canEnqueueLocation) { ?>
            <li class="nav-item">
                <a class="nav-link" id="location-tab" data-toggle="tab" href="#location" role="tab"
                   aria-controls="location" aria-selected="true">
                    <?php echo $words->getBuffered('AdminMassMailEnqueueLocation'); ?>
                </a>
            </li>
            <?php } ?>
            <?php if ($this->canEnqueueGroup) { ?>
                <li class="nav-item">
                    <a class="nav-link" id="group-tab" data-toggle="tab" href="#group" role="tab"
                       aria-controls="group" aria-selected="true">
                        <?php echo $words->getBuffered('AdminMassMailEnqueueGroup'); ?>
                    </a>
                </li>
            <?php } ?>
            <?php if ($this->type == 'RemindToLog' && $this->canEnqueueReminder) { ?>
            <li class="nav-item">
                <a class="nav-link" id="reminder-tab" data-toggle="tab" href="#reminder" role="tab"
                   aria-controls="reminder" aria-selected="true">
                    <?php echo $words->getBuffered('AdminMassMailEnqueueMailToConfirmReminder'); ?>
                </a>
            </li>
            <?php } ?>
            <?php if ($this->type == 'TermsOfUse' && $this->canEnqueueTermsOfUse) { ?>
                <li class="nav-item">
                    <a class="nav-link" id="termsofuse-tab" data-toggle="tab" href="#termsofuse" role="tab"
                       aria-controls="termsofuse" aria-selected="true">
                        <?php echo $words->getBuffered('AdminMassMailEnqueueTermsOfUse'); ?>
                    </a>
                </li>
            <?php } ?>
            </ul>
        <div class="tab-content" id="myTabContent">
            <?php if ($this->canEnqueueMembers) { ?>
            <div class="tab-pane fade show active mt-2" id="members" role="tabpanel" aria-labelledby="members-tab">
                <div class="form-check">
                        <input class="form-check-input" type="radio" id="allmembers" name="members-type" value="allmembers"
                                <?php if (isset($vars['members-type']) && ($vars['members-type'] == 'allmembers')) {
                                    echo 'checked="checked"';
                                }
                                ?>/>
                            <label class="form-check-label"
                                for="allmembers"><?php echo $words->get('AdminMassMailEnqueueAllMembers'); ?></label>
                        <div class="form-group mt-2"><label for="maxmembers"><?php echo $words->get('AdminMassMailEnqueueMaxMessages'); ?>
                                :</label>
                        <input class="o-input" type="text" id="max-messages" name="max-messages" size="60"
                                   value="<?php if (isset($vars['max-messages'])) {
                                        echo $vars['max-messages'];
                                   } ?>"/>
                        </div>
                </div>
                <div class="form-check mt-2">
                <input class="form-check-input" type="radio" id="selectedmembers" name="members-type" value="usernames"
                                <?php
                                if (isset($vars['members-type'])) {
                                    if (($vars['members-type'] == 'usernames')) {
                                        echo 'checked="checked"';
                                    }
                                } else {
                                    echo 'checked="checked"';
                                }
                                ?>/>
                <label class="form-check-label"
                    for="selectedmembers"><?php echo $words->get('AdminMassMailEnqueueSelectedMembers'); ?></label>
                <div class="form-group mt-2">
                <label for="Usernames"><?php echo $words->get('AdminMassMailEnqueueUsernames'); ?>:</label>
                <input class="o-input" type="text" id="usernames" name="usernames" size="60"
                                   value="<?php if (isset($vars['usernames'])) {
                                       echo $vars['usernames'];
                                   } ?>"/>
                            <small class="text-muted"><?php echo $words->get('AdminMassMailEnqueueUsernamesInfo'); ?></small>
                </div>
                </div>
                <div><input class="btn btn-primary float-right" type="submit" name="enqueuemembers"
                                                     value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitMembers'); ?>"/><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->canEnqueueLocation) { ?>
            <div class="tab-pane fade show mt-2" id="location" role="tabpanel" aria-labelledby="location-tab">
                <div class="form-group">
                    <label class="o-input-label" for="CountryIsoCode">Choose a country</label><br>
                    <select id="CountryIsoCode" name="CountryIsoCode" class="o-input select2">
                        <option value="0">Select a country</option>
                        <?php
                        foreach ($countries as $country) {
                            echo '<option value="' . $country->country . '">' . $country->name . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="o-input-label"AdminUnits">Choose an administrative unit</label>
                    <select id="AdminUnits" name="AdminUnits" class="o-input select2" disabled="disabled">
                        <option value="0">All administrative units</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="o-input-label" for="Places">Choose a place:</label>
                    <select id="Places" name="Places" class="o-input select2" disabled="disabled">
                        <option value="0">All places</option>
                    </select>
                </div>
                <div>
                    <input class="btn btn-primary float-right" type="submit" name="enqueuelocation"
                        value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitLocation'); ?>"/>
                    <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->canEnqueueGroup) { ?>
            <div class="tab-pane fade show mt-2" role="tabpanel" aria-labelledby="group-tab" id="group">
                <div class="form-group">
                    <label class="o-input-label" for="IdGroup">Choose a group</label>
                    <select id="IdGroup" name="IdGroup" class="o-input select2" ;>
                        <option value="0">Select a group</option>
                        <?php
                        foreach ($groups as $group) {
                            echo '<option value="' . $group->id . '">' . $group->Name . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="float_right"><br/><input class="button" type="submit" name="enqueuegroup"
                                                     value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitGroup'); ?>"/><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->type == 'RemindToLog' && $this->canEnqueueReminder) { ?>
            <div  class="tab-pane fade show" role="tabpanel" aria-labelledby="reminder-tab" id="reminder">
                <div class="type-text">
                    <?php echo $words->get('AdminMassMailEnqueueReminderInfo'); ?>
                </div>
                <div class="float_right"><br/><input class="button" type="submit" name="enqueuereminder"
                                                     value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitReminder'); ?>"/><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->type == 'MailToConfirmReminder' && $this->canEnqueueMailToConfirmReminder) { ?>
            <div class="tab-pane fade show" role="tabpanel" aria-labelledby="mailtoconfirm-tab" id="mailtoconfirm">
                <div class="type-text">
                    <?php echo $words->get('AdminMassMailEnqueueMailToConfirmReminderInfo', $this->mailToConfirmCount); ?>
                </div>
                <div class="float_right"><br/><input class="button" type="submit" name="enqueuemailtoconfirmreminder"
                                                     value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitMailToConfirmReminder'); ?>"/><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->type == 'TermsOfUse' && $this->canEnqueueTermsOfUse) { ?>
            <div class="tab-pane fade show mt-2" role="tabpanel" aria-labelledby="termsofuse-tab" id="termsofuse">
                <div class="type-text">
                    <?php echo $words->get('AdminMassMailEnqueueTermsOfUseInfo'); ?>
                </div>
                <div class="float_right"><br/><input class="button" type="submit" name="enqueuetermsofuse"
                                                     value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitTermsOfUse'); ?>"/><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        <?php } ?>
        </div>
    </form>
    <script type="text/javascript">
        $( document ).ready(function() {
            // geo dropdown stuff
            let countries = $('#CountryIsoCode');
            let adminUnits = $('#AdminUnits');
            let places = $('#Places');
            countries.change(function () {
                var value = countries.val();
                // clear admin units and places list
                adminUnits.empty().append('<option selected="selected" value="0">All administrative units</option>');
                places.empty().append('<option selected="selected" value="0">All places</option>');
                if (value === 0) {
                    adminUnits.attr('disabled', 'disabled');
                    places.attr('disabled', 'disabled');
                } else {
                    // and rebuild the admin units select with the admin units for the selected country
                    $.getJSON('admin/massmail/getadminunits/' + value, function (data) {
                        adminUnits.select2('destroy');
                        adminUnits.removeAttr('data-select2-id');
                        var html = '';
                        var len = data.length;
                        for (var i = 0; i < len; i++) {
                            html += '<option value="' + data[i].admin1 + '">' + data[i].name + '</option>';
                        }
                        adminUnits.append(html);
                        adminUnits.select2({
                            theme: 'bootstrap4',
                            containerCssClass: 'form-control'
                        });
                    });
                    adminUnits.removeAttr('disabled');
                }
            });
            adminUnits.change(function () {
                var value = adminUnits.val();
                // clear places list
                places.empty().append('<option selected="selected" value="0">All places</option>');
                if (value === 0) {
                    places.attr('disabled', 'disabled');
                } else {
                    $.getJSON('admin/massmail/getplaces/' + $('#CountryIsoCode').val() + '/' + value, function (data) {
                        places.select2('destroy');
                        places.removeAttr('data-select2-id');
                        var html = '';
                        var len = data.length;
                        for (var i = 0; i < len; i++) {
                            html += '<option value="' + data[i].geonameId + '">' + data[i].name + '</option>';
                        }
                        places.append(html);
                        places.select2({
                            theme: 'bootstrap4',
                            containerCssClass: 'form-control'
                        });
                    });
                    places.removeAttr('disabled');
                }
            });
        });
    </script>
</div>
