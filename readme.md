# NuAuth Package

This is a package created for Authentication at NUSAIT.
(Other people can use it, but it is pretty specific for our own use)

## Install
1. run `composer require nusait/nu-auth`
2. add in `app` configuration file's service providers: `Nusait\NuAuth\NuAuthServiceProvider::class`
3. you can run `php artisan vendor:publish`.  You should see two files being copied to your config directory: `nuauth.php` and `ldap.php`.  Take a look at `ldap.php` to see what you need to put in your .env file.
4. change `auth` configuration's driver to "nuauth"

## To Use
1. just run "Auth::attempt" like normal. :D Happy Time
