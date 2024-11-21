<?php
/*
Plugin Name: PayamGostar2 Identity client login for WordPress .
Plugin URI: https://crm.payamgostar.com/
Description: Login and authenticate Wordpress users using PayamGostar Server credentials
Version: 1.0.0
Author: saman.hosseini
Author URI: https://crm.payamgostar.com/
*/

require_once 'base_variables.php';
require_once 'utility.php';
require_once 'password_flow.php';
require_once 'oauthclient_layout.php';
require_once(ABSPATH . 'wp-admin/includes/plugin.php');



class oc_oauthclient_controller
{
  protected static $instance = NULL;

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function __construct()
  {
    if (! function_exists('debug_wp_remote_post_and_get_request')) :
      function debug_wp_remote_post_and_get_request($response, $context, $class, $r, $url)
      {
        error_log('------------------------------');
        error_log($url);
        error_log(json_encode($response));
        error_log($class);
        error_log($context);
        error_log(json_encode($r));
      }
      add_action('http_api_debug', 'debug_wp_remote_post_and_get_request', 10, 5);
    endif;

    add_filter('authenticate', 'loginizer_wp_authenticate', 10001, 3);
    add_action('admin_menu', array($this, 'addMenuPage'));
    add_action('init', array($this, 'save_oauthclient_config'));

    register_activation_hook(__FILE__, array($this, 'lwliad_activate_oauth_plug'));
    register_uninstall_hook(__FILE__, 'deletePluginDB');
  }

  function addMenuPage()
  {
    add_menu_page('payamgostarclient', 'پیام گستر', 'manage_options', 'PayamGostar Client authentication intergrating with Identity Server', 'oc_oauthclient_layout');
  }

  function lwliad_activate_oauth_plug()
  {
    wp_redirect('plugins.php');
  }

  function save_oauthclient_config()
  {
    if (self::oc_is_site_admin() && isset($_POST['action'])) {
      if ($_POST['action'] == 'oauthconfig') {

        if (isset($_POST['OAuthConfig_nonce']) && !empty($_POST['OAuthConfig_nonce']) && wp_verify_nonce(sanitize_key($_POST['OAuthConfig_nonce']), 'OAuthConfig_nonce')) {

          update_option('oc_base_url', isset($_POST['base_url']) ? sanitize_text_field($_POST['base_url']) : '');
          update_option('oc_domain_name', isset($_POST['domain_name']) ? sanitize_text_field($_POST['domain_name']) : '');

          self::success('Successfully saved the configuration.');
        }
      } else if ($_POST['action'] == 'saveSettingsForm') {
        if (isset($_POST['saveSettingsForm_nonce']) && !empty($_POST['saveSettingsForm_nonce']) && wp_verify_nonce(sanitize_key($_POST['saveSettingsForm_nonce']), 'saveSettingsForm_nonce')) {
          update_option('oc_restrictWPUserCreation', isset($_POST['oc_restrictWPUserCreation']) ? sanitize_text_field($_POST['oc_restrictWPUserCreation']) : '');
          if (Constants::get_restrict_WPUserCreation() == 'on') {
            self::success('Enabled the check to restrict WP User Creation.');
          } else {
            self::success('Disabled the check to restrict WP User Creation.');
          }
        }
      }
    }
  }

