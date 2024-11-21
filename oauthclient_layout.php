<?php

function oc_oauthclient_layout()
{
    wp_enqueue_script("serverlist", plugins_url('/Assets/js/serverslist.js', __FILE__));
    wp_enqueue_script("guides", plugins_url('/Assets/js/guides.js', __FILE__));
    wp_enqueue_script("servernames", plugins_url('/Assets/js/select_server.js', __FILE__));
    wp_enqueue_script("scripts", plugins_url('/Assets/js/scripts.js', __FILE__));
    wp_enqueue_style("css_styles", plugins_url('/Assets/css/layout.css', __FILE__));

?>


    <?php
    oauthclientconfig();
    ?>

<?php }
function oauthclientconfig()
{

?>
    <style>
        .server_config_table td:first-cild {
            width: 234px
        }
    </style>

    <div id="save-status"></div>
    <div class="card">
        <div class="innerpart" style="min-height:250px">
            <h3 class="title-line">تنظیمات احراز هویت</h3>
            <div class="sessionborder">
                <form id="oauthconfig" method="post" action="">
                    <input type="hidden" name="action" value="oauthconfig" />
                    <?php wp_nonce_field('OAuthConfig_nonce', 'OAuthConfig_nonce') ?>
                    <table class="server_config_table">
                        <tr id="base_url_section">
                            <td><label for="base_url" style="width:173px;display:inline-block">Base Url</label></td>
                            <td><input type="text" id="base_url" name="base_url" style="width: 410px;    max-width: 100%;" placeholder="Base Url" value="<?php echo esc_attr(Constants::get_base_url()); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="btn-cntnr"><input class="buttons_style" type="submit" id="clientconfig" value="ذخیره تنظیمات" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <div class="innerpart">
            <div style="margin-top:1.5rem;">
                <form action="" method="POST" id="saveSettings">
                    <input type="hidden" name="action" value="saveSettingsForm" />
                    <?php wp_nonce_field('saveSettingsForm_nonce', 'saveSettingsForm_nonce') ?>
                    <h3 class="title-line"> ایجاد محدودیت برای ثبت نام کاربران جدید</h3>
                    <input type="checkbox" id="oc_restrictWPUserCreation" name="oc_restrictWPUserCreation" <?php if (Constants::get_restrict_WPUserCreation() == 'on') {
                                                                                                                echo esc_attr('checked');
                                                                                                            } ?> />
                    فعال کردن محدود کردن کاربر برای ایجاد/ورود به سیستم در صورتی که کاربر قبلاً در WP وجود نداشته باشد.
                    <!-- <div style="display:flex; margin:1rem;">
                    <textarea id="reasonToRestrictWPUser" name="reasonToRestrictWPUser" style="padding:0px 8px; margin-bottom:0px;"><?php echo get_option('reasonToRestrictWPUser'); ?></textarea>
                    </div> -->
                    <div class="btn-cntnr">
                        <button type="submit" class="buttons_style">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php
}
