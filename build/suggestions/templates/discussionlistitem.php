<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th></tr>
<?php endif;
echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '/discuss">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $suggestion->description . '</p></td>';
echo '</tr>';
?>