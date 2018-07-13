<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_User extends Kohana_Socialite_OAuth_User{
    
    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var string
     */
    public $refresh_token;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var int
     */
    public $expires_in;

    /**
     * Set the token on the user.
     *
     * @param  array  $token
     * @return $this
     */
    public function set_token($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refresh_token
     * @return $this
     */
    public function set_refresh_token($refresh_token)
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param  int  $expires_in
     * @return $this
     */
    public function set_expires_in($expires_in)
    {
        $this->expires_in = $expires_in;

        return $this;
    }
}