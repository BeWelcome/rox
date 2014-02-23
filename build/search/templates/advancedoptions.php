<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 05.01.14
 * Time: 12:44
 *
 * Provides the advancedoptions for the search.
 * Also send through an Ajax request if someone clicks on advanced search link with JS enabled
 *
 */

$words = new MOD_words;
?>
<table id="search-advanced-options">
    <tr>
        <td><input type="hidden" name="search-advanced" value="1" />
            <strong class="small"><?php echo $words->getFormatted('Username'); ?></strong><br/>
            <input type="text" name="search-username" id="search-username" size="30" maxlength="30" value=""/>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('FindPeopleMinimumAge'); ?></strong><br/>
            <?php echo getAgeDropDown($vars, 'search-age-minimum'); ?>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('Gender'); ?></strong><br/>
            <?php echo getGenderDropDown($vars); ?>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('Groups'); ?></strong><br/>
            <?php echo getGroupOptionsDropDown($vars); ?>
        </td>
        <td valign="top" rowspan="2">
            <strong class="small"><?php echo $words->getFormatted('FindPeopleAccomodationTitle'); ?></strong><br/>
            <input type="checkbox" name="search-accommodation[]" id="search-accommodation-anytime" value="anytime"
                   checked="checked" class="sval"/>
            <label
                for="search-accommodation-anytime"><?php echo $words->get('Accomodation_anytime'); ?></label><br/>
            <input type="checkbox" name="search-accommodation[]" id="search-accommodation-dependonrequest"
                   value="dependonrequest" checked="checked" class="sval"/>
            <label
                for="search-accommodation-dependonrequest"><?php echo $words->get('Accomodation_dependonrequest'); ?></label><br/>
            <input type="checkbox" name="search-accommodation[]" id="search-accommodation-neverask" value="neverask"
                   checked="checked" class="sval"/>
            <label
                for="search-accommodation-neverask"><?php echo $words->get('Accomodation_neverask'); ?></label><br/>
            <strong class="small">
                <?php echo $words->getFormatted('FindPeopleAccomodationTip'); ?>
            </strong>
        </td>
        <td valign="top" rowspan="2">
            <strong class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></strong><br/>
            <input type="checkbox" name="search-typical-offer[]" id="search-typical-guidedtour" value="guidedtour"
                   class="sval"/>
            <label for="search-typical-guidedtour"><?php echo $words->get('TypicOffer_guidedtour'); ?></label><br/>
            <input type="checkbox" name="search-typical-offer[]" id="search-typical-dinner" value="dinner"
                   class="sval"/>
            <label for="search-typical-dinner"><?php echo $words->get('TypicOffer_dinner'); ?></label><br/>
            <strong class="small">
                <?php echo $words->getFormatted('FindPeopleTypicOfferTip'); ?>
            </strong>
        </td>
    </tr>
    <tr>
        <td>
            <strong class="small"><?php echo $words->getFormatted('TextToFind'); ?></strong><br/>
            <input type="text" name="search-text" id="search-text" size="30" maxlength="30" value=""/>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('FindPeopleMaximumAge'); ?></strong><br/>
            <?php echo getAgeDropDown($vars, 'search-age-maximum'); ?>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('FindPeopleMemberStatus'); ?></strong><br/>
            <select name="search-membership">
                <option value="0"><?php echo $words->getBuffered('Active'); ?></option>
                <option value="1"><?php echo $words->getBuffered('All'); ?></option>
            </select><?php echo $words->flushBuffer(); ?>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('SearchLanguages'); ?></strong><br/>
            <?php echo getLanguagesOptionsDropDown($vars); ?>
        </td>
    </tr>
</table>
