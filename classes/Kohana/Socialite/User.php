<?php defined('SYSPATH') or die('No direct script access.');

interface Kohana_Socialite_User
{
    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function get_id();

    /**
     * Get the nickname / username for the user.
     *
     * @return string
     */
    public function get_nickname();

    /**
     * Get the first name of the user.
     *
     * @return string
     */
    public function get_first_name();

    /**
     * Get the last name of the user.
     *
     * @return string
     */
    public function get_last_name();

    /**
     * Get the birthday (UNIX TIME) of the user.
     *
     * @return string
     */
    public function get_birthday();

    /**
     * Get the gender of the user.
     *
     * @return string
     */
    public function get_gender();

    /**
     * Get the photo / image URL for the user.
     *
     * @return string
     */
    public function get_photo();

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function get_email();

    /**
     * Get the phone number of the user.
     *
     * @return string
     */
    public function get_phone();

    /**
     * Get the profile URL of the user.
     *
     * @return string
     */
    public function get_profile();
}