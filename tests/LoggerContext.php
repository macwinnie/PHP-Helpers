<?php

namespace macwinnie\PHPHelpersTests;

use macwinnie\PHPHelpers\Logger;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class LoggerContext implements Context {

    static protected $logged = [];

    /**
     * @BeforeScenario
     */
    // prepare for scenario execution
    public static function prepareForTheScenario() {
        static::$logged = [];
    }

    /**
     * @AfterScenario
     */
    // clean up scenario execution
    public static function cleanupScenario() {
        if ( env( 'LOG_PATH' ) != NULL ) {
            $path_segments = explode( DIRECTORY_SEPARATOR, env( 'LOG_PATH' ) );
            $del_path = '';
            $checked = [];
            foreach ( $path_segments as $i => $seg ) {
                // we don't want to reflect the current path '.' or its parent '..'
                $cleanse = [ '.', '..' ];
                // we don't want to reflect the child, that will be checked next
                if ( isset( $path_segments[ $i + 1 ] ) ) {
                    $cleanse[] = $path_segments[ $i + 1 ];
                }
                // let's work with the $checked variable as path segments
                // already checked after this step
                $checked[] = $seg;
                $path = implode( DIRECTORY_SEPARATOR, $checked );
                // let's find the siblings of the path reflected next
                $sibs  = array_diff( scandir( $path ), $cleanse );
                if ( count( $sibs ) == 0 ) {
                    // delete the current path
                    rm_recursive( $path );
                    // we don't need to check further
                    break;
                }
            }
        }
    }

    /**
     * @Given I log the message :msg with level :lvl
     */
    public function iLogTheMessageWithLevel($msg, $lvl) {
        static::$logged[ 'message' ] = $msg;
        static::$logged[ 'level' ]   = $lvl;
        static::$logged[ 'return' ]  = Logger::$lvl( $msg );
    }

    /**
     * @Given the env variable :name with value :value
     */
    public function theEnvVariableWithValue($name, $value) {
        putenv( sprintf( '%s="%s"', $name, $value ) );
        $_ENV[ $name ] = $value;
        $_SERVER[ $name ] = $value;
        switch (strtolower($value)) {
            case 'yes':
            case '(yes)':
            case 'true':
            case '(true)':
                $value = true;
            case 'no':
            case '(no)':
            case 'false':
            case '(false)':
                $value = false;
            case 'empty':
            case '(empty)':
                $value = '';
            case 'null':
            case '(null)':
                $value = NULL;
        }
        Assert::assertEquals( $value, env( $name ) );
    }

    /**
     * @Then the message matches the regex :regex
     */
    public function theMessageMatchesTheRegex( $regex ) {
        Assert::assertMatchesRegularExpression( $regex, static::$logged[ 'return' ] );
    }

    /**
     * @Then the message contains current class name
     */
    public function theMessageContainsCurrentClassName() {
        $classname = get_class( new static() );
        Assert::assertStringContainsString( $classname, static::$logged[ 'return' ] );
    }

    /**
     * @Given the logging dir :path
     */
    public function theLoggingDir( $path ) {
        $this->theEnvVariableWithValue( 'LOG_PATH', $path );
        if ( ! is_dir( env( 'LOG_PATH' ) ) ) {
            mkdir( env( 'LOG_PATH' ), 0700, true );
        }
        Assert::assertTrue( is_dir( env( 'LOG_PATH' ) ) );
    }

    /**
     * @Then the global logfile should contain an entry with that message
     */
    public function theGlobalLogfileShouldContainAnEntryWithThatMessage() {
        throw new PendingException();
    }

    /**
     * @Then the loglevel logfile should contain an entry with that message
     */
    public function theLoglevelLogfileShouldContainAnEntryWithThatMessage() {
        throw new PendingException();
    }
}
