<?php
$ranks = SuggestionsModel::getRanksAsArray($_SESSION['lang']);
$states = SuggestionsModel::getStatesAsArray();
$optionStates = SuggestionOption::getStatesAsArray($_SESSION['lang']);
?><table id="votingresults">
    <tr>
        <td><h2><?php echo htmlspecialchars($this->suggestion->summary, ENT_COMPAT, 'utf-8'); ?></h2></td>
        <td colspan="2" class="rank"><strong><?php if ($this->suggestion->voteCount) { echo $words->get('SuggestionsVotesGiven', $this->suggestion->voteCount); }?></strong></td>
        <td class="rank"><strong><?= $words->get($states[$this->suggestion->state]); ?></strong></td>
    </tr>
    <tr><td colspan="4"><?php echo $this->purifier->purify($this->suggestion->description); ?></td></tr>
    <tr><td colspan="4"><hr class="suggestion" /></td></tr>
    <?php
if ($this->suggestion->optionsVisibleCount == 0) { ?>
    <tr><td colspan="4"><?= $words->get('SuggestionsNoVoteInfo'); ?></td></tr>
<?php } else {
    foreach($this->suggestion->options as $option) {
      if (!$option->deleted) { ?>
          <tr><td style="vertical-align:top;"><h3><?php echo htmlspecialchars($option->summary, ENT_COMPAT, 'utf-8'); ?></h3></td>
              <td colspan="2" class="rank"><strong><?= $ranks[$option->rank]; ?></strong></td><td class="rank"><strong><?php if ($option->state) {
                    echo $optionStates[$option->state]; } else { echo $words->get('SuggestionsOptionRejected'); }?></strong></td></tr>
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
}
?>
</table>
<hr />

