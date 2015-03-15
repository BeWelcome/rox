<!-- #nav: main navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bewelcome-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?=$active_menu_item == ('main' || '') ? 'main' : ''; ?>"><img height="19px" src="images/logo_index_top.png" alt="Be Welcome" /></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bewelcome-navbar-collapse-1">
    <ul class="nav navbar-nav">
<?php if ($logged_in) { ?>
        <li class="dropdown">
          <a href="members/<?=$username?>" class="dropdown-toggle" data-toggle="dropdown"><?=$words->get('MyProfile')?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
              <li><a href="members/<?=$username?>"><?=$words->get('Profile')?></a></li>
              <li><a href="editmyprofile"><?=$words->get('EditMyProfile')?></a></li>
              <li><a href="mypreferences"><?=$words->get('MyPreferences')?></a></li>
              <li><a href="messages"><?=$words->get('MyMessages')?></a></li>
              <li><a href="mynotes"><?=$words->get('ProfileMyNotes')?></a></li>
              <li><a href="groups/mygroups"><?=$words->get('MyGroups')?></a></li>
          </ul>
        </li>
<?php } ?>
        <li class="dropdown">
          <a href="search" class="dropdown-toggle" data-toggle="dropdown"><?=$words->get('FindMembers')?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="searchmembers"><?=$words->get('MapSearch')?></a></li>
                <li><a href="search"><?=$words->get('TextSearch')?></a></li>
            <li><a href="places"><?=$words->get('BrowseCountries')?></a></li>
            <li><a href="search"><?=$words->get('FindMembers')?></a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=$words->get('CommunityMenu')?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="forums" title="<?=$words->getSilent('AgoraTagLine')?>"><?=$words->get('CommunityDiscussions')?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="groups/search" title="<?=$words->getSilent('GroupsTagLine')?>"><?=$words->get('Groups')?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="activities"><?=$words->get('Activities')?></a></li>
            <li><a href="suggestions"><?=$words->get('Suggestions')?></a></li>
            <li><a href="trip"><?=$words->get('Trips')?></a></li>
            <li><a href="blog"><?=$words->get('Blogs')?></a></li>
            <li><a href="wiki"><?=$words->get('Wiki')?></a></li>
          </ul>
        </li>
        <li>
        <a href="safety"><?=$words->get('Safety')?></a>
        </li>
        <li class="dropdown">
          <a href="about" class="dropdown-toggle" data-toggle="dropdown"><?=$words->get('GetAnswers')?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
                <li><a href="faq"><?=$words->get('Faq')?></a></li>
                <li><a href="feedback"><?=$words->get('ContactUs')?></a></li>
                <li><a href="about/getactive"><?=$words->get('About_GetActive')?></a></li>
                <li><a href="donate"><?=$words->get('DonateLink')?></a></li>
          </ul>
        </li>
<? if (isset($volunteer) && $volunteer) { ?>
        <li class="dropdown">
          <a href="volunteer" class="dropdown-toggle" data-toggle="dropdown"><?=$words->get('Volunteer')?> <b class="caret"></b></a>
          <?=$this->volunteerMenu() ?>
        </li>
<? } ?>
      </ul>
        <?php $this->quicksearch() ?>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container -->
</nav>