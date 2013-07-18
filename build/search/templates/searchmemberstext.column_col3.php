<?php
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['search-location'] = '';
    $vars['search-can-host'] = 1;
    $vars['search-number-items'] = 10;
    $vars['search-sort-order'] = SearchModel::ORDER_NAME_DESC;
}
$members = array ();
$locations = array ();
$results = $this->getRedirectedMem('results');
if ($results) {
    switch ($results['type']) {
        case 'members':
            $members = $results['values'];
            break;
        case 'locations':
            $locations = $results['values'];
            break;
    }
}

$Accommodation = array();
$Accommodation['anytime'] = $words->getBuffered('Accomodation_anytime');
$Accommodation['dependonrequest'] = $words->getBuffered('Accomodation_dependonrequest');
$Accommodation['neverask'] = $words->getBuffered('Accomodation_neverask');

$layoutbits = new MOD_layoutbits();

// The whole page is in one form to be able to fill the fields with the correct content even
// when switching between pages of the result
?>
<div>
	<form method="post" name="searchmembers-form"
		style="padding-bottom: 0.5em; width: 100%;">
        <?php echo $this->layoutkit->formkit->setPostCallback('SearchController', 'searchMembersSimpleCallback');?>
        <div class="floatbox bottom" style="width:100%">
			<div class="float_left">
				<label for='search-location'><span class="small"><?=$words->get('SearchEnterLocation');?></span></label><br />
				<div>
					<input type="hidden" name="search-geoname-id"
						id="search-geoname-id" value="0" /> <input name="search-location"
						id="search-location"
						value="<?php echo $vars['search-location']; ?>" /> <img
						id="search-loading" style="visibility: hidden"
						src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" />
					<noscript>
						<br /> <span class="small"><?php echo $words->get('SearchLocationFormat');?></span>
					</noscript>
				</div>
        <?php echo $words->flushBuffer(); ?></div>
			<div class="float_left">
				<span class="small"><?=$words->get('CanHost');?></span><br /> <select
					id="search-can-host" name="search-can-host" style="width: 5em;"><?php
    for($ii = 1; $ii < 30; $ii++) :
        echo "<option value='{$ii}'>{$ii}</option>";
    endfor
    ;
    ?></select>
			</div>
		</div>
<div class="floatbox" clear: both;">
<label class="labela"><input type="image" src="images/icons/expand.png" id="search-advanced-image" name="search-advanced-image" align="top"/> <?php echo $words->getFormatted('SearchMembersAdvanced'); ?></label>
</div>
<div class="floatbox">
<div class="float_left"><?php
    $numberOfItems = array( '5', '10', '20', '50', '100'); ?><label for="search-number-items">Show </label><select name="search-number-items"><?php
        foreach ($numberOfItems as $number) :
            echo '<option value="' . $number . '"';
            if ($vars['search-number-items']) :
                echo ' selected="selected"';
            endif;
            echo ' >' . $number . '</option>';
        endforeach;?></select><span> items per page. </span><label for="search-order">Order by </label><select name="search-items"><option>name desc</option><option>name asc</option><option>age asc</option></select></div>

			<div class="float_right">
				<input
					id="search-reset-button" name="search-reset-button" class="button"
					type="reset"
					value="<?php echo $words->getBuffered('SearchClearValues'); ?>" />
			</div>

			<div class="float_right">
				<input
					id="search-submit-button" name="search-submit-button"
					class="button" type="submit"
					value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>" />
			</div>
</div>
<div class="floatbox">
		<?php if (!$results) : ?>
		<span id="search-search-info"><?php echo $words->get('SearchInfo'); ?></span>
		<?php endif; ?>
</div>
    <div><?php
    if (!empty($members)) :
        echo $results['count'] . "<br>";
        // Initialise pager widget
        $params = new StdClass;
        $params->strategy = new FullPagePager();
        $params->page_url = 'search/members/text';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $results['count'];
        $params->active_page = 1;
        $params->items_per_page = 5;
        $pager = new PagerWidget($params);
        $pager->render();?>
