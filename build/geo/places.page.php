<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class PlacesPage extends PageWithActiveSkin
{

    protected function leftSidebar()
    {
    }

    protected function teaserHeadline() {
        return $this->getWords()->getBuffered('Places');
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'places';
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/places.css';
       return $stylesheets;
    }
    
    protected function column_col3() {
       $this->displayPlacesOverview($this->continents);
    }    
    
    /*
    *  Custom functions
    *
    */
    
    protected function displayPlacesOverview($continents) {
        $words = new MOD_words();
        $countrylist = '<table><tr>';
        
        foreach ($continents as $continent) {
            if ($continent->name != 'Antarctica') {
                $countrylist .= '<td style="vertical-align: top;"><h3>'.$continent->getTranslatedName().'</h3>'.$this->displayContinent($continent->getHierarchyChildren()).'</td>';
            }
        }
        $countrylist .= '</tr></table>';
        ?>
        <h2><?php echo $words->get('Country_overview_title'); ?></h2>
        <div class="countrylist"><?php echo $countrylist; ?></div>
        <?php
    }

    protected function displayContinent($countries) {
        $html = '';
        $html .= '<ul>';
        foreach ($countries as $country) {
            $usage = $country->getUsageForAllTypes();
            $number = (isset($usage['member_primary'])) ? $usage['member_primary'] : false;
           $html .= '<li class="spritecontainer"><div class="sprite sprite-'.strtolower($country->fk_countrycode).'"><a href="places/'.$country->fk_countrycode.'"></a></div> <a href="places/'.$country->fk_countrycode.'" class="'.($number ? 'highlighted' : 'grey').'">'.$country->getTranslatedName();
            if ($number) {
               $html .= '<span class="small grey"> ('.$number.')</span>';
            }
            $html .= '</a></li>';
        }
        $html .= '</ul>';
        return $html;   
    }

}

?>
