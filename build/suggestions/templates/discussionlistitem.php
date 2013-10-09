<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th>
    <th><?php echo $words->get('SuggestionNumberOfPosts'); ?></th>
    <th><?php echo $words->get('SuggestionsVotingStarts'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
echo '<td>' . $suggestion->posts . '</td>';
echo '<td>' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_OPEN) . '</td>';
echo '</tr>';
?>