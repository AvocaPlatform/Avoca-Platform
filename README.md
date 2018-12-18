# Avoca Platform
A powerful PHP Platform for your business

## Install
* install composer
* composer install

## Run
#### Run from public/ folder
* Setup virtual host to public/ folder
* Go to application/config/avoca.php
* Change value to
`$config['public_folder'] = '';`

#### Run from root folder
* Go to application/config/avoca.php
* Change value to
`$config['public_folder'] = 'public';`

## Config
* Go to application/config/config.php
* Change
`$config['base_url'] = 'http://localhost/Avoca-Platform';`
* Create empty database
* Setting database in application/config/database.php
* Run: 
`http://localhost/Avoca-Platform/install` to  install

## Developer
* First controller: application/controllers
* View auto load from views/[theme]/templates/[controller]/[action].twig