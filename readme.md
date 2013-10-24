# NuAuth Package

This is a package created for Authentication at NUSAIT.
(Other people can use it, but it is pretty specific for our own use)

## Install
1. in `composer.json` add: `"nusait/nu-auth", "dev-master"`
2. run `composer update`
3. add in `app` configuration file's service providers: `'Nusait\NuAuth\NuAuthServiceProvider'`
4. change `auth` configuration's driver to "nuauth"
5. (optional) you can run `php artisan config:publish nusait/nu-auth` then go to `app/config/packages/nusait/nu-auth/config.php` to see the config options available.

## To Use
1. just run "Auth::attempt" like normal. :D Happy Time

## Note:
- You would need ldap credential ready in the config folder