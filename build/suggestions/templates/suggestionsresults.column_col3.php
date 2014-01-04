<?php
$ranks = SuggestionsModel::getRanksAsArray($_SESSION["lang"]);
$states = SuggestionsModel::getStatesAsArray();
?><table style="width:100%">
    <tr>
        <th colspan="3"><h2><?php echo htmlspecialchars($this->suggestion->summary, ENT_COMPAT, 'utf-8'); ?></h2></th>
        <th><?= $words->get($states[$this->suggestion->state]); ?></th>
    </tr>
    <?php if ($this->suggestion->voteCount) { ?>
    <tr><td>Votes given: <?= $this->suggestion->voteCount; ?></td></tr>
    <?php } ?>
    <tr><td colspan="4"><?php echo $this->purifier->purify($this->suggestion->description); ?></td></tr>
    <tr><td colspan="4"><hr class="suggestion" /></td></tr>
    <?php
foreach($this->suggestion->options as $option) {
  if (!$option->deleted) { ?>
      <tr><td colspan="3" style="vertical-align:top;"><h3><?php echo htmlspecialchars($option->summary, ENT_COMPAT, 'utf-8'); ?></h3></td><td><?= $ranks[$option->rank]; ?></td></tr>
<?php
      foreach($ranks as $rank => $rankName) {
          if ($rank == 4) {
              $tableRow = '<tr><td class="description" rowspan="4" style="vertical-align:top;">' . $this->purifier->purify($option->description)  . '</td>'
                . '<td class="resultsleft">' . $rankName . ':</td><td class="resultsright">'
                . $this->suggestion->ranks[$option->id][$rank] . '</td><td class="rank" rowspan="4" style="vertical-align:top;">'
                . '</td></tr>';
          } else {
              $tableRow = '<tr><td class="resultsleft">' . $rankName . ':</td><td class="resultsright">'
              . $this->suggestion->ranks[$option->id][$rank] . '</td></tr>';
          }
          echo $tableRow . "\r\n";
      } ?>
      <tr><td colspan="4"><hr class="suggestion" /></td></tr>
<?php  }
}
?>
</table>
<hr />

