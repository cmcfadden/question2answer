ipset Firewall
=========

This role allows you to open up ports in the firewall using ipset.

Requirements
------------

None

Role Variables
--------------
See `defaults/main.yml` for the list of variables.


Dependencies
------------

See `meta/main.yml`

Example Playbook
----------------

    - hosts: servers
      vars:
        ipset_rule_names:
          - "http_allowed"
          - "https_allowed"
          - "8443_allowed"
      roles:
         - { role: ipset }

Testing
-------

This role includes tests that can be executed using kitchen.

Prerequisites:
* ChefDK
* Vagrant
* Virtualbox
* chef gem install kitchen-vagrant kitchen-ansible

Running tests:
```
kitchen test
```

Author Information
------------------

Dan Keller, Andrew Zenk, Debbie Gillespie
