<?php

/**
 * This file is a helper file for helper functions within PHP.
 *
 * It is published unter CC BY-SA 4.0 license.
 *
 * Source: [macwinnie @ GitHub](https://github.com/macwinnie/PHP-Helpers/)
 *
 * [Documentation](https://macwinnie.github.io/PHP-Helpers/files/src-functions.html) can be found online.
 *
 * Installable by using [Composer](https://packagist.org/packages/macwinnie/phphelpers)
 */

/**
 * regex delimiter that can be used in combination with functions by this
 * helper file
 *
 * @var string
 */
const REGEX_DELIMITER = '/';

/**
 * function to translate format string to regex to retrieve entry values
 *
 * `sscanf` should be sufficient in most situations – but especially if the
 * format string doesn't contain spaces after conversion specifications, it
 * fails. Especially that's the case with LDAP DN definitions, which is why
 * this function was coded.
 *
 * Usage could be e.g.
 *
 * ```php
 * <?php
 *
 * $rGroups  = [
 *     'DN'  => [ 0 ],
 *     'uid' => [ 1 ],
 *     'dc1' => [ 2 ],
 *     'dc2' => [ 3 ],
 *     'dc3' => [ 4 ],
 * ];
 *
 * $dnFormat = 'uid=%s,ou=people,dc=%s,dc=%s,dc=%s';
 * $regex    = format2regex( $dnFormat, null, true );
 *
 * $mappings = getRegexOccurences( $regex, 'uid=jdoe,ou=people,dc=compartment,dc=example,dc=com', $rGroups );
 *
 * echo( json_encode( $mappings, JSON_PRETTY_PRINT ) );
 *
 * // [
 * //     {
 * //         "full": "uid=jdoe,ou=people,dc=compartment,dc=example,dc=com",
 * //         "length": 51,
 * //         "offset": 0,
 * //         "DN": "uid=jdoe,ou=people,dc=compartment,dc=example,dc=com",
 * //         "uid": "jdoe",
 * //         "dc1": "compartment",
 * //         "dc2": "example",
 * //         "dc3": "com"
 * //     }
 * // ]
 *
 * ```
 *
 * @param  string      $format    format string to be analyzed
 * @param  string|null $match     RegEx string to replace format parts – defaults to `(.*?)`
 * @param  boolean     $fullMatch set true for a full match
 *
 * @return string                 RegEx to be used further
 *
 * @todo   handling of '%%' within format string what equals single '%' within a string
 */
function format2regex ( $format, $match = null, $fullMatch = false ) {

    if ( $match === null ) {
        $match = '(.*?)';
    }

    // regex for matching format conversion specifications
    // https://regex101.com/r/FDGzbV/1/
    $regex = REGEX_DELIMITER . '%([0-9]+\$)?((-|\+| |0|\'.)+)?([0-9]+)?(\.[0-9]+)?([bcdeEfFgGosxX])' . REGEX_DELIMITER . 'x';

    /*
     * structure of matches – according to documentation of PHP function `sprintf`:
     *
     *        0  full match
     * IGNORE 1  placeholder
     *        2  Flags [+|-| |0|('.)]
     * IGNORE 3  last of Flags
     *        4  min width
     *        5  max with / # decimals
     *        6  identifier
     */

    $parts = preg_split( $regex, $format );
    $parts = array_map( 'delimiter_preg_quote', $parts );

    return REGEX_DELIMITER . ( $fullMatch ? '^' : '' ) . implode( $match, $parts ) . ( $fullMatch ? '$' : '' ) . REGEX_DELIMITER;
}

/**
 * helper function to quote regular delimiter / in RegEx String
 *
 * @param  string      $string    string to be quoted
 * @param  string|null $delimiter delimiter to be used for quoting – defaults to `null`
 *                                to use REGEX_DELIMITER defined by this helper file
 *
 * @return string                 quoted string
 */
function delimiter_preg_quote ( $string, $delimiter = null ) {
    if ( $delimiter === null ) {
        $delimiter = REGEX_DELIMITER;
    }
    return preg_quote( $string, $delimiter );
}

