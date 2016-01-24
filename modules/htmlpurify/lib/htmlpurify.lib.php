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

*/
/**
 * Collection of functions that create elements for a page.
 *
 * An example for its use:
 * $layoutbits = MOD_layoutbits::get();  // get the singleton instance
 * $id = $geo->getCityID($cityname);
 *
 * @author Andreas (bw/cs:lemon-head)
 */
class MOD_htmlpure
{
    const ALLOWED_HTML = 'p,b,a[href],br,i,strong,em,ol,ul,li,dl,dt,dd,img[src|alt|width|height],blockquote,strike,del';

    /**
     * Singleton instance
     *
     * @var MOD_layoutbits
     * @access private
     */
    private static $_instance;

    public function __construct()
    {
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**
     * wrapper for lazy loading and instantiating an HTMLPurifier object
     *
     * @access public
     * @return object
     */
    public function getPurifier()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', SCRIPT_BASE . '/data');
        return new HTMLPurifier($config);
    }

    public function getBasicHtmlPurifier()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', SCRIPT_BASE . '/data');
        $config->set('HTML.Allowed', 'p,b,a[href],br,i,strong,em,ol,ul,li,dl,dt,dd,blockquote');
        $config->set('AutoFormat.AutoParagraph', true);
        return new HTMLPurifier($config);
    }

    public function getAdvancedHtmlPurifier()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', SCRIPT_BASE . '/data');
        $config->set('HTML.Allowed', 'p,b,a[href|target],br,i,strong,em,ol,ul,li,dl,dt,dd,blockquote');
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.AutoParagraph', true); // automatically turn double newlines into paragraphs
        $config->set('AutoFormat.Linkify', true); // automatically turn stuff like http://domain.com into links
        return new HTMLPurifier($config);
    }

    public function getSuggestionsHtmlPurifier() {
        return self::getSophisticatedHtmlPurifier();
    }

    public function getActivitiesHtmlPurifier() {
        return self::getSophisticatedHtmlPurifier();
    }

    public function getMessagesHtmlPurifier() {
        return self::getSophisticatedHtmlPurifier();
    }

    public function getForumsHtmlPurifier() {
        // allow tables in forum posts to be able to format the suggestion results nicely
        // don't offer tables in TinyMCE for now
        return self::getSophisticatedHtmlPurifier(array('table[id]', 'tr[class]', 'td[class|rowspan]', 'th[class]', 'span[style]'));
    }

    public function getMailHtmlPurifier() {
        // allow tables in forum posts to be able to format the suggestion results nicely
        // don't offer tables in TinyMCE for now
        return self::getSophisticatedHtmlPurifier(array('table[id]', 'tr[class]', 'td[class|rowspan]', 'th[class]', 'span[class]', 'hr', 'h3'));
    }

    private function getSophisticatedHtmlPurifier($additionalTags = false)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', SCRIPT_BASE . '/data');
        $allowedHtml = self::ALLOWED_HTML;
        if ($additionalTags) {
            foreach($additionalTags as $tag) {
                $allowedHtml .= ',' . $tag;
            }
        }
        $config->set('HTML.Allowed', $allowedHtml);
        $config->set('HTML.MaxImgLength', '500');
        $config->set('HTML.TargetBlank', true);
        $config->set('CSS.MaxImgLength', '500px');
        $config->set('CSS.AllowedProperties', array('text-decoration'));
        $config->set('AutoFormat.AutoParagraph', true); // automatically turn double newlines into paragraphs
        $config->set('AutoFormat.Linkify', true); // automatically turn stuff like http://domain.com into links
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        $config->set('CSS.AllowTricky','true');
        return new HTMLPurifier($config);
    }
}
