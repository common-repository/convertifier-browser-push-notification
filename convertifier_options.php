<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * User: msonowal
 * Date: 05/11/16
 * Time: 7:21 PM
 */
$path= plugins_url('/', __FILE__ );
?>
<div class="welcome-panel cf_plugin">
    <header>
        <div class="cf_logo"><img src="<?=$path.'images/convertifier-logo.png'?>" alt=""></div>
        <div class="cf_social">
            <ul>
                <li class="cf_website"><a href="https://www.convertifier.com/" target="_blank"><i class="fa fa-globe" aria-hidden="true"></i>www.convertifier.com</a></li>
                <li class="cf_email"><a href="mailto:divya@convertifier.com" target="_blank"><i class="fa fa-envelope" aria-hidden="true"></i>divya@convertifier.com</a></li>
                <li class="cf_skype"><a href="#" target="_blank"><i class="fa fa-skype" aria-hidden="true"></i>convertifier</a></li>
            </ul>
        </div>
    </header>
    <div class="cf_steps">
        <ul>
            <li class="cf_done">
                <div class="cf_circle"><i class="check_icn"></i><i>1</i></div>
                <span>Overview</span>
                <div class="cf_dots"></div>
            </li>
            <li>
                <div class="cf_circle"><i class="check_icn"></i><i>2</i></div>
                <span>Setup</span>
                <div class="cf_dots"></div>
            </li>
            <li>
                <div class="cf_circle"><i class="check_icn"></i><i>3</i></div>
                <span>Config</span>
            </li>
        </ul>
    </div>
    <div class="cf_tabs">
        <div class="cf_tab cf_tab-1 active">
            <div class="cf_row">
                <div class="cf_img_block">
                    <img src="<?=$path.'images/notification-preveiw-1.png'?>" class="img-responsive" alt="" />
                </div>
                <div class="cf-50-content">
                    <h2><small>You must have heard the term ‘Browser push notification’.<br /> So, what it is?.</small></h2>
                    <p>Browser push notifications are the notifications or messages which get delivered to the browser and/or mobile home screen of the subscriber/app user. These notifications inform the user about any latest update or offer on the application or mobile app and takes them to the corresponding page on the website or the mobile app.</p>
                </div>
            </div>
            <div class="cf_row">
                <h2>How do browser push notifications work?</h2>
                <p>When any user comes on your website, the website asks for a permission from user to send notifications. As soon as the user allows, he gets subscribed for receiving the browser push notifications. After this, you can start sending notifications to the user.</p>
                <img src="<?=$path.'images/notification-preveiw-2.png'?>" class="pull-left img-responsive mrg-btm-10" alt="">
                <img src="<?=$path.'images/notification-preveiw-3.png'?>" class="pull-right img-responsive" alt="">
            </div>
            <button class="next_btn">Next</button>
        </div>
        <div class="cf_tab cf_tab-2">
            <div class="cf_row">
                <div class="w-60">
                    <h4>Integrating Convertifier with your website</h4>
                    <p>Sign up for free on <a href="https://www.convertifier.com/" target="_blank">Convertifier.com</a> with the website you want to integrate. You can always change the website and plan later.
In the platform option, select WordPress. If your website is SSL enabled or is a https website, then enable it else disable it. Enter your website URL and the time zone as per you want to send the notification. Enable notification history if you want the history panel to appear on your website. You can send welcome notification to your users when they subscribe. Just enable the ‘welcome notification’ and enter the details of the notification.</p>
                    <h4>Generate Firebase Cloud Messaging (FCM) Id</h4>
                    <p>Sign in to the firebase console <a href="https://firebase.google.com" target="_blank">https://firebase.google.com</a>. Create a New Project. Enter your project name and select your country or region. Click on the gear/settings symbol in the left sidebar and click on the ‘Project settings’. Click on the ‘Cloud Messaging’ tab and there you would find the server key and sender Id.</p>
                </div>
                <div class="w-40">
                    <img src="<?=$path.'images/notification-preveiw-4.png'?>" class="img-responsive" alt="">
                </div>
            </div>

            <div class="cf_row">
                <div class="w-45">
                    <img src="<?=$path.'images/notification-preveiw-5.png'?>" class="img-responsive" alt="">
                </div>
                <div class="w-50 pull-right">
                    <h4>Integrating your FCM project with Convertifier</h4>
                    <p>Enter your FCM sender ID and FCM server key..</p>
                </div>
            </div>
            <div class="cf_row">
                <div class="w-45">
                    <h4>Website Integration & Verification</h4>
                    <p>Copy the application key from Convertifier website integration settings and paste it into your WordPress plugin back end. After inserting the key in the back end, click on the ‘Verify settings’ button in the Convertifier website integration settings. This completes your integration.</p>
                </div>
                <div class="w-50 pull-right">
                    <img src="<?=$path.'images/notification-preveiw-6.png'?>" class="img-responsive" alt="">
                </div>
            </div>
            <button class="next_btn">Next</button>
        </div>
        <div class="cf_tab cf_tab-3">
            <div class="cf_row">
                <div class="w">
                    <h2>Convertifier</h2>
                    <p>You need to have a  <a target="_blank" href="https://www.convertifier.com">Convertifier</a> account before you start sending push notifications. <br/>
                    </p>
                    <form method="post" action="options.php">
                    <?php settings_fields( 'convertifier_settings' ); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">FCM ID &nbsp;</th>
                                <td><input style="min-width: 320px;" type="text" name="convertifier_fcm_id" value="<?php echo esc_html(get_option('convertifier_fcm_id'));?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Your Convertifier APP Key &nbsp;</th>
                                <td><input style="min-width: 320px;" type="text" name="convertifier_app_key" value="<?php echo esc_html(get_option('convertifier_app_key'));?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Your Convertifier API Token &nbsp;</th>
                                <td><textarea style="min-width: 320px;min-height: 120px;" type="text" name="convertifier_app_secret"><?php echo esc_html(get_option('convertifier_app_secret'));?></textarea></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p class="submit">
                                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
                                    </p>
                                    Sign up for free at <a target="_blank" href="https://www.convertifier.com">www.convertifier.com </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
    </div><!-- .cf_tabs -->
</div><!-- .cf_plugin -->
<script type="text/javascript">
    jQuery(document).on('click','.cf_plugin .next_btn',function(){
        jQuery(this).parent().removeClass('active').next().addClass('active');
        jQuery('.cf_plugin .cf_steps').find('.cf_done').next().addClass('cf_done');
        jQuery('body').animate({ scrollTop: 0 }, 600);
    });
</script>