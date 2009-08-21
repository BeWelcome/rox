<p><?php echo $words->getFormatted("CommentOfTheMomentExplanation",$iiMax) ; ?></p>

<table class="full">

    <tr>
        <th><?=$words->getFormatted("CommentFrom","") ?></th>
        <th><?=$words->getFormatted("CommentTo") ?></th>
        <th><?=$words->getFormatted("CommentWhen") ?></th>
        <th><?=$words->getFormatted("CommentText") ?></th>
        <th></th>
    </tr>
    <?php
    $c=$data ;
    if (isset($c->UsernameFrom )) {
    ?>
    <tr class="<?php $styles[0] ?>">
        <td>
            <a href="members/<?php echo $c->UsernameFrom ?>"><img src="members/avatar/<?php echo $c->UsernameFrom ?>"></a>
            <a class="username" href="members/<?php echo $c->UsernameFrom ?>">
            <?php echo $c->UsernameFrom ?></a><br />
            <a href="members/<?php echo $c->UsernameFrom ?>/comments"><?php echo $words->getFormatted('ViewComments') ?></a><br />
            <?php echo $c->CountryNameFrom; ?>
        </td>
        <td>
            <a href="members/<?php echo $c->UsernameTo ?>"><img src="members/avatar/<?php echo $c->UsernameTo ?>"></a>
            <a class="username" href="members/<?php echo $c->UsernameTo ?>"><?php echo $c->UsernameTo ?></a><br />
            <a href="members/<?php echo $c->UsernameTo ?>/comments"><?php echo $words->getFormatted('ViewComments') ?></a><br />
            <?php echo $c->CountryNameTo; ?>
        </td>
        <td>
            <strong class="<?php echo $c->Quality ?>">
                <?php echo $words->getFormatted('CommentQuality_'.$c->Quality); ?>
            </strong><br /><br />
            <?php echo MOD_layoutbits::ago($c->unix_updated); ?>
        </td>
        <td>
            <p><?php echo $c->TextWhere ?></p>
            <p><?php echo $c->TextFree ?></p>
        </td>
        <td>
        </td>
    </tr>
    <?php } ?>
</table>
<?php echo "<a href='lastcomments'>",$words->getFormatted('LastCommentsLink'),"</a>"; ?>
