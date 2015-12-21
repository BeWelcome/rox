<script type="text/Javascript">
    var noMatchesFound = "<?php echo $words->getSilent('SearchNoMatchesFound');?>";
    var searchSimple = "<?php echo $words->getSilent('SearchMembersSimple');?>";
    var searchAdvanced = "<?php echo $words->getSilent('SearchMembersAdvanced');?>";
    var checkAllTextTranslation = "<?php echo $words->getSilent('SearchMembersCheckAll');?>";
    var uncheckAllTextTranslation = "<?php echo $words->getSilent('SearchMembersUncheckAll');?>";
    var noneSelectedTextTranslation = "<?php echo $words->getSilent('SearchMembersNoneSelected');?>";
    var selectedTextTranslation = "<?php echo $words->getSilent('SearchMembersSelected');?>";
</script><?php

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
<div class="bw_row"><!--  around form -->
<?php if (count($this->errors) > 0) :
    echo '<div class="error">';
    foreach ($this->errors as $error) :
        echo '<p>' . $words->get($error) . '</p>';
    endforeach;
    echo '</div>';
endif; ?>
<?php echo $this->layoutkit->formkit->setPostCallback('SearchController', 'searchMembersCallback'); ?>
<form method="get" id="searchmembers-form" action="#">
<div class="greybackground">
    <table class="full">
        <tr>
            <td>
                <label for='location'><span class="small"><?= $words->get('SearchEnterLocation'); ?></span></label><br/>
                    <div>
                        <input type="hidden" name="location-geoname-id" id="location-geoname-id"
                            value="<?php echo $this->vars['location-geoname-id']; ?>"/>
                        <input type="hidden" name="location-latitude" id="location-latitude"
                            value="<?php echo $this->vars['location-latitude']; ?>"/>
                        <input type="hidden" name="location-longitude" id="location-longitude"
                            value="<?php echo $this->vars['location-longitude']; ?>"/>
                        <input name="location" id="location" class="location-picker" value="<?php echo $this->vars['location']; ?>"/>
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
            <td>
                <br/><input id="search-submit-button" name="search-submit-button" class="button" type="submit"
            value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"/>
            </td>
        </tr>
    </table>
    <div id="search-advanced" class="clearfix">
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
                href="/search/members/text"><?php echo $words->getFormatted('SearchMembersSimple'); ?></a>
        <?php } else { ?>
            <img id="search-advanced-loading"
                 src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" style="width:10px" alt="<?php echo $words->getSilent("SearchMembersAdvancedLoading"); ?>" />
            <a name="search-advanced"
                href="/search/members/text/advanced"><?php echo $words->getFormatted('SearchMembersAdvanced'); ?></a>
        <?php } ?>
    </div>
</div>

<div class="clearfix bw_row">
    <div class="bw_row"><?php
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
<div class="clearfix">
    <?php if (!$this->results) : ?>
        <?php echo $words->get('SearchInfo'); ?>
    <?php endif; ?>
