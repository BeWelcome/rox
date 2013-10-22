<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50)) . '</p></td>';
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_IMPLEMENTED) {
    echo '<td class="details">' . $words->get('SuggestionsImplemented') . '</td>';
} else {
    echo '<td class="details">' . $words->get('SuggestionsInDevelopment') . '</td>';
}
echo '</tr>';
?>