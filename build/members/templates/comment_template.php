<?php
    $iiMax = (isset($max) && count($comments) > $max) ? $max : count($comments);
    $tt = array ();
    for ($ii = 0; $ii < $iiMax; $ii++) {
        $c = $comments[$ii];
        $quality = "neutral";
        if ($c->comQuality == "Good") {
            $quality = "good";
        }
        if ($c->comQuality == "Bad") {
            $quality = "bad";
        }

    $tt = explode(",", $comments[$ii]->Lenght);
    // var_dump($c);
?>

  <div class="subcolumns profilecomment">

    <div class="c75l" >
      <div class="subcl" >
        <a href="people/<?=$c->Username?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->Username?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p>
              <strong class="<?=$quality?>"><?=$c->comQuality?></strong><br/>
              <span class="small grey"><?=$words->get('CommentFrom','<a href="people/'.$c->Username.'">'.$c->Username.'</a>')?> - <?=$c->created?></span>
            </p>
            <p>
              <em><?=$c->TextWhere?></em>
            </p>
            <p>
              <?=$c->TextFree?>
            </p>
            <p>
              <em class="small"><?=$words->get('CommentLastUpdated')?>: <?=$layoutbits->ago($c->unix_updated)?></em>
            </p>
            <hr />
        </div> <!-- comment -->
      </div> <!-- subcl -->
    </div> <!-- c75l -->
    <div class="c25r" >
      <div class="subcr" >
        <ul class="linklist" >
            <li>
                <?php
                    for ($jj = 0; $jj < count($tt); $jj++) {
                        if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
                        echo "                    <li>", $words->get("Comment_" . $tt[$jj]), "</li>\n";
                    }
                ?>
            </li>
            <li>
                <a href="feedback?IdCategory=4" ><img src="images/icons/error.png" alt="<?=$words->get('ReportCommentProblem')?>" /> <?=$words->get('ReportCommentProblem')?></a>
            </li>
        </ul>
      </div> <!-- subcr -->
    </div> <!-- c25r -->
  </div> <!-- subcolumns -->
<?php
}
?>
