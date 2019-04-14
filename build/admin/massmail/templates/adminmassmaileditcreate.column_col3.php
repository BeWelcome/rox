<?php
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('AdminMassmailController', 'massmailEditCreateCallback');

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

<form method="post">
<?php echo $callback_tag; ?>
    <input type="hidden" name="Id" value="<?php echo $id; ?>">

    <div class="form-group">
        <small id="headerInfo" class="form-text text-muted">Please write in <strong>English</strong></small>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <label class="input-group-text" for="Type">Type</label>
            </div>
            <?php
            $options = array(
                "None" => $this->words->getSilent('AdminMassMailEditSelectType')
            );
            if ($this->newsletterSpecific) {
                $options["Specific"] = $this->words->getSilent('AdminMassMailEditTypeSpecific');
            }
            if ($this->newsletterGeneral) {
                $options["Normal"] = $this->words->getSilent('AdminMassMailEditTypeGeneral');
            }
            if ($this->loginReminder) {
                $options["RemindToLog"] = $this->words->getSilent('AdminMassMailEditTypeLoginReminder');
            }
            if ($this->suggestionsReminder) {
                $options["SuggestionReminder"] = $this->words->getSilent('AdminMassMailEditTypeSuggestionsReminder');
            }
            if ($this->termsOfUse) {
                $options["TermsOfUse"] = $this->words->getSilent('AdminMassMailEditTypeTermsOfUse');
            }
            if ($this->mailToConfirmReminder) {
                $options["MailToConfirmReminder"] = $this->words->getSilent('AdminMassMailEditTypeMailToConfirmReminder');
            }
            ?>

            <select class="form-control" id="Type" name="Type" aria-describedby="typeHelp" <?php if ((!$this->canChangeType) && ((count($options) == 1) || ($id != 0))) { echo 'disabled="disabled"'; }?>>
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
        <small id="typeHelp" class="form-text text-muted">What kind of MassMail do you want to send?</strong></small>
    </div>
    <div class="form-group">
        <label for="Name">WordCode for the newsletter</label>
        <input type="text" class="form-control" id="Name" name="Name" value="<?php echo $name; ?>" <?php if ($id != 0) { echo 'readonly="readonly"'; }?> placeholder="EnterYourWordCode">
        <small id="nameHelp" class="form-text text-muted">Give the code name of the broadcast as a word entry (must not exist in words table previously) like <strong>NewsJuly2019</strong> or <strong>LoginReminder2019</strong> without spaces!</small>
    </div>
    <div class="form-group">
        <label for="Subject">Title for the newsletter</label>
        <input type="text" class="form-control" id="Subject" name="Subject" value="<?php echo $subject; ?>" >
        <small id="subjectHelp" class="form-text text-muted">This will be shown as the subject of the e-mail</small>
    </div>
    <div class="form-group">
        <label for="Body">Text of the mail</label>
        <textarea class="form-control" id="Body" name="Body" rows="10"></textarea>
        <small id="bodyHelp" class="form-text text-muted">Body of the newsletter (%username%, if any, will be replaced by the username at sending)</small>
    </div>
    <div class="form-group">
        <label for="Description">Description of the WordCode</label>
        <textarea class="form-control" id="Description" name="Description" rows="3"
        <?php
        if ($id != 0) {
            echo ' readonly="readonly"';
        }?>
        ><?php echo $description; ?>
        </textarea>
        <small id="descriptionHelp" class="form-text text-muted">Description (as translators will see it in AdminWord). If relevant, please give tips to the translators.</small>
    </div>

    <input type="submit" class="btn btn-primary" name="submit" value="<?php
    if ($id != 0) {
        echo $words->get('AdminMassMailUpdate');
    }  else {
        echo $words->get('AdminMassMailCreate');
    }
    ?>" />
</form>