<?php
foreach ($comments as $c) {
    // var_dump($c);
?>
<div class="info clearfix">
  <h3>Comments for <?=$username?></h3>
  <div class="subcolumns">

    <div class="c75l" >
      <div class="subcl" >
        <a href="people/<?=$c->Username?>"  title="See admin's profile" >
           <img class="float_left framed"  src="/"  height="50px"  width="50px"  alt="Profile" >
        </a>
        <div style="display: block; float: left;">
        <p>
          <strong> from <a href="people/<?=$c->Username?>"><?=$c->Username?></a> </strong>
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
