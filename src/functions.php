<?php

namespace macwinnie\RegexFunctions;

const REGEX_DELIMITER = '/';

/**
 * function to translate format string to regex to retrieve entry values
 *
 * `sscanf` should do it in most situations – but especially if the format doesn't
 * contain spaces after conversion specifications, it fails. So with
 * DN-Definitions with LDAP.
 *
 * @param  string $format format string to be analyzed
 * @return string         RegEx to be used further
 */
function format2regex ( $format ) {
    $match = '(.*?)';

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

    return REGEX_DELIMITER . implode( $match, $parts ) . REGEX_DELIMITER;
}

/**
 * helper function to quote regular delimiter / in RegEx String
 *
 * @param  string $string string to be quoted
 * @return string         quoted string
 */
function delimiter_preg_quote ( $string ) {
    return preg_quote( $string, REGEX_DELIMITER );
}

/**
 * function to fetch RegEx occurences from string / template
 *
 * @param  string  $template the template string
 * @return [mixed]           fetch additional group elements and put it into key;
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
