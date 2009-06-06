<?php

/*
 Autoloader and dependency injection initialization for Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//Indicate where Swift Mailer lib is found
defined('SWIFT_LIB_DIRECTORY')
  or define('SWIFT_LIB_DIRECTORY', dirname(__FILE__));

//Path to classes inside lib
define('SWIFT_CLASS_DIRECTORY', SWIFT_LIB_DIRECTORY . '/classes');

//Load Swift utility class
require_once SWIFT_CLASS_DIRECTORY . '/Swift.php';

//Start the autoloader
Swift::registerAutoload();

//Load the init script to set up dependency injection
require_once SWIFT_LIB_DIRECTORY . '/swift_init.php';
