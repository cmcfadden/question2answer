describe command('/opt/rh/httpd24/root/usr/sbin/httpd -V') do
  its('stdout') { should match /^Server version: Apache\/2\.4.*$/ }
end

describe service('httpd24-httpd') do
  it { should be_enabled }
  it { should be_installed }
  it { should be_running }
end

describe command('curl http://testvhost/') do
  its('stdout') { should eq 'Successful vhost test' }
end

describe command('curl -I http://testvhost-ssl/') do
  its('stdout') { should match 'HTTP\/1.1 301 Moved Permanently' }
end

describe command('curl --insecure https://testvhost-ssl/') do
  its('stdout') { should eq 'Successful vhost test' }
end
