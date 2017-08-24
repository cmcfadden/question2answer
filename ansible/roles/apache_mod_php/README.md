apache_mod_php
=========

Copies in the default php.conf file so that PHP will be enabled on your server.

Requirements
------------

None

Role Variables
--------------

See `defaults/main.yml`

Dependencies
------------

See `meta/main.yml`

Example Playbook
----------------

```yaml
  - hosts: servers
    roles:
      - apache_mod_php
```


Author Information
------------------

Ian Whitney, Debbie Gillespie and Davin Lageroos @ ASR
