gaspSync
=========

# Introduction

---

the current version is NOT READY FOR PRODUCTION,
ONLY FOR TEST FOR NOW


A in progress php implementation of the mozilla 1.5 sync service.


The goal is to be able to install it on a Nas.

What works :
 - Sync storage 1.5 Server (tested with firefox for computer)
 - Token 1.5 Server

What doesn't works :
 - Fxa account (you need to use the Fxa official account for now)



# Install & Configuration
---

## PHP Extensions

extension :
Required  :
- gmp
- curl
in the future for Fxa part  :
- scrypt


## Server Configuration

- publish /web
You need to point the /web directory to the **root** of a web directory (with SSL).
virtual host can help. We are forced to respond to url like "/.well-known/browserid".

- sample apache virtualhost :

```
<VirtualHost *:4000>
ServerName exemple.com
DocumentRoot "/var/services/web/gaspSync/web"
SSLEngine on
</VirtualHost>
```



in command line launch :
- the **init** command to set somes vars
- **yii migrate** and type "yes" to create and empty database
- For the client configuraiton, open the root uri of gaspSync


## Client configuration

- in firefox go to the root uri of gaspSync
- add a permanent exception if the certificat is selfsigned


__Information :__
- !! Warning, when you **LOGOUT** from Fxa the tokenserverurl is reset, you need to restart firefox !!
- Each time you relaunch Firefox, the user.js is applied.

## Running tests

open 2 terminal :
- go in tests dir execute "./launchServer.sh"
- go in tests dir execute "codeception run"

## gaspSync hosted on the same computer

if the server is on the same computer you need to desactivate a security :
 - Desactive the rules **ABE locale**

```
SYSTEM rule:
Site LOCAL
Accept from LOCAL
The o
Deny
```

## Install Scrypt PHP module (not functionnal for now,write down  only for reference here)

Debian Turnkey :
```
apt-get install make php-pear php5-dev
pecl install scrypt
```
http://benjamin-balet.info/multimedia/synology/cross-compilation-pour-synology/
http://www.aeropassion.net/leblog/post/2013/12/Cross-compilation-pour-un-NAS-Synology



## Debugging

**How to turn on sync logs:**

Open [about:config] and turn this at  **true** :
  - services.sync.log.appender.file.logOnSuccess
  - services.sync.log.logger.engine.bookmarks

Restart Firefox and look at: [about:sync-log]


  - in firefox go to [about:sync-log] [2]
  - you now know the directory where all the "success" and "failure" sync are stored.

## Usefull Urls for FFSync

- Token Server (configurated via user.js, need an assertion):
   *https://IP:4000/tokenServer/1.0/sync/1.5*
- Signup page
   *https://IP:4000/signup?service=sync&context=fx_desktop_v1*
- Login page
   *https://IP:4000*



# Ressources & tools used
---

## Javascript

- __jQuery__ : http://jquery.org

## PHP

 - __Yii 2__ : http://www.yiiframwework.com
 - __HKDF php implementation__ : https://gist.github.com/narfbg/8793435
