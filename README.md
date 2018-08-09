Installation Guide

-You first need to install a wamp server, It can be 64 or 32.
This application use php 7.0.29
-During the installation the installer will give you the link of multiple vc files to download

-Install composer or you computer

-Install nodejs

-install Yarn

-clone this projec to the www directory of your wamp

run:

composer install
(make a git status, if web/ckeditor had been deleted then checkout them)

yarn install

yarn run encore dev

php bin/console doctrine:database:create

php bin/console doctrine:schema:update --force

php bin/console doctrine:fixtures:load --append

You are ready to go