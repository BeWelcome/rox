<script type="text/Javascript">
    var noMatchesFound = "<?php echo $words->getSilent('SearchNoMatchesFound');?>";
    var searchSimple = "<?php echo $words->getSilent('SearchMembersSimple');?>";
    var searchAdvanced = "<?php echo $words->getSilent('SearchMembersAdvanced');?>";
    var checkAllTextTranslation = "<?php echo $words->getSilent('SearchMembersCheckAll');?>";
    var uncheckAllTextTranslation = "<?php echo $words->getSilent('SearchMembersUncheckAll');?>";
    var noneSelectedTextTranslation = "<?php echo $words->getSilent('SearchMembersNoneSelected');?>";
    var selectedTextTranslation = "<?php echo $words->getSilent('SearchMembersSelect');?>";
</script><?php
$errors = $this->errors;
if (empty($this->vars)) {
    $vars = array();
    $vars['search-location'] = '';
    $vars['search-can-host'] = 1;
    $vars['search-distance'] = 25;
    $vars['search-geoname-id'] = 0;
    $vars['search-latitude'] = 0;
    $vars['search-longitude'] = 0;
    $vars['search-number-items'] = 10;
    $vars['search-sort-order'] = SearchModel::ORDER_ACCOM;
    $vars['search-page-current'] = 1;
    $vars['search-username'] = '';
    $vars['search-age-minimum'] = 0;
    $vars['search-age-maximum'] = 0;
    $vars['search-gender'] = 0;
    $vars['search-groups'] = array();
    $vars['search-accommodation'] = array('anytime', 'dependonrequest', 'neverask');
    $vars['search-typical-offer'] = array();
    $vars['search-text'] = '';
    $vars['search-membership'] = 0;
    $vars['search-languages'] = array();
    $this->vars = $vars;
}
$members = array();
$memberResultsReturned = false;
$locations = array();
$results = $this->results;
if ($results) {
    switch ($results['type']) {
        case 'members':
            $memberResultsReturned = true;
            $members = $results['values'];
            break;
        case 'places':
            $locations = $results['locations'];
            break;
        case 'admin1s':
            $locations = $results['locations'];
            break;
        case 'countries':
            $locations = $results['locations'];
            break;
    }
}

$orderBy = array();
$orderArray = SearchModel::getOrderByArray();
foreach ($orderArray AS $key => $order) :
    $orderBy[$key] = $words->getSilent($orderArray[$key]['WordCode'] . 'Asc');
    $orderBy[$key + 1] = $words->getSilent($orderArray[$key]['WordCode'] . 'Desc');
endforeach;

$layoutbits = new MOD_layoutbits();

// The whole page is in one form to be able to fill the fields with the correct content even
// when switching between pages of the result
?>
<div class="row"><!--  around form -->
<?php if (count($errors) > 0) :
    echo '<div class="error">';
    foreach ($errors as $error) :
        echo '<p>' . $words->get($error) . '</p>';
    endforeach;
    echo '</div>';
endif; ?>
<?php echo $this->layoutkit->formkit->setPostCallback('SearchController', 'searchMembersCallback'); ?>
<form method="get" id="searchmembers-form" name="searchmembers-form"action="">
<div class="greybackground">
    <table>
        <tr>
            <td>
                <label for='search-location'><span class="small"><?= $words->get('SearchEnterLocation'); ?></span></label><br/>
                    <div>
                        <input type="hidden" name="search-geoname-id" id="search-geoname-id"
                            value="<?php echo $this->vars['search-geoname-id']; ?>"/>
                        <input type="hidden" name="search-latitude" id="search-latitude"
                            value="<?php echo $this->vars['search-latitude']; ?>"/>
                        <input type="hidden" name="search-longitude" id="search-longitude"
                            value="<?php echo $this->vars['search-longitude']; ?>"/>
                        <input name="search-location" id="search-location" value="<?php echo $this->vars['search-location']; ?>"/>
                        <img id="search-loading"
                            src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" alt="<?php echo $words->getSilent("SearchMembersLoading"); ?>" />
                   </div>
                    <?php echo $words->flushBuffer(); ?>
            </td>
            <td>
                <span class="small"><?= $words->get('SearchCanHostAtLeast'); ?></span><br/> <select id="search-can-host"
                name="search-can-host"><?php
                $canHost = array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 10 => '10', 20 => '20');
                foreach ($canHost as $value => $display) :
                    echo '<option value="' . $value . '"';
                    if ($value == $this->vars['search-can-host']) {
                        echo ' selected="selected"';
                    }
                    echo '>' . $display . '</option>';
                endforeach;
                ?></select>
            </td>
            <td>
                <span class="small"><?= $words->get('SearchDistance'); ?></span><br/> <select id="search-distance"
                name="search-distance"><?php
                $distance = array(0 => $words->getSilent("SearchExactMatch"), 5 => '5 km/3 mi', 10 => '10 km/6 mi', 25 => '25 km/15 mi', 50 => '50 km/30 mi', 100 => '100 km/60 mi');
                foreach ($distance as $value => $display) :
                    echo '<option value="' . $value . '"';
                    if ($value == $this->vars['search-distance']) {
                        echo ' selected="selected"';
                    }
                    echo '>' . $display . '</option>';
                endforeach;
                ?></select><?php echo $words->flushBuffer(); ?>
            </td>
            <td class="float_right">
                <br/><input id="search-submit-button" name="search-submit-button" class="button" type="submit"
            value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"/>
            </td>
        </tr>
    </table>
    <div id="search-advanced" class="floatbox">
        <?php if ($this->showAdvanced) {
                $vars = $this->vars; // Needed because advanced options might be loaded through ajax as well
                require_once('advancedoptions.php');
            } ?>
    </div>
