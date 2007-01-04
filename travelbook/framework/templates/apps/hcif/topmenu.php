<?php
/**
 * This is the HC topmenu
 * 
 * @package includes
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License(GPL)
 */
?>
<ul id="hc-topmenu">
    <li><a href="http://secure.hospitalityclub.org/hc/menu.php">Menu</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/hcworld.php">Countries</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/search.php">Search</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/mymailbox.php?cid=<? echo isset($_SESSION['Username']) ? $_SESSION['Username'] : ''; ?>">Messages</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/travel.php?cid=<? echo isset($_SESSION['Username']) ? $_SESSION['Username'] : ''; ?>">Profile</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/forum.php">Forum</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/groupmembership.php">Groups</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/chat.php">Chat</a></li>
    <li><a href="http://gallery.hospitalityclub.org">Gallery</a></li>
    <li><a href="http://secure.hospitalityclub.org/hc/submit.php?template=helpus">Help us!</a></li>
</ul>