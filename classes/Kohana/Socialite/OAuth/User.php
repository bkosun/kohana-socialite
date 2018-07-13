<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Socialite_OAuth_User implements Kohana_Socialite_User
{
    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    public $id;

    /**
     * The user's nickname / username.
     *
     * @var string
     */
    public $nickname;

    /**
     * The user's first name.
     *
     * @var string
     */
    public $first_name;

    /**
     * The user's last name.
     *
     * @var string
     */
    public $last_name;

    /**
     * The user's surname.
     *
     * @var string
     */
    public $surname;

    /**
     * The user's birthday.
     *
     * @var string
     */
    public $birthday;

    /**
     * The user's gender.
     *
     * @var string
     */
    public $gender;

    /**
     * The user's photo image URL.
     *
     * @var string
     */
    public $photo;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    public $email;

    /**
     * The user's phone number.
     *
     * @var string
     */
    public $phone;

    /**
     * The user's website.
     *
     * @var string
     */
    public $website;

    /**
     * The user's profile URL.
     *
     * @var string
     */
    public $profile;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    public $raw;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Get the nickname / username for the user.
     *
     * @return string
     */
    public function get_nickname()
    {
        return $this->nickname;
    }

    /**
     * Get the first name of the user.
     *
     * @return string
     */
    public function get_first_name()
    {
        return $this->first_name;
    }

    /**
     * Get the last name of the user.
     *
     * @return string
     */
    public function get_last_name()
    {
        return $this->last_name;
    }

    /**
     * Get the surname of the user.
     *
     * @return string
     */
    public function get_surname()
    {
        return $this->surname;
    }

    /**
     * Get the birthday of the user.
     *
     * @return string
     */
    public function get_birthday()
    {
        return $this->birthday;
    }

    /**
     * Get the gender of the user.
     *
     * @return string
     */
    public function get_gender()
    {
        return $this->gender;
    }

    /**
     * Get the photo / image URL for the user.
     *
     * @return string
     */
    public function get_photo()
    {
        return $this->photo;
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function get_email()
    {
        return $this->email;
    }

    /**
     * Get the phone number of the user.
     *
     * @return string
     */
    public function get_phone()
    {
        return $this->phone;
    }

    /**
     * Get the website of the user.
     *
     * @return string
     */
    public function get_website()
    {
        return $this->website;
    }

    /**
     * Get the profile URL of the user.
     *
     * @return string
     */
    public function get_profile()
    {
        return $this->profile;
    }

    /**
     * Get the raw user array.
     *
     * @return array
     */
    public function get_raw()
    {
        return $this->raw;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @param  array  $user
     * @return $this
     */
    public function set_raw(array $user)
    {
        $this->raw = $user;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function map(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->{$key} = $value;
        }

        return $this;
    }
}