<?php
    $activefieldset = "";
    $defaultfieldset = "members";
    $formkit = $this->layoutkit->formkit;
    $callback_tags = $formkit->setPostCallback('AdminController', 'massmailEnqueueCallback');

    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors)) {
        echo '<div class="error">';
        foreach($errors as $error) {
            echo $words->get($error) . '<br />';
        }
      echo '</div>';
    }
    
    $vars = $this->getRedirectedMem('vars');
    $action = $this->getRedirectedMem('action');
    if (!empty($action)) {
        switch($action) {
            case 'enqueueMembers' : 
                $activefieldset = 'members';
                break;
            case 'enqueueLocation' : 
                $activefieldset = 'location';
                break;
            case 'enqueueGroup' : 
                $activefieldset = 'group';
                break;
            case 'enqueueVote' : 
                $activefieldset = 'vote';
                break;
        }
    } else {
        if ($this->canEnqueueMembers) {
            $defaultfieldset =  'members';
        } elseif ($this->canEnqueueLocation) {
            $defaultfieldset =  'location';
        } elseif ($this->canEnqueueGroup) {
            $defaultfieldset =  'group';
        } elseif ($this->canEnqueueVote) {
            $defaultfieldset =  'members';
        }
    }
    
    $words = new MOD_words();
