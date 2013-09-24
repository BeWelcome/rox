<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
if ($discuss) :
echo '<h3><a href="suggestions/' . $suggestion->id . '/discuss">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
else :
    echo '<h3><a href="suggestions/' . $suggestion->id . '/addoptions">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
endif;
echo '<p>' . $purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
if ($discuss) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/addoptions">'
        . '<img src="images/icons/add.png" alt="' . $words->getBuffered('SuggestionsAddOptions') . '" /></a><br><a href="suggestions/'
        . $suggestion->id . '/addoptions">' . $words->getBuffered('SuggestionsAddOptions') . '</a></td>';
endif;
echo '</tr>';
?>