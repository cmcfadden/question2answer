describe group('root') do
  it { should exist }
end

describe user('root') do
  it { should exist }
  its('group') { should eq 'root' }
end

describe group('swadm') do
  it { should_not exist }
end

describe user('swadm') do
  it { should_not exist }
end

[
  '/',
  '/bin',
  '/etc',
  '/opt',
  '/var',
  '/var/log',
  '/var/run',
  '/src',
  '/tmp'
].each do |directory|
  describe file(directory) do
    it { should be_directory }
    it { should be_owned_by 'root' }
    it { should be_grouped_into 'root' }
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

describe file('/swadm') do
  it { should_not exist }
end

describe command('echo -n $PATH') do
  its('stdout') { should_not include('/swadm/bin') }
end

describe command('echo -n $EDITOR') do
  its('stdout') { should eq 'vim' }
end

describe command('ldconfig -p') do
  its('stdout') { should include('umn_ansible_test') }
end
