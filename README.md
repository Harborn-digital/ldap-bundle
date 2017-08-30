# LDAP bundle
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]

The bundle extends LDAP authentication of the Symfony LDAP component with the ability to automatically create / fetch users from eg. a database. This enables you to (easily) add LDAP authentication to existing authentication bundles.

## Installation using Composer
Run the following command to add the package to the composer.json of your project:

``` bash
$ composer require connectholland/ldap-bundle
```

### Enable the bundle
Enable the bundle in the kernel:

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ConnectHolland\LdapBundle\ConnectHollandLdapBundle(),
        // ...
    );
}
```

## Configuring the bundle
The bundle requires the following configuration to function in your `security.yml` file:

``` yaml
# app/config/security.yml
security:
    # ...

    providers:
        my_ldap:
            connect_holland_ldap: # Configuration part of this bundle.
                connection:
                    host: ldap.example.com
                user_factory:
                    type: doctrine # Currently 2 types available (doctrine and sulu). Custom user factories can be defined through a 'service' key with the id of the service.
                    user_class: 'AppBundle\Entity\User'
                    username_column: username
                    user_property_map: # Mapping of LDAP attributes (keys) to user properties (values).
                        uid: username
                        givenname: firstname
                        sn: lastname
                        mail: email
                base_dn: ou=users,dc=example,dc=com
                search_dn: ~
                search_password: ~
                default_roles:
                    - ROLE_ADMIN
                uid_key: uid

    firewalls:
        somename:
            # ...
            form_login_ldap:
                provider: my_ldap
                service: security.user.provider.concrete.my_ldap.client # Service created by the bundle.
                dn_string: 'uid={username},ou=users,dc=example,dc=com'
```

For more information and a detailed description of the various options, see [Authenticating against an LDAP server](https://symfony.com/doc/2.8/security/ldap.html) within the Symfony documentation.

### Configuring the bundle for Sulu CMS
To add LDAP authentication to Sulu CMS add the following configuration to `app/config/admin/security.yml`:
``` yaml
# app/config/admin/security.yml

security:
    # ...
    
    ldap:
        connect_holland_ldap: # Configuration part of this bundle.
            connection:
                host: ldap.example.com
            user_factory:
                type: sulu 
                user_property_map: # Mapping of LDAP attributes (keys) to user properties (values).
                    uid: username
                    givenname: contact.first_name
                    sn: contact.last_name
                    mail: email
            base_dn: ou=users,dc=example,dc=com
            search_dn: ~
            search_password: ~
            default_roles:
                - User # The name of the role within your Sulu CMS.
            uid_key: uid

    firewalls:
        admin:
            # ...

            form_login_ldap:
                login_path: sulu_admin.login
                check_path: sulu_admin.login_check
                success_handler: sulu_security.authentication_handler
                failure_handler: sulu_security.authentication_handler
                csrf_provider: security.csrf.token_manager
                provider: ldap
                service: security.user.provider.concrete.ldap.client
                dn_string: 'uid={username},ou=users,dc=example,dc=com'
```

Adjust the settings within `custom_user_ldap` according to your LDAP configuration.

## Configuration reference
``` yaml
connect_holland_ldap:
    connection:
        host: ldap.example.com
        port: 389
        encryption: ssl # tls or ssl
        options:
            protocol_version: 3
            referrals: false
    user_factory:
        type: sulu # doctrine or sulu
        service: ~ # Reference to your own user factory service.
        user_class: ~ # Fully qualified class name of your user entity. Only used for doctrine user factory type.
        username_column: ~ # Name of the username column. Only used for doctrine user factory type.
        user_property_map: # Mapping of LDAP attributes (keys) to user properties (values).
            uid: username
            givenname: firstname
            sn: lastname
            mail: email
    base_dn: ~
    search_dn: ~
    search_password: ~
    default_roles:
        - ~
    uid_key: sAMAccountName
    filter: '({uid_key}={username})'
```

[ico-build]: https://travis-ci.org/ConnectHolland/ldap-bundle.svg
[ico-coverage]: https://coveralls.io/repos/github/ConnectHolland/ldap-bundle/badge.svg

[link-build]: https://travis-ci.org/ConnectHolland/ldap-bundle
[link-coverage]: https://coveralls.io/github/ConnectHolland/ldap-bundle
