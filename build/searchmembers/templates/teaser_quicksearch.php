<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="page-teaser clearfix">
    <h1><?php echo $words->getFormatted('quicksearchTitle'); ?></h1>
    <div>
        <div id="searchteaser" style="width: 40%"  class="float_left">
            <fieldset id="searchtop" name="searchtop">
            <strong class="small"><?php echo $words->getFormatted('FindPeopleEnterSomething'); ?></strong><br />
            <form action="searchmembers/quicksearch" method="get">
            <input type="text" name="searchtext" size="25" maxlength="30" id="text-field" value="Search...." onfocus="this.value='';"/>
            <?php PPostHandler::setCallback('quicksearch_callbackId', 'SearchmembersController', 'index'); ?>
            <input type="hidden" name="quicksearch_callbackId" value="1"/>
            <input type="submit" class="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>" id="submit-button" class="button" />
            </form>
            &nbsp; &nbsp; &nbsp; 
            </fieldset>
        </div>   
        <div id="searchteaser2" style="width: 40%" class="float_left">
            <p><a href="searchmembers/mapon"><?php echo $words->getFormatted('TryMapSearch'); ?>!</a></p>
        </div>
    </div>
</div>
