<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
    <th></th>
    <th class="details"><?php echo $words->get('SuggestionNumberOfPosts') . '<br/>' . 
            $words->get('SuggestionNbOptions'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionsVotingStarts'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
echo '<td class="details"></td>';
echo '<td class="details">' . $suggestion->posts . '</td>';
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_OPEN) . '</td>';
echo '</tr>';
?>