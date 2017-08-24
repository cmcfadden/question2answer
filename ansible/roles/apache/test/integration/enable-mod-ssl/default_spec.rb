describe service('httpd') do
  it { should be_enabled }
  it { should be_installed }
  it { should be_running }
end

describe command('httpd -t -D DUMP_MODULES') do
  its('stdout') { should match /\sssl_module\s/ }
end
