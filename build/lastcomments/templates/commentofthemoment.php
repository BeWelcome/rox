<p><?php echo $words->getFormatted("CommentOfTheMomentExplanation",$iiMax) ; ?></p>

<table class="full">
    <colgroup>
        <col width="12%" />
        <col width="12%" />
        <col width="12%" />
    </colgroup>

    <?php
    $c=$data ;
    if (isset($c->UsernameFrom )) {
    ?>
    <tr>
        <td>
            <?php echo MOD_layoutbits::PIC_50_50($c->UsernameFrom); ?><br />
            <a class="username" href="members/<?php echo $c->UsernameFrom ?>"><?php echo $c->UsernameFrom ?></a>
            <a  href="members/<?php echo $c->UsernameFrom ?>/comments" title="<?php echo $words->getFormatted('ViewComments'); ?>"></a><br />
            <?php echo $c->CountryNameFrom ?>
        </td>
        <td>
            <p class="<?php echo $c->Quality ?>"><img src="images/icons/tango/22x22/go-next.png" alt="comment to" /><br />
                <?php echo $words->getFormatted('CommentQuality_'.$c->Quality); ?>
            </p>
            <span class="small"><?php echo MOD_layoutbits::ago($c->unix_updated);?></span>
        </td>
        <td>
            <?php echo MOD_layoutbits::PIC_50_50($c->UsernameTo); ?><br />
            <a class="username" href="members/<?php echo $c->UsernameTo ?>"><?php echo $c->UsernameTo ?></a>
            <a href="members/<?php echo $c->UsernameTo ?>/comments" title="<?php echo $words->getFormatted('ViewComments'); ?>"></a><br />
            <?php echo $c->CountryNameTo ?>
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
<p><?php echo "<a href='lastcomments'>",$words->getFormatted('LastCommentsLink'),"</a>"; ?></p>
