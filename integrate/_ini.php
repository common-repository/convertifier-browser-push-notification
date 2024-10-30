<?php
ini_set('display_errors', '0');
ob_start();
$user_ip = getUserIP();
$ip 	= gethostbyname('app.convertifier.com');
if (defined('CONVERTIFIER_DEBUG') && CONVERTIFIER_DEBUG) {
	$ip = gethostbyname('convertifierfinal.dev');
}
$errors = ['errors' => true, 'message'  => 'Invalid Unauthorised attempt'];
if ($user_ip != $ip) {	
	echo wp_json_encode($errors);
	ob_end_flush();
	return;
}
$r_app_key 	= $request->get_param( 'app_key' );
$r_fcm_id 	= $request->get_param( 'fcm_id' );
$r_app_secret 	= $request->get_param( 'app_secret' );
if (is_null($r_app_key) || is_null($r_fcm_id) || is_null($r_app_secret)) {
	$errors['message'] = 'Invalid Parameters';
	echo wp_json_encode($errors);
	ob_end_flush();
	return;
}

$app_key = get_option('convertifier_app_key');
if ($app_key != $r_app_key) {
	$errors['message'] = 'App Key Mismatched';
	echo wp_json_encode($errors);
	ob_end_flush();
	return;
}

update_option('convertifier_app_key', $r_app_key);
update_option('convertifier_fcm_id', $r_fcm_id);
update_option('convertifier_app_secret', $r_app_secret);

$errors['errors']	= false;
$errors['message']	= 'Information updated successfully';
echo wp_json_encode($errors);
ob_end_flush();
?>