</div>
<div><?php
if ($this->membersResultsReturned) :
    $loginMessageShown = false;
    if (!$this->member) :
        if ($this->results['countOfMembers'] != $this->results['countOfPublicMembers']) :
            echo '<p>' . $words->get('SearchShowMore', $words->getSilent('SearchShowMoreLogin'), '<a href="/login' . "/search/members/text?" . http_build_query($this->vars) . '&search-page-#login-widget">', '</a>');
            echo $words->flushBuffer() . '</p>';
            $loginMessageShown = true;
        endif;
    endif;
    if (!empty($this->results['members'])) :
        $this->pager->render();?>
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
            foreach ($this->results['members'] as $member) {
                $accommodationIcon = ShowAccommodation($member->Accomodation);
                // replace line breaks '\r\n' by html line break element '<br/>'
                $profileSummary = $this->purifier->purify($member->ProfileSummary);
                $occupation = $this->purifier->purify($member->Occupation);
                echo '<tr class="' . (($ii % 2) ? 'blank' : 'highlight') . '">';
                echo '<td class="memberleft">';
                echo '<div class="picture"><div>' . $layoutbits->PIC_75_75($member->Username) . '</div>';
                echo '<div><a href="/members/' . $member->Username . '" target="_blank">' . $member->Username . '</a></div>';
                echo '</div>';
                echo '</td><td class="memberright">';
                echo '<div class="left">';
                echo '<strong><a href="/members/' . $member->Username . '" target="_blank">' . (empty($member->Name) ? $member->Username : $member->Name) . '</a></strong>';
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
                echo '<div class="clearfix"></div>';
                echo $words->get('SearchMemberSinceInfo', '<strong>' . date('d M y', strtotime($member->created)) . '</strong>') . '<br />';
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
        $this->pager->render();
    else:
        if (!$loginMessageShown) {
            echo $words->get('SearchMembersNoneFound');
        }
    endif;
endif;
if ($this->locationsResultsReturned) :
    echo '<p><strong>' . $words->get('SearchSelectLocation') . '</strong></p>';
    echo '<div class="clearfix">';
    if (isset($this->results['biggest'])) :
        // biggest
        $i = 0;
        foreach ($this->results['biggest'] as $big) :
            if ($big->cnt == 0) continue;
            $class = 'c33l';
            if ($i % 3 == 0) {
                echo '
                        <div class="subcolumns bw_row">';
            };
            if ($i % 3 == 2) {
                $class = 'c33r';
            }
            echo '<div class="' . $class . '"><span id="geoname' . $big->geonameid . '"><input type="submit" id="geonameid-' . $big->geonameid . '" name="geonameid-' . $big->geonameid . '" value="' . htmlentities($big->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                . htmlentities($big->admin1, ENT_COMPAT, 'utf-8') . ', ' . htmlentities($big->country, ENT_COMPAT, 'utf-8') . ', ' . $words->get('SearchSuggestionsMembersFound', $big->cnt);
            echo '</span></div>';
            if ($i %3 == 2) {
                echo '</div>';
            }
            $i++;
        endforeach;
        if ($i % 3 != 0) :
            echo '</div>';
        endif;
        if ($i != 0) :
            echo '</div>';
        endif;
    endif;
    $i = 0;
    switch($this->results['type']) {
        case 'admin1s':
            $type = 'admin1';
            break;
        case 'countries':
            $type = 'country';
            break;
        default:
            $type = '';
    }
    foreach ($this->locations as $location) :
        $class = 'c33l';
        if ($i % 3 == 0) {
            echo '<div class="subcolumns bw_row">';
        }
        if ($i % 3 == 2) {
            $class = 'c33r';
        }
        echo '<div class="' . $class . '">';
        if (empty($type)) :
            echo '<span id="geoname' . $location->geonameid . '"><input type="submit" id="geonameid-' . $location->geonameid . '" name="geonameid-' . $location->geonameid . '" value="' . htmlentities($location->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                . ((isset($location->admin1)) ? htmlentities($location->admin1, ENT_COMPAT, 'utf-8') . ', ' : '') . htmlentities($location->country, ENT_COMPAT, 'utf-8') . ', ';
            if ($location->cnt == 0) :
                echo $words->get('SearchSuggestionsNoMembersFound');
            else :
                echo $words->get('SearchSuggestionsMembersFound', $location->cnt);
            endif;
            echo '</span>';
        else :
            if ($type == 'admin1') :
                $text = $location->admin1;

            else :
                $text = $location->country;
            endif;
            echo '<input type="submit" name="' . $type . '-' . htmlentities($text, ENT_COMPAT, 'utf-8') . '" value="' . htmlentities($text, ENT_COMPAT, 'utf-8') . '" />';
        endif;
        echo '</div>';
        if ($i % 3 == 2) :
            echo '</div>';
        endif;
        $i++;
    endforeach;
    if ($i % 3 != 0) :
        echo '</div>' . "\n";
    endif;
    ?><?php
endif;
?>
</div>
</form>
</div><!-- around form-->