<?php
if ($count == 0) :
    echo '<tr><th class="description">' . $words->get('Suggestion') . '</th>';
    echo '<th class="details">' . $words->get('SuggestionNumberOfPosts') . '<br />' .
        $words->get('SuggestionNbOptions') . '</th>';
    echo '<th class="details">' . $words->get('SuggestionsVotingStarts') . '</th></tr>';
endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
echo '<td class="details">' . $suggestion->posts . ' / ' . $suggestion->optionsVisibleCount . '</td>';
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_ADDOPTIONS) . '</td>';
echo '</tr>';
echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
echo '<td class="description" colspan="3">';
echo $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50)) . '</td>';
echo '</tr>';
?>