# Kohana Socialite
The Kohana Socialite module provides an expressive, fluent interface to OAuth authentication.

## Getting Started
1. Download the module into your modules subdirectory.
2. Enable the module in your bootstrap file:

  ```php
  /**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
// ...
    'socialite'       => MODPATH.'socialite',
));
  ```
3. Make sure the settings in `config/socialite.php` are correct for your environment. If not, copy the file to `application/config/socialite.php` and change the values accordingly.

## Usage
```php

class Controller_Auth extends Controller
{
    public function action_index()
    {
        $driver = '{%YOUR_DRIVER%}';
        $redirect_uri = Route::url('auth', array('action' => 'index',));
        
        $socialite = Socialite::factory()
            ->driver($driver)
            ->redirect_url($redirect_uri);
        
        try {
            $user = $socialite->user();
        } catch (Socialite_Exception $e) {
            throw new Kohana_Exception($e->getMessage());
        }
        
        // Retrieving User Details
        $id = $user->get_id();
        $nickname = $user->get_nickname();
        $first_name = $user->get_first_name();
        $surname = $user->get_surname();
        $birthday = $user->get_birthday();
        $gender = $user->get_gender();
        $photo = $user->get_photo();
        $email = $user->get_email();
        $phone = $user->get_phone();
        $website = $user->get_website();
        $profile = $user->get_profile();
    }
}

```

## Providers
...

## Acknowledgements
The author drew extensive inspiration from the [Laravel Socialite](https://github.com/laravel/socialite/) package and its related documentation.

## License
This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)