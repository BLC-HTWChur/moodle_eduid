<?php
require_once('../../config.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/externallib.php');
require_once('lib.php');

header('Content-type: application/json');
$eduid_auth = get_auth_plugin('eduid');

// return true if everything is ok otherwise returns the error code
function params_valid() {
	// parameters needed are access_token and service_shortname
	global $DB;
	$access_token_valid = isset($_GET['access_token']) and !empty($_GET['access_token']);
	$service_shortname = isset($_GET['service_shortname']) and !empty($_GET['service_shortname']);

	// check the other parameters
	if( !$access_token ) {
		return 3;
	} else if( !$service_shortname ) {
		return 4;
	} else {
		return true;
	}
}

// this function extracts the valid token from the database;
// if a valid token is not available then a new one is generated
function get_valid_external_token($service, $userid, $context, $validuntil) {
	global $DB;
	$entry = $DB->get_records('external_tokens', array('externalserviceid'=>$service->id, 'userid'=>$userid, 'contextid' => $context->id));
	$entryid = key($entry);
	$entry = reset($entry);
	if(empty($entry) || $entry->validuntil < time()) {
		$DB->delete_records('external_tokens', array('id' => $entryid));
		return external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $userid, $context, $validuntil);
	} else {
		return $entry->token;
	}
}

// check the parameters
$params_check = params_valid();

if( $params_check ) {
	// check the service access token first
	$service_access_record = $DB->get_record('auth_eduid_tokens', array('token' => $_GET['access_token']));

	// check if the service shortname is in the externa_services table
	$service = $DB->get_record('external_services', array('shortname' => $_GET['service_shortname']));
	if($service === false) {
		echo json_encode( $eduid_auth->error(4) );
		return;
	} else {
		// get the system context
		$context = context_system::instance();
		// initialize the token and expiration date
		$token = $service->token;
		$expiration_date = $service->validuntil;
		// if the token is not valid then create a new one
		if($service_access_record->expirationdate < time()) {
			echo json_encode( $eduid_auth->error(3) );
			return;
		} else if($service->validuntil < time()) {
			$expiration_date = time() + $eduid_auth->config->service_token_expiration_date;
			// generate a new token
			$token = get_valid_external_token($service, $service_access_record->userid, $context, $expiration_date);
		}
		// output the token information
		echo json_encode(array(
			'token' => $token,
			'expires_in' => time() - $service->validuntil
		));
	}
} else {
	echo json_encode( $eduid_auth->error($params_check) );
}
?>
