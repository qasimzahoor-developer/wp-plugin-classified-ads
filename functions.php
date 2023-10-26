<?php
/*
Global functions
*/

//CSRF solution
function csrf_generate($unique_string){
	return wp_hash_password($unique_string.session_id());
}
function csrf_verify($verify_string, $hash){
	return wp_check_password($verify_string.session_id(), $hash);
}
//messages and alerts
function setAlert($data){
	$_SESSION['message'] = $data;
}
function displayAlert(){
	if(isset($_SESSION['message']['displayed']) AND $_SESSION['message']['displayed'] === 'true'){ 
		unset($_SESSION['message']);
		return;
	}
	if(isset($_SESSION['message'])){ 
		$_SESSION['message']['displayed'] = 'true';
		return $_SESSION['message'];
	}
	return ;
}