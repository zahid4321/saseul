Install below
---

### httpd24 
> $ yum install -y httpd24*
---

### php71 
> $ yum install -y php71*
---

### memacached
> $ yum install -y memcached
---

### git
> $ yum install -y git
---

### openssl-devel
> $ yum install -y openssl-*
---

### php-ed25519-ext
- $ git clone git://github.com/encedo/php-ed25519-ext.git
- $ cd php-ed25519-ext
- $ phpize <br>
- $ ./configure
- $ make 
- $ sudo make install 
---

### mongodb
> Refer to mongodb h.p.
---

### php-mongodb-driver
> Refer to mongodb h.p.
---

### make service
> $ ln -s `YourSaseulSourceRoot`/saseul/saseuld/bin/saseuld.service /etc/init.d/saseuld

### service start
> Need API Server
- Please modify saseuld/src/System/Config.php 
- $ curl 'http://localhost/reqeust/genesis';
- $ sudo service saseuld start
- $ tail -f `YourSaseulSourceRoot`/saseul/saseuld/logs/saseuld.log

## etc
- Some test code needs modification.

Development is still in progress.

