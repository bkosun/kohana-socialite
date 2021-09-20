<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_Provider_Vkontakte extends Kohana_Socialite_OAuth_Two_Provider
{
    /**
     * The unique provider ID.
     */
    const IDENTIFIER = 'VKONTAKTE';

    /**
     * The API language.
     */
    protected $_api_lang = 'en';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $_scopes = array('email',);

    /**
     * The fields being requested.
     *
     * @var array
     */
    protected $_fields = array('screen_name', 'first_name', 'last_name', 'nickname', 'bdate', 'sex', 'photo_max_orig', 'email', 'site',);

    /**
     * Get the authorize URL.
     *
     * @return string
     */
    protected function get_auth_url($state)
    {
        return $this->build_auth_url_from_base('https://oauth.vk.com/authorize', $state);
    }

    /**
     * Get the access Token URL
     *
     * @return string
     */
    protected function get_token_url()
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * Get the user by access token
     *
     * @param  array  $access_token
     * @throws Socialite_Exception
     * @return array
     */
    protected function get_user_by_token(array $access_token)
    {
        $api_url = 'https://api.vk.com/method/users.get';

        $params = array(
            'access_token' => $access_token['access_token'],
            'user_ids' => $access_token['user_id'],
            'fields' => implode(',', $this->_fields),
            'lang' => $this->_api_lang,
            'v' => '5.62',
        );
        
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
                ':message' => $data['error']['error_msg'] ,
            ));
        }

        $data = $data['response']['0'];

        if (in_array('email', $this->get_scopes()))
        {
            $data['email'] = Arr::get($access_token, 'email');
        }

        return $data;
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

        if (Arr::get($user, 'bdate'))
        {
            if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', Arr::get($user, 'bdate'), $matches))
            {
                array_shift($matches);
                list($birthday['day'], $birthday['month'], $birthday['year']) = $matches;
            }
            elseif (preg_match('/(\d{1,2})\.(\d{1,2})/', Arr::get($user, 'bdate'), $matches))
            {
                array_shift($matches);
                list($birthday['day'], $birthday['month']) = $matches;
            }

            $birthday['day'] = str_pad($birthday['day'], 2, '0', STR_PAD_LEFT);
            $birthday['month'] = str_pad($birthday['month'], 2, '0', STR_PAD_LEFT);
        }

        switch (Arr::get($user, 'sex')){
            case '1':
                $gender = 'female';
                break;
            case '2':
                $gender = 'male';
                break;
            default:
                $gender = NULL;
        }
        
        $profile = 'https://vk.com/id'.Arr::get($user, 'id');

        return (new Socialite_OAuth_Two_User())
            ->set_raw($user)
            ->map(array(
                'id' => Arr::get($user, 'id'),
                'nickname' => Arr::get($user, 'screen_name'),
                'first_name' => Arr::get($user, 'first_name'),
                'last_name' => Arr::get($user, 'last_name'),
                'surname' => Arr::get($user, 'nickname'),
                'birthday' => $birthday,
                'gender' => $gender,
                'photo' => Arr::get($user, 'photo_max_orig'),
                'email' => Arr::get($user, 'email'),
                'phone' => NULL,
                'website' => Arr::get($user, 'website'),
                'profile' => $profile,
            ));
    }
}