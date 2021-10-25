<?php

namespace macwinnie\PHPHelpers;

use DateTime, DateTimeZone;

/**
 * Logger class that handles writing out logfiles for PHP programs.
 *
 * Possible loglevels are:
 * * DEBUG
 * * INFO
 * * NOTICE
 * * WARNING
 * * ERROR
 * * CRITICAL
 * * ALERT
 * * EMERGENCY
 *
 * A regular logging entry (in the combined logfile) has the format:
 * `Y-m-d H:i:s.u [loglevel] [class_calling] [length]: message`
 *
 * (See PHP documentation for date format; `LOG_MICROSEC=false` will remove `.u` from date-time.)
 *
 * Within the loglevel specific files, the `[loglevel]` entry is removed.
 *
 * `[length]` represents the length of the logmessage `message` (in Bytes); date, time and the square braced parts are not reflected.
 *
 * Possible environmental variables – defaults are defined in `static::$localDefaults`:
 * | environmental variable | description |
 * | ---------------------- | ----------- |
 * | `LOG_PATH`             | where should the log files be placed? Defaults to `/tmp/logs/` |
 * | `LOG_LEVEL`            | one of the loglevels, defaults to `ERROR` |
 * | `LOG_MICROSEC`         | should microseconds be reflected? Default is `true` |
 * | `LOG_TIMEZONE`         | the timezone the logs should reflect, defaults to environmental variable `TIMEZONE` if it exists or `Europe/Berlin` |
 * | `LOG_COMBINED`         | if `true`, additionally a `full.log` file will be created |
 * | `LOG_ONLY_COMBINED`    | if `true` and `LOG_COMBINED` also `true`, only the `full.log` file is written out – else, there will also be `loglevel.log` files |
 */
class Logger {

    /**
     * dictionary of additional filenames
     *
     * @var [string]
     */
    static private $filenames = [
        'full' => 'full.log',
    ];

    /**
     * ordered (!) list of available LogLevels
     *
     * @var [string]
     */
    static protected $availableLoglevels = [
        'DEBUG',
        'INFO',
        'NOTICE',
        'WARNING',
        'ERROR',
        'CRITICAL',
        'ALERT',
        'EMERGENCY',
    ];

    /**
     * dictionary of defaults for used environmental variables
     *
     * @var [mixed]
     */
    static protected $localDefaults = [
        'LOG_PATH'          => '/tmp/logs/',
        'LOG_LEVEL'         => 'error',
        'LOG_MICROSEC'      => true,
        'LOG_TIMEZONE'      => 'Europe/Berlin',
        'LOG_COMBINED'      => true,
        'LOG_ONLY_COMBINED' => true,
    ];

    // working variables
    private $envs = [];
    private $time = NULL;
    private $lvl  = NULL;
    private $msg  = NULL;
    private $clss = NULL;
    private $spec = false;

    /**
     * initialize the logger
     *
     * @param  string $loglevel loglevel out of static::$availableLoglevels
     * @param  string $message  the message to be logged
     *
     * @throws \Exception       thrown when loglevel does not exist
     */
    protected function __construct ( $loglevel, $message, $class ) {

        $loglevel = strtoupper( $loglevel );
        if ( ! in_array( $loglevel, static::$availableLoglevels ) ) {
            throw new \Exception( sprintf( "The loglevel '%s' does not exist!", $loglevel ) );

        }

        // fetch values of environmental functions
        foreach ( static::$localDefaults as $key => $default ) {
            if ( $key == 'LOG_TIMEZONE' ) {
                $this->envs[ $key ] = env( $key, env( 'TIMEZONE', $default ) );
            }
            else {
                $this->envs[ $key ] = env( $key, $default );
            }
        }
        $this->envs[ 'LOG_LEVEL' ] = strtoupper( $this->envs[ 'LOG_LEVEL' ] );

        // set all information
        if ( $this->envs[ 'LOG_MICROSEC' ] ) {
            $dateformat = 'Y-m-d H:i:s.u';
        }
        else {
            $dateformat = 'Y-m-d H:i:s';
        }
        $this->time = DateTime::createFromFormat( 'U.u', microtime( true ))
                                ->setTimezone( new DateTimeZone( $this->envs[ 'LOG_TIMEZONE' ] ) )
                                ->format( $dateformat );
        $this->lvl  = $loglevel;
        $this->msg  = $message;
        $this->clss = $class;

        $this->writeOut();
    }

