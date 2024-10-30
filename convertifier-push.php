<?php

if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Convertifier Push Notifications
Plugin URI: https://wordpress.org/plugins/convertifier-browser-push-notification/
Description: Push notifications by Convertifier, this plugin lets you send conversion focused browser push notifications to your users. Using Convertifier, you can easily send the push notifications. Through our dashboard, you can select a specific set of users and accordingly send them push notifications.
By using this WordPress plugin, you can send the notifications as soon as you publish the post. If you are scheduling the post, the push notification will also get scheduled and will be sent to your users as soon as the post is published. Also, if you do not want to send the notifications as soon as you publish, then you can create a campaign from Convertifier dashboard and send it at a different time of the day. After installing the WordPress plugin, you can also customize the title and body of the notification. The featured image of the post will be used as the icon of the push notification being sent
Author: Convertifier
Author URI: https://www.convertifier.com
Version: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
define( 'CONVERTIFIER_DEBUG', false);
define( 'CONVERTIFIER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if (defined('CONVERTIFIER_DEBUG') && CONVERTIFIER_DEBUG) {
	define( 'CONVERTIFIER_API_URL', 'https://convertifierfinal.dev/api/v1/');	
}else{
	define( 'CONVERTIFIER_API_URL', 'https://app.convertifier.com/api/v1/');
}
define('CONVERTIFIER_AUTH_KEY', convertifier_auth_key());
$convertifier_header = '
<link rel="manifest" href="'.CONVERTIFIER_PLUGIN_URL.'manifest.json.php?FCM_ID">
<script type="text/javascript">
        ConvertifierSettings = { config: { app_key: "CONVERTIFIER_APPKEY", worker_location:"'.CONVERTIFIER_PLUGIN_URL.'sdk/ConvertifierSDKWorker.js.php?appkey=CONVERTIFIER_APPKEY"} };
</script>
<script src="https://cdn.convertifier.com/js/ConvertifierSDKWorker.js" async></script>';

add_action ( 'wp_head', 'convertifier_headercode', 1);
add_action( 'wp_footer', 'convertifier_footer');
add_action( 'admin_menu', 'convertifier_admin_menu' );
add_action( 'admin_init', 'convertifier_register_settings' );
add_action( 'admin_notices','convertifier_warn');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

add_action( 'rest_api_init', function () {
	register_rest_route( 'convertifier/v1', '/integrate', [
		'methods' => 'POST',
		'callback' => 'convertifier_integrate',
	]);
} );

add_action('add_meta_boxes', 'convertifier_register_meta_boxes');
add_action("save_post", "save_convertifier_notification", 3, 3);
add_action( 'transition_post_status', 'convertifier_push_on_publish', 20, 3);

if (!function_exists('convertifier_write_log')) {
    function convertifier_write_log ( $description='', $log='' )  {
        //if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( $description.' '. print_r( $log, true ) );
            } else {
                error_log( $description.' '. $log );
            }
        //}
    }
}

if (!function_exists('curl_init')) {
  function admin_notice_curl_not_installed() {
  	?>
	  <div class="error notice">
		  <p><strong>Convertifier Push Notification:</strong> <em>cURL is not installed on this server. cURL is required to send notifications. Please make sure cURL is installed on your server before continuing.</em></p>
	  </div>
	<?php
  }
  add_action( 'admin_notices', 'admin_notice_curl_not_installed');
}

function convertifier_auth_key()
{
	$convertifier_key 		= get_option('convertifier_app_key');
	$secret 		      	= get_option('convertifier_app_secret');
	return base64_encode( $convertifier_key . ':' . $secret );
}

function convertifier_register_meta_boxes() {
    add_meta_box("convertifier-meta-box", "Convertifier Push Notifications", "convertifier_post_meta_box", "post", "side", "high", null);
}

function convertifier_post_meta_box($object)
{
	wp_nonce_field(basename(__FILE__), "meta-box-nonce");
	$title = get_post_meta($object->ID, "convertifier_notification_title", true);
	$body = get_post_meta($object->ID, "convertifier_notification_body", true);
	$checked = get_post_meta($object->ID, "convertifier_post_push", true);
	?>
	<label for="convertifier_notification_title">Notification Title</label>
	<input type="text" id="convertifier_notification_title" name="convertifier_notification_title" placeholder="Enter custom title for notification" style="width: 100%;" value="<?php echo esc_html($title);?>" maxlength="73">
	<label for="convertifier_notification_body">Notification Body</label>
	<textarea id="convertifier_notification_body" name="convertifier_notification_body" style="width: 100%;" placeholder="Enter Notification description" title="Enter Notification description" maxlength="130"><?php echo esc_html($body);?></textarea>
	<input type="checkbox" name="convertifier_post_push" id="convertifier_post_push" value="1" <?php echo esc_html(($checked)?'checked':'');?> >
	<label for="convertifier_post_push">Send Push notification on Published</label>
	<?php
}

