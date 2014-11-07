# SCAM (Scammers Can't Acquire Money)
secure bitcoin market built on php.

## Quickstart
### phpbrew (install php 5.4)
install phpbrew itself:

```bash
curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
chmod +x phpbrew
sudo mv phpbrew /usr/bin/phpbrew
```

install php 5.4.34 with minimal extensions (pdo, mysql and multibyte only required for now):

```bash
phpbrew install 5.4.34 +pdo +mb +mysql
```

### composer
install extensions needed for composer:

```bash
phpbrew ext install json
phpbrew ext install filter
phpbrew ext install hash
phpbrew ext install ctype
```

install composer

```bash
phpbrew install-composer
```

### application
clone repo from github (requires git):

```bash
git clone https://github.com/MatthiasWinzeler/scam.git
cd scam
```

install dependencies using composer

```bash
composer install
```

install mysql, then init database with the provided scripts:

```bash
for sql_file in app/install/*.sql; do mysql -uroot -p < $sql_file; done
```

run server:

```bash
php -S localhost:3000
```

Access it with your webbrowser pointing to http://localhost:3000

## Developer notes
To debug, install xdebug and configure it for your favorite IDE:

```bash
phpbrew ext install xdebug stable
```

<!---
Todo: Requirements (ie PHP, MySQL, ImageMagick, Bitcoin)
evtl: Notes for production (use Apache & PHP, update php -> how, ..., Bitcoinstuff)
-->