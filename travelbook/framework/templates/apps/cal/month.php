<?php
$y = isset($_SESSION['APP_cal_currentyear']) ? $_SESSION['APP_cal_currentyear'] : idate('Y'); 
$m = isset($_SESSION['APP_cal_currentmonth']) ? $_SESSION['APP_cal_currentmonth'] : idate('m');
$Cal = new Cal;
$mCal = $Cal->calcCalMonth($y, $m);

$dayOfWeekShrt = array();
$monthNames = array();
$i18n = new MOD_i18n('date.php');
$dayOfWeekShrt = $i18n->getText('dayOfWeekShrt');
$monthNames = $i18n->getText('monthNames');

if (!isset($_GET['raw'])) {
?>
<div id="cal-month-small">
<?php
}
?>
    <div class="stretch">
        <a href="cal/<?php
$t = mktime(0, 0, 0, $m-1, 1, $y);
echo date('Ymd', $t) 
?>" class="l" onclick="Cal.updateMonth('<?php echo date('Y', $t); ?>', '<?php echo date('m', $t); ?>', 'cal-month-small');return false;">&laquo;</a>

        <a href="cal/<?php 
$t = mktime(0, 0, 0, $m+1, 1, $y);
echo date('Ymd', mktime(0, 0, 0, $m+1, 1, $y)) 
?>" class="r" onclick="Cal.updateMonth('<?php echo date('Y', $t); ?>', '<?php echo date('m', $t); ?>', 'cal-month-small');return false;">&raquo;</a>
    
        <strong><a href="cal/<?php 
$t = mktime(0, 0, 0, $m, 1, $y);
echo date('Ymd', $t) ?>"><?php
echo $monthNames[idate('m', $t)].' '.date('Y', $t);     
?></a></strong>
    </div>
<div class="clear"></div>
    <table class="calmonth">
        <thead>
            <tr>
                <th></th>
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
                <td class="isowk"><?php echo $ISOWk; ?></td>
<?php
    foreach ($week as $day=>$t) {
        echo '<td';
        if (date('Ymd', $t) == date('Ymd')) {
            echo ' class="today"';
        } 
        echo '><a href="cal/'.date('Ymd', $t).'">'.$day.'</a></td>';
    }
?>
            </tr>
<?php
}
?>
        </tbody>
    </table>
<?php
if (!isset($_GET['raw'])) {
?>
</div>
<?php
}
?>
