# RegEx Functions

This small repo is used to maintain my regularly reused RegEx functions.

They can be used within any code for example like that:

```php

use \macwinnie\RegexFunctions as rf;

var_dump( rf\format2regex( 'uid=%s,ou=people,dc=example,dc=com' ) );
// string(43) "/uid\=(.*?),ou\=people,dc\=example,dc\=com/"

```

## Composer

Installable by running `composer require macwinnie/regexfunctions`.

## Licence

[CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.en)
