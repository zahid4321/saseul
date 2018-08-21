# saseuld

## Install below

### httpd24

    $ yum install -y httpd24*

### php71

    $ yum install -y php71*

### memacached

    $ yum install -y memcached

### git

    $ yum install -y git

### openssl-devel

    $ yum install -y openssl-*

### php-ed25519-ext

    $ git clone git://github.com/encedo/php-ed25519-ext.git
    $ cd php-ed25519-ext
    $ phpize
    $ ./configure
    $ make
    $ sudo make install

### mongodb

Refer to [MongoDB homepage](https://www.mongodb.com/).

### php-mongodb-driver

Refer to [MongoDB homepage](https://www.mongodb.com/).

## Make service

    $ ln -s `YourSaseulSourceRoot`/saseul/saseuld/bin/saseuld.service /etc/init.d/saseuld

## Service Start

Need API Server.

Please modify `saseuld/src/System/Config.php`.

    $ curl 'http://localhost/reqeust/genesis'
    $ sudo service saseuld start
    $ tail -f `YourSaseulSourceRoot`/saseul/saseuld/logs/saseuld.log

## Etc

Some test code needs modification.

Development is still in progress.
