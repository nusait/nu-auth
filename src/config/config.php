<?php
return [
	'autoCreate' => false,
	'netidColumn' => 'netid',
	'firstNameColumn' => 'first_name',
	'lastNameColumn' => 'last_name',
	'emailColumn' => 'email',
	/**
	 * user creator callback
	 * @description This is useful if you want to define how to create your users.  You might want add columns that's
	 *   not in the default user.  You have the raw $metadata, and the ldap object at your disposal.
	 * @example function ($user, $metadata, \Nusait\Nuldap\NuLdap $ldap) {
	 *   $user->first_name = $metadata['givenname'][0];
	 *   $user->emplid = $metadata['sn'][0];
	 *   ...
	 *   $user->save();
	 *   return $user;
	 * }
	 */
	'userCreator' => null,

];