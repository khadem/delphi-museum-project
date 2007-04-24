<?
// Start session
session_start();

// Login states
define("DELPHI_LOGGED_IN", 0);
define("DELPHI_LOGGED_OUT", -1);
define("DELPHI_NO_SUCH_USER", -2);
define("DELPHI_PASSWD_WRONG", -3);
define("DELPHI_REG_PENDING", -4);

// Turn this off at some point.
ini_set('display_errors', "On");

//Bring in the user's config file
require_once('../../config.php');

// Include pear database handler, smarty
require_once "$CFG->dirroot/libs/pear/MDB2.php";
require_once "$CFG->dirroot/libs/smarty/Smarty.class.php";

// Connect to the database
$dsn = "$CFG->dbtype://$CFG->dbuser:$CFG->dbpass@$CFG->dbhost/$CFG->dbname";

$db =& MDB2::factory($dsn);
if (PEAR::isError($db)) {
    die($db->getMessage());
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);

// Determin user's login state
require_once "$CFG->dirroot/modules/auth/checkLogin.php";
$login_state = checkLogin();
// echo $login_state;

// Setup template object
$t = new Smarty;
$t->template_dir = "$CFG->dirroot/themes/$CFG->theme/templates/";
$t->compile_dir = "$CFG->dirroot/libs/smarty/compile/";
$t->cache_dir = "$CFG->dirroot/libs/smarty/cache/";

// Change comment on these when you're done developing to improve performance
//$t->force_compile = true;
//$t->caching = true;

// Assign any global smarty values here.
$t->assign('themeroot', "$CFG->wwwroot/themes/$CFG->theme");
$t->assign('wwwroot', $CFG->wwwroot);
$t->assign('thumbs', $CFG->image_thumb);
$t->assign('thumbs_square', $CFG->image_thumb_square);
$t->assign('mids', $CFG->image_medium);
$t->assign('fulls', $CFG->image_full);

if( $login_state == DELPHI_LOGGED_IN || $login_state == DELPHI_REG_PENDING){
	$t->assign('username', $_SESSION['username']);
}


?>