/**
 * function to fetch RegEx occurences from string / template
 *
 * @param  string  $template the template string
 *
 * @return array             fetch additional group elements and put it into key;
 *                           if value is `[ 'name' => [ 1,2,3 ] ]`, the tool will return
 *                           the first non-empty group out of 1, 2 or 3
 */
function getRegexOccurences ( $regex, $template, $group_attributes = null ) {
    preg_match_all( $regex, $template, $matches, PREG_OFFSET_CAPTURE );
    $returns = [];
    foreach ($matches[0] as $c => $set) {
        /** fetch occurences */
        foreach ($set as $i => $value) {
            switch ($i) {
                case 0:
                    $returns[$c] = [];
                    $returns[$c]['full'] = $value;
                    $returns[$c]['length'] = strlen($value);
                    break;
                case 1:
                    $returns[$c]['offset'] = $value;
                    break;
            }
        }
        /** fetch name if name group(s) is / are defined */
        if ( $group_attributes != null ) {
            foreach ($group_attributes as $attr => $groups) {
                if ( ! is_array( $groups ) ) {
                    $groups = [ $groups ];
                }
                $i = 0;
                do {
                    $returns[ $c ][ $attr ] = $matches[ $groups[ $i++ ] ][ $c ][ 0 ];
                } while (
                    $returns[ $c ][ $attr ] == '' and
                    $i < count($groups)
                );
            }
        }
    }
    return $returns;
}

/**
 * function to retrieve a specific value out of a given
 * (nested) array by dot-joined following keys.
 *
 * @param  array   $array         array to be searched
 * @param  string  $dotted_key    dot-joined key-tree to retrieve the value
 * @param  mixed   $default       default value if key-tree not found;
 *                                defaults to NULL
 * @param  boolean $returnKeyTree if set `True`, the return value is an array
 *                                with two values: `keyTree` and `value`.
 *                                `NULL` if default has to be returned .
 *
 * @return mixed
 */
function getArrayValue ( $array, $dotted_key, $default = NULL, $returnKeyTree = False ) {
    $returnDefault = False;

    $keytree = explode( '.', $dotted_key );
    $prepend = '';
    $ktfound = [];

    foreach ( $keytree as $k ) {
        $key = $prepend . $k;
        if ( is_array( $array ) and isset( $array[ $key ] ) ) {
            $array         = $array[ $key ];
            $ktfound[]     = $key;
            $prepend       = '';
            $returnDefault = False;
        }
        elseif ( is_array( $array ) ) {
            $prepend       = $key . '.';
            $returnDefault = True;
        }
        else {
            $returnDefault = True;
        }
    }

    if ( $returnDefault ) {
        $value   = $default;
        $ktfound = NULL;
    }
    else {
        $value = $array;
    }

    if ( $returnKeyTree ) {
        return [
            'keyTree' => $ktfound,
            'value'   => $value,
        ];
    }
    else {
        return $value;
    }
}

/**
 * extended function `getArrayValue`: will remove value (and key) from array
 * if found
 *
 * @param  array   $array         array to be searched
 * @param  string  $dotted_key    dot-joined key-tree to retrieve the value
 * @param  mixed   $default       default value if key-tree not found;
 *                                defaults to NULL
 *
 * @return mixed
 */
function extractArrayValue ( &$array, $dotted_key, $default = NULL ) {
    // get key tree and value
    $x = getArrayValue( $array, $dotted_key, $default, true );
    $keyTree = $x[ 'keyTree' ];
    $value   = $x[ 'value' ];
    $subvals = [];
    // remove found value and key from array
    if ( $keyTree != NULL and is_array( $keyTree ) ) {
        rmValueByKeyTree( $array, $keyTree );
    }
    // return value
    return $value;
}

/**
 * function to remove a value specified by the key-tree from an array
 *
 * @param  array  &$array   array, the value should be removed from
 * @param  array  $keytree  ordered list of array keys
 *
 * @return void
 */
function rmValueByKeyTree ( &$array, $keytree = [] ) {
    $key = array_shift( $keytree );
    if ( is_array( $keytree ) and ! empty( $keytree ) ) {
        rmValueByKeyTree( $array[ $key ], $keytree );
    }
    else {
        unset( $array[ $key ] );
    }
}

