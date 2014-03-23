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
<table id="search-advanced-options" class="full" style="width:100%">
    <tr>
        <td colspan="2"><input type="hidden" name="search-advanced" value="1" />
            <strong class="small"><?php echo $words->getFormatted('Username'); ?></strong><br/>
            <input type="text" name="search-username" id="search-username" size="30" maxlength="30" value="<?php echo $vars['search-username']; ?>"/>
        </td>
        <td colspan="2"><?php echo getGroupOptionsDropDown($vars); ?></td>
        <td valign="top" rowspan="3">
            <strong class="small"><?php echo $words->getFormatted('FindPeopleAccomodationTitle'); ?></strong><br/>
            <?php echo getAccommodationOptions($vars); ?>
            <strong class="small">
                <?php echo $words->getFormatted('FindPeopleAccomodationTip'); ?>
            </strong></td>
        <td valign="top" rowspan="3"><strong class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></strong><br/>
            <?php echo getTypicalOfferOptions($vars); ?>
            <strong class="small">
                <?php echo $words->getFormatted('FindPeopleTypicOfferTip'); ?>
            </strong>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <strong class="small"><?php echo $words->getFormatted('TextToFind'); ?></strong><br/>
            <input type="text" name="search-text" id="search-text" size="30" maxlength="30" value="<?php echo $vars['search-text']; ?>"/>
        </td>
        <td colspan="2"><?php echo getLanguagesOptionsDropDown($vars); ?></td>
    </tr>
    <tr>
        <td>
            <strong class="small"><?php echo $words->getFormatted('FindPeopleMinimumAge'); ?></strong><br/>
            <?php echo getAgeDropDown($vars, 'search-age-minimum'); ?>
        </td>
        <td>
            <strong class="small"><?php echo $words->getFormatted('FindPeopleMaximumAge'); ?></strong><br/>
            <?php echo getAgeDropDown($vars, 'search-age-maximum'); ?>
        </td>
        <td><?php echo getGenderDropDown($vars); ?></td>
        <td><?php echo getMembershipCheckbox($vars); ?></td>
    </tr>
</table>
