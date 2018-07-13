<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Socialite_OAuth_One_User extends Kohana_Socialite_OAuth_User {

    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The user's access token secret.
     *
     * @var string
     */
    public $token_secret;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @param  string  $token_secret
     * @return $this
     */
    public function set_token($token, $token_secret)
    {
        $this->token = $token;
        $this->token_secret = $token_secret;

        return $this;
    }
}