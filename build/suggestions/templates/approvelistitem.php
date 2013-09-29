<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th style="text-align: center"></th><th style="text-align: center"></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if ((($this->member && $this->member->id == $suggestion->createdby) || $this->hasSuggestionRight)) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/edit">'
        . '<img src="images/icons/comment_edit.png" alt="' . $words->getBuffered('SuggestionsEdit') . '" /></a><br><a href="suggestions/'
        . $suggestion->id . '/edit">' . $words->getBuffered('SuggestionsEdit') . '</a></td>';
else :
    echo '<td></td>';
endif;
if ($this->hasSuggestionRight) :
    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/approve">'
        . '<img src="images/icons/tick.png" alt="' . $words->getBuffered('SuggestionsApproveReject') . '" /></a><br><a href="suggestions/'
        . $suggestion->id . '/approve">' . $words->getBuffered('SuggestionsApproveReject') . '</a></td>';
else :
    echo '<td></td>';
endif;
echo '</tr>';
?>