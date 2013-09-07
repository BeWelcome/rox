<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionsEdit'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionsApprove'); ?></th></tr>
<?php endif;
echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 30px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if (($this->member->id == $uggestion->creator) || $this->hasSuggestionsRight) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/edit"><img src="images/icons/calculator.png" /></a></td>';
else :
    echo '<td></td>';
endif;
if ($this->hasSuggestionsRight) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/approve"><img src="images/icons/calculator.png" /></a></td>';
else :
    echo '<td></td>';
endif;
echo '</tr>';
?>