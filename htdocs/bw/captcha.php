<?php
/* Fichier captcha.png.php */
session_start();

header("Content-type: image/png");

$img = imagecreate (50,15) or die ("Problème de création GD");
$background_color = imagecolorallocate ($img, 255, 255, 255);
$ecriture_color = imagecolorallocate($img, 0, 0, 0);
imagestring ($img, 20, 4, 0, $_SESSION['TheCaptcha'] , $ecriture_color);
echo imagepng($img);

?>
