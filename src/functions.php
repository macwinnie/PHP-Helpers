<?php

/**
 * This file is a helper file for helper functions used for coding with RegExes.
 *
 * It is published unter CC BY-SA 4.0 license.
 *
 * Source: [macwinnie @ GitHub](https://github.com/macwinnie/RegexFunctions-PHP/)
 *
 * [Documentation](https://macwinnie.github.io/RegexFunctions-PHP/files/src-functions.html) can be found online.
 *
 * Installable by using [Composer](https://packagist.org/packages/macwinnie/regexfunctions)
 */

namespace macwinnie\RegexFunctions;

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
 * use macwinnie\RegexFunctions as rf;
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
 * $regex    = rf\format2regex( $dnFormat, null, true );
 *
 * $mappings = rf\getRegexOccurences( $regex, 'uid=jdoe,ou=people,dc=compartment,dc=example,dc=com', $rGroups );
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
    $parts = array_map( 'macwinnie\RegexFunctions\delimiter_preg_quote', $parts );

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
 *                           if value is `[ 'name' => [ 1,2,3 ] ]`, the tool will return the
 *                           first non-empty group out of 1, 2 or 3
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
