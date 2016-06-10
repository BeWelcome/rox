<?php

    $vars = $this->getRedirectedMem('vars');
   
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('AdminWordController', 'findTranslationsCallback');
    
    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors)) {
        echo '<div class="error">';
        foreach($errors as $error) {
            echo $words->get($error) . "<br />";
        }
        echo "</div>";
    }


if ($this->_session->has( 'trData' )){
    $data = $_SESSION['trData'];
    unset($_SESSION['trData']);
}
?>
<form method="post" name="TrEdit">
<?= $callback_tag ?>
<table class="admin" border="0">
  <tr>
    <td class="label"><label for="code">Code:</label> </td>
    <td><input name="EngCode" id="code" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>" size="40">
  </td></tr>
  <tr>
    <td class="label"><label for="code">Description:</label> </td>
    <td><input name="EngDesc" id="code" value="<?= htmlspecialchars($this->formdata['EngDesc']) ?>" size="40">
  </td></tr>
  <tr>
    <td class="label"><label for="code">Sentence:</label> </td>
    <td><input name="Sentence" id="code" value="<?= htmlspecialchars($this->formdata['Sentence']) ?>" size="40">
  </td></tr>
  <tr>
    <td class="label"><label for="lang">Language:</label> </td>
    <td>

    <select id="lang" name="lang"><option value=""></option>
<?php
    foreach($this->langarr as $language) {
        echo '<option value="' . htmlspecialchars($language->ShortCode) . '"';
        if ($this->formdata['lang'] == $language->ShortCode) {
            echo ' selected="selected"';
        }
        echo '>' . htmlspecialchars(trim($language->EnglishName)) . ' (' . htmlspecialchars($language->ShortCode) . ')</option>';
    }
?>
</select></td></tr>
  <tr>
    <td colspan="2">
      <input class="button" type="submit" name="findBtn" value="Find">
    </td>
  </tr>
</table>
</form>
<?php
if (isset($data[0])){
?>

<table>
<tr><th>Code & Description</th><th>Sentence</th></tr>
<?php
foreach($data as $dat){
?>
<tr><td><b>
<?php
if ($dat->inScope){
    echo '<a href="/admin/word/edit/'.$dat->EngCode.'/'.$dat->TrShortcode.'">';
    echo $dat->EngCode.' - '.$words->get("lang_".$dat->TrShortcode).'</a>';    
} else {
    echo $dat->EngCode.' - '.$words->get("lang_".$dat->TrShortcode);
}
?>
</b><br>
        <span class="smallXtext"><?= $dat->EngDesc ?></span></td>
    <td><?= $dat->Sentence ?></td></tr>
<?php } ?>
</table><p>
<?php
} elseif (isset($data)){
    echo 'No results were found.';
}
?>
