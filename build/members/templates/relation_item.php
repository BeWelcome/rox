<li class="clearfix">
  <span class="float-left">
    <a href="<?php echo PVars::getObj('env')->baseuri . "members/" . $rel->Username; ?>" title="See profile <?php echo $rel->Username; ?>">
      <img class="framed" src="members/avatar/<?php echo $rel->Username; ?>/50" height="50" width="50" alt="Profile">
    </a>
  </span>
<?php if ($myself): ?>
  <span class="float-right">
    <a class="button" role="button" href="<?php echo PVars::getObj('env')->baseuri . "members/" . $username . "/relations/delete/".$rel->id; ?>" onclick="return confirm('<?php echo $words->get('Relation_delete_confirmation'); ?>');"><?php echo $words->get('Delete'); ?></a>
  </span>
<?php endif; ?>
  <a href="<?php echo PVars::getObj('env')->baseuri."members/" . $rel->Username; ?>" ><?php echo $rel->Username; ?></a><br />
  <?php echo $rel->Comment; ?>
</li>
