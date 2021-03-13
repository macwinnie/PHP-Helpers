# RegEx Functions

This small repo is used to maintain my regularly reused RegEx functions.

They can be used within any code for example like that:

```php

use \macwinnie\RegexFunctions as rf;

var_dump( rf\format2regex( 'uid=%s,ou=people,dc=example,dc=com' ) );
// string(43) "/uid\=(.*?),ou\=people,dc\=example,dc\=com/"

```
