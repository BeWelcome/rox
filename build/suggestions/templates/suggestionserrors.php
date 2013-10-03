<?php
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        if (strstr($error, '###') !== false) {
            list($error, $count, $dummy) = explode('###', $error);
            $errStr .= $words->get($error, $count);
        } else {
            $errStr .= $words->get($error);
        }
        $errStr .= "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
?>