function save_convertifier_notification($post_id, $post, $update)
{
	if (!isset($_POST['meta-box-nonce']) || !wp_verify_nonce($_POST['meta-box-nonce'], basename(__FILE__)))
        return $post_id;
    if(!current_user_can('edit_post', $post_id))
        return $post_id;
    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
    $slug = 'post';
    if($slug != $post->post_type)
        return $post_id;
    update_convertifier_notification_data($post_id);
}
function update_convertifier_notification_data($post_id)
{
	$notification_title = $notification_body  = '';
    $notification_send  = 0;
    if (isset($_POST['convertifier_notification_title'])) {
        $notification_title = sanitize_text_field($_POST['convertifier_notification_title']);
    } 
    if (isset($_POST['convertifier_notification_body'])) {
        $notification_body = sanitize_text_field($_POST['convertifier_notification_body']);
    } 
    if (isset($_POST["convertifier_post_push"])) {
        $notification_send = 1;
    }
    update_post_meta($post_id, 'convertifier_notification_title', $notification_title);
    update_post_meta($post_id, 'convertifier_notification_body', $notification_body);  
    update_post_meta($post_id, "convertifier_post_push", $notification_send);
}

function convertifier_push_on_publish( $new_status, $old_status, $post ) {

	if(current_user_can('publish_posts') || current_user_can('edit_published_posts')) {
		if ($old_status=='trash' || $new_status=='trash') {
			return;
		}
	    if (( 'publish' == $new_status && 'publish' !== $old_status ) && 'post' == $post->post_type) {
	    	$data = getNotificationDetails($old_status, $post);
	    	if ($data['send']==1) {
	    		$return = convertifier_notification_push($data);
		    	if ($return['status']) {
		    		$_SESSION['convertifier_errors'] = $return['message'];
		    	}else{
		    		$_SESSION['convertifier_api_response'] = $return['message'];
		    	}
	    	}
	    }
	}
}
function getNotificationDetails($old_status, $post)
{
	convertifier_write_log('POST DATA : ', $post);
	$notification_title = $notification_body  = '';
	$notification_send  = 0;
	$icon = 'default';

	if ($old_status=='future') {
		$notification_title  = get_post_meta($post->ID, "convertifier_notification_title", true);
		$notification_body   = get_post_meta($post->ID, "convertifier_notification_body", true);
		$notification_send = get_post_meta($post->ID, "convertifier_post_push", true);
	}else{
	    if (isset($_POST['convertifier_notification_title'])) {
	        $notification_title = sanitize_text_field($_POST['convertifier_notification_title']);
	    } 
	    if (isset($_POST['convertifier_notification_body'])) {
	        $notification_body = sanitize_text_field($_POST['convertifier_notification_body']);
	    }
	    if (isset($_POST["convertifier_post_push"])) {
	        $notification_send = 1;
	    }
	}
	if (has_post_thumbnail($post->ID)) {
		$post_thumbnail_id = get_post_thumbnail_id($post->ID);
		$thumbnail_array   = wp_get_attachment_image_src($post_thumbnail_id, array(80, 80), true);
		if (!empty($thumbnail_array)) {
			$icon 		= $thumbnail_array[0];
    	}
	}

	$data = [
			'title'	=>	$notification_title,
			'body'	=>	$notification_body,
			'send'	=>	$notification_send,
			'url'	=>	get_permalink($post),
			'icon'	=>	$icon,
	];
	return $data;
}

