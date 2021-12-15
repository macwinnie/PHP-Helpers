<?php

namespace macwinnie\PHPHelpersTests;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class AnyContext implements Context {

    private static $envTest = [];

    /**
     * @BeforeScenario
     */
    // prepare for scenario execution
    public static function prepareForTheScenario( BeforeScenarioScope $scope ) {
        // only run on env.feature
        if ( strpos( $scope->getFeature()->getFile(), 'env.feature' ) !== false ) {
            static::$envTest = [];
        }
    }

    /**
     * @Given the existing empty directory :path
     */
    public function theExistingEmptyDirectory($path) {
        if ( is_dir( $path ) ) {
            // we don't want to reflect the current path '.' or its parent '..'
            $cleanse  = [ '.', '..' ];
            $contents = array_diff( scandir( $path ), $cleanse );
            Assert::assertEquals( 0, count( $contents ) );
        }
        else {
            mkdir( $path, 0700 );
            Assert::assertTrue( is_dir( $path ) );
        }
    }

    /**
     * @Given the existing new directory :path
     */
    public function theExistingNewDirectory($path) {
        Assert::assertFalse( is_dir( $path ) );
        mkdir( $path, 0700, true );
        Assert::assertTrue( is_dir( $path ) );
    }

    /**
     * @Given the existing new empty file :path
     */
    public function theExistingNewEmptyFile($path) {
        Assert::assertFalse( is_file( $path ) );
        $file = fopen( $path, "w" );
        fwrite( $file, "" );
        fclose( $file );
        Assert::assertTrue( is_file( $path ) );
    }

    /**
     * @Then by removing :path the directory does no more exist
     */
    public function byRemovingTheDirectoryDoesNoMoreExist($path) {
        Assert::assertTrue( is_dir( $path ) );
        rm_recursive( $path );
        Assert::assertFalse( is_dir( $path ) );
    }

    /**
     * @Given the env variable :name with value :value
     */
    public function theEnvVariableWithValue( $name, $value ) {
        static::setEnvValue( $name, $value );
    }

    /**
     * helper function to set an env value
     *
     * @param string $name  env name to be filled by value
     * @param string $value value to set
     */
    public static function setEnvValue( $name, $value ) {
        putenv( sprintf( '%s="%s"', $name, $value ) );
        $_ENV[ $name ] = $value;
        $_SERVER[ $name ] = $value;
    }

    /**
     * @Given the string - expected set
     */
    public function theStringExpectedSet( TableNode $table ) {
        static::$envTest = $table->getHash();
    }

    /**
     * helper function to check test results against true, false and NULL
     *
     * @param  string &$val given expected value
     *
     * @return mixed        actual expected value
     */
    private static function checkSpecial ( &$val ) {
        switch ( strtoupper( $val ) ) {
            case 'TRUE':
                $val = true;
                break;
            case 'FALSE':
                $val = false;
                break;
            case 'NULL':
                $val = NULL;
                break;
        }
    }

    /**
     * @Then transforming with val2boolEmptyNull returns the expected
     */
    public function transformingWithValboolemptynullReturnsTheExpected() {
        foreach ( static::$envTest as $test ) {
            static::checkSpecial( $test[ 'expected' ]);
            Assert::assertSame( $test[ 'expected' ], val2boolEmptyNull( $test[ 'string' ] ) );
        }
    }

    /**
     * @Given the env - value - expected set
     */
    public function theEnvValueExpectedSet( TableNode $table ) {
        static::$envTest = $table->getHash();
    }

    /**
     * @Then setting env and receiving the value matches the expected
     */
    public function settingEnvAndReceivingTheValueMatchesTheExpected() {
        foreach ( static::$envTest as $test ) {
            $this->theEnvVariableWithValue( $test[ 'env' ], $test[ 'val' ] );
            static::checkSpecial( $test[ 'expected' ]);
            Assert::assertSame( $test[ 'expected' ], env( $test[ 'env' ] ) );
        }
    }

    /**
     * @Given trimming quotes from :arg1 attributes
     */
    public function trimmingQuotesFromAttributes( $arg1 ){
        foreach ( static::$envTest as &$test ) {

            $attr = $test[ $arg1 ];
            $a    = substr( $attr,  0, 1);
            $o    = substr( $attr, -1, 1);
            $quotes = [ '"', "'" ];

            if ( $a == $o and in_array( $a, $quotes ) ) {
                $test[ $arg1 ] = substr( $attr, 1, strlen( $attr ) - 2 );
            }
        }
    }

    /**
     * @Then trimming if is string returns expected values
     */
    public function trimmingIfIsStringReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], trimIfString( $test[ 'string' ] ) );
        }
    }

    /**
     * @Then camelize returns expected values
     */
    public function camelizeReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], camelize( $test[ 'string' ] ) );
        }
    }

    /**
     * @Then camelize by underscore returns expected values
     */
    public function camelizeByUnderscoreReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], camelize( $test[ 'string' ], '_' ) );
        }
    }

    /**
     * @Then camelize keeping camels returns expected values
     */
    public function camelizeKeepingCamelsReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], camelize( $test[ 'string' ], ' _', true ) );
        }
    }

    /**
     * @Then pascalize returns expected values
     */
    public function pascalizeReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], pascalize( $test[ 'string' ] ) );
        }
    }

    /**
     * @Then snakify returns expected values
     */
    public function snakifyReturnsExpectedValues() {
        foreach ( static::$envTest as $test ) {
            Assert::assertSame( $test[ 'expected' ], snakify( $test[ 'string' ] ) );
        }
    }

}
