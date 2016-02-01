composer:
	composer validate
	composer install

cs: composer
	vendor/bin/php-cs-fixer fix --config=.php_cs --verbose --diff

it: cs test

test: composer
	vendor/bin/phpunit --configuration phpunit.xml --columns max
