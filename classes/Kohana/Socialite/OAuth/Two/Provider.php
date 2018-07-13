<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Socialite_OAuth_Two_Provider extends Kohana_Socialite_OAuth_Two implements Kohana_Socialite_Provider
{
    /**
     * The HTTP request instance.
     *
     * @var Request
     */
    protected $_request;

    /**
     * The Session instance.
     *
     * @var Session
     */
    protected $_session;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $_client_id;

    /**
     * The client secret key.
     *
     * @var string
     */
    protected $_client_secret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $_redirect_url;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $_scopes = array();

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $_scope_separator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $_encoding_type = PHP_QUERY_RFC1738;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $_stateless = FALSE;

    /**
     * Constructor.
     *
     * @param Request $request
     * @param Session $session
     * @param string $client_id
     * @param string $client_secret
     * @param string $scopes
     * @param string $redirect_url
     */
    public function __construct(Request $request, Session $session, $client_id, $client_secret, $scopes, $redirect_url)
    {
        $this->_request = $request;
        $this->_session = $session;
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;
        $this->_scopes = array_merge($this->_scopes, $scopes);
        $this->_redirect_url = $redirect_url;
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     * @return string
     */
    abstract protected function get_auth_url($state);

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function get_token_url();

    /**
     * Get the raw user for the given access token.
     *
     * @param  array  $access_token
     * @return array
     */
    abstract protected function get_user_by_token(array $access_token);

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return Socialite_OAuth_Two_User
     */
    abstract protected function map_user_to_object(array $user);

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return string|void
     * @throws HTTP_Exception
     */
    public function redirect()
    {
        $state = NULL;

        if ($this->uses_state())
        {
            $this->_session->set('state', $state = $this->get_state());
        }

        HTTP::redirect($this->get_auth_url($state));
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $url
     * @param  string  $state
     * @return string
     */
    protected function build_auth_url_from_base($url, $state)
    {
        return $url.'?'.http_build_query($this->get_code_fields($state), '', '&', $this->_encoding_type);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string  $state
     * @return array
     */
    protected function get_code_fields($state)
    {
        $fields = array(
            'client_id' => $this->_client_id,
            'redirect_uri' => $this->_redirect_url,
            'scope' => $this->format_scopes($this->get_scopes(), $this->_scope_separator),
            'response_type' => 'code',
        );

        if ($this->uses_state())
        {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->_parameters);
    }

    /**
     * Format the given scopes.
     *
     * @param  array  $scopes
     * @param  string  $scope_separator
     * @return string
     */
    protected function format_scopes(array $scopes = array(), $scope_separator = ',')
    {
        return implode($scope_separator, $scopes);
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @throws Socialite_Exception
     * @return Socialite_OAuth_Two_User
     */
    public function user()
    {
        if ($this->has_invalid_state())
        {
            throw new Socialite_Exception('Invalid request state detected: :state', [
                ':state' => $this->_request->query('state')
            ]);
        }

        $access_token = $this->get_access_token_response($this->get_code());

        $user = $this->map_user_to_object($this->get_user_by_token($access_token));

        return $user->set_token($access_token)
            ->set_refresh_token(Arr::get($access_token, 'refresh_token'))
            ->set_expires_in(Arr::get($access_token, 'expires_in'));
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  array  $access_token
     * @return Socialite_OAuth_Two_User
     */
    public function get_user_from_token(array $access_token)
    {
        $user = $this->map_user_to_object($this->get_user_by_token($access_token));

        return $user->set_token($access_token);
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function has_invalid_state()
    {
        if ($this->is_stateless())
        {
            return FALSE;
        }

        $state = $this->_session->get('state');

        return ! (strlen($state) > 0 AND $this->_request->query('state') === $state);
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
                ':message' => $data['error_description'],
            ));
        }

        return $data;
    }

    /**
     * Get the fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function get_token_fields($code)
    {
        return array(
            'client_id' => $this->_client_id,
            'client_secret' => $this->_client_secret,
            'code' => $code,
            'redirect_uri' => $this->_redirect_url,
        );
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function get_code()
    {
        return $this->_request->query('code');
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param  array  $scopes
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->_scopes = array_unique(array_merge($this->_scopes, $scopes));

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function get_scopes()
    {
        return $this->_scopes;
    }

    /**
     * Set the redirect URL.
     *
     * @param  string  $url
     * @return $this
     */
    public function redirect_url($url)
    {
        $this->_redirect_url = $url;

        return $this;
    }

    /**
     * Set the session instance.
     *
     * @param  Session  $session
     * @return $this
     */
    public function set_session(Session $session)
    {
        $this->_session = $session;

        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function set_request(Request $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function uses_state()
    {
        return ! $this->_stateless;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function is_stateless()
    {
        return $this->_stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->_stateless = TRUE;

        return $this;
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function get_state()
    {
        return Text::random('alnum', 40);
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param  array  $parameters
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->_parameters = $parameters;

        return $this;
    }
}