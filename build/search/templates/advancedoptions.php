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
            <span class="small"><?php echo $words->getFormatted('Username'); ?></span><br/>
            <input type="text" name="search-username" id="search-username" size="30" maxlength="30" value="<?php echo $vars['search-username']; ?>"/>
        </td>
        <td colspan="2"><?php echo getGroupOptionsDropDown($vars); ?></td>
        <td valign="top" rowspan="3">
            <span class="small"><?php echo $words->getFormatted('FindPeopleAccomodationTitle'); ?></span><br/>
            <?php echo getAccommodationOptions($vars); ?>
            <span class="small clearfix">
                <span class="float_right"><i><?php echo $words->getFormatted('FindPeopleAccomodationTip'); ?></i></span>
            </span>
            <br/>
            <br/>
         <span class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></span><br/>
            <?php echo getTypicalOfferOptions($vars); ?>
            <span class="small clearfix">
                <span class="float_right"><i><?php echo $words->getFormatted('FindPeopleTypicOfferTip'); ?></i></span>
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="small"><?php echo $words->getFormatted('TextToFind'); ?></span><br/>
            <input type="text" name="search-text" id="search-text" size="30" maxlength="30" value="<?php echo $vars['search-text']; ?>"/>
        </td>
        <td colspan="2"><?php echo getLanguagesOptionsDropDown($vars); ?></td>
    </tr>
    <tr>
        <td colspan="2" class="clearfix">
            <div class="float_left advance-margin">
                <span class="small"><?php echo $words->getFormatted('FindPeopleMinimumAge'); ?></span><br/>
                <?php echo getAgeDropDown($vars, 'search-age-minimum'); ?>
            </div>
            <div class="float_left advance-margin">
                <span class="small"><?php echo $words->getFormatted('FindPeopleMaximumAge'); ?></span><br/>
                <?php echo getAgeDropDown($vars, 'search-age-maximum'); ?>
            </div>
            <div class="float_left advance-margin">
                <?php echo getGenderDropDown($vars); ?>
            </div>
        </td>
        <td><?php echo getMembershipCheckbox($vars); ?></td>
    </tr>
</table>
