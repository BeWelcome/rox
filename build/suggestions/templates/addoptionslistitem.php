<?php
if ($count == 0) :
    echo '<tr><th class="description">' . $words->get('Suggestion') . '</th>';
    echo '<th></th>';
    echo '<th class="details">' . $words->get('SuggestionNumberOfPosts') . '<br/>' . 
        $words->get('SuggestionNbOptions') . '</th>';
    echo '<th class="details">' . $words->get('SuggestionsVotingStarts') . '</th></tr>';
endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
if ($discuss) :
    echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/addoptions">'
        . '<img src="images/icons/add.png" alt="' . $words->getBuffered('SuggestionsAddOptions') . '" /></a><br><a href="suggestions/'
        . $suggestion->id . '/addoptions">' . $words->getBuffered('SuggestionsAddOptions') . '</a></td>';
    echo '<td class="details">' . $suggestion->posts . '<br />' . count($suggestion->options) . '</td>';
else :
    echo '<td></td>';
    echo '<td class="details">' . $suggestion->posts . '<br />' . count($suggestion->options) . '</td>';
endif;
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_ADDOPTIONS) . '</td>';
echo '</tr>';
?>