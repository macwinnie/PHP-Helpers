# RegEx Functions

This small repo is used to maintain my regularly reused PHP functions, e.g. RegEx functions.

## Usage

They can be used within any code for example like that:

```php

use \macwinnie\PHPHelpers as rf;

var_dump( rf\format2regex( 'uid=%s,ou=people,dc=example,dc=com' ) );
// string(43) "/uid\=(.*?),ou\=people,dc\=example,dc\=com/"

```

## Installation by Composer

This repo is meant to be included within other projects, so simply run this command to require it:

```sh
composer require macwinnie/phphelpers
```

Since the tool is also tested, please ensure to run `composer install --no-dev` in production stages, so no unnecessary tools are installed and autoloaded within your production environment!

## Testing

All functionalities are developed along the BDD (behaviour driven development) principles. Therefor, [Behat](https://docs.behat.org) is used to write Gherkin test scenarios and test them – all that takes part within the `/tests` folder.

To run the tests, you'll need the full composer installation with all dependencies, the production and development ones. For example use `devopsansiblede/apache:latest` to run everything that follows:

```sh
docker pull devopsansiblede/apache
docker run -p80:80 -d --name phphelpers -v $(pwd):/var/www/html devopsansiblede/apache
docker exec -it -u www-data phphelpers composer install
docker exec -it -u www-data phphelpers vendor/bin/behat
```

## Documentation

[Documentation current master state](https://macwinnie.github.io/PHP-Helpers/files/src-functions.html)

The functions within this repository are documented with DocBlock style. To visualize the documentation, the project is using [phpDocumentor](https://phpdoc.org/) to generate a viewable website with the documentation within the directory `/docs`.

To create the latest documentation, simply run the following Docker command:

```sh
docker pull phpdoc/phpdoc:3
rm -rf docs
docker run --rm -v $(pwd):/data phpdoc/phpdoc:3 --setting=graphs.enabled=true -d src -t docs
```

*ATTENTION:* The phpDocumentor tag `latest` from Docker is somehow a very old one – one wants to use a version tag like the `:3` above.

## Licence

[CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.en)
