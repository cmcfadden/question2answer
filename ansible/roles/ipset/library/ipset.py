#!/usr/bin/python

DOCUMENTATION = '''
---
module: ipset
author: "Andrew Zenk (@azenk)"
short_description: Manages ipsets
requirements: [ ipset ]
description:
    - Manage ipsets on a host
options:
    name:
        required: true
        description: 
            - The name of the ipset to manage
    set_type:
        required: false
        default: "hash:net"
        description:
            - The type of the ipset.  Required for state: present.
    state:
        required: false
        default: "present"
        choices: [ present, absent ]
        description:
            - Whether the set should exist or not on the host.
    members:
        required: false
        default: []
        description:
            - A list of set members
'''

import re

class IPSetException(RuntimeError):
    
    pass


class IPSet(object):

    def __init__(self, module):
        self.module = module
        self.ipset_bin_path = self.module.get_bin_path('ipset')

    def execute_command(self, cmd):
        return self.module.run_command(cmd)

    def create(self, ipset):
        cmd = [self.ipset_bin_path, 'create', ipset.name, ipset.set_type]
        rc, out, err = self.execute_command(cmd)
        if rc != 0:
            raise IPSetException(' '.join(cmd) + ": " + out + err)
        self.add(ipset, ipset.members)

    def load(self, name):
        cmd = [self.ipset_bin_path, 'list', name]
        rc, out, err = self.execute_command(cmd)
        if rc != 0:
            return None

        return IPSetInstance.parse_listing(out)

    def update(self, ipset, new_members):
        ipset = self.load(ipset.name)
        missing_members = set(new_members) - ipset.members
        surplus_members = ipset.members - set(new_members)
        self.add(ipset, missing_members)
        self.delete(ipset, surplus_members)

        return len(missing_members) != 0 or len(surplus_members) != 0

    def add(self, ipset, members):
        for entry in members:
            cmd = [self.ipset_bin_path, 'add', ipset.name, entry, '-exist']
            rc, out, err = self.execute_command(cmd)
            if rc != 0:
                raise IPSetException(' '.join(cmd) + ": " + out + err)

    def delete(self, ipset, members):
        for entry in members:
            cmd = [self.ipset_bin_path, 'del', ipset.name, entry, '-exist']
            rc, out, err = self.execute_command(cmd)
            if rc != 0:
                raise IPSetException(' '.join(cmd) + ": " + out + err)

    def destroy(self, ipset):
        cmd = [self.ipset_bin_path, 'destroy', ipset.name]
        return self.execute_command(cmd)

        
class IPSetInstance(object):

    equality_attributes = ['name', 'members']

    def __init__(self, name, **kwargs):
        self.name = name
        self.set_type = kwargs.get('set_type')
        self.revision = kwargs.get('revision')
        self.size = kwargs.get('size')
        self.counters = kwargs.get('counters')
        self.references = kwargs.get('references')
        self.members = set(kwargs.get('members'))
        
    @staticmethod
    def parse_member(member):
        return member

    @classmethod
    def parse_members(cls, members):
        return set(filter(lambda s: s != '', map(cls.parse_member, members.strip().split('\n'))))

    @staticmethod
    def parse_listing(listing):
        data = dict()
        regex="Name: (?P<name>.*)\n"
        regex+="Type: (?P<set_type>.*)\n"
        regex+="Revision: (?P<revision>.*)\n"
        regex+="Header: (?P<header>.*)\n"
        regex+="Size in memory: (?P<size>.*)\n"
        regex+="References: (?P<references>.*)\n"
        regex+="Members:\W*\n(?P<members>.*)"
        
        m = re.match(regex, listing, re.MULTILINE | re.DOTALL)
        data = m.groupdict()
        data['revision'] = int(data['revision'])
        data['size'] = int(data['size'])
        data['references'] = int(data['references'])
        data['members'] = IPSetInstance.parse_members(data['members'])
        return IPSetInstance(**data)

    def __eq__(self, other):
        return isinstance(other, self.__class__) and all(map(lambda a: hasattr(other, a) and hasattr(self, a) and getattr(other, a) == getattr(self, a), self.equality_attributes))

    def __ne__(self, other):
        return not self.__eq__(other)
        

def main():
    module = AnsibleModule(
        argument_spec = dict(
            state=dict(default='present', choices=['present', 'absent'], type='str'),
            name=dict(required=True, type='str'),
            set_type=dict(type='str', default='hash:net'),
            members=dict(required=False, type='list', default=[]),
        ),
        supports_check_mode=True
    )

    state = module.params['state']

    ipset = IPSet(module)

    module.debug('IPSet binary wrapper created')

    result = {}
    result['name'] = module.params['name']
    result['state'] = module.params['state']

    existing_ipset = ipset.load(module.params['name'])
    new_ipset = IPSetInstance(module.params['name'], set_type=module.params['set_type'], members=module.params['members'])

    changed = not (new_ipset == existing_ipset or (state == 'absent' and existing_ipset is None))
    result['changed'] = changed

    if not changed or module.check_mode:
        module.exit_json(**result)

    try:
        if state == 'absent' and existing_ipset is not None:
            ipset.destroy(existing_ipset)

        elif state == 'present' and existing_ipset is None:
            ipset.create(new_ipset)

        elif state == 'present':
            ipset.update(existing_ipset, new_ipset.members)
    except Exception as e:
        module.fail_json(name=new_ipset.name, msg=e.message)

    module.exit_json(**result)

# import module snippets
from ansible.module_utils.basic import *

if __name__ == '__main__':
    main()
