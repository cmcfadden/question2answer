control 'apache-version' do
    httpd_version = /^Server version: Apache\/2\.4/

    if os[:release] =~ /^6/ then
        httpd_version = /^Server version: Apache\/2\.2/
    end

    describe command('httpd -V') do
        its('stdout') { should match httpd_version }
    end

end

describe service('httpd') do
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
