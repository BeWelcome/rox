
<!-- #nav: main navigation -->
<div id="nav">

<!-- son of suckerfish navigation -->
  <script type="text/javascript"><!--//--><![CDATA[//><!--

    sfHover = function() {
        var sfEls = document.getElementById("nav_main").getElementsByTagName("li");
        for (var i=0; i<sfEls.length; i++) {
            sfEls[i].onmouseover=function() {
                this.className+=" sfhover";
            }
            sfEls[i].onmouseout=function() {
                this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
            }
        }
    }
    if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>

    <ul id="nav_main">
        <li id="logo">
          <a href="<?=$active_menu_item == ('main' || '') ? 'main' : ''; ?>">
            <img src="images/logo_index_top.png" alt="Be Welcome" />
          </a>
        </li>
  <?php if ($logged_in) { ?>
      <li><a href="members/<?=$username?>">My Account</a>
          <ul>
              <li><a href="members/<?=$username?>">Profile</a></li>
              <li><a href="editmyprofile">Edit Profile</a></li>
              <li><a href="mypreferences">My Preferences</a></li>
              <li><a href="messages">Messages</a></li>
          </ul>
      </li>
  <?php } ?>
        <li><a href="search">Find People</a>
            <ul>
                <li><a href="searchmembers">Map-search</a></li>
                <li><a href="places">Browse Countries</a></li>
            </ul>
        </li>
        <li><a href="explore">Explore</a>
            <ul>
                <li><a href="forums">Forum</a></li>
                <li><a href="groups">Groups</a></li>
                <li><a href="trip">Trips</a></li>
                <li><a href="gallery">Gallery</a></li>
                <li><a href="blog">Blogs</a></li>
                <li><a href="chat">Chat</a></li>
            </ul>
        </li>
        <li><a href="about">About BeWelcome</a>
            <ul>
                <li><a href="faq">FAQ</a></li>
                <li><a href="feedback">Contact Us</a></li>
                <li><a href="about/getactive">Get Active</a></li>
            </ul>
        </li>
    </ul>



<!-- old navigation -
    <ul>
        <li id="logo">
          <a href="<?=$active_menu_item == ('main' || '') ? 'main' : ''; ?>">
            <img src="images/logo_index_top.png" alt="Be Welcome" />
          </a>
        </li>
      <?php

foreach ($menu_items as $item) {
    $name = $item[0];
    $url = $item[1];
    $wordcode = $item[2];
    $not_translatable = isset($item[3]) && $item[3];
    if ($name === $active_menu_item) {
        $attributes = ' class="active"';
    } else {
        $attributes = '';
    }

      ?>
      <li<?=$attributes ?>>
        <a href="<?=$url ?>">
          <span><? if ($not_translatable) { echo $wordcode; } else { echo $words->getBuffered($wordcode); } ?></span>
        </a>
        <?=$words->flushBuffer(); ?>
        <ul>
            <li><a href="#">test</a></li>
            <li><a href="#">test</a></li>
            <li><a href="#">test</a></li>
        </ul>
      </li>
      <?php

}

      ?>
    </ul>
-->


<!-- show login fields or searchbox, depending if logged in or not-->

    <?php $this->quicksearch() ?>

</div> <!-- nav -->



