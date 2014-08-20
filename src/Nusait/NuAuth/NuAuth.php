<?php namespace Nusait\NuAuth;
/**
 * NU Authentication Driver
 * @author Chris Walker
 * @author  Hao Luo <howlowck@gmail.com>
 */
use Illuminate\Hashing\HasherInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\UserProviderInterface;
use Nusait\Nuldap\NuLdap;
use Config;


class NuAuth extends EloquentUserProvider implements UserProviderInterface {
    protected $model;
    protected $hasher;
    protected $netidKey;
    protected $autoCreate;
    public function __construct(HasherInterface $hasher, $model, $config)
    {
        $this->model = $model;
        $this->hasher = $hasher;
        $this->autoCreate = $config['autoCreate'];
        $this->netidKey = $config['netidColumn'];
        $this->firstNameKey = $config['firstNameColumn'];
        $this->lastNameKey = $config['lastNameColumn'];
        $this->emailKey = $config['emailColumn'];
    }
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
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


            $firstNameKey = $this->firstNameColumn;
            $lastNameKey = $this->lastNameColumn;
            $emailKey = $this->emailColumn;

            $info = $this->retrieveLdapUserInfo($idValue);
            $user = $this->createModel();
            $user->$firstNameKey = $info['first_name'];
            $user->$lastNameKey = $info['last_name'];
            $user->$idKey = $idValue;
            $user->$emailKey = $info['email'];
            $user->save();
            return $user;
        }
        return null;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }
    /**
     * Validate Ldap User with given Credientials with netid and password
     * @param  array $credentials
     * @return bool
     */
    protected function validateLdapCredentials(array $credentials)
    {
        $ldap = new NuLdap();
        $netid = $credentials[$this->netidKey];
        $password = $credentials['password'];
        return $ldap->validate($netid, $password);
    }
    /**
     * Get Info on Ldap netid
     * @param  \Illuminate\Auth\UserInterface $user
     * @return array
     */
    private function retrieveLdapUserInfo($netid)
    {
        $ldap = new NuLdap(Config::get('ldap.rdn'), Config::get('ldap.password'));
        $metadata = $ldap->searchNetid($netid);

        $result['first_name'] = $metadata['givenname'][0];
        $result['last_name'] = $metadata['sn'][0];
        $result['email'] = $metadata['mail'][0];

        return $result;
    }
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
       $plain = $credentials['password'];
       $authPassword = $user->getAuthPassword();

       $credentials[$this->netidKey] = strtolower($credentials[$this->netidKey]);
       if ( ! empty($authPassword)) return $this->hasher->check($plain, $authPassword);
       $result = $this->validateLdapCredentials($credentials);
       return $result;
    }
    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}