/**
 * function to generate a random string of a given length
 *
 * @param  integer $length    length, the string should have
 * @param  mixed   $alphabets default is `NULL`, so the random string will
 *                            consist out of `0-9a-f`; if of `String` type,
 *                            the random string will be generated out of all
 *                            characters of that string; if it is a List /
 *                            Array of strings, the randomized string will –
 *                            as length is at least as high as the count of
 *                            given alphabents – contain at least one char
 *                            out of all those alphabets.
 *
 * @return string             random string
 */
function randomString( $length = 16, $alphabets = NULL ) {
    if ( $alphabets == NULL ) {
        if ( function_exists('random_bytes') ) {
            $bytes = random_bytes( $length / 2 );
        }
        else {
            $bytes = openssl_random_pseudo_bytes( $length / 2 );
        }
        $result = bin2hex( $bytes );
    }
    else {
        if ( ! is_array( $alphabets ) ) {
            $alphabets = [ $alphabets ];
        }
        $counts = count( $alphabets );
        $result = '';
        $used   = [];
        while ( strlen( $result ) < $length ) {
            // fetch the alphabet
            do {
                $i = random_int( 0, $counts - 1 );
            } while ( in_array( $i, $used ) );
            $len = strlen( $alphabets[ $i ] );
            $j = random_int( 0, $len - 1 );
            $result .= $alphabets[ $i ][ $j ];
        }
    }
    return $result;
}

if ( ! function_exists( 'env' ) ) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    function env ( $key, $default = NULL ) {

        try {
            $value = $_ENV[ $key ];
        } catch ( \Exception $e ) {
            $value = NULL;
        }

        if ($value === NULL) {
            return $default;
        }
        $value = val2boolEmptyNull( $value );

        $regex = '/^(\s*["\'])?(.*?)(["\']\s*)?$/x';
        preg_match( $regex, $value, $matches );
        if (
            $value == $matches[0] and
            isset( $matches[3] ) and
            trim( $matches[1] ) == trim( $matches[3] )
        ) {
            $value = $matches[2];
        }

        return $value;
    }
}

/**
 * helper function for env function to translate (string) values
 * into boolean values, empty string or null
 *
 * @param  string $value value to be transformed
 *
 * @return mixed         transformed value
 */
function val2boolEmptyNull ( $value ) {
    switch (strtolower($value)) {
        case 'yes':
        case '(yes)':
        case 'true':
        case '(true)':
            return true;
        case 'no':
        case '(no)':
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
        default:
            return $value;
    }
}

/**
 * Remove a directory and all its content
 *
 * @param  string  $path directory path
 *
 * @return boolean       `true` on success, `false` on error
 */
function rm_recursive( $path ) {
    // we don't want to reflect the current path '.' or its parent '..'
    $cleanse = [ '.', '..' ];
    if ( is_dir( $path ) ) {
        $files = array_diff( scandir( $path ), $cleanse );
        foreach ( $files as $file ) {
            $newPath = implode( DIRECTORY_SEPARATOR, [ $path, $file ] );
            if ( is_dir( $newPath ) ) {
                rm_recursive( $newPath );
            }
            else {
                unlink( $newPath );
            }
        }
        return rmdir( $path );
    }
}

/**
 * trim value if is a string
 *
 * @param  mixed  $var   value to be trimmed if string
 * @param  string $chars defaults to `NULL` so default of `trim` function is used
 *
 * @return mixed         trimmed string if string was given – or untouched value
 */
function trimIfString( $var, $chars = NULL ) {
    if ( is_string( $var ) ) {
        if ( $chars == NULL ) {
            $var = trim( $var );
        }
        else {
            $var = trim( $var, $chars );
        }
    }
    return $var;
}

/**
 * helper function for chunking strings by
 * a bunch of separator characters
 *
 * @param  string $value           string to be chunked
 * @param  string $chars           characters to chunk by, defaults to space ` `
 * @param  string $normalizeLocale defaults to `de_DE` – have a look on to `iconv`
 * @return array                   list of chunks of string
 */
function chunkString( string $value, string $chars = ' ', string $normalizeLocale = 'de_DE' ) {
    $regex = REGEX_DELIMITER . '[' . delimiter_preg_quote ( $chars ) . ']' . REGEX_DELIMITER;

    setlocale( LC_ALL, $normalizeLocale );
    $value = iconv("UTF-8", "ASCII//TRANSLIT", $value);

    return preg_split( $regex, $value );

}

