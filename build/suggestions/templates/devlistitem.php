<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th></th><th class="details"></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if (($suggestion->state & $suggestionState) == SuggestionsModel::SUGGESTIONS_IMPLEMENTED) {
    echo '<td></td>';
    echo '<td class="details">' . $words->get('SuggestionsImplemented') . '</td>';
} else {
    if ($this->hasSuggestionRight && count($suggestion->options) == 0) {
        echo '<td class="details"><a href="/suggestions/' . $suggestion->id . '/implemented/">Set Implemented</a></td>';
    } else {
        echo '<td></td>';
    }
    echo '<td class="details">' . $words->get('SuggestionsInDevelopment') . '</td>';
}
echo '</tr>';
if (count($suggestion->options) == 0) {
    echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
    echo '<td colspan="3">';
    echo $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50));
} else {
    foreach($suggestion->options as $option) {
        if (($option->state & $optionState) == $optionState)
        {
            echo '<tr class="' . (($count % 2) ? 'highlightmiddle' : 'blankmiddle') . '">';
            if ($this->hasSuggestionRight && $optionState == SuggestionOption::IMPLENENTING) {
                echo '<td>';
            } else {
                echo '<td  colspan="3">';
            }
            echo '<h4>' . $this->purifier->purify($option->summary) . '</h4>';
            echo '</td>';
            if ($this->hasSuggestionRight && $optionState == SuggestionOption::IMPLENENTING) {
                echo '<td><a href="/suggestions/' . $suggestion->id . '/implemented/' . $option->id . '">Set Implemented</a></td>';
                echo '<td></td>';
            }
            echo '</tr>';
            echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
            echo '<td colspan="3">' . $this->purifier->purify($layoutbits->truncate_words($option->description, 50)) . '</td>';
            echo '</tr>';
        }
    }
}
echo '<td>';
echo '</tr>';
?>