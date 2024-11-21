<?php
class Constants
{
    const CLIENTID = "Wordpress.Client";
    const CLIENTSECRET = "86vib2Gae8Xp";
    const CLIENTSCOPE = "email profile roles openid app.api.employeeprofile.read";

    const ACCESS_TOKEN_COOKIE_NAME = "oc_access_token";
    const ID_TOKEN_COOKIE_NAME = "oc_id_token";

    const AUTHORIZE_ENDPOINT = "/connect/authorize";
    const TOKEN_ENDPOINT = "/connect/token";
    const ENDSESSION_ENDPOINT = "/connect/endsession";
    const USERINFO_ENDPOINT = "/connect/userinfo";
    const CURRENTUSERINFO_ENDPOINT = "/party/v1/private/user/current";

    const LOGOUT_URL = "/logout";

    const EXPIRE_FIELD = 'exp';

    public static function get_base_url()
    {
        return get_option('oc_base_url');
    }

    public static function get_test()
    {
        return get_option('oc_test');
    }

    public static function get_logout_url()
    {
        return self::get_base_url() . self::LOGOUT_URL;
    }

    public static function get_authorize_endpoint()
    {
        return self::get_base_url() . self::AUTHORIZE_ENDPOINT;
    }

    public static function get_token_endpoint()
    {
        return self::get_base_url() . self::TOKEN_ENDPOINT;
    }

    public static function get_endsession_endpoint()
    {
        global $wp;
        $id_token = isset($_COOKIE[Constants::ID_TOKEN_COOKIE_NAME]) ? $_COOKIE[Constants::ID_TOKEN_COOKIE_NAME] : '';


        return self::get_base_url() . self::ENDSESSION_ENDPOINT . '?' . 'post_logout_redirect_uri=' . home_url($wp->request) . '&id_token_hint=' . $id_token;
    }

    public static function get_userinfo_endpoint()
    {
        return self::get_base_url() . self::USERINFO_ENDPOINT;
    }

    public static function get_currentuserinfo_endpoint()
    {
        if (self::get_test()) {
            return 'http://localhost:40005' . self::CURRENTUSERINFO_ENDPOINT;
        }
        return str_replace('accounts', 'app', self::get_base_url()) . self::get_mid_url() . self::CURRENTUSERINFO_ENDPOINT;
    }

    public static function get_restrict_WPUserCreation()
    {
        return get_option('oc_restrictWPUserCreation');
    }

    public static function get_userid()
    {
        return 'userId';
    }

    public static function get_user_email()
    {
        return 'email';
    }

    public static function get_user_firstname()
    {
        return 'firstName';
    }

    public static function get_user_lastname()
    {
        return 'lastName';
    }

    public static function get_user_displayname()
    {
        return 'displayname';
    }
}
