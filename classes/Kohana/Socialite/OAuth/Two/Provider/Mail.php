<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_Provider_Mail extends Kohana_Socialite_OAuth_Two_Provider
{
    /**
     * The unique provider ID.
     */
    const IDENTIFIER = 'MAIL';

    /**
     * The API language.
     */
    protected $_api_lang = NULL;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $_scopes = array();

    /**
     * The fields being requested.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $_use_state = TRUE;

    /**
     * Get the authorize URL.
     *
     * @param  string  $state
     * @return string
     */
    protected function get_auth_url($state)
    {
        return $this->build_auth_url_from_base('https://connect.mail.ru/oauth/authorize', $state);
    }

    /**
     * Get the access Token URL.
     *
     * @return string
     */
    protected function get_token_url()
    {
        return 'https://connect.mail.ru/oauth/token';
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

        if ( ! $data)
        {
            throw new Socialite_Exception('Error retrieving access token: :message', array(
                ':message' => 'Unexpected response',
            ));
        }

        return $data;
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
        $api_url = 'http://www.appsmail.ru/platform/api';
        
        $params = array(
            'method' => 'users.getInfo',
            'secure' => TRUE,
            'app_id' => $this->_client_id,
        );

        $params['sig'] = $this->get_sign($params, $access_token['access_token']);
        $params['session_key'] = $access_token['access_token'];

        $response = Request::factory($api_url)
            ->headers(array('User-Agent' => 'Kohana-Socialite', 'Accept' => 'application/json',))
            ->post($params)
            ->method(HTTP_Request::POST)
            ->execute()
            ->body();

        $data = json_decode($response, TRUE);

        if (isset($data['error']))
        {
            throw new Socialite_Exception('Error retrieving user info: :message', array(
                ':message' => $data['error']['error_msg'],
            ));
        }

        return $data['0'];
    }

    /**
     * Get the signature for API request.
     *
     * @param  array   $params
     * @param  string  $access_token
     * @return string
     */
    protected function get_sign(array $params, $access_token)
    {
        $signature = '';
        ksort($params);

        foreach ($params as $key => $value)
        {
            $signature .= $key.'='.$value;
        }

        $signature = md5($signature.'session_key='.$access_token.$this->_client_secret);

        return $signature;
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
     * Create the user object
     *
     * @param  array  $user
     * @return Socialite_OAuth_Two_User
     */
    protected function map_user_to_object(array $user)
    {
        $birthday = NULL;

        if (Arr::get($user, 'birthday'))
        {
            if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', Arr::get($user, 'birthday'), $matches))
            {
                array_shift($matches);
                list($birthday['day'], $birthday['month'], $birthday['year']) = $matches;
            }
        }

        switch (Arr::get($user, 'sex')){
            case '0':
                $gender = 'male';
                break;
            case '1':
                $gender = 'female';
                break;
            default:
                $gender = NULL;
        }

        return (new Socialite_OAuth_Two_User())
            ->set_raw($user)
            ->map(array(
                'id' => Arr::get($user, 'uid'),
                'nickname' => Arr::get($user, 'nick'),
                'first_name' => Arr::get($user, 'first_name'),
                'last_name' => Arr::get($user, 'last_name'),
                'surname' => NULL,
                'birthday' => $birthday,
                'gender' => $gender,
                'photo' => Arr::get($user, 'pic_190'),
                'email' => Arr::get($user, 'email'),
                'phone' => NULL,
                'website' => NULL,
                'profile' => Arr::get($user, 'link'),
            ));
    }
}