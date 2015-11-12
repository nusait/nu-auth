<?php
// The public NU ldap server is: directory.northwestern.edu with port 389
// The private NU ldap server is: ldaps://registry.northwestern.edu/ with port 636

return [
    "rdn"       => env('ldap_rdn', null),
    "password"  => env('ldap_pass', null),
    "host"      => env('ldap_host', 'directory.northwestern.edu'),
    "port"      => env('ldap_port', 389)
];