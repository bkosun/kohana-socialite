<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
    'default' => array(
        /**
         * Default config for Socialite
         */
        'facebook' => array(
            /**
             * Facebook provider settings
             *
             * string   client_id        Application ID
             * string   client_secret    Secret key applications
             * string   redirect_url     Default redirect URL
             * array    scopes           Scopes
             *
             * @link https://developers.facebook.com/apps
             */
            'client_id' => '',
            'client_secret' => '',
            'redirect_url' => '',
            'scopes' => array(),
        ),
        'instagram' => array(
            /**
             * Instagram provider settings
             *
             * string   client_id        Application ID
             * string   client_secret    Secret key applications
             * string   redirect_url     Default redirect URL
             * array    scopes           Scopes
             *
             * @link https://www.instagram.com/developer/clients/
             */
            'client_id' => '',
            'client_secret' => '',
            'redirect_url' => '',
            'scopes' => array(),
        ),
        'vkontakte' => array(
            /**
             * Vkontakte provider settings
             *
             * string   client_id        Application ID
             * string   client_secret    Secret key applications
             * string   redirect_url     Default redirect URL
             * array    scopes           Scopes
             *
             * @link https://vk.com/apps?act=manage
             */
            'client_id' => '',
            'client_secret' => '',
            'redirect_url' => '',
            'scopes' => array(),
        ),
    ),
);