<?php
/**
 * Created by Manash Jyoti Sonowal.
 * User: msonowal
 * Date: 07/11/16
 * Time: 1:06 PM
 */
	header("Content-Type: application/json");
	header("X-Robots-Tag: none");
    $fcm_id = array_keys($_GET);
    $fcm_id = @$fcm_id[0];
?>
{
    "start_url": "/",
    "gcm_sender_id": "<?=$fcm_id;?>",
    "gcm_user_visible_only": true
}