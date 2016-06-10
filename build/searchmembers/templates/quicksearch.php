<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

Refactoried by JeanYvea after the first move to Rox

*/

$words = new MOD_words($this->getSession());
$styles = array( 'highlight', 'blank' ); // alternating background for table rows


echo $words->getFormatted("SearchResultsFor","<b>".htmlspecialchars($TReturn->searchtext, ENT_QUOTES)."</b>"),"<br />" ;
?>

<?php
    $iCountMemberFound=count($TReturn->TMembers) ;
    if ($iCountMemberFound>0) {
?>
        <h2><?=$words->getFormatted("Username") ?></h2>
        <table class="full">


<?php
        $ii=0 ;
        $icol=0 ;
        foreach($TReturn->TMembers as $m) {
            if ($icol % 3==0) {
                $ii++ ;
                ?><tr class="<?=$styles[$ii%2] ?>"><?php
            }
            ?>
            <td align="center">
                <?=MOD_layoutbits::PIC_50_50($m->Username) ;?>
                <br />
                <a class="username" href="members/<?=$m->Username ?>"><?=$m->Username ?></a><br />
            <a href="<?="places/".$m->fk_countrycode."/".$m->RegionName."/".$m->CityName ?>"> <?=$m->CountryName."/".$m->RegionName."/".$m->CityName?></a>
            </td>
            <?php 
            $icol++ ;
            if ($icol%3==0) { 
                echo "</tr>" ;
            }
            ?>
        <?php
        }
        ?>
        </table>
    <?php
    } // end of if they are members found
    
    $iCountPlacesFound=count($TReturn->TPlaces) ;
    if ($iCountPlacesFound>0) {
?>
        <h2><?=$words->getFormatted("Location") ?></h2>
        <table class="full">


<?php
        $ii=0 ;
        foreach($TReturn->TPlaces as $p) {
            $ii++ ;
            ?>
            <tr class="<?=$styles[$ii%2] ?>">
            <td align="center">
                <?php echo "<a href=\"",$p->link,"\">" ;
                if (!empty($p->CountryName)) {
                    echo $p->CountryName,"::" ;
                }
                if (!empty($p->RegionName)) {
                    echo $p->RegionName,"::" ;
                }
                echo $p->name,"</a>" ;
                if ($p->NbMembers>1) {
                    echo " (",$p->NbMembers," ",$words->getFormatted("Members"),")" ;
                }
                else {
                    echo " (",$p->NbMembers," ",$words->getFormatted("Member"),")" ;
                }
                
                ?>
            </td>
            
            </tr>
        <?php
        }
        ?>
        </table>
    <?php
    } // end of if they are places found

    $iCountForumTags=count($TReturn->TForumTags) ;
    if ($iCountForumTags>0) {
?>
        <h2><?=$words->getFormatted("tags") ?></h2>
        <table class="full">


<?php
        $ii=0 ;
        foreach($TReturn->TForumTags as $p) {
            $ii++ ;
            ?>
            <tr class="<?=$styles[$ii%2] ?>">
            <td align="center">
                <?php echo "<a href=\"","forums/t".$p->IdTag,"\">" ?>
                (<?=$p->NbThreads?> <?=$words->getFormatted("Threads")?>)</a>
            </td>
            
            </tr>
        <?php
        }
        ?>
        </table>
    <?php
    } // end of if they are forum tags found
    
    if (($iCountMemberFound<=0) and ($iCountPlacesFound<=0)  and ($iCountForumTags<=0)) {
        echo $words->getFormatted("QuickSearchMembersNoResults",htmlspecialchars($TReturn->searchtext, ENT_QUOTES),"<a href=\"searchmembers\">".$words->getFormatted("MapSearch")."</a>") ;
    }
