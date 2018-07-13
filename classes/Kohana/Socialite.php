<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Socialite {

    /**
     * Config.
     *
     * @var array
     */
    protected $_config;

    /**
     * Factory.
     *
     * @param array $config
     * @return Socialite
     * @throws Kohana_Exception
     */
    public static function factory(array $config = array())
    {
        return new Socialite($config);
    }

    /**
     * Constructor.
     *
     * @param array $config
     * @throws Kohana_Exception
     */
    public function __construct(array $config = array())
    {
        $default_config = (array) Kohana::$config->load('socialite.default');

        $this->_config = array_merge($default_config, $config);
    }

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @throws Socialite_Exception
     * @return Socialite_OAuth_Two_Provider
     */
    public function driver($driver)
    {
        $driver = strtolower($driver);

        $request = Request::initial();
        $session = Session::instance();

        $client_id = $this->_config[$driver]['client_id'];
        $client_secret = $this->_config[$driver]['client_secret'];
        $scopes = $this->_config[$driver]['scopes'];
        $redirect_url = $this->_config[$driver]['redirect_url'];

        $provider = 'Socialite_OAuth_Two_Provider_'.ucfirst($driver);
        
        if ( ! class_exists($provider))
            throw new Socialite_Exception('Provider :provider is not exists!', array(':provider' => $provider,));

        return new $provider($request, $session, $client_id, $client_secret, $scopes, $redirect_url);
    }
}