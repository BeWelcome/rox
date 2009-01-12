
  <h3>Comments for <?=$username?></h3>
<?php
	$iiMax = count($comments);
	$tt = array ();
	$info_styles = array(0 => "        <div class=\"info clearfix\">\n", 1 => "        <div class=\"info highlight clearfix\">\n");
	for ($ii = 0; $ii < $iiMax; $ii++) {
        $c = $comments[$ii];
		$quality = "neutral";
		if ($c->comQuality == "Good") {
			$quality = "good";
		}
		if ($c->comQuality == "Bad") {
			$quality = "bad";
		}
    echo $info_styles[($ii%2)];
    $tt = explode(",", $comments[$ii]->Lenght);
    // var_dump($c);
?>
        <style>
        div.neutral a.username{
			color: #000000;
		}
        div.good a.username{
			color: green;
		}
        div.bad a.username{
			color: red;
		}
        </style>
  <div class="subcolumns">

    <div class="c75l" >
      <div class="subcl <?=$quality?>" >
        <a href="people/<?=$c->Username?>"  title="See admin's profile" >
           <img class="float_left framed"  src="/"  height="50px"  width="50px"  alt="Profile" >
        </a>
        <div style="display: block; float: left; width: 70%">
        <p>
          <strong><?=$c->comQuality?> from <a href="people/<?=$c->Username?>" class="username"><?=$c->Username?></a> </strong>
        </p>
        <p>
          <small><?=$c->TextFree?></small>
        </p>
        <p>
          <em><?=$c->TextWhere?></em>
        </p>
        <hr />
        </div>
      </div>
    </div>
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
                <?=$c->Lenght?>
            </li>
            <li>
                <a href="feedback.php?IdCategory=4"><img src="images/icons/error.png" alt="Report a problem with this comment" ></a>
            </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php
}
?>