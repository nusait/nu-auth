<?php namespace Nusait\NuAuth;
/**
 * NU Authentication Driver
 * @author Chris Walker
 * @author  Hao Luo <howlowck@gmail.com>
 */
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherInterface;
use Nusait\Nuldap\NuLdap;


class NuAuth extends EloquentUserProvider implements UserProvider {
    protected $model;
    protected $hasher;
    protected $netidKey;
    protected $autoCreate;
    protected $ldap;

    public function __construct(HasherInterface $hasher, $model, $config, $ldapConfig)
    {
        $this->model = $model;
        $this->hasher = $hasher;
        $this->autoCreate = $config['autoCreate'];
        $this->netidKey = $config['netidColumn'];
        $this->firstNameKey = $config['firstNameColumn'];
        $this->lastNameKey = $config['lastNameColumn'];
        $this->emailKey = $config['emailColumn'];
        $this->userCreator = isset($config['userCreator']) ? $config['userCreator'] : null;
        $this->ldapCred = $ldapConfig;


    }
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @param \Closure $userCreator
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();
        $idKey = $this->netidKey;

        $credientials[$idKey] = strtolower($credentials[$idKey]);
        foreach ($credentials as $key => $value)
        {
            if ( ! str_contains($key, 'password')) $query->where($key, $value);
        }

        if ($query->first()) return $query->first();
        if ( ! $this->autoCreate) return null;
        if ($this->validateLdapCredentials($credentials)) {
            $idValue = $credentials[$idKey];
            return $this->createNewUser($idValue);
        }
        return null;
    }

    protected function createNewUser($idValue) {

        $idKey = $this->netidKey;
        $firstNameKey = $this->firstNameKey;
        $lastNameKey = $this->lastNameKey;
        $emailKey = $this->emailKey;

        $userCreator = $this->userCreator;

        $info = $this->retrieveLdapUserInfo($idValue);
        $user = $this->createModel();

        if (is_callable($userCreator)) {
            $metadata = $this->retrieveLdapMetadata($idValue);
            $ldap = $this->getLdap();
            return $userCreator($user, $metadata, $ldap);
        }

        $user->$firstNameKey = $info['first_name'];
        $user->$lastNameKey = $info['last_name'];
        $user->$idKey = strtolower($idValue);
        $user->$emailKey = $info['email'];
        $user->save();
        return $user;
    }

    /**
     * Validate Ldap User with given Credientials with netid and password
     * @param  array $credentials
     * @return bool
     */
    protected function validateLdapCredentials(array $credentials)
    {

        $ldap = $this->getLdap();
        $netid = $credentials[$this->netidKey];
        $password = $credentials['password'];
        return $ldap->validate($netid, $password);
    }
    /**
     * Get Info on Ldap netid
     * @param  \Illuminate\Auth\UserInterface $user
     * @return array
     */
    protected function retrieveLdapUserInfo($netid)
    {
        $metadata = $this->retrieveLdapMetadata($netid);

        $result['first_name'] = $metadata['givenname'][0];
        $result['last_name'] = $metadata['sn'][0];
        $result['email'] = $metadata['mail'][0];

        return $result;
    }

    protected function retrieveLdapMetadata($value, $key = 'netid')
    {
        $ldap = $this->getLdap();
        return $ldap->search($key, $value);
    }

    protected function getLdap() {
        if (is_null($this->ldap)) {
            $rdn = $this->ldapCred['rdn'];
            $pass = $this->ldapCred['password'];
            $host = $this->ldapCred['host'];
            $port = $this->ldapCred['port'];
            $this->ldap = new NuLdap($rdn, $pass, $host, $port);
        }
        return $this->ldap;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials)
    {
       $plain = $credentials['password'];
       $authPassword = $user->getAuthPassword();

       $credentials[$this->netidKey] = strtolower($credentials[$this->netidKey]);
       if ( ! empty($authPassword)) return $this->hasher->check($plain, $authPassword);
       $result = $this->validateLdapCredentials($credentials);
       return $result;
    }
}