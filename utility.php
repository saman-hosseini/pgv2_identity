<?php
class Utility
{
    static function get_userid_by_username($username)
    {
        global $wpdb;

        $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_login= %s", $username));

        if ($user_id) {
            return $user_id;
        } else {
            return null;
        }
    }

    static function get_userid_by_user_email($user_email)
    {
        global $wpdb;

        $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE LOWER(user_email)= %s", $user_email));

        if ($user_id) {
            return $user_id;
        } else {
            return null;
        }
    }

    static function change_username($user_id, $new_username)
    {
        global $wpdb;

        $q  = $wpdb->prepare("UPDATE $wpdb->users SET user_login = %s WHERE ID = %s", $new_username, $user_id);
        $wpdb->query($q);
    }

    static function read_jwt_payload($access_token)
    {
        $id_array = explode(".", $access_token);
        if (isset($id_array[1])) {
            $id_body = base64_decode($id_array[1]);
            if (is_array(json_decode($id_body, true))) {
                return json_decode($id_body, true);
            }
        }

        echo 'Invalid access_token.<br><b>access_token : </b>' . esc_attr($access_token);
        exit;
    }

    static function wp_set_cookie($cookie_name, $access_token, $expire)
    {
        setcookie($cookie_name, $access_token, $expire, COOKIEPATH, COOKIE_DOMAIN);
    }

    static function wp_remove_cookie($cookie_name)
    {
        setcookie($cookie_name, "", time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}