    /**
     * function that arranges the writeout to the logfile
     *
     * @return void
     */
    private function writeOut() {

        $ell_key = array_search( $this->envs[ 'LOG_LEVEL' ], static::$availableLoglevels );
        $rll_key = array_search( $this->lvl, static::$availableLoglevels );

        static::ensureLogPathExists();

        if ( $rll_key >= $ell_key ) {
            if ( $this->envs[ 'LOG_COMBINED' ] ) {
                $this->spec = false;
                $this->appendToFile( static::getFilename( 'full' ), strval( $this ) );
            }
            if (
                ! $this->envs[ 'LOG_COMBINED' ] or
                ! $this->envs[ 'LOG_ONLY_COMBINED' ]
            ) {
                $this->spec = true;
                $this->appendToFile( static::getFilename( $this->lvl ), strval( $this ) );
            }
            $this->spec = false;
        }
    }

    /**
     * function that performs the write out
     *
     * @param  string $name    filename
     * @param  string $content content to append to the logfile
     * @return mixed           Returns the same like `file_put_contents`:
     *                         This function returns the number of bytes that were
     *                         written to the file, or `false` on failure.
     */
    private function appendToFile( $name, $content ) {
        $path = implode( DIRECTORY_SEPARATOR, [ $this->envs[ 'LOG_PATH' ], $name ] );
        $file = fopen( $path, 'a' );
        fwrite( $file, $content . "\n");
        fclose( $file );
    }

    /**
     * create string representation of current log entry
     *
     * @return string log entry
     */
    public function __toString() {

        $format = static::getLogStringFormat( $this->spec );

        $msgsize = strlen( $this->msg );

        return sprintf( $format, $this->time, $this->lvl, $this->clss, $msgsize, $this->msg );
    }

    /**
     * function that handles loglevel method calls to this static class and
     * returns an `\Exception` on all unkown static method calls on this class
     *
     * @param  string $name name of the unknown callable
     * @param  mixed  $args arguments passed for the callable:
     *                      # message
     *                      # return – should the (loglevel) message be returned?
     *
     * @return mixed        result of the callable, if there is a matching loglevel
     *
     * @throws \Exception   Fatal undefined method when callable is not equal to defined loglevel
     */
    public static function __callStatic ( $name, $args ) {
        $name = strtoupper( $name );
        if ( in_array( $name, static::$availableLoglevels ) ) {
            $log = new static ( $name, $args[0], static::get_calling_class() );
            if (
                isset( $args[1] ) and
                $args[1] == true
            ) {
                return strval( $log );
            }
        }
        else {
            throw new \Exception( sprintf( "Fatal: Call to undefined method %s:%s()", get_called_class(), $name ) );
        }
    }

    /**
     * get the name of the calling class for logging
     *
     * @return string namespace and class name of calling class
     */
    protected static function get_calling_class() {

        // fetch debug trace
        $trace = debug_backtrace();
        // fetch current class name
        $class = array_shift( $trace )[ 'class' ];

        foreach ( $trace as $entry ) {
            // first differing class name is the one searched
            if ( $entry[ 'class' ] != $class ) {
                return $entry[ 'class' ];
            }
        }
    }

    /**
     * get filename for logfiles
     *
     * @param  string $file logfile
     *
     * @return string       filename for logfile
     *
     * @throws \Exception   if the requested logfile type does not exist
     */
    public static function getFilename ( $file ) {
        if ( in_array( strtoupper( $file ), static::$availableLoglevels ) ) {
            return sprintf( '%s.log', strtolower( $file ) );
        }
        elseif ( isset( static::$filenames[ $file ] ) ) {
            return static::$filenames[ $file ];
        }
        else {
            throw new \Exception( "The requested file does not exist." );
        }
    }

    /**
     * Ensure that the log path does exist
     *
     * @return boolean Returns `true` on success or `false` on failure.
     */
    public static function ensureLogPathExists () {
        $success = true;
        if ( ! is_dir( env( 'LOG_PATH', static::$localDefaults[ 'LOG_PATH' ] ) ) ) {
            $success = mkdir( env( 'LOG_PATH', static::$localDefaults[ 'LOG_PATH' ] ), 0700, true );
        }
        return $success;
    }

    /**
     * return the format string for a log message
     *
     * @param  boolean $logSpecific is the log message used in a specific loglevel context,
     *                              e.g. the specific loglevel log file (`true`) or not, e.g.
     *                              the global log file
     *
     * @return string               log message format
     */
    public static function getLogStringFormat ( $logSpecific = true ) {
        $format = '%1$s';
        if ( ! $logSpecific ) {
            $format .= ' [%2$s]';
        }
        $format .= ' [%3$s] [%4$s]: %5$s';
        return $format;
    }
}