/**
 * Invert the camelCase or PascalCase of a string into its parts. Case of words will not be changed!
 *
 * @param  string       $camel            the camelCase / PascalCase to be split
 * @param  mixed|string $delimiterImplode defaults to a normal space ` ` – if set to `NULL`,
 *                                        the list of elements, the given string consists of,
 *                                        will be returned instead of a imploded string.
 *
 * @return array|string                   if `$delimiterImplode` is set to `NULL`, an array of strings
 *                                        is returned – if the value of `$delimiterImplode` allows to
 *                                        implode the string parts of the camelCase / PascalCase
 *                                        string, that imploded string is returned.
 */
function decamelize( string $camel, mixed $delimiterImplode = ' ' ) {
    $regex  = REGEX_DELIMITER . '(?=[A-Z])' . REGEX_DELIMITER;
    $pieces = preg_split( $regex, $camel );
    $pieces = array_filter( $pieces, fn( $value ) => ! is_null( $value ) && $value !== '' );
    try {
        if ( $delimiterImplode == NULL ) {
            throw new \Exception( 'Return pieces' );
        }
        $implode = implode( $delimiterImplode, $pieces );
        return $implode;
    } catch ( \Exception $e ) {
        return $pieces;
    }
}

/**
 * function to camleize a string
 *
 * camelCase is a naming convention in which the
 * first letter of each word in a compound word
 * is capitalized, except for the first word.
 *
 * @param  string  $value           to convert to camelCase
 * @param  string  $chars           string containing all characters, to
 *                                  separate the string at
 * @param  boolean $keepCamel       keep camels – defaults to false
 * @param  string  $normalizeLocale defaults to `de_DE` – have a look on to `iconv`
 *                                  documentation since that is relevant for
 *                                  translating umlauts like `Ä` into `AE` ...
 *
 * @return string
 */
function camelize( string $value, string $chars = ' ', bool $keepCamel = False, string $normalizeLocale = 'de_DE' ) {
    return lcfirst(
        pascalize( $value, $chars, $keepCamel, $normalizeLocale )
    );
}

/**
 * function to turn a string into PascalCase
 *
 * PascalCase is a naming convention in which the
 * first letter of every word in a compound word
 * is capitalized. The first letter is the only
 * thing different to camelCase.
 *
 * @param  string  $value           to convert to PascalCase
 * @param  string  $chars           string containing all characters, to
 *                                  separate the string at
 * @param  boolean $keepCamel       keep camels – defaults to false
 * @param  string  $normalizeLocale defaults to `de_DE` – have a look on to `iconv`
 *                                  documentation since that is relevant for
 *                                  translating umlauts like `Ä` into `AE` ...
 *
 * @return string
 */
function pascalize( string $value, string $chars = ' ', bool $keepCamel = False, string $normalizeLocale = 'de_DE' ) {

    $chunks = chunkString( $value, $chars, $normalizeLocale );

    if ( $keepCamel ) {

        $oldChunks = $chunks;
        $chunks = [];

        foreach ( $oldChunks as $chunk ) {
            $chunks = array_merge( $chunks, decamelize( $chunk, NULL ) );
        }
    }

    $ucfirsted = array_map( function ( $s ) {
        $s = strtolower( $s );
        return ucfirst( $s );
    }, $chunks );

    return implode( '', $ucfirsted );
}

/**
 * snakify a string
 *
 * @param  string  $value           to convert to snake_case
 * @param  string  $chars           string containing all characters, to
 *                                  separate the string at
 * @param  string  $normalizeLocale defaults to `de_DE` – have a look on to `iconv`
 *                                  documentation since that is relevant for
 *                                  translating umlauts like `Ä` into `AE` ...
 *
 * @return string
 */
function snakify( string $value, string $chars = ' ', string $normalizeLocale = 'de_DE' ) {

    $chunks = chunkString( $value, $chars, $normalizeLocale );

    $lowered = array_map( function ( $s ) {
        return strtolower( $s );
    }, $chunks );

    return implode( '_', $lowered );
}
