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
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class LoggerContext implements Context {

    static protected $logged = [];

    /**
     * @BeforeScenario
     */
    // prepare for scenario execution
    public static function prepareForTheScenario( BeforeScenarioScope $scope ) {
        // only run on logger.feature
        if ( strpos( $scope->getFeature()->getFile(), 'logger.feature' ) !== false ) {
            static::$logged = [];
        }
    }

    /**
     * @AfterScenario
     */
    // clean up scenario execution
    public static function cleanupScenario( AfterScenarioScope $scope ) {
        // to run only when logger.feature is run ...
        if ( strpos( $scope->getFeature()->getFile(), 'logger.feature' ) !== false ) {
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
                    $sibs = [];
                    if ( is_dir( $path ) ) {
                        $sibs = array_diff( scandir( $path ), $cleanse );
                    }
                    if ( count( $sibs ) == 0 ) {
                        // delete the current path
                        rm_recursive( $path );
                        // we don't need to check further
                        break;
                    }
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
        static::$logged[ 'return' ]  = Logger::$lvl( $msg, true );
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
     * @Given the logging path :path
     */
    public function theLoggingPath( $path ) {
        AnyContext::setEnvValue( 'LOG_PATH', $path );
        Assert::assertTrue( Logger::ensureLogPathExists() );
        Assert::assertDirectoryExists( env( 'LOG_PATH' ) );
    }

    /**
     * @Then the global logfile should contain an entry with that message
     */
    public function theGlobalLogfileShouldContainAnEntryWithThatMessage() {
        $filename = implode( DIRECTORY_SEPARATOR, [ env( 'LOG_PATH' ), Logger::getFilename( 'full' ) ] );
        Assert::assertFileExists( $filename );
        $log_lines = array_filter( array_map( 'trim', explode( "\n", file_get_contents( $filename ) ) ) );
        Assert::assertContains( static::$logged[ 'return' ], $log_lines );
    }

    /**
     * @Then the loglevel logfile should contain an entry with that message
     */
    public function theLoglevelLogfileShouldContainAnEntryWithThatMessage() {
        $filename = implode( DIRECTORY_SEPARATOR, [ env( 'LOG_PATH' ), Logger::getFilename( static::$logged[ 'level' ] ) ] );
        Assert::assertFileExists( $filename );
        $log_lines = array_filter( array_map( 'trim', explode( "\n", file_get_contents( $filename ) ) ) );
        $pattern = format2regex( Logger::getLogStringFormat( false ), '(.*?)', true );
        $format  = Logger::getLogStringFormat( true );
        preg_match( $pattern, static::$logged['return'], $matches);
        unset($matches[0]);
        Assert::assertContains( vsprintf( $format, $matches ), $log_lines );
    }

    /**
     * @Then the global logfile does not exist
     */
    public function theGlobalLogfileDoesNotExist() {
        $filename = implode( DIRECTORY_SEPARATOR, [ env( 'LOG_PATH' ), Logger::getFilename( 'full' ) ] );
        Assert::assertFileNotExists( $filename );
    }

    /**
     * @Then the loglevel logfile does not exist
     */
    public function theLoglevelLogfileDoesNotExist() {
        $filename = implode( DIRECTORY_SEPARATOR, [ env( 'LOG_PATH' ), Logger::getFilename( static::$logged[ 'level' ] ) ] );
        Assert::assertFileNotExists( $filename );
    }
}
