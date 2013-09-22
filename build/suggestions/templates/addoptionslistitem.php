<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '/addoptions">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
echo '</tr>';
?>