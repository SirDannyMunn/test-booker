# Project info

- [Project Ideas](#project)
- [Installation](#installation)

[Robotjs](https://github.com/octalmage/robotjs) is a desktop automation package to control fake user actions **for node**.
[Robotjs-browser ](https://github.com/ml1nk/robotjs-browser) Browser version.

sudo apt-get install polipo
# configure polipo
# nano /etc/polipo/config
# restart polipo
# sudo /etc/init.d/polipo restart
# sudo npm -g install chromedriver --unsafe-perm=true --allow-root

# sudo apt-get install linuxbrew-wrapper
# brew update
# brew update
# brew doctor
# sudo mkdir -p /home/vagrant/.linuxbrew/include /home/vagrant/.linuxbrew/lib /home/vagrant/.linuxbrew/opt /home/vagrant/.linuxbrew/sbin /home/vagrant/.linuxbrew/var/homebrew/linked
# sudo chown -R $(whoami) /home/vagrant/.linuxbrew/include /home/vagrant/.linuxbrew/lib /home/vagrant/.linuxbrew/opt /home/vagrant/.linuxbrew/sbin /home/vagrant/.linuxbrew/var/homebrew/linked
# export PATH=$PATH:/home/vagrant/.linuxbrew/opt/go/libexec/bin
# brew install https://raw.githubusercontent.com/scrapinghub/crawlera-headless-proxy/master/crawlera-headless-proxy.rb
# export PATH=$PATH:/home/vagrant/.linuxbrew/Cellar/crawlera-headless-proxy/1.0.0/bin
# crawlera-headless-proxy --help

- crawlera-headless-proxy -c ~/Dev/laravel-web-crawler/app/Browser/proxy/config.toml

sudo ln -s /etc/polipo/config /home/vagrant/Dev/laravel-web-crawler/polipo

# Add cron task
# crontab -e
# * * * * * cd /home/vagrant/Dev/laravel-web-crawler && php artisan schedule:run >> /dev/null 2>&1

# for CA management
# sudo apt install libnss3-tools
# sudo certutil -d sql:$HOME/.pki/nssdb -A -n 'Crawlera CA' -i crawlera-ca.crt -t TCP,TCP,TCP
# (For server) sudo certutil -d sql:$HOME/.pki/nssdb -A -n 'Crawlera CA' -i crawlera-ca.crt -t P,P,P
# Check CA store
# certutil -L -d sql:${HOME}/.pki/nssdb
# >> Crawlera CA                                                  CT,C,C

- php artisan config:clear
- php artisan config:cache
- php artisan horizon
