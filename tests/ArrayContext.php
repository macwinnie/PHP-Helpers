<?php

namespace macwinnie\PHPHelpersTests;

use macwinnie\PHPHelpers as rf;

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
class ArrayContext implements Context {

    protected static $inspectedArray = [];
    protected static $foundValue     = NULL;

    /**
     * @BeforeFeature
     */
    // prepare for feature execution
    public static function prepareForTheFeature() {
        static::$inspectedArray = [];
        static::$foundValue     = NULL;
    }

    /**
     * @Given the JSON array
     */
    public function theJsonArray( PyStringNode $string ) {
        static::$inspectedArray = json_decode( $string, true );
        try {
            Assert::assertEquals( JSON_ERROR_NONE, json_last_error() );
        } catch ( ExpectationFailedException $e ) {
            throw new ExpectationFailedException( 'JSON Error: ' . json_last_error_msg() );
        }
    }

    /**
     * @When I search for the key-tree :key
     */
    public function iSearchForTheKeyTree( $keytree ) {
        static::$foundValue = rf\getArrayValue( static::$inspectedArray, $keytree );
    }

    /**
     * @When I search for the key-tree :keytree with default :default
     */
    public function iSearchForTheKeyTreeWithDefault( $keytree, $default ) {
        static::$foundValue = rf\getArrayValue( static::$inspectedArray, $keytree, $default );
    }

    /**
     * @Then I should get the value :return
     */
    public function iShouldGetTheValue( $return ) {
        Assert::assertEquals( $return, static::$foundValue );
    }
}