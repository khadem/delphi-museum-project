<?php 
/* Include Files *********************/
require_once("../../libs/env.php");
require_once("authUtils.php");
/*************************************/
// If the user isn't logged in, send to the login page.
if(($login_state != DELPHI_LOGGED_IN) && ($login_state != DELPHI_REG_PENDING)){
	header( 'Location: ' . $CFG->wwwroot . '/modules/auth/login.php' );
	die();
}

$t->assign('page_title', 'PAHMA/Delphi: Edit Permission Definitions');
$opmsg = "";

// This needs to verify perms. 
if( !currUserHasPerm( 'EditPerms' ) ) {
	$opmsg = "You do not have rights to Edit permissions. <br />
		Please contact your Delphi administrator for help.";
	$t->assign('perm_error', $opmsg);

	$t->display('adminPermissions.tpl');
	die();
}

$style_block = "<style>
td.title { border-bottom: 2px solid black; font-weight:bold; text-align:left; 
		font-style:italic; color:#777777; }
td.perm_label { font-weight:bold; color:#61615f; }
td.permname { font-weight:bold; }
td.perm { border-bottom: 1px solid black; }
td.permdesc textarea { font-family: Arial, Helvetica, sans-serif; }
form.form_row  { padding:0px; margin:0px;}
</style>";

$t->assign("style_block", $style_block);

$themebase = $CFG->wwwroot.'/themes/'.$CFG->theme;

$script_block = '
<script type="text/javascript" src="'.$themebase.'/scripts/setupXMLHttpObj.js"></script>
<script>

// The ready state change callback method that waits for a response.
function updatePermRSC() {
  if (xmlhttp.readyState==4) {
		if( xmlhttp.status == 200 ) {
			// Maybe this should change the cursor or something
			window.status = "Permission updated.";
	    //alert( "Response: " + xmlhttp.status + " Body: " + xmlhttp.responseText );
		} else {
			alert( "Error encountered when trying to update permission.\nResponse: "
			 				+ xmlhttp.status + "\nBody: " + xmlhttp.responseText );
		}
	}
}

function updatePerm(permName) {
	// Could change cursor and disable button until get response
	var descTextEl = document.getElementById("D_"+permName);
	var desc = descTextEl.value;
	if( desc.length <= 2 )
		alert( "You must enter a description that is at least 3 characters long" );
	else if( !xmlhttp )
		alert( "Cannot update permission - no http obj!\n Please advise Delphi support." );
	else {
		var url = "../../api/updatePerm.php";
		var args = "p="+permName+"&d="+desc;
		//alert( "Preparing request: POST: "+url+"?"+args );
		xmlhttp.open("POST", url, true);
		xmlhttp.setRequestHeader("Content-Type",
															"application/x-www-form-urlencoded" );
		xmlhttp.onreadystatechange=updatePermRSC;
		xmlhttp.send(args);
		//window.status = "request sent: POST: "+url+"?"+args;
		var el = document.getElementById("U_"+permName);
		el.disabled = true;
	}
}
// This should go into a utils.js - how to include?
function enableElement( elID ) {
	//alert( "enableElement" );
	var el = document.getElementById(elID);
	el.disabled = false;
	//window.status = "Element ["+elID+"] enabled.";
}

</script>';

$t->assign("script_block", $script_block);

if(isset($_POST['delete'])){
	if(empty($_POST['perm']))
		$opmsg = "Problem deleting perm.";
	else {
		$permname = $_POST['perm'];
		$deleteQ = "DELETE FROM permission WHERE name='".$permname."'";
		$res =& $db->query($deleteQ);
		if (PEAR::isError($res)) {
			$opmsg = "Problem deleting permission \"".$permname."\".<br />".$res->getMessage();
		} else {
			$opmsg = "Permission \"".$permname."\" deleted.";
		}
	}
}
else if(isset($_POST['add'])){
	if(empty($_POST['perm']) || empty($_POST['desc']))
		$opmsg = "Problem adding new permission: You must specify both a name and a description.";
	else {
		$permname = $_POST['perm'];
		$permdesc = $_POST['desc'];
		$addQ = "INSERT IGNORE INTO permission(name, description, creation_time)"
			." VALUES ('".$permname."', '".$permdesc."', now())";
		$res =& $db->query($addQ);
		if (PEAR::isError($res)) {
			$opmsg = "Problem adding permission \"".$permname."\".<br />".$res->getMessage();
		} else {
			$opmsg = "Permission \"".$permname."\" added.";
		}
	}
}

function getFullPerms(){
	global $db;
   /* Get all the users and their assigned perms */
	$q = "select name, description from permission";
	$res =& $db->query($q);
	if (PEAR::isError($res))
		return false;
	$perms = array();
	while ($row = $res->fetchRow()) {
		$perm = array(	'name' => $row['name'], 
						'description' => $row['description']);
		
		array_push($perms, $perm);
	}
	// Free the result
	$res->free();
	return $perms;
}

$perms = getFullPerms();
if($perms){
	$t->assign('perms', $perms);
}

if($opmsg!="")
	$t->assign('opmsg', $opmsg);

$t->display('adminPermissions.tpl');
?>
