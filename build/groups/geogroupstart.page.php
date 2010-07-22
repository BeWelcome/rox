<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
*/
/**
 * This page shows an overview of the group
 *
 */
class GeoGroupStartPage extends GroupsBasePage
{

    protected function getSubmenuItems()
    {
        $items = array();

        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();

        if ($this->group)
        {
            $group_id = $this->group->id;
            $items[] = array('start', $this->getPlacesURL(), $words->getSilent('GeoGroupOverview'));
            $items[] = array('members', $this->getPlacesURL('geogroup_members'), $words->getSilent('GeoGroupMembers'));
            $items[] = array('forum', $this->getPlacesURL('geogroup_forum'), $words->getSilent('geoGroupDiscussions'));
            $items[] = array('wiki', $this->getPlacesURL('geogroup_wiki'), $words->getSilent('GeoGroupWiki'));
            if ($this->isGroupMember())
            {
                $items[] = array('membersettings', $this->getPlacesURL('membersettings'), $words->getSilent('GeoGroupMembersettings'));
            }
            if ($this->member && $this->member->hasPrivilege('GroupsController', 'GroupSettings', $this->group))
            {
                $items[] = array('admin', $this->getPlacesURL('groupsettings'), $words->getSilent('GroupGroupsettings'));
            }

        }
        return $items;
    }

    protected function teaserContent()
    {
        $words = $this->getWords();
        $loc_count = count($this->locations);
        $url_array = array('countrycode' => $this->country->fk_countrycode);
        for ($i = 0; $i < ($loc_count); $i++)
        {
            $url_array['location' . ($i +1)] = $this->locations[$i]->name;
        }
        $children = $this->location->getHierarchyChildren();
        ?>
        <div id="teaser" class="clearfix">
            <div id="geobreadcrumb" class="row floatbox">
                <h3 class="grey">
                <? /*<a href="places" style="color:#888; padding: 0 .2em;"><img src="images/icons/world.png"></a> */?>
                <a href="places" ><?=$words->get('CountryTitle')?></a>
                <?php
                if ($continent = $this->country->getContinent()) {
                    $continent_name = $continent->getTranslatedName();
                    echo " > <a href='places/continent/{$continent->name}'>{$continent_name}</a>";
                }
                if ($this->country && $this->locations)
                {
                    $country_name = $this->country->getTranslatedName();
                    $url_array = array('location' => $this->country->fk_countrycode);
                    echo " > <a href='{$this->router->url('geogroup_country', $url_array)}'>{$country_name}</a>";
                }
                $i = 0;
                if ($this->locations)
                {
                    $loc_count = count($this->locations) -1;
                    for ($i = 0; $i <= $loc_count; $i++)
                    {
                        $url_array['location' . ($i + 1)] = $this->locations[$i]->name;
                        if ($i != $loc_count) {
                            $location_name = $this->locations[$i]->getTranslatedName();
                            $temp_array = implode('/',$url_array);
                            echo " > <a href='{$this->router->url('geogroup_country', array('location' => $temp_array))}'>{$location_name}</a>";
                        }
                    }
                }
                ?>
                </h3>
            </div>

            <div class="floatbox">
            <h1 class="geoheading"><a href="<?=$this->getPlacesURL()?>"><?=$this->location->getTranslatedName() ?></a></h1>
                    <? if ($children) {
                            $chooseloc_text = $words->getBuffered('ChooseLocation');
                            if ($children[0] && $children[0]->isCity()) $chooseloc_text = $words->getBuffered('ChooseCity');
                            if ($children[0] && $children[0]->isRegion()) $chooseloc_text = $words->getBuffered('ChooseRegion');
                    ?>
                    <div id="browseregions_dropdown" >
                        <select onchange="window.location.href=this.value; return false">
                            <option label="<?=$chooseloc_text?>" value="<?=$this->getPlacesURL()?>"><?=$chooseloc_text?></option>
                            <?php
                            // TODO: Add $child->member_count variable
                            foreach ($children as $child)
                            {
                                ?>
                            <option label="<?=$child->name?>" value="<?=$this->getPlacesURL().'/'.$child->name ?>"><?=$child->getTranslatedName()?> <?=($child->member_count != 0) ? (' ('.$child->member_count.')') : ''?></option>
                                <?
                            }
                            ?>
                        </select>
                        <?=$words->flushBuffer()?>
                    </div> <!-- browseregions_dropdown -->
                    <script type="text/javascript">
                    $('browseregions_dropdown').show();
                    </script>
            </div>
                    <? } ?>
        </div>
        <?php
    }

    protected function column_col3()
    {
        $words = $this->getWords();

        $group_id = $this->group->id;
        $memberlist_widget = new GroupMemberlistWidget();
        $memberlist_widget->setGroup($this->group);
        $memberlist_widget->setLimit(5);

        $wiki = new WikiController();
        if ($this->isGroupMember()) {

            $actionurl = 'group/'.$group_id;
            $wiki->editProcess($actionurl);
        }
        $wikipage = $this->group->Name;

        $loc_count = count($this->locations);
        $url_array = array('countrycode' => $this->country->fk_countrycode);
        for ($i = 0; $i < ($loc_count); $i++)
        {
            $url_array['location' . ($i +1)] = $this->locations[$i]->name;
        }
        $children = $this->location->getHierarchyChildren();
        if (!$this->location->isCity()) {
            $cities = $this->location->getCitiesBelow(true);
            $cities_mod = array();
            foreach ($cities as $city)
            {
                $group = $this->model->getGeoGroup($city);
                $city->member_count = ($group) ? $group->getMemberCount() : 0;
                $ancestor_line = $city->getAncestorLine();
                $city->url_string = '';
                foreach ($ancestor_line as $ancestor) {
                    if (!$ancestor->parentCountryId) {
                        $city->url_string = $ancestor->fk_countrycode.'/'.$city->url_string;
                        break;
                    }
                    $city->url_string = $ancestor->name.'/'.$city->url_string;
                }
                array_push($cities_mod,$city);
            }

            // sort an array of objects
            $key = 'member_count';
            $object = $cities_mod;
            for ($i = count($object) - 1; $i >= 0; $i--)
            {
              $swapped = false;
              for ($j = 0; $j < $i; $j++)
              {
                   if ($object[$j]->$key < $object[$j + 1]->$key)
                   {
                        $tmp = $object[$j];
                        $object[$j] = $object[$j + 1];
                        $object[$j + 1] = $tmp;
                        $swapped = true;
                   }
              }
              if (!$swapped) break;
            }
            // limit the array to 15 elements
            array_splice($object,15);
            $cities = $object;
        }

        if ($this->group->getForumThreads()) {
            $group_forum_widget = new GroupForumWidget();
            $group_forum_widget->setGroup($this->group);
            $group_forum_widget->setLimit(8); //limit to 8 threads
            $group_forum_widget->setURI('places/forum/'. implode('/',$url_array) . '/');
            //$Forums->groupURI = $this->getPlacesURL('geogroup_forum');
        }
        include "templates/geogroupstart.php";
    }

    protected function getSubmenuActiveItem() {
        return 'start';
    }

    protected function getPlacesURL($prefix = 'geogroup_country') {
        $url_array = array('countrycode' => $this->country->fk_countrycode);
        if ($this->locations)
        {
            $loc_count = count($this->locations) -1;
            for ($i = 0; $i <= $loc_count; $i++)
            {
                $url_array['location' . ($i + 1)] = $this->locations[$i]->name;
            }
        }
        $location = implode('/',$url_array);
        return $this->router->url($prefix, array('location' => $location));
    }
}
