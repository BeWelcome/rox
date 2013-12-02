<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
        <th class="details"><?php echo $words->get('SuggestionsRank'); ?></th></tr>
<?php endif;
echo '<tr class="' . $background = (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description" colspan="2">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary);
echo '</a></h3></td>';
echo '</tr>';
foreach($suggestion->options as $option) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
    echo '<td>' . $this->purifier->purify($option->summary) . '</td>';
    if ($this->member) {
        echo '<td style="text-align: center;"><a href="/suggestions/' . $suggestion->id . '/upvote/' . $option->id . '">up vote</a><br/>'
            . $option->rankVotes . '<br /><a href="/suggestions/' . $suggestion->id . '/downvote/' . $option->id . '">down vote</a></td>';
    } else {
        echo '<td style="text-align: center;">' . $option->rankVotes . '</td>';
    }
    echo '</tr>';
}
?>