(Experimental HKDF implementation for CodeIgniter's encryption class)
 - __C scrypt module for PHPH__ : https://github.com/DomBlack/php-scrypt
 - __PHP Personna__ :  https://github.com/jyggen/persona
 - __create temporary personna account__ : http://personatestuser.org/
   - https://github.com/mozilla/personatestuser.org
   - http://personatestuser.org/email_with_assertion/https%3A%2F%2fjedp.gov

## Ressources & Documentations  :

#### Global
- description de l'architecture : : https://wiki.mozilla.org/Identity/Firefox_Accounts
- description du protocole : https://github.com/mozilla/fxa-auth-server/wiki/onepw-protocol :
- List des serveurs : https://developer.mozilla.org/en-US/Firefox_Accounts#Firefox_Accounts_deployments
- KeyServerProtocol et test vectors : https://wiki.mozilla.org/Identity/AttachedServices/KeyServerProtocol (slighlty deprecated)
 - test vectors tools : https://github.com/mozilla/fxa-python-client

#### Content Server

- Fxa Client Javascript* : https://github.com/mozilla/fxa-js-client

#### Fxa Auth Server
- https://github.com/mozilla/fxa-auth-server (all the important docs are here, **one pw**)
- https://developer.mozilla.org/en-US/Persona/Identity_Provider_Overview
- https://github.com/mozilla/browserid-verifier (local & remote Verifier)
  - https://github.com/mozilla/id-specs/blob/prod/browserid/index.md
  - https://developer.mozilla.org/fr/Persona/vue_densemble_du_protocole
  - https://login.persona.org/.well-known/browserid (get public key for a host)

#### Sync Server (implemented)

-services.mozilla.com/mozsvc/v1/node_secret/
-https://docs.services.mozilla.com/storage/apis-1.5.html
-SyncStorage API v1.5* pour dev le serveur sans gerer les authentification

##### Token Server (implemented)
 - https://wiki.mozilla.org/Services/Sagrada/TokenServer
 - http://docs.services.mozilla.com/token/apis.html
 - (Implemented)  https://docs.services.mozilla.com/token/index.html#tokenserver
 - (Implemented) https://docs.services.mozilla.com/token/user-flow.html*


 Each Service Node has a unique Master Secret that it shares with the Login Server,which is used to sign and validate authentication tokens. Multiple secrets can be active at any one time to support graceful rolling over to a new secret.

To simplify management of these secrets, the tokenserver maintains a single list of master secrets and derives a secret specific to each node using HKDF:

node-info = "services.mozilla.com/mozsvc/v1/node_secret/" + node-name
node-master-secret = HKDF(master-secret, salt=None, info=node-info, size=digest-length)

The node-specific Master Secret is used to derive keys for various cryptographic routines. At startup time, the Login Server and Node should pre-calculate and cache the signing key as follows:

sig-secret: HKDF(node-master-secret, salt=None, info="SIGNING", size=digest-length)

By using a no salt (or a fixed salt) these secrets can be calculated once and then used for each request.

When issuing or checking an Auth Token, the corresponding Token Secret is calculated as:

token-secret: b64encode(HKDF(node-master-secret, salt=token-salt, info=auth-token, size=digest-length))

Note that the token-secret is base64-encoded for ease of transmission back to the client.

https://developer.mozilla.org/fr/Persona/API_de_verification

## TurnKey

````
php5 gmp
php5 curl
samba :

apt-get update
apt-get install samba
apt-get install webmin-samba
apt-get install php5-curl
apt-get install php5-gmp
apt-get install php5-dev
apt-get install php5-sqlite
apt-get install php5-intl
apt-get install php5-gd

apt-get install make
apt-get update && apt-get install webmin
pecl install scrypt
````


Add to the php.ini :

````
extension=scrypt.so
extension=curl.so
extension=gmp.so
````

__configuration apache :__
- activer le mode rewrite
- reboot

__configure samba__



## Synology reference

Compilation de packages synology :
https://github.com/SynoCommunity/spksrc
 usefull command :

````
/usr/syno/sbin/synoservicecfg --list
/usr/syno/sbin/synoservicecfg --list-config
/usr/syno/sbin/synoservicecfg --show-config
/usr/syno/sbin/synoservicecfg --is-enabled httpd-user
/usr/syno/sbin/synoservicecfg --status
/usr/syno/sbin/synoservicecfg --restart httpd-user
/usr/syno/sbin/synoservicecfg --reload httpd-user
/usr/syno/sbin/synoservicecfg --is-all-up
````

## Copyright

When not explicitly set, files are placed under a [3 clause BSD license][3]


[1]:about:support
[2]:about:sync-log
[3]:http://www.opensource.org/licenses/BSD-3-Clause
