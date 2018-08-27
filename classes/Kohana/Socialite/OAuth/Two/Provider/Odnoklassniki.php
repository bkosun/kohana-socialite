<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_Provider_Odnoklassniki extends Kohana_Socialite_OAuth_Two_Provider
{
    /**
     * The unique provider ID.
     */
    const IDENTIFIER = 'ODNOKLASSNIKI';

    /**
     * The API language.
     */
    protected $_api_lang = NULL;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $_scopes = array('VALUABLE_ACCESS', 'GET_EMAIL',);

    /**
     * The fields being requested.
     *
     * @var array
     */

    protected $_fields = array('UID', 'FIRST_NAME', 'LAST_NAME', 'BIRTHDAY', 'GENDER', 'PIC_FULL', 'EMAIL', 'URL_PROFILE');

    /**
     * Get the authorize URL.
     *
     * @return string
     */
    protected function get_auth_url($state)
    {
        return $this->build_auth_url_from_base('https://connect.ok.ru/oauth/authorize', $state);
    }

    /**
     * Get the access Token URL.
     *
     * @return string
     */
    protected function get_token_url()
    {
        return 'https://api.ok.ru/oauth/token.do';
    }

    /**
     * Get the user by access token.
     *
     * @param array $access_token
     * @return array|mixed
     * @throws Kohana_Exception
     * @throws Socialite_Exception
     */
    protected function get_user_by_token(array $access_token)
    {
        $api_url = 'https://api.ok.ru/fb.do';

        $fields = implode(',', $this->_fields);

        $public_key = Kohana::$config->load('socialite.default.odnoklassniki.client_public');

        if ( ! $public_key)
        {
            throw new Socialite_Exception('Missing required parameters: :param', array(
                ':param' => 'public_key',
            ));
        }
        
        $params = array(
            'method' => 'users.getCurrentUser',
            'application_key' => $public_key,
            'fields' => $fields,
            'format' => 'JSON',
        );

        $params['sig'] = $this->get_sign($params, $access_token['access_token']);
        $params['access_token'] = $access_token['access_token'];

        $response = Request::factory($api_url)
            ->headers(array('User-Agent' => 'Kohana-Socialite', 'Accept' => 'application/json',))
            ->post($params)
            ->method(HTTP_Request::POST)
            ->execute()
            ->body();

        $data = json_decode($response, TRUE);

        if (isset($data['error_code']))
        {
            throw new Socialite_Exception('Error retrieving user info: :message', array(
                ':message' => $data['error_msg'],
            ));
        }

        return $data;
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

        $signature = md5($signature.md5($access_token.$this->_client_secret));

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
     * Create the user object.
     *
     * @param  array  $user
     * @return Socialite_OAuth_Two_User
     */
    protected function map_user_to_object(array $user)
    {
        $birthday = NULL;

        if (Arr::get($user, 'birthday'))
        {
            if (preg_match('/(\d{4})\-(\d{2})\-(\d{2})/', Arr::get($user, 'birthday'), $matches))
            {
                array_shift($matches);
                list($birthday['year'], $birthday['month'], $birthday['day']) = $matches;
            }
        }

        switch (Arr::get($user, 'gender')){
            case 'female':
                $gender = 'female';
                break;
            case 'male':
                $gender = 'male';
                break;
            default:
                $gender = NULL;
        }

        $profile = 'https://ok.ru/profile/'.Arr::get($user, 'uid');

        return (new Socialite_OAuth_Two_User())
            ->set_raw($user)
            ->map(array(
                'id' => Arr::get($user, 'uid'),
                'nickname' => NULL,
                'first_name' => Arr::get($user, 'first_name'),
                'last_name' => Arr::get($user, 'last_name'),
                'surname' => NULL,
                'birthday' => $birthday,
                'gender' => $gender,
                'photo' => Arr::get($user, 'pic_full'),
                'email' => Arr::get($user, 'email'),
                'phone' => NULL,
                'website' => NULL,
                'profile' => Arr::get($user, 'url_profile') ? Arr::get($user, 'url_profile') : $profile ,
            ));
    }
}