#!/usr/bin/env bash

# add patches
composer install --ansi

# but skip dev dependencies
composer update --no-dev --ansi

# downgrade with rector
rm -rf rector-local
mkdir rector-local
composer require rector/rector --working-dir rector-local --no-interaction
rector-local/vendor/bin/rector process bin src vendor --config build/rector-downgrade-php-72.php --ansi

# prefix
sh prefix-code.sh
