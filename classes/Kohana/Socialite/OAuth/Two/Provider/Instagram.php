<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_Provider_Instagram extends Kohana_Socialite_OAuth_Two_Provider
{
    /**
     * The unique provider ID.
     */
    const IDENTIFIER = 'INSTAGRAM';

    /**
     * The API language.
     */
    protected $_api_lang = NULL;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $_scopes = array('basic', 'public_content',);

    /**
     * The fields being requested.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $_scope_separator = ' ';

    /**
     * Get the authorize URL.
     *
     * @param  string  $state
     * @return string
     */
    protected function get_auth_url($state)
    {
        return $this->build_auth_url_from_base('https://api.instagram.com/oauth/authorize', $state);
    }

    /**
     * Get the access Token URL.
     *
     * @return string
     */
    protected function get_token_url()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @throws Socialite_Exception
     * @return array
     */
    public function get_access_token_response($code)
    {
        $response = Request::factory($this->get_token_url())
            ->headers(array('User-Agent' => 'Kohana-Socialite', 'Accept' => 'application/json',))
            ->post($this->get_token_fields($code))
            ->method(HTTP_Request::POST)
            ->execute()
            ->body();

        $data = json_decode($response, TRUE);

        if (isset($data['error_message']))
        {
            throw new Socialite_Exception('Error retrieving access token: :message', array(
                ':message' => $data['error_message'],
            ));
        }

        return array('access_token' => $data['access_token'],);
    }

    /**
     * Get the user by access token.
     *
     * @param  array  $access_token
     * @throws Socialite_Exception
     * @return array
     */
    protected function get_user_by_token(array $access_token = array())
    {
        $api_url = 'https://api.instagram.com/v1/users/self';

        $params = array('access_token' => $access_token['access_token'],);
        $params['sig'] = $this->get_sign('/users/self', $params , $this->_client_secret);

        $response = Request::factory($api_url)
            ->headers(array('User-Agent' => 'Kohana-Socialite', 'Accept' => 'application/json',))
            ->query($params)
            ->method(HTTP_Request::GET)
            ->execute()
            ->body();

        $data = json_decode($response, TRUE);

        if (isset($data['error_message']) OR $data['meta']['code'] != 200)
        {
            throw new Socialite_Exception('Error retrieving user info: :message', array(
                ':message' =>  isset($data['error_message']) ? $data['error_message'] : $data['meta']['error_message'],
            ));
        }

        return $data['data'];
    }

    /**
     * Get the signature for API request.
     *
     * @param  string  $endpoint
     * @param  array   $params
     * @param  string  $secret
     * @return string
     */
    protected function get_sign($endpoint, array $params, $secret)
    {
        $signature = $endpoint;
        ksort($params);

        foreach ($params as $key => $value) 
        {
            $signature .= '|'.$key.'='.$value;
        }

        return hash_hmac('sha256', $signature, $secret, FALSE);
    }

    /**
     * Get the fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function get_token_fields($code)
    {
        return array_merge(parent::get_token_fields($code), array(
            'grant_type' => 'authorization_code',
        ));
    }

    /**
     * Create the user object.
     *
     * @param  array  $user
     * @return Socialite_OAuth_Two_User
     */
    protected function map_user_to_object(array $user)
    {
        $names = explode(' ', Arr::get($user, 'full_name'));

        list($first_name, $last_name) = $names;

        $profile = 'https://www.instagram.com/'.Arr::get($user, 'username');

        return (new Socialite_OAuth_Two_User())
            ->set_raw($user)
            ->map(array(
                'id' => Arr::get($user, 'id'),
                'nickname' => Arr::get($user, 'username'),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'surname' => NULL,
                'birthday' => NULL,
                'gender' => NULL,
                'photo' => Arr::get($user, 'profile_picture'),
                'email' => NULL,
                'phone' => NULL,
                'website' => Arr::get($user, 'website'),
                'profile' => $profile,
            ));
    }
}