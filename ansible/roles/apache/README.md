Apache
=========
This role ensures that the httpd package is installed, creates the required apache directories in /swadm, copies in the default httpd.conf file, creates necessary symbolic links, and makes sure apache is enabled at boot. It also provides configuration for your vhosts and copies any needed keys and certificates to the server.

Related Roles
-------------
A functional apache webserver configuration will include several roles.  A brief summary of each roles responsibilities and functionality is included below.  Detailed information about each role is left to the readme files within each repository.

**apache (this role):**
* creates the global configuration for apache server.
* loads additional .conf files placed in apache_confd_dir and apache_vhostsd_dir

**ipset_firewall:**
* opens up ports on the firewall.  Without this role, ports 80 and 443 will be
* closed and you will be unable to access your application

Requirements
------------

Apache needs to be installed.

### SSL Certificates and Keys
If you need traffic to your site encrypted, you will need to provide an SSL certificate
and a private key. You will need to manually create these file. We recommend that you store
both your certificate and key in your repository, however, you should encrypt your
key before adding it to your repo. To encrypt your key, execute the following command:

```
openssl pkcs8 -topk8 -in [unencrypted_private_key_file_name] -out [encrypted_private_key_file_name]
```
The above command will create an encrypted key in a file using the name you provide in the `[encrypted_private_key_file_name]`. You will be prompted for a password. Ansible will need
this password to decrypt your key once it has copied it to the server. Use
Ansible vault or LastPass to set the `apache_ssl_certificate_key_passphrase` variable to the
password you used to generate the encrypted key.

Role Variables
--------------

Example configuration for two virtual hosts on the same server:
```
---
apache_vhosts:
  -
    server_name: fyparent-staging.ofyp.umn.edu
    document_root: /swadm/www/fyparent.ofyp.umn.edu/current/public
    maintenance_mode: true
    use_ssl: true
    ssl_certificate_cer: "{{ lookup('file', 'ssl/fyparent-staging_ofyp_umn_edu_cert.cer') }}"
    ssl_certificate_key: "{{ lookup('file', 'ssl/fyparent-staging_ofyp_umn_edu_cert.pem') }}"
  -
    server_name: fyparent2-staging.ofyp.umn.edu
    document_root: /swadm/www/fyparent2.ofyp.umn.edu/current/public
    maintenance_mode: true
    use_ssl: false
    vhost_directory_additions:
      - "{{ lookup('file', 'files/apache/CodeIgniterApacheRewrite.conf') }}"
      - "some additional configuration"
```

If you are not using the `apache_vhosts` variable, and need to enable the ssl module for use with a configuration provided by another role, you'll need to set
```
apache_enable_ssl: true
```

See `defaults/main.yml` for additional configuration options.

Dependencies
------------

env role

Example Playbook
----------------

See [playbook-directory-template](https://github.umn.edu/ansible-roles/playbook-directory-template).

Testing
-------

Requirements
```
virtualbox
vagrant
chefdk
chef gem install kitchen-ansible
chef gem install librarian-ansible
```

Running
```
kitchen test
```

Author Information
------------------

Andrew Zenk, Debbie Gillespie, David Naughton, Jonathon Walz, Matt Maloney
