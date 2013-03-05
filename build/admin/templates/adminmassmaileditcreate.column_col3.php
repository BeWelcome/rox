<?php
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('AdminController', 'massmailEditCreateCallback');

    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors))
    {
        echo '<div class="error">';
        foreach($errors as $error) {
            echo $words->get($error) . "<br />";
        }
        echo "</div>";
    }
    
    $vars = $this->getRedirectedMem('vars');

    if (empty($vars)) {
        $id = $this->id;
        $name = $this->name;
        $subject = $this->subject;
        $body = $this->body;
        $description = $this->description;
        $type = $this->type;
    } else {
        $id = $vars['Id'];
        $name = $vars['Name'];
        $subject = $vars['Subject'];
        $body = $vars['Body'];
        $description = $vars['Description'];
        $type = $vars['Type'];
    }
?>
<div id="adminmassmail">
<form method="post" class="yform full">
<?php echo $callback_tag; ?>
<input type="hidden" name="Id" value="<?php echo $id; ?>">
<p class="note center">Please write here in <strong>English</strong></p>
<div class="type-text">
<?php 
$options = array();
if ($this->newsletterSpecific) {
    $options["Specific"] = $this->words->get('AdminMassMailEditTypeSpecific');
}
if ($this->newsletterGeneral) {
    $options["Normal"] = $this->words->get('AdminMassMailEditTypeGeneral');
}
?>
Type: <select id="Type" name="Type" <?php if ((!$this->canChangeType) && ((count($options) == 1) || ($id != 0))) { echo 'disabled="disabled"'; }?> />
<?php
    foreach($options as $key => $option) {
        $opt = '<option value="' . $key . '"';
        if ($key == $type) {
            $opt .= ' selected="selected"';
        }
        $opt .= '>' . $option . '</option>';
        echo $opt;
    }
?>
</select>
</div>
<div class="type-text">
<p>Give the code name of the broadcast as a word entry (must not exist in words table previously) like <b>NewsJuly2007</b> or <b>NewsAugust2007</b> without spaces!</p>
<label for="Name">WordCode for the newsletter</label>
<input type="text" id="Name" name="Name" value="<?php echo $name; ?>" <?php if ($id != 0) { echo 'readonly="readonly"'; }?> />
</div>

<div class="type-text">
<label for="BroadCast_Title_">Subject for the newsletter</label>
<input type="text" id="Subject" name="Subject" value="<?php echo $subject; ?>" />
</div>
  
<div class="type-text">
<label for="BroadCast_Body_">Body of the newsletter (%username%, if any, will be replaced by the username at sending)</label>
<textarea id="Body" name="Body" rows="30"><?php echo $body; ?></textarea>
</div>
  
<div class="type-text">
<label for="Description">Description (as translators will see it in AdminWord) </label>
<textarea id="Description" name="Description" rows="8"
<?php 
if ($id != 0) { 
    echo ' readonly="readonly"';
}?>
><?php echo $description; ?></textarea>
</div>

<div class="type-button">
  <input type="submit" name="submit" value="<?php
  if ($id != 0) {
    echo $words->get('AdminMassMailUpdate');
  }  else {
    echo $words->get('AdminMassMailCreate');
  }
  ?>" />
</div>
</form>
</div>