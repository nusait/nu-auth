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

## Advanced  
Sometimes you want to have a more flexible way of creating your user.  Maybe you want to include the user's emplid when you save it to the database.
You can set the "userCreator" config property as a callback as such

```php

[...

'userCreator' => function ($user, $metadata, $ldap) {
    // $user is the laravel model.
    // $metadata is the raw metadata from ldap.
    // $ldap is the \Nusait\NuLdap\Ldap object.
    $user->first_name = $metadata['given_name'][0];
    $user->last_name = $metadata['sn'][0];
    $user->emplid = $metadata['uid'][0];
    $user->save();
    
    //remember to return the $user
    return $user;
};

]
```
