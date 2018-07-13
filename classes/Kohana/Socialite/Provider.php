<?php defined('SYSPATH') or die('No direct script access.');

interface Kohana_Socialite_Provider
{
    /**
     * Redirect the user to the authentication page for the provider.
     * @return string
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return Socialite_User
     */
    public function user();
}