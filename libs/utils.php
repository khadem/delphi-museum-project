<?php
/**
 * Returns true if the email looks valid
 */
function emailValid($email){
	$atCharPos = strpos( $email, '@');
	$dotCharROff = strrpos( $email, '.');
	if( !$dotCharROff )
		return false;

	$dotCharROff = strlen($email) - $dotCharROff - 1;
 
	// Domain suffix must be at least 2 chars, and at most 6 (.museum)
	if (( $atCharPos	> 0 ) && ( $dotCharROff >= 2 ) && ( $dotCharROff <= 6 )){
		return true;
	} else{
		return false;		
	}
}

function website_urlValid($website_url){
	if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $website_url)){
		return true;
	} else{
		return false;		
	}
}

function real_nameValid($realName){
	return true;
}

function aboutValid($about){
	return true;
}

function passValid($pass, $pass2){
	if(strlen($pass) >= 6 && $pass == $pass2){
		return true;
	} else {
		return false;
	}
}


function getRoles(){
	global $mysqli;
  /* Get all the roles */
	$q = "select name from role";
	$result = $mysqli->query($q);
	if(!$result)
		return false;
	else {
		while ($row = $result->fetch_array()) {
			$roles[] = $row[0];
		}
	}
	return $roles;
}


function sendDelphiMail($nameTo, $emailTo, $subj, $plaintextmsg, $htmlmsg, $emailFrom = "delphi@hearstmuseum.berkeley.edu", $nameFrom = "Delphi"){
	require_once 'XPM/XPM3_MAIL.php';
	
	$mail = new XPM3_MAIL;
	$mail->Delivery('local');
	$mail->From($emailFrom, $nameFrom);
	$mail->AddTo($emailTo, $nameTo);
	$mail->Text($plaintextmsg);
	$mail->Html($htmlmsg);
	return $mail->Send($subj);
	
	
}

?>
