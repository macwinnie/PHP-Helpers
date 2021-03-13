<?php

namespace macwinnie\RegexFunctions;

/**
 * function to translate format string to regex to retrieve entry values
 *
 * @param  string $format format string to be analyzed
 * @return string         RegEx to be used further
 */
function format2regex ( $format ) {
    $match = '(.*?)';

    $regex = '/%([0-9]+\$)?((-|\+| |0|\'.)+)?([0-9]+)?(\.[0-9]+)?([bcdeEfFgGosxX])/x';
    $parts = preg_split( $regex, $format );
    $parts = array_map( 'preg_quote', $parts );

    return '/' . implode( $match, $parts ) . '/';
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
