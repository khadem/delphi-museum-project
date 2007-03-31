<?
// Turn this off at some point.
ini_set('display_errors', "On");


//Bring in the user's config file
require_once('../../config.php');


// Include pear database handler and smarty
require_once "$CFG->dirroot/libs/pear/MDB2.php";
require_once "$CFG->dirroot/libs/smarty/Smarty.class.php";


// Connect to the database
$dsn = "$CFG->dbtype://$CFG->dbuser:$CFG->dbpass@$CFG->dbhost/$CFG->dbname";

$db =& MDB2::factory($dsn);
if (PEAR::isError($db)) {
    die($db->getMessage());
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);

// Setup template object
$t = new Smarty;
$t->template_dir = "$CFG->dirroot/themes/$CFG->theme/templates/";
// For other compile and cache directory options, see the comment by Pablo Veliz at the bottom of this article.
$t->compile_dir = "$CFG->dirroot/libs/smarty/compile/";
$t->cache_dir = "$CFG->dirroot/libs/smarty/cache/";

// Change comment on these when you're done developing to improve performance
//$t->force_compile = true;
//$t->caching = true;

## GLOBALS:  $db, $t
session_start();

// Assign any global smarty values here.
$t->assign('themeroot', "$CFG->wwwroot/themes/$CFG->theme");
$t->assign('wwwroot', $CFG->wwwroot);
?>