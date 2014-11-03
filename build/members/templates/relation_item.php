<li class="clearfix">
  <span class="float_left">
    <a href="<?php echo PVars::getObj('env')->baseuri . "members/" . $rel->Username; ?>" title="See profile <?php echo $rel->Username; ?>">
      <img class="framed" src="members/avatar/<?php echo $rel->Username; ?>?xs" height="50px" width="50px" alt="Profile">
    </a>
  </span>
<?php if ($myself): ?>
  <span class="float_right">
    <a class="button" role="button" href="<?php echo PVars::getObj('env')->baseuri . "members/" . $username . "/relations/delete/".$rel->id; ?>" onclick="return confirm('<?php echo $words->get('Relation_delete_confirmation'); ?>');"><?php echo $words->get('Delete'); ?></a>
  </span>
<? endif; ?>
  <a href="<?php echo PVars::getObj('env')->baseuri."members/" . $rel->Username; ?>" ><?php echo $rel->Username; ?></a><br />
  <?php echo $rel->Comment; ?>
</li>
