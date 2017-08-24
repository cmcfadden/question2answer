# env

Performs common tasks and promotes best practices associated with provisioning OIT-hosted machines, in collaboration with OIT. Most, if not all, other UMN Ansible community roles depend on it.

env, like the community roles in general, is designed for provisioning jointly managed machines, a service OIT offers for RHEL 7 and above. For legacy reasons, we still support provisioning fully managed machines, but only on RHEL 6. We encourage any users of community roles still using fully managed machines to switch to jointly managed as soon as possible.

## Collaborating with OIT

Historically, OIT offered only fully managed or self managed machines.

OIT does no management of the software on self managed machines. Users can become root and have complete control. We designed the community roles to allow collaboration with OIT, so we have never directly supported the provisioning of self managed machines.

OIT does not allow users to become root on fully managed machines. Instead, they allow only a small set of privileged operations via `sudo` and a shared `swadm` account. Even with the power of Ansible, these restrictions make it difficult to automate provisioning of fully managed machines.

On the newer jointly managed machines, OIT allows users to become root, but still manages some of the software, like security patches to low-level systems packages. This has allowed for the best working relationship yet between OIT and the UMN Anisble roles community, which is why we are moving toward supporting only jointly managed machines.

On both jointly and fully managed machines, OIT provides a shared `swadm` account, a `swadm` group, and a `/swadm` directory for user-installed and -managed items. env ensures that all those things exist. Partly this is for legacy support of fully managed RHEL 6 machines. But even though we have root on jointly managed machines, sometimes we still want to re-home applications. We continue to use the `swadm` system for that, partly to build on a shared, historical understanding of `/swadm` as the place to put things to avoid users and OIT clobbering each other's changes.

To help support that re-homing, env creates some sub-directories of `/swadm`, and configures it to automatically grant privileges on all items in the directory tree to the `swadm` user and group. After running this role, the following directories should exist: 

```
  /swadm
  /swadm/bin
  /swadm/etc
  /swadm/etc/profile.d (only on fully managed)
  /swadm/opt
  /swadm/var
  /swadm/var/log
  /swadm/var/run
  /swadm/src
  /swadm/tmp
```

## Best Practices

### Environment Variables and Other Shell Configuration

We recommend setting environment variables and doing other shell configuration in separate, custom config files in a common location, like `/etc/profile.d/`. Most shells load the files in that directory on startup by default. See the `create custom scripts in profile.d` task in [tasks/main.yml](tasks/main.yml) for an example.

We recommend against modifying a single, common configuration file, like `.bashrc`, because that is more likely to lead to changes clobbering each other and other conflicts.

### Indexing Shared Libraries

Indexing shared libraries presents a special configuration case. RHEL and many other systems provide a directory similar to `/etc/profile.d/`, but especially for shared libraries, usually `/etc/ld.so.conf.d/`. Each file in that directory must be a list of directories that contain shared library files (usually with a `.so` extension on Linux). Running `ldconfig` will index all the shared library files in all those directories, so applications can find and load them at runtime.  

The procedure to create these custom config files is similar to what we described for the custom scripts above. The major differences are that the env role provides different default variables for them, `env_etc_ld_so_confd` and `env_ld_so_confd_prefix`, and a handler that calls `ldconfig`, called `index shared libraries`. Tasks that modify these config files should notify that handler. See the tests in [test/integration/index_shared_libaries.yml](test/integration/index_shared_libaries.yml) for an example.

We recommend against setting the `LD_LIBRARY_PATH` environment variable to do this configuration, because, again, it is more likely to lead to changes clobbering each other. However, on fully managed machines, OIT does not allow users to write to `/etc/ld.so.conf.d/` or to run `ldconfig`. So in those environments, set `LD_LIBRARY_PATH` in a custom script as described above. Be sure to do so non-destructively, by including any already existing value in your new value, e.g.

```
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/your/custom/path/
```

## Requirements

### Jointly Managed Connections and Privileges

When provisioning jointly managed hosts, connect and run Ansible tasks as your personal UMN internet ID, *not* as `swadm`. Your personal account should have the privilege to become root. If it does not, contact oialinux@umn.edu.

Also, be sure to [make OIT aware of your public keys](https://github.umn.edu/OIT-Infrastructure-Chef/chef-umn-shared-attributes/wiki/Adding-User-Keys-and-Collections), so they don't clobber them with their provisioning.

#### Local Tasks and `sudo`

To solve permission problems we encountered when running as personal accounts, we addded `ansible_become: true` to the default variables for this role, forcing all tasks to run as root. While this solved problems on jointly managed remote machines, it introduced new problems when running tasks on control machines (localhost). As of this writing, Ansible ignores `ansible_become=false` overrides on individual tasks, and instead runs them via `sudo`. To work around that, put all tasks to be run as a non-root user in a separate file, and include it with `ansible_become=false`:

```
include: lpass.yml ansible_become=false
```

### Fully Managed Connections and Privileges

As we mentioned above, OIT does not allow personal accounts to become root on fully managed hosts. If you must provision fully managed machines, you must set these variables to use this role:

```
ansible_become: false
ansible_user: swadm
```

## Role Variables

See `defaults/main.yml`.

## Dependencies

None.

## Example Playbook

See [playbook-directory-template](https://github.umn.edu/ansible-roles/playbook-directory-template).


## Author Information

Andrew Zenk, Travis Galloway, Debbie Gillespie, David Naughton, Kirk Madson, Jonathon Walz
