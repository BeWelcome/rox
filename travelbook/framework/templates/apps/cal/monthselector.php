<?php
$dayOfWeekShrt = array();
$monthNames = array();
$i18n = new MOD_i18n('date.php');
$dayOfWeekShrt = $i18n->getText('dayOfWeekShrt');
$monthNames = $i18n->getText('monthNames');

$y = isset($_SESSION['APP_cal_currentyear']) ? $_SESSION['APP_cal_currentyear'] : idate('Y'); 
$m = isset($_SESSION['APP_cal_currentmonth']) ? $_SESSION['APP_cal_currentmonth'] : idate('m');
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