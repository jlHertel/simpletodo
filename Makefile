run-app:
	docker compose up prod

unit-test: setup-app
	bin/phpunit

setup-app:
	php composer.phar install