</div>
<div class="advance-container">
        <div class="advance-link">
        <?php if ($this->showAdvanced) { ?>
            <a name="search-simple"
                href="search/members/text"><?php echo $words->getFormatted('SearchMembersSimple'); ?></a>
        <?php } else { ?>
            <a name="search-advanced"
                href="search/members/text/advanced"><?php echo $words->getFormatted('SearchMembersAdvanced'); ?></a>
        <?php } ?>
    </div>
</div>

<div class="floatbox row">
    <div class="row"><?php
        $numberOfItems = array('5', '10', '20', '50', '100');
        $select = '<select name="search-number-items">';
        foreach ($numberOfItems as $number) :
            $select .= '<option value="' . $number . '"';
            if ($this->vars['search-number-items'] == $number) :
                $select .= ' selected="selected"';
            endif;
            $select .= ' >' . $number . '</option>';
        endforeach;
        $select .= '</select>';
        echo $words->get('SearchShowItems', $select);
        $select = '<select name="search-sort-order">';
        foreach ($orderBy AS $key => $order) :
            $select .= '<option value="' . $key . '"';
            if ($this->vars['search-sort-order'] == $key) :
                $select .= ' selected="selected"';
            endif;
            $select .= '>' . $order . '</option>';
        endforeach;
        $select .= '</select>';
        echo $words->get('SearchOrderItems', $select); ?></div>

</div>
<div class="floatbox">
    <?php if (!$results) : ?>
        <?php echo $words->get('SearchInfo'); ?>
    <?php endif; ?>
