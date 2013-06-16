<?php 
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
?>