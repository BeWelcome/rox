<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionVoteEndDate'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionNbVotes'); ?></th></tr>
<?php endif;
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td style="padding-bottom: 30px; width: 80%;">';
    echo '<h3><a href="suggestions/' . $suggestion->id . '/vote">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
    echo '<td style="text-align: center;">' . date('d.m.Y', $suggestion->votingendts) . '</td>';
    echo '<td style="text-align: center;">' . $suggestion->votes . '</td>';
    echo '</tr>';
?>