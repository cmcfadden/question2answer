describe group('swadm') do
  it { should exist }
end

describe user('swadm') do
  it { should exist }
  its('group') { should eq 'swadm' }
end

[
  '/swadm',
  '/swadm/bin',
  '/swadm/etc',
  '/swadm/opt',
  '/swadm/var',
  '/swadm/var/log',
  '/swadm/var/run',
  '/swadm/src',
  '/swadm/tmp'
].each do |directory|
  describe file(directory) do
    it { should be_directory }
    it { should be_owned_by 'swadm' }
    it { should be_grouped_into 'swadm' }
  end
end

describe file('/etc/profile.d') do
  it { should be_directory }
  it { should be_owned_by 'root' }
  it { should be_grouped_into 'root' }
end

describe file('/etc/ld.so.conf.d') do
  it { should be_directory }
  it { should be_owned_by 'root' }
  it { should be_grouped_into 'root' }
end

describe command('echo -n $PATH') do
  its('stdout') { should include('/swadm/bin') }
end

describe command('echo -n $EDITOR') do
  its('stdout') { should eq 'vim' }
end

describe command('ldconfig -p') do
  its('stdout') { should include('umn_ansible_test') }
end
