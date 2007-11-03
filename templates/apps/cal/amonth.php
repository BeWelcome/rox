<?php
$Cal = new Cal;
$mCal = $Cal->calcCalMonth($y, $m);

$dayOfWeekShrt = array();
$monthNames = array();
$i18n = new MOD_i18n('date.php');
$dayOfWeekShrt = $i18n->getText('dayOfWeekShrt');
$monthNames = $i18n->getText('monthNames');
?>
<div class="acal">
    <div class="stretch">
        <a href="cal/<?php
$t = mktime(0, 0, 0, $m-1, 1, $y);
echo date('Ymd', $t) 
?>" class="l" onclick="Cal.aCalUpdate('<?php echo date('Y', $t); ?>', '<?php echo date('m', $t); ?>');return false;">&laquo;</a>

        <a href="cal/<?php 
$t = mktime(0, 0, 0, $m+1, 1, $y);
echo date('Ymd', mktime(0, 0, 0, $m+1, 1, $y)) 
?>" class="r" onclick="Cal.aCalUpdate('<?php echo date('Y', $t); ?>', '<?php echo date('m', $t); ?>');return false;">&raquo;</a>
    
        <strong><?php
$t = mktime(0, 0, 0, $m, 1, $y);
echo $monthNames[idate('m', $t)].' '.date('Y', $t);     
?></strong>
    </div>
<div class="clear"></div>
    <table class="calmonth">
        <thead>
            <tr>
<?php
foreach ($dayOfWeekShrt as $d) {
    echo '<th>'.$d.'</th>';
}
?>
            </tr>
        </thead>
        <tbody>
<?php
foreach ($mCal as $ISOWk=>$week) {
?>
            <tr>
<?php
    foreach ($week as $day=>$t) {
        echo '<td';
        if (date('Ymd', $t) == date('Ymd')) {
            echo ' class="today"';
        } 
        echo '><a href="cal/'.date('Ymd', $t).'" onclick="Cal.aCalSet('.idate('Y', $t).', '.idate('m', $t).', '.idate('d', $t).');return false;">'.$day.'</a></td>';
    }
?>
            </tr>
<?php
}
?>
        </tbody>
    </table>
</div>