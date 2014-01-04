<?php
$layoutbits = new MOD_layoutbits();
if (count($this->options) == 0) {
echo "<p><strong>" . $words->get($this->NoItems) . "</strong></p>";
} else {
$this->pager->render();
$order = ""; $title = "Sort randomly (default)";
if ($this->order == 'desc') { $order = '?order=asc'; $title = 'Sort ascending (votes)'; }
if ($this->order == 'asc') { $order = ''; $title = 'Sort randomly (default)'; }
if (!$this->order) { $order =  '?order=desc'; $title = 'Sort descending (votes)';}
?>
<table id='suggestionslist'>
        <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
            <?php if ($this->hasSuggestionRight) {
                echo '<th class="details"></th>';
            } ?>
            <th class="details"><a href="/suggestions/rank<?php echo $order;?>"
            title="<?php echo $title; ?>"><?php echo $words->get('SuggestionsRank'); ?></a></th></tr>
<?php
    $count= 0;
    foreach($this->options as $option) {
        echo '<tr class="' . $background = (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
        echo '<td class="description">';
        echo '<h3><a href="suggestions/' . $option->suggestionId . '">' . htmlspecialchars($option->summary);
        echo '</a></h3></td>';
        if ($this->hasSuggestionRight) {
            echo '<td class="details"><a href="/suggestions/' . $option->suggestionId . '/implementing/'
                . $option->id .'">Set Implementing</a></td>';
        }
        echo '<td rowspan="2" style="text-align: center; vertical-align: top;"><div style="text-align: center">';
        if ($this->member) {
            if ($option->vote == +1) {
                echo '<i class="icon-angle-up icon-3x" title="Already upvoted"></i><br />';
            } else {
                echo '<a name="upvote_' . $option->id . '" href="/suggestions/' . $option->id . '/upvote" class="icon-chevron-up icon-3x" title="upvote"></a><br/>';
            }
            echo '<span class="big">' . $option->rankVotes . '</span><br />';
            if ($option->vote == -1) {
                echo '<i class="icon-angle-down icon-3x" title="Already downvoted"></i>';
            } else {
                echo '<a name="downvote_' . $option->id . '" href="/suggestions/' . $option->id . '/downvote"class="icon-chevron-down icon-3x" title="down vote"></a>';
            }
        } else {
            echo '<span class="big"' . $option->rankVotes . '</span>';
        }
        echo '</div></td></tr>';
        echo '<tr class="' . $background = (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
        if ($this->hasSuggestionRight) {
            echo '<td colspan="2">';
        } else {
            echo '<td>';
        }

        echo $this->purifier->purify($option->description) . '</td>';
        echo '</tr>';
        $count++;
    }
?>
</table>
<?php $this->pager->render();
} ?>