</div>
<div><?php
if ($memberResultsReturned) :
    if (!$this->member) :
        if ($results['countOfMembers'] != $results['countOfPublicMembers']) :
            echo '<p>' . $words->get('SearchShowMore', $words->getSilent('SearchShowMoreLogin'), '<a href="/login/search#login-widget">', '</a>');
            echo $words->flushBuffer() . '</p>';
        endif;
    endif;
    if (!empty($members)) :
        // Initialise pager widget
        $params = new StdClass;
        $params->strategy = new FullPagePager();
        $params->page_url = "/search/members/text?" . http_build_query($this->vars);
        $params->page_url_marker = 'search-page-';
        $params->page_method = 'form';
        if ($this->member) {
            $params->items = $results['countOfMembers'];
        } else {
            $params->items = $results['countOfPublicMembers'];
        }
        $params->active_page = $this->vars['search-page-current'];
        $params->items_per_page = $this->vars['search-number-items'];
        $pager = new PagerWidget($params);
        $pager->render();?>
        <table id="memberresults">
            <thead>
            <tr>
                <th colspan="2"><?php echo $words->get('SearchHeaderMember'); ?></th>
                <th><?php echo $words->get('ProfileSummary'); ?></th>
                <th><?php echo $words->get('SearchHeaderDetails'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $ii = 0;
            // $markers contains the necessary array setup for Javascript to get the markers onto the map
            foreach ($members as $member) {
                $accommodationIcon = ShowAccommodation($member->Accomodation);
                $offerIcons = GetOfferIcons($member->TypicOffer);
                // replace line breaks '\r\n' by html line break element '<br/>'
                $profileSummary = $this->purifier->purify($member->ProfileSummary);
                $occupation = $this->purifier->purify($member->Occupation);
                echo '<tr class="' . (($ii % 2) ? 'blank' : 'highlight') . '">';
                echo '<td class="memberleft">';
                echo '<div class="picture"><div>' . $layoutbits->PIC_75_75($member->Username, 'class="framed"') . '</div>';
                echo '<div><a href="members/' . $member->Username . '" target="_blank">' . $member->Username . '</a></div>';
                echo '</div>';
                echo '</td><td class="memberright">';
                echo '<div class="left">';
                echo '<strong><a href="members/' . $member->Username . '" target="_blank">' . (empty($member->Name) ? $member->Username : $member->Name) . '</a></strong>';
                if ($member->MessageCount) {
                    echo '<a href="messages/with/' . $member->Username . '"><img src="images/icons/comments.png" alt="'
                        . $words->getSilent('messages_allmessageswith', $member->Username)
                        . '" title="' . $words->getSilent('messages_allmessageswith', $member->Username) . '" /></a>';
                }
                echo '<br />';
                $prefix = "";
                if (!empty($member->Age)) {
                    echo $words->get('SearchYearsOld', $member->Age);
                    $prefix = ", ";
                }
                $gender = $layoutbits->getGenderTranslated($member->Gender, $member->HideGender, false);
                if (!empty($gender)) {
                    echo $prefix . $gender;
                }
                echo '<br />';
                echo $member->CityName . ", " . $member->CountryName . '<br />';
                echo '<br />';
                echo $member->Occupation . '</div>';
                echo '</td>';
                echo '<td class="summary">' . $profileSummary . '</td>';
                echo '<td class="details"><div class="red"><div class="left">' . $accommodationIcon . '</div>'
                    . '<div>' . $words->get('SearchMaxGuestInfo', '<strong>' . $member->MaxGuest . '</strong>') . '<br />'
                    . $words->get('SearchCommentsInfo', '<strong>' . $member->CommentCount . '</strong>') . '</div></div>';
                echo '<div class="clearfix"></div>' . $offerIcons . '<br />';
                echo $words->get('SearchMemberSinceInfo', '<strong>' . date('Y-m-d', strtotime($member->created)) . '</strong>') . '<br />';
                $lastlogin = (($member->LastLogin == '0000-00-00') ? 'Never' : $layoutbits->ago(strtotime($member->LastLogin)));
                $class = 'red';
                if ($member->LastLogin <> '0000-00-00') {
                    switch ($layoutbits->ago_qualified(strtotime($member->LastLogin))) {
                        case 0:
                            $class = 'green';
                            break;
                        case 1:
                            $class = 'orange';
                            break;
                        case 2:
                            $class = 'red';
                            break;
                    }
                }

                echo $words->get('SearchMemberLastLoginInfo', '<span class="' . $class . '">' . $lastlogin . '</span>');
                echo "</td></tr>\n";
                $ii++;
            }
            ?>
            </tbody>
        </table>
        <?php
        $pager->render();
    else:
        echo $words->get('SearchMembersNoneFound');
    endif;
endif;
if (!empty($locations)) :
    echo '<p>' . $words->get('SearchSelectLocation') . '</p>';
    if (isset($results['biggest'])) :
        $i = 0;
        foreach ($results['biggest'] as $big) :
            if ($big->cnt > 0) :
                switch ($i % 3) :
                    case 0 :
                        echo '<div class="floatbox">
                    <div class="subcolumns row"><div class="c33l">';
                        break;
                    case 1 :
                        echo '<div class="c33l">';
                        break;
                    case 2 :
                        echo '<div class="c33r">';
                        break;
                endswitch;
                echo '<span id="geoname' . $big->geonameid . '"><input type="submit" id="geonameid-' . $big->geonameid . '" name="geonameid-' . $big->geonameid . '" value="' . htmlentities($big->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                    . htmlentities($big->admin1, ENT_COMPAT, 'utf-8') . ', ' . htmlentities($big->country, ENT_COMPAT, 'utf-8') . ', ';
                if ($big->cnt == 0) :
                    echo $words->get('SearchSuggestionsNoMembersFound');
                else :
                    echo $words->get('SearchSuggestionsMembersFound', $big->cnt);
                endif;
                echo '</span></div>';
                if ($i % 3 == 2) :
                    echo '</div>';
                endif;
                $i++;
            endif;
        endforeach;
        if ($i % 3 != 0) :
            echo '</div>';
        endif;
        if ($i != 0) :
            echo '</div>';
        endif;
    endif;
    switch ($results['type']) :
        case "places":
            ?>
            <div class="floatbox">
            <?php
            $i = 0;
            foreach ($locations as $location) :
                switch ($i % 3) :
                    case 0 :
                        echo '<div class="subcolumns row"><div class="c33l">';
                        break;
                    case 1 :
                        echo '<div class="c33l">';
                        break;
                    case 2 :
                        echo '<div class="c33r">';
                        break;
                endswitch;
                echo '<span id="geoname' . $location->geonameid . '"><input type="submit" id="geonameid-' . $location->geonameid . '" name="geonameid-' . $location->geonameid . '" value="' . htmlentities($location->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                    . ((isset($location->admin1)) ? htmlentities($location->admin1, ENT_COMPAT, 'utf-8') . ', ' : '') . htmlentities($location->country, ENT_COMPAT, 'utf-8') . ', ';
                if ($location->cnt == 0) :
                    echo $words->get('SearchSuggestionsNoMembersFound');
                else :
                    echo $words->get('SearchSuggestionsMembersFound', $location->cnt);
                endif;
                echo '</span></div>';
                if ($i % 3 == 2) :
                    echo '</div>';
                endif;
                $i++;
            endforeach;
            if ($i % 3 != 0) :
                echo '</div>';
            endif;?></div><?php
            break;
        case "admin1s":
            ?>
            <div class="floatbox">
            <?php
            $i = 0;
            foreach ($locations as $location) :
                switch ($i % 3) :
                    case 0 :
                        echo '<div class="subcolumns row"><div class="c33l">';
                        break;
                    case 1 :
                        echo '<div class="c33l">';
                        break;
                    case 2 :
                        echo '<div class="c33r">';
                        break;
                endswitch;
                echo '<input type="submit" name="admin1-' . htmlentities($location->admin1, ENT_COMPAT, 'utf-8') . '" value="' . htmlentities($location->admin1, ENT_COMPAT, 'utf-8') . '" />';
                echo '</div>';
                if ($i % 3 == 2) :
                    echo '</div>';
                endif;
                $i++;
            endforeach;
            if ($i % 3 != 0) :
                echo '</div>';
            endif;?></div><?php
            break;
        case "countries":
            ?>
            <div class="floatbox">
            <?php
            $i = 0;
            foreach ($locations as $location) :
                switch ($i % 3) :
                    case 0 :
                        echo '<div class="subcolumns row"><div class="c33l">';
                        break;
                    case 1 :
                        echo '<div class="c33l">';
                        break;
                    case 2 :
                        echo '<div class="c33r">';
                        break;
                endswitch;
                echo '<input type="submit" name="country-' . htmlentities($location->code, ENT_COMPAT, 'utf-8') . '" value="' . htmlentities($location->country, ENT_COMPAT, 'utf-8') . '" />';
                echo '</div>';
                if ($i % 3 == 2) :
                    echo '</div>';
                endif;
                $i++;
            endforeach;
            if ($i % 3 != 0) :
                echo '</div>';
            endif;?></div><?php
            break;
    endswitch;
endif;
?>
</div>
</form>
</div><!-- around form-->
<?php
function ShowAccommodation($accommodation)
{
    $words = new MOD_words();
    switch($accommodation) {
        case 'anytime':
            $imgSrc = 'images/icons/yesicanhost.png';
            break;
        case 'dependonrequest':
            $imgSrc = 'images/icons/yesicanhost.png';
            break;
        case 'neverask':
            $imgSrc = 'images/icons/yesicanhost.png';
            break;
    }

    $altText = $words->getSilent('Accomodation_' . $accommodation);
    $imgTag = '<img src="' . $imgSrc . '" title="' . $altText . '" '
        . ' alt="' . $altText . '" />';
    return $imgTag;
}

function GetOfferIcons($offer)
{
    $words = new MOD_words();
    $icons = '';
    if (strstr($offer, "CanHostWeelChair")) {
        $icons .= '<img src="images/icons/wheelchairblue.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_CanHostWheelChair') . '" title="'
            . $words->getSilent('TypicOffer_CanHostWheelChair') . '" />';
    }
    if (strstr($offer, "dinner")) {
        $icons .= '<img src="images/icons/dinner.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_dinner') . '" title="'
            . $words->getSilent('TypicOffer_dinner') . '" />';
    }
    if (strstr($offer, "guidedtour")) {
        $icons .= '<img src="images/icons/guidedtour.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_guidedtour') . '" title="'
            . $words->getSilent('TypicOffer_guidedtour') . '" />';
    }
    return $icons;
}
?>