function convertifier_notification_push($data = [])
{
	$return = ['status'=> false, 'message'=>''];
	try {

		$convertifier_key 		= get_option('convertifier_app_key');
		$secret 		      	= get_option('convertifier_app_secret');
		$data['source']			= 'wordpress';

		$convertifier_api_url 	= 'https://app.convertifier.com/api/v1/notification/send';
        if (defined('CONVERTIFIER_DEBUG') && CONVERTIFIER_DEBUG) {
        	$convertifier_api_url = 'https://convertifierfinal.dev/api/v1/notification/send';
        }

		$response = wp_remote_post( $convertifier_api_url, array(
	        'method'	=> 'POST',
	        'body'		=> $data, // variable is set
	        'timeout'	=> apply_filters( 'http_request_timeout', 15),
	        'sslverify' => (defined('CONVERTIFIER_DEBUG') && CONVERTIFIER_DEBUG)? false: true,
	        'headers' 	=> ['Authorization' => 'Basic ' . base64_encode( $convertifier_key . ':' . $secret )]
    	));
    	if ( is_wp_error( $response ) ) {
    		$return['status'] = true;
		    $error_message = $response->get_error_message();
		    $return['message'] = "Something went wrong: $error_message";
		} else {
		    $body 				= json_decode($response['body']);
		    if ($body!= null) {
		    	$return['message'] .= 'API Response: <strong>'.$body->message.'</strong>';
		    }
		}
		
	} catch (Exception $e) {
		$return['status'] = true;
		$return['message'] .= 'Exception : '.$e->getMessage();
		try{
			if (defined('CONVERTIFIER_DEBUG') && CONVERTIFIER_DEBUG) {
				convertifier_write_log(' Exception : '.$e->getMessage());
			}
		}catch(Exception $ex){
			//MayBe write Error
		}
	}
	return $return;
}

function load_custom_wp_admin_style($hook) {
    if($hook != 'toplevel_page_convertifier_options') {
        return;
    }

    wp_enqueue_style('convertifier-style', plugins_url( '/style.css', __FILE__ ));
}

function convertifier_integrate(WP_REST_Request $request) {
	require_once('integrate/_ini.php');
}

function add_action_links ( $links ) {
    $mylinks = array(
        '<a href="' . admin_url( 'admin.php?page=convertifier_options' ) . '">Settings</a>',
    );
    return array_merge( $links, $mylinks );
}
function convertifier_admin_menu()
{
    add_menu_page('Convertifier Push', 'Convertifier Push &nbsp;', 'create_users', 'convertifier_options', 'convertifier_options', 'dashicons-testimonial',65);
}
function getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    }elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    }else {
        $ip = $remote;
    }
    return $ip;
}
function convertifier_register_settings()
{
	register_setting('convertifier_settings','convertifier_app_key');
	register_setting('convertifier_settings','convertifier_fcm_id');
	register_setting('convertifier_settings','convertifier_app_secret');
}
function convertifier_headercode()
{
	global $convertifier_header;
	$app_key = get_option('convertifier_app_key');
	$fcm_id = get_option('convertifier_fcm_id');

	if ($app_key) {
		$convertifier_header = str_replace('FCM_ID', $fcm_id, $convertifier_header);
		echo str_replace('CONVERTIFIER_APPKEY', $app_key, $convertifier_header); // only output if options were saved
	}
}
function convertifier_footer()
{
	echo '<div class="notification-bar"></div><script> window.onload = function(){
     if (location.protocol=="http:" && location.hostname != "localhost") {
        var getCN = Convertifier.getCookies(\'cn\'); if ( getCN=="" ) { getCN = null; }
        var param = "endpoint="+getCN+"&appkey="+ConvertifierSettings.config.app_key ;
        initHistory(param);
     }};</script>';
}
function convertifier_options()
{
	require_once( plugin_dir_path( __FILE__ ) . 'convertifier_options.php' );
}
function isConvertifierSetupComplete()
{
	$option = get_option("convertifier_app_key");
	$fcm_id = get_option('convertifier_fcm_id');
	$app_secret = get_option('convertifier_app_secret');
	return (!$option || !$fcm_id || !$app_secret);
}
function convertifier_warn()
{
	if (!is_admin())
		return;
	
	if (isConvertifierSetupComplete()) {
		$url = admin_url( "admin.php?page=convertifier_options");
		echo "<div class='error notice is-dismissible'>
		<p><strong>
		Convertifier Push Notification setup is not complete. You need to provide details <a href='".admin_url( 'admin.php?page=convertifier_options' )."'> Plugin Settings </a></strong><br>
		You must enter your details from <a target='_blank' href='https://app.convertifier.com/website/settings#integration'>Convertifier</a> to start sending push notifications.
		</p>
		</div>";
	}
	if (isset($_SESSION['convertifier_errors']) && $_SESSION['convertifier_errors']!='') {
		echo "<div class='error notice is-dismissible'><p>Error while trying to send push notification <br><strong>".$_SESSION['convertifier_errors']."</strong></p></div>";
		unset($_SESSION['convertifier_errors']);
	}
	if (isset($_SESSION['convertifier_api_response'])) {
		echo "<div class='updated notice notice-success is-dismissible'><p>".$_SESSION['convertifier_api_response']."</p></div>";
		unset($_SESSION['convertifier_api_response']);
	}
}

require_once( plugin_dir_path( __FILE__ ) . 'includes/dashboard_widget.php' );