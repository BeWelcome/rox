<?php
if ($count == 0) :
    echo '<tr><th>' . $words->get('Suggestion') . '</th>';
    if ($discuss) :
        echo '<th></th>';
    else :
        echo '<th>' . $words->get('SuggestionNbOptions') . '<br/>' . $words->get('SuggestionNumberOfPosts') . '</th>';
    endif;
    echo '<th>' . $words->get('SuggestionsVotingStarts') . '</th></tr>';
endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
if ($discuss) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/addoptions">'
        . '<img src="images/icons/add.png" alt="' . $words->getBuffered('SuggestionsAddOptions') . '" /></a><br><a href="suggestions/'
        . $suggestion->id . '/addoptions">' . $words->getBuffered('SuggestionsAddOptions') . '</a></td>';
else :
    echo '<td>' . count($suggestion->options) . '<br />' . $suggestion->posts . '</td>';
endif;
echo '<td>' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_ADDOPTIONS) . '</td>';
echo '</tr>';
?>