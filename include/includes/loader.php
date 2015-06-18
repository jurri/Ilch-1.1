<?php
#   Copyright by Manuel
#   Support www.ilch.de

defined('main') or die('no direct access');

# load init script (constants, php settings, etc) 
require_once('include/includes/init.php');

# load all needed classes
require_once('include/includes/class/tpl.php');
require_once('include/includes/class/design.php');
require_once('include/includes/class/menu.php');
if (version_compare(PHP_VERSION, '5.0') > -1) {
    require_once('include/includes/class/pwcrypt.php');
}

# fremde classes laden
if (version_compare(PHP_VERSION, '5.3') == -1) {
    require_once('include/includes/class/xajax.php4.inc.php');
} else {
    require_once('include/includes/class/xajax.php5.inc.php');
}

# load all needed func
require_once('include/includes/func/db/mysql.php');
require_once('include/includes/func/calender.php');
require_once('include/includes/func/funkt.php');
require_once('include/includes/func/user.php');
require_once('include/includes/func/escape.php');
require_once('include/includes/func/allg.php');
require_once('include/includes/func/debug.php');
require_once('include/includes/func/bbcode.php');
require_once('include/includes/func/profilefields.php');
require_once('include/includes/func/statistic.php');
require_once('include/includes/func/listen.php');
require_once('include/includes/func/forum.php');
require_once('include/includes/func/forumex.php');
require_once('include/includes/func/warsys.php');
require_once('include/includes/func/ic_mime_type.php');

# load something else
require_once ('include/includes/lang/de.php');