?>
<div id="adminmassmailenqueue">
<form method="post" name="mass-mail-enqueue-form" id="mass-mail-enqueue-form" class="fieldset-menu-form" enctype="multipart/form-data">
<?php echo $callback_tags; ?>
<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<?php if ($this->canEnqueueMembers) { ?>
<fieldset id="members">
    <legend><?php echo $words->getBuffered('AdminMassMailEnqueueMembers');?></legend>
    <table>
    <tr><td colspan="3"><?php echo $words->flushBuffer(); ?></td></tr>
    <tr>
    <td><input type="radio" id="allmembers" name="members-type" value="allmembers" 
    <?php if (isset($vars['members-type'])) {
        if (($vars['members-type'] == 'allmembers')) {
        echo 'checked="checked"';
    } } else {
        echo 'checked="checked"';
    }
    ?>
    /></td>
    <td colspan="2"><label for="allmembers"><?php echo $words->get('AdminMassMailEnqueueAllMembers'); ?></label></td>
    </tr>
    <tr>
    <td></td><td><label for="maxmembers"><?php echo $words->get('AdminMassMailEnqueueMaxMessages'); ?>:</label></td><td><input type="text" id="max-messages" name="max-messages" size="60" value="<?php if (isset($vars['max-messages'])) { echo $vars['max-messages']; }?>" /></td>
    </tr>
    <tr>
    <td><input type="radio" id="selectedmembers" name="members-type" value="usernames"     
    <?php if (isset($vars['members-type']) && ($vars['members-type'] == 'usernames')) {
        echo 'checked="checked"';
    } ?>/></td>
    <td colspan="2"><label for="selectedmembers"><?php echo $words->get('AdminMassMailEnqueueSelectedMembers'); ?></label></td>
    </tr>
    <tr>
    <td></td><td><label for="Usernames"><?php echo $words->get('AdminMassMailEnqueueUsernames'); ?>:</label></td><td><input type="text" id="usernames" name="usernames" size="60" value="<?php if (isset($vars['usernames'])) { echo $vars['usernames']; }?>" /></td>
    </tr>
    <tr>
    <td></td><td></td><td><strong class="small"><?php echo $words->get('AdminMassMailEnqueueUsernamesInfo');?></strong></td>
    </tr>
    </table>
    <div class="float_right"><br /><input class="button" type="submit" name="enqueuemembers" 
        value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitMembers'); ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
<?php } ?>
<?php if ($this->canEnqueueLocation) { ?>
<fieldset id="location">
    <legend><?php echo $words->getBuffered('AdminMassMailEnqueueLocation');?></legend>
    <div class="type-text"><?php echo $words->flushBuffer(); ?>
    <div class="type-select">
        <label for="CountryIsoCode">Choose a country</label><br>
        <select id="CountryIsoCode" name="CountryIsoCode" style="width:55em;">
        <option value="0">Select a country</option>
        <?php
        foreach($countries as $country) {
            echo '<option value="' . $country->iso_alpha2 . '">' . $country->name . '</option>';
        }
        ?>
        </select>
    </div>
    <div class="type-select">
        <label for="AdminUnits">Choose an administrative unit</label>
        <select id="AdminUnits" name="AdminUnits" style="width:55em;" disabled="disabled">
        <option value="0">All administrative units</option>
        <option value="1">Some administrative units</option>
        </select>
    </div>
    <div class="type-select">
        <label for="Places">Choose a place:</label>
        <select id="Places" name="Places" style="width:55em;" disabled="disabled">
        <option value="0">All places</option>
        </select>
    </div>
    </div>
    <div class="float_right"><br /><input class="button" type="submit" name="enqueuelocation" 
        value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitLocation'); ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
<?php } ?>
<?php if ($this->canEnqueueGroup) { ?>
<fieldset id="group">
    <legend><?php echo $words->getBuffered('AdminMassMailEnqueueGroup');?></legend><?php echo $words->flushBuffer(); ?>
    <div class="type-select">
        <label for="IdGroup">Choose a group</label>
        <select id="IdGroup" name="IdGroup" style="width: 55em";>
        <option value="0">Select a group</option>
        <?php
        foreach($groups as $group) {
            echo '<option value="' . $group->id . '">' . $group->Name . '</option>';
        }
        ?>
        </select>
    </div>
    <div class="float_right"><br /><input class="button" type="submit" name="enqueuegroup" 
        value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitGroup'); ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
<?php } ?>
<?php if ($this->canEnqueueVote) { ?>
<fieldset id="vote">
    <legend><?php echo $words->getBuffered('AdminMassMailEnqueueVote');?></legend></legend><?php echo $words->flushBuffer(); ?>
    <div class="type-text">
        <label for="Limit">Number of posters in thread (will be multiplied by 3): </label><input type="text" id="poster" name="poster" size="4" value="<?php if (isset($vars['poster'])) { echo $vars['poster']; }?>" />
    </div>
    <div class="float_right"><br /><input class="button" type="submit" name="enqueuevote" 
        value="<?php echo $words->getBuffered('AdminMassMailEnqueueSubmitVote'); ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
<?php } ?>
</form>
<script type="text/javascript">
    document.observe("dom:loaded", function() {
      var activeFieldset = '<?php echo $activefieldset; ?>';
      if (activeFieldset == '') {
        var defaultFieldset = '<?php echo $defaultfieldset; ?>';
        // Trim leading hashbang
        var hashValue = document.location.hash.replace('#!', '');
        if (hashValue == '') {
          activeFieldset = defaultFieldset;
        } else {
          /* This allows URLs like "/editmyprofile#!profileaccommodation",
           * which opens the "Accommodation" form tab after loading the page.
           * The hashbang value needs to match the ID of the fieldset that
           * is to be opened.
           */
          var tab = document.getElementById(hashValue);
          if (tab != null && tab.tagName.toLowerCase() == 'fieldset') {
            activeFieldset = hashValue;
          } else {
            activeFieldset = defaultFieldset;
          }
        }
      }
      new FieldsetMenu('mass-mail-enqueue-form', {active: activeFieldset});
      
      // geo dropdown stuff
      jQuery.noConflict();
      jQuery('#CountryIsoCode').change(function() {
        var value = jQuery('#CountryIsoCode').val();
        // clear admin units and places list
        jQuery('#AdminUnits').empty().append('<option selected="selected" value="0">All administrative units</option>');
        jQuery('#Places').empty().append('<option selected="selected" value="0">All places</option>');
        if (value == 0) {
          jQuery('#AdminUnits').attr('disabled', 'disabled');
          jQuery('#Places').attr('disabled', 'disabled');
        } else {
          // and rebuild the admin units select with the admin units for the selected country
          jQuery.getJSON('admin/massmail/getadminunits/' + value, function(data){
            var html = '';
            var len = data.length;
            for (var i = 0; i< len; i++) {
              html += '<option value="' + data[i].fk_admincode + '">' + data[i].name + '</option>';
            }
            jQuery('#AdminUnits').append(html);
          });
          jQuery('#AdminUnits').removeAttr('disabled');
        }
      });
      jQuery('#AdminUnits').change(function() {
        var value = jQuery('#AdminUnits').val();
        // clear places list
        jQuery('#Places').empty().append('<option selected="selected" value="0">All places</option>');
        if (value == 0) {
          jQuery('#Places').attr('disabled', 'disabled');
        } else {
          jQuery.getJSON('admin/massmail/getplaces/' + jQuery('#CountryIsoCode').val() + '/' + value, function(data){
            var html = '';
            var len = data.length;
            for (var i = 0; i< len; i++) {
              html += '<option value="' + data[i].geonameid + '">' + data[i].name + '</option>';
            }
            jQuery('#Places').append(html);
          });
          jQuery('#Places').removeAttr('disabled');
        }
      });
    });
</script>
</div>