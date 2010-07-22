<?php
 /*
 *  Signup Team Mail template
 */
 
?>
<table>
    <tr>
        <td>Candidate:</td>
        <td><?=urldecode($vars['firstname'])?> <?=urldecode($vars['lastname'])?></td>
    </tr>
    <tr>
        <td>Place:</td>
        <td><?php 
            echo $countryname ;
            if  ($countryname<>$Data->EnglishCountryName) {
                echo "(",$Data->EnglishCountryName,")" ;
            }
            echo "/",$cityname ;
            if  ($cityname<>$Data->EnglishCityName) {
                echo "(",$Data->EnglishCityName,")" ;
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>E-mail:</td>
        <td><?=$vars['email']?>
    </tr>
    <tr>
        <td>Language used: </td>
        <td><?=$language."(".$Data->EnglishLanguageName.")" ?></td>
    </tr>
    <?php
if (!empty($vars['feedback'])) {
?>
    <tr>
        <td>Feedback: </td>
        <td bgcolor="#FFC"><?=$vars['feedback']?></td>
    </tr>
<?php
}
?>

</table>
<br/>
<a href="<?=PVars::getObj('env')->baseuri?>bw/admin/adminaccepter.php">Check and accept this member</a>