  function login_user($userinfo, $access_token, $id_token)
  {
    $user_login = $userinfo[Constants::get_userid()];
    $user_email = strtolower($userinfo[Constants::get_user_email()]);
    if (empty($user_email) == false) {
      $user_id = Utility::get_userid_by_user_email($user_email);
    }

    if (empty($user_id) == true) {
      $user_id = Utility::get_userid_by_username($user_login);
    }

    if (Constants::get_restrict_WPUserCreation() == 'on' && $user_id == NULL) {
      wp_redirect(site_url('wp-login.php?registration=disabled'));
      exit;
    } else {
      $user_info = array();
      $user_info['user_login'] = $user_login;
      $user_info['user_email'] = $user_email;
      $user_info['first_name'] = $userinfo[Constants::get_user_firstname()];
      $user_info['last_name'] = $userinfo[Constants::get_user_lastname()];
      $user_info['display_name'] = $userinfo[Constants::get_user_displayname()] . ' ' . $userinfo[Constants::get_user_lastname()];

      if ($user_id) {
        $user_info['ID'] = $user_id;
        Utility::change_username($user_id, $user_login);
        $user = wp_update_user($user_info);
      } else {
        $user_info['user_nicename'] = $user_login;
        $user_info['user_pass'] =  wp_generate_password(12, false);
        $user = wp_insert_user($user_info);
      }

      wp_set_current_user($user);
      wp_set_auth_cookie($user);
      $exp = Utility::read_jwt_payload($access_token)[Constants::EXPIRE_FIELD];
      Utility::wp_set_cookie(Constants::ACCESS_TOKEN_COOKIE_NAME, $access_token, $exp);
      if (isset($id_token)) {
        Utility::wp_set_cookie(Constants::ID_TOKEN_COOKIE_NAME, $id_token, $exp);
      }
      //$user  = get_user_by('ID', $user);
      do_action('wp_login', $user->user_login, $user);
      wp_redirect(home_url());
      exit;
    }
  }

  function showAttributeslist($user_info)
  {

    $attribute_keys = array();
    if ($user_info) {
      echo '<div  id="wrapper">';
      echo '<tr><td colspan="2"> <b>Note: </b>You need to copy the <b>Attribute Name</b> and save in the Attribute Mapping which is below the configuration</td></tr><br><br>';
      echo '<table style="width: 90%" >';
      echo '<tr style="background-color: #009879; border: 1px solid #999; color: #ffffff; padding: 0.5rem; text-align: center;"><td >Attribute Name</td><td >Attribute Value</td></tr>';

      $user_info = self::makeNonNested($user_info);

      foreach ($user_info as $key => $value) { {
          $attribute_keys[] = $key;
          echo "<tr><td style='border: 1px solid #999; padding: 0.5rem; text-align: left;'>" . esc_attr($key) . "</td><td style='border: 1px solid #999; padding: 0.5rem;text-align: left;'>" . implode('<br/>', (array)esc_attr($value)) . "</td></tr>";
        }
      }
      echo '<tr><td colspan="2"  style="padding-left: 40%"><input type="button" onClick="closeAndRefresh();" class="buttons_style" style="text-decoration: none;width: auto;border-radius: 4px !important; background-color: #ea530be8; cursor: pointer " id ="attribute-close-btn" value="Close"/></td></tr>';
      echo '</table></div> ';
?>
      <script>
        window.onunload = refreshParent;

        function refreshParent() {
          window.opener.location.reload();
        }

        function closeAndRefresh() {
          window.opener.location.reload();
          self.close();
        }
      </script>
<?php
    }
  }

  /* Notifications on success and error messages */

  public static function success($message, $button = NULL)
  {
    $class = 'notethick';
    $messageHtml = sprintf('<div class="%1$s " style="margin-left:12rem;margin-top:2rem"><p>%2$s %3$s</p></div>', esc_attr($class), esc_html($message), $button);
    echo "<script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('save-status').insertAdjacentHTML('beforeend', '{$messageHtml}'); });</script>";
  }

  function error($message)
  {
    $class = 'errornote';
    $messageHtml = sprintf('<div class="%1$s" style="margin-left:12rem; margin-top:2rem"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    echo "<script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('save-status').insertAdjacentHTML('beforeend', '{$messageHtml}'); });</script>";
  }

  function makeNonNestedRecursive(array &$out, $key, array $in)
  {
    foreach ($in as $k => $v) {
      if (is_array($v)) {
        self::makeNonNestedRecursive($out, $key . $k . '_', $v);
      } else {
        $out[$key . $k] = $v;
      }
    }
  }

  function oc_is_site_admin()
  {
    return in_array('administrator',  wp_get_current_user()->roles);
  }


  function makeNonNested(array $in)
  {
    $out = array();
    self::makeNonNestedRecursive($out, '', $in);

    return $out;
  }

  function deletePluginDB()
  {
    delete_option('oc_base_url');
    delete_option('oc_domain_name');
    delete_option('oc_attributes_names_received');

    delete_option('oc_restrictWPUserCreation');
  }
}

$OAuth_Client = oc_oauthclient_controller::getInstance();
