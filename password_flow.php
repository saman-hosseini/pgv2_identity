<?php

function loginizer_wp_authenticate($user, $username, $password)
{

    $result = SendLoginRequest($username, $password);

    if ($result === false) {
        return new WP_Error('server error', 'server error');
    } else if (!empty($result['access_token'])) {
        $user_id = Utility::get_userid_by_username($username);

        if (Constants::get_restrict_WPUserCreation() == 'on' && $user_id == NULL) {
            $Message = 'registration disabled';
            return new WP_Error('login_error', $Message);
        } else {
            $user_info = array();
            $user_info['first_name'] = $username;
            $user_info['user_login'] = $username;

            if ($user_id) {
                $user_info['ID'] = $user_id;
                $user = wp_update_user($user_info);
            } else {
                $user_info['user_pass'] =  wp_generate_password(12, false);
                $user = wp_insert_user($user_info);
            }

            wp_set_current_user($user);
            wp_set_auth_cookie($user);
            $user  = get_user_by('ID', $user);
            //do_action('wp_login', $user->user_login, $user);
            //exit;
        }
        return $user;
    } else if ($result['error_description'] == 'NotActive') {
        $Message = 'متاسفانه حساب کاربری شما هنوز فعال نشده است';
        return new WP_Error('login_error', $Message);
    } else if ($result['error_description'] == 'RequiresVerification') {
        $Message = 'لطفا مراحل ثبت نام رو تکمیل کنید';
        return new WP_Error('login_error', $Message);
    }
    else {
        $Message = 'نام کاربری یا رمزعبور اشتباه است';
        return new WP_Error('login_error', $Message);
    }
    return new WP_Error('login_error', 'login_error');
}

function SendLoginRequest($username, $password)
{
    try {
        $obj = array(
            "grant_type" => "password", 
            "username" => "$username",
            "password" => "$password",
            "deviceUid" => "wordpress",
            "platform" => "Web"
        );
        $params = http_build_query($obj);
        $login_url = Constants::get_token_endpoint(); //"http://192.168.11.9/api/v2/auth/login";
        $number_of_connection_tries = 1;
        $response = null;
        while ($number_of_connection_tries > 0) {
            $response = wp_remote_post($login_url, array(
                'timeout' => 60,
                'body' => $params,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length' => strlen($params)
                )
            ));
            if (is_wp_error($response)) {
                $number_of_connection_tries--;
                continue;
            } else {
                break;
            }
        }

        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    } catch (Exception $ex) {
        return false;
    }
}
