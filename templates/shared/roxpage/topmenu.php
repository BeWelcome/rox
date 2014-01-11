
<!-- #nav: main navigation -->
<div id="nav">

<!-- son of suckerfish navigation (script to teach IE hover class used in dropdown menu-->
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
      <li><a href="members/<?=$username?>"><?=$words->get('MyProfile')?></a>
          <ul>
              <li><a href="members/<?=$username?>"><?=$words->get('Profile')?></a></li>
              <li><a href="editmyprofile"><?=$words->get('EditMyProfile')?></a></li>
              <li><a href="mypreferences"><?=$words->get('MyPreferences')?></a></li>
              <li><a href="messages"><?=$words->get('MyMessages')?></a></li>
              <li><a href="mynotes"><?=$words->get('ProfileMyNotes')?></a></li>
              <li><a href="groups/mygroups"><?=$words->get('MyGroups')?></a></li>
          </ul>
      </li>
  <?php } ?>
        <li><a href="search"><?=$words->get('FindMembers')?></a>
            <ul>
                <li><a href="searchmembers"><?=$words->get('MapSearch')?></a></li>
                <li><a href="places"><?=$words->get('BrowseCountries')?></a></li>
            </ul>
        </li>
        <li><a href="community"><?=$words->get('CommunityMenu')?></a>
            <ul>
                <li><a href="forums" title="<?=$words->getSilent('AgoraTagLine')?>"><?=$words->get('CommunityDiscussions')?></a><?php echo $words->flushBuffer(); ?></li>
                <li><a href="groups/search" title="<?=$words->getSilent('GroupsTagLine')?>"><?=$words->get('Groups')?></a><?php echo $words->flushBuffer(); ?></li>
                <li><a href="activities"><?=$words->get('Activities')?></a></li>
                <?php // if ($logged_in) { ?>
                <li><a href="suggestions"><?=$words->get('Suggestions')?></a></li>
                <?php // } ?>
                <li><a href="trip"><?=$words->get('Trips')?></a></li>
                <li><a href="blog"><?=$words->get('Blogs')?></a></li>
                <li><a href="wiki"><?=$words->get('Wiki')?></a></li>
            </ul>
        </li>
        <li><a href="safety"><?=$words->get('Safety')?></a></li>
        <li><a href="about"><?=$words->get('GetAnswers')?></a>
            <ul>
                <li><a href="faq"><?=$words->get('Faq')?></a></li>
                <li><a href="feedback"><?=$words->get('ContactUs')?></a></li>
                <li><a href="about/getactive"><?=$words->get('About_GetActive')?></a></li>
                <li><a href="donate"><?=$words->get('DonateLink')?></a></li>
            </ul>
        </li>
        <? if (isset($volunteer) && $volunteer) { ?>
        <li><a href="volunteer"><?=$words->get('Volunteer')?></a>
            <?=$this->volunteerMenu() ?>
        </li>
        <? } ?>
    </ul>

<!-- show login fields or searchbox, depending if logged in or not-->
    <?php $this->quicksearch() ?>

</div> <!-- nav -->



