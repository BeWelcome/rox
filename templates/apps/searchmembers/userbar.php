<?php
$words = new MOD_words();
?>
<script type="text/javascript">

/* BTchange is the small view-menu in searchmembers */

ViewImg1 = "images/misc/one.gif";
ViewImg1_f2 = "images/misc/one_f2.gif";

ViewImg2 = "images/misc/two.gif";
ViewImg2_f2 = "images/misc/two_f2.gif";

ViewImg3 = "images/misc/three.gif";
ViewImg3_f2 = "images/misc/three_f2.gif";

function BTchange (IdImg, ImgObj) {
  document.getElementById(IdImg).src = ImgObj;
}
function changeSortOrder (SortOrder) {
  varsOnLoad = '/varsonload';
  varSortOrder = '/'+SortOrder;
  document.getElementById('filterorder').value = SortOrder;
  searchGlobal(0);
  varsOnLoad = '';
}
</script>
 
 <div id="nextmap">
 <div class="floatbox" style="padding: 0 0 10px 0">
<span class="small" style="padding: 0; float: left;"><?php echo $words->getBuffered('View'); ?>: &nbsp; </span>
<table border="0" cellpadding="0" cellspacing="0" width="63" style="padding: 0; float: left;">
  <tr>
   <td style="padding: 0;"><a <?php if ($mapstyle != 'mapon') echo 'href="searchmembers/mapon"'; ?> alt="Map view" style="background-color: #fff;" onmouseover="BTchange('IdImg1', ViewImg1_f2)" onmouseout="BTchange('IdImg1', ViewImg1<?php if ($mapstyle=='mapon') echo '_f2'; ?>)"><img name="one" src="images/misc/one<?php if ($mapstyle=='mapon') echo '_f2'; ?>.gif" width="30" height="24" border="0" alt="" id="IdImg1"></a></td>
<?php /*   
    <td style="padding: 0;"><a style="background-color: #fff;" <?php if ($mapstyle != 'mix') echo 'href="searchmembers/mix"'; ?>  alt="Mixed view" onmouseover="BTchange('IdImg2', ViewImg2_f2)" onmouseout="BTchange('IdImg2', ViewImg2<?php if ($mapstyle=='mix') echo '_f2'; ?>)" onfocus="BTchange('IdImg2', ViewImg2_f2)" ><img name="two" src="images/misc/two<?php if ($mapstyle=='mix') echo '_f2'; ?>.gif" width="30" height="24" border="0" alt="" id="IdImg2"></a></td>
*/ ?>
   <td style="padding: 0;"><a style="background-color: #fff;" alt="Text view" <?php if ($mapstyle != 'mapoff') echo 'href="searchmembers/mapoff"'; ?> onmouseover="BTchange('IdImg3', ViewImg3_f2)" onmouseout="BTchange('IdImg3', ViewImg3<?php if ($mapstyle=='mapoff') echo '_f2'; ?>)"><img name="three" src="images/misc/three<?php if ($mapstyle=='mapoff') echo '_f2'; ?>.gif" width="33" height="24" border="0" alt="" id="IdImg3"></a></td>
  </tr>
</table>
<span class="small" style="padding: 0; float: left;">&nbsp; &nbsp; <?php echo $words->getBuffered('SortOrder'); ?>: &nbsp; </span>
<form id="changethisorder">
<select Name="OrderBy" id="thisorder" onChange="changeSortOrder(this.value);">
    <?php foreach($TabSortOrder as $key=>$val) { ?>
    <option value="<?php echo $key; ?>"><?php echo $words->getBuffered($val); ?></option>
    <?php } ?>
</select>
</form>
</div>
<div id="member_list"></div>
<div id="help_and_markers"></div>
</div>
