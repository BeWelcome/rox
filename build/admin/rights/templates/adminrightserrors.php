<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 18.04.14
 * Time: 14:05
 */
$errors = $this->getRedirectedMem('errors');
if ($errors) {
    echo '<div class="error">';
    foreach($errors as $error) {
        echo '<p>' . $this->words->get($error) . '</p>';
    }
    echo '</div>';
}