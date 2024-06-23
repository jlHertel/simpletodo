## Simple TODO app

### Running locally
To run the application locally you just need Docker.
Once you have docker installed and running:
```sh
make run-app
```

Optionally, if you don't have Make:
```sh
docker compose up prod
```

The application will be built and a container will start with the app on port 8080.
Please note that the application is using an embedded SQLite database and is running the built-in
PHP webserver, thus this is not recommended for PRODUCTION deployments.

### Endpoint documentation
The application has 5 different endpoints. The APIs are documented using OpenAPI.
You can see the schema [here](./openapi.yml).
If you are using a JetBrains IDE you probably already have a plugin to display the schema.
If that is not the case, you can copy&paste the schema in any online editor like this: https://editor.swagger.io/

Note: After editting anything in the application you might need to update the schema.
For this you can use the command:
```sh
vendor/bin/openapi src -o openapi.yml
```

### Running unit tests
If you want to run the unit tests you will need:

- Make (Optional)
- PHP 8.3+
- Composer

Once you have those setup you can simply run:
```sh
make unit-test
```

Optionally, if you don't have Make, you can run:
```sh
composer install
bin/phpunit
```

[1]: https://www.jetbrains.com/help/idea/http-client-in-product-code-editor.html