<table class="full" style="width: 100%">
<thead>
<tr><th colspan="2">Member</th><th>Profile Summary</th><th>Details</th></tr>
</thead>
<tbody>
<?php
$ii = 0;
// $markers contains the necessary array setup for Javascript to get the markers onto the map
foreach($members as $member) {
    $accomodationIcon = ShowAccommodation($member->Accomodation, $Accommodation);
    $offerIcons = GetOfferIcons($member->TypicOffer);
    $restrictionIcons = GetRestrictionIcons($member->Restrictions);
    // replace line breaks '\r\n' by html line break element '<br/>'
    $profileSummary = str_replace("\\r\\n", "<br/>", $member->ProfileSummary);
    $occupation = str_replace("\\r\\n", "<br/>", $member->Occupation);
    echo '<tr class="' . (($ii % 2) ? 'blank' : 'highlight') . '">';
    echo '<td style="width: 95px; padding-right:1ex; text-align:center; vertical-align: top; word-wrap: break-word;">';
    echo '<div padding-right: 1em; text-align: center"><div>' . $layoutbits->PIC_75_75($member->Username, 'class="framed"') . '</div>';
    echo '<div><a href="members/' . $member->Username . '" target="_blank">'.$member->Username.'</a></div>';
    echo '</div>';
    echo '</td><td style="padding-right:1ex;vertical-align: top;">';
    echo '<div style="float:left;">';
    echo '<strong><a href="members/' . $member->Username . '" target="_blank">'.(empty($member->Name) ? $member->Username : $member->Name).'</a></strong>';
    echo '<br>' . $words->get('SearchYearsOld', $member->Age);
    if (!$member->HideGender) {
        echo ", " . $layoutbits->getGenderTranslated($member->Gender, $member->HideGender, false);
    }
    echo '<br>';
    echo $member->CityName . ", " . $member->CountryName .'<br>';
    echo '<div style="display: block; padding-top:0.5em";>';
    echo $member->Occupation . '</div></div>';
    echo '</div>';
    echo '</td>';
    echo '<td style="width: 50%; padding-right:1ex; align:left; vertical-align: top;">' .$profileSummary.'</td>';
    echo '<td style="width: 20%; align:left; vertical-align: top;"><div class="red" style="display: block;"><div style="float: left; padding-right:1ex;">'.$accomodationIcon . '</div><div>Max. guests: <strong>' . $member->MaxGuest . '</strong><br><strong>' .
      $member->CommentCount .
      '</strong> comments</div></div>';
    echo '<div class="clearfix"></div>' . $offerIcons . $restrictionIcons . '<br>';
    echo 'Member since: <strong>' . date('d M y', strtotime($member->created)) . '</strong><br>';
    $lastlogin = (($member->LastLogin == '0000-00-00') ? 'Never' : $layoutbits->ago(strtotime($member->LastLogin)));
    $class = 'style="color: red;"';
    if ($member->LastLogin <> '0000-00-00')
    {
        switch($layoutbits->ago_qualified(strtotime($member->LastLogin)))
        {
            case 0:
                $class = 'style="color: green;"';
                break;
            case 1:
                $class = 'style="color: orange;"';
                break;
            case 2:
                $class = 'style="color: red;"';
                break;
        }
    }

    echo 'Last login: <span ' . $class . '>' . $lastlogin . '</span></td></tr>';
    echo "\n";
    $ii++;
}
?>
</tbody></table>
<?php
        $pager->render();
    endif;

    if (!empty($locations)) :
        print_r($locations);
        foreach ( $locations as $location ) :
            print_r($locations, true);
        endforeach;
    endif;
    ?>
    </div>
	</form>
</div>
<?php
function ShowAccommodation($accom, $Accommodation)
{
    if ($accom == "anytime")
       return "<img src=\"images/icons/yesicanhost.png\" title=\"".$Accommodation['anytime']."\"  alt=\"yesicanhost\" />";
    if (($accom == "dependonrequest") || ($accom == ""))
       return "<img src=\"images/icons/maybe.png\" title=\"".$Accommodation['dependonrequest']."\"  alt=\"dependonrequest\"   />";
    if ($accom == "neverask")
       return "<img src=\"images/icons/nosorry.png\" title=\"".$Accommodation['neverask']."\"  alt=\"neverask\" />";
}
function GetOfferIcons($offer)
{
    $words = new MOD_words();
    $icons = '';
    if (strstr($offer, "CanHostWeelChair"))
    {
        $icons .= '<img src="images/icons/wheelchairblue.png" width="22" height="22" alt="' . $words->getSilent('wheelchair') . '" title="' . $words->getSilent('CanHostWeelChairYes') . '" />';
    }
    if (strstr($offer, "dinner"))
    {
        $icons .= '<img src="images/icons/dinner.png" width="22" height="22" alt="' . $words->getSilent('dinner') . '" title="' . $words->getSilent('dinner') . '" />';
    }
    if (strstr($offer, "guidedtour"))
    {
        $icons .= '<img src="images/icons/guidedtour.png" width="22" height="22" alt="' . $words->getSilent('guidedtour') . '" title="' . $words->getSilent('guidedtour') . '" />';
    }
    return $icons;
}

function GetRestrictionIcons($restrictions)
{
    $words = new MOD_words();
    $icons = '';
    if (strstr($restrictions, "NoSmoker"))
    {
        $icons .= '<img src="images/icons/no-smoking.png" width="22" height="22" alt="' . $words->getSilent('wheelchair') . '" title="' . $words->getSilent('CanHostWeelChairYes') . '" />';
    }
    if (strstr($restrictions, "NoAlchool"))
    {
        $icons .= '<img src="images/icons/no-alcohol.png" width="22" height="22" alt="' . $words->getSilent('dinner') . '" title="' . $words->getSilent('dinner') . '" />';
    }
    if (strstr($restrictions, "NoDrugs"))
    {
        $icons .= '<img src="images/icons/no-drugs.png" width="22" height="22" alt="' . $words->getSilent('guidedtour') . '" title="' . $words->getSilent('guidedtour') . '" />';
    }
    return $icons;
}
?>