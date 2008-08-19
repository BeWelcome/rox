<?php
foreach ($comments as $c) {
    //var_dump($c);
    $username = $c->Username;
    $text = $c->TextFree;
?>
<div class="info clearfix"> 
  <div class="subcolumns">

    <div class="c75l" >
      <div class="subcl" >
        <a href="people/<?=$username?>"  title="See profile admin" >
           <img class="float_left framed"  src="/"  height="50px"  width="50px"  alt="Profile" >
        </a>

        <p>
          <strong> from <a href="people/<?=$username?>"><?=$username?></a> </strong>
        </p>
        <p>
          <small>November 24, 2006, 9:59 am (UTC)</small>
          <em><?=$text?></em>
        </p>
  <hr />
        </P>
        <P></P>
      </div>
    </div>
    <div class="c25r" >
      <div class="subcr" >
        <ul class="linklist" >
<li>
  <a href="people/henri">henri</A>
</li>
<li>She/he belongs to my family</LI>
        </ul>
        <ul class="linklist" >
<li>
  <a href="feedback.php?IdCategory=4">Report a problem with this comment</A>
</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
}
?>
