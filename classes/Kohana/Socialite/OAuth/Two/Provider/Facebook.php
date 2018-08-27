<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite_OAuth_Two_Provider_Facebook extends Kohana_Socialite_OAuth_Two_Provider
{
    /**
     * The unique provider ID.
     */
    const IDENTIFIER = 'FACEBOOK';

    /**
     * The API language.
     */
    protected $_api_lang = 'en_US';

    /**
     * The scopes being requested.
     *
     *@info user_mobile_phone scope temporarily disabled
     *@link https://developers.facebook.com/blog/post/2011/01/14/platform-updates--new-user-object-fields--edge-remove-event-and-more/
     *@link https://developers.facebook.com/blog/post/447/
     *
     * @var array
     */
    protected $_scopes = array('public_profile', 'email',);

    /**
     * The fields being requested.
     *
     * @var array
     */
    protected $_fields = array('first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'email', 'website', 'link',);

    /**
     * Get the authorize URL.
     *
     * @param  string  $state
     * @return string
     */
    protected function get_auth_url($state)
    {
        return $this->build_auth_url_from_base('https://www.facebook.com/v2.8/dialog/oauth', $state);
    }

    /**
     * Get the access Token URL.
     *
     * @return string
     */
    protected function get_token_url()
    {
        return 'https://graph.facebook.com/oauth/access_token';
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

        if (isset($data['error']))
        {
            throw new Socialite_Exception('Error retrieving access token: :message', array(
                ':message' => $data['error']['message'],
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
        $api_url = 'https://graph.facebook.com/me';

        $params = array(
            'access_token' => $access_token['access_token'],
            'fields' => implode(',', $this->_fields),
            'locale' => $this->_api_lang,
        );

        $response = Request::factory($api_url)
            ->headers(array('User-Agent' => 'Kohana-Socialite', 'Accept' => 'application/json',))
            ->query($params)
            ->method(HTTP_Request::GET)
            ->execute()
            ->body();

        $data = json_decode($response, TRUE);

        if (isset($data['error']))
        {
            throw new Socialite_Exception('Error retrieving user info: :message', array(
                ':message' => $data['error']['message'],
            ));
        }

        return $data;
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
            if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', Arr::get($user, 'birthday'), $matches))
            {
                array_shift($matches);
                list($birthday['month'], $birthday['day'], $birthday['year']) = $matches;
            }
            elseif (preg_match('/(\d{2})\.(\d{2})/', Arr::get($user, 'birthday'), $matches))
            {
                array_shift($matches);
                list($birthday['month'], $birthday['day']) = $matches;
            }
            elseif (preg_match('/(\d{4})/', Arr::get($user, 'birthday'), $matches))
            {
                array_shift($matches);
                list($birthday['year']) = $matches;
            }
        }

        // http://stackoverflow.com/questions/2821061/facebook-api-how-do-i-get-a-facebook-users-profile-image-through-the-facebook
        $photo = 'https://graph.facebook.com/v2.8/'.Arr::get($user, 'id').'/picture?type=large';

        return (new Socialite_OAuth_Two_User())
            ->set_raw($user)
            ->map([
                'id' => Arr::get($user, 'id'),
                'nickname' => NULL,
                'first_name' => Arr::get($user, 'first_name'),
                'last_name' => Arr::get($user, 'last_name'),
                'surname' => Arr::get($user, 'middle_name'),
                'birthday' => $birthday,
                'gender' => $gender,
                'photo' => $photo,
                'email' => Arr::get($user, 'email'),
                'phone' => NULL,
                'website' => Arr::get($user, 'website'),
                'profile' => Arr::get($user, 'link'),
        ]);
    }
}