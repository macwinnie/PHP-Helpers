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
class ArrayContext implements Context {

    protected static $inspectedArray  = [];
    protected static $inspectedObject = NULL;
    protected static $foundValue      = NULL;

    /**
     * @BeforeScenario
     */
    // prepare for scenario execution
    public static function prepareForTheScenario( BeforeScenarioScope $scope ) {
        static::$inspectedArray  = [];
        static::$inspectedObject = NULL;
        static::$foundValue      = NULL;
    }

    /**
     * @Given the JSON array
     */
    public function theJsonArray( PyStringNode $string ) {
        static::$inspectedArray = $this->jsonString2Array( $string );
    }

    /**
     * @Given the JSON object
     */
    public function theJsonObject( PyStringNode $string ) {
        static::$inspectedObject = json_decode( $string );
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
        static::$foundValue = getArrayValue( static::$inspectedArray, $keytree );
    }

    /**
     * @When I search for the key-tree :keytree with default :default
     */
    public function iSearchForTheKeyTreeWithDefault( $keytree, $default ) {
        static::$foundValue = getArrayValue( static::$inspectedArray, $keytree, $default );
    }

    /**
     * @Then I should get the value :return
     */
    public function iShouldGetTheValue( $return ) {
        Assert::assertEquals( $return, static::$foundValue );
    }

    /**
     * @When I extract – search and remove – the key-tree :keytree
     */
    public function iExtractSearchAndRemoveTheKeyTree( $keytree ) {
        static::$foundValue = extractArrayValue( static::$inspectedArray, $keytree );
    }

    /**
     * @Then the JSON representation of the remaining array should look like
     */
    public function theJsonRepresentationOfTheRemainingArrayShouldLookLike( PyStringNode $string ) {
        // analyze given JSON representation
        try {
            $newJson = json_decode( $string, true );
            Assert::assertEquals( JSON_ERROR_NONE, json_last_error() );
        } catch ( ExpectationFailedException $e ) {
            throw new ExpectationFailedException( 'JSON Error: ' . json_last_error_msg() );
        }
        ksort( $newJson );
        $string  = json_encode( $newJson );
        $curArr  = static::$inspectedArray;
        ksort( $curArr );
        $compare = json_encode( $curArr );
        Assert::assertEquals( $string, $compare );
    }

    /**
     * @Then searching for JSON returns :bool
     */
    public function searchingForJsonReturns( $bool, PyStringNode $string) {
        Assert::assertSame(
            str2bool( $bool ),
            in_array_recursive(
                json_decode( $string, true ),
                static::$inspectedArray
            )
        );
    }

    /**
     * @Given compacting the values :commaseparated
     */
    public function compactingTheValues( $commaseparated ) {

        $functionValues = array_map( 'trim', explode( ',', $commaseparated ) );
        array_unshift( $functionValues, static::$inspectedObject );

        static::$foundValue = call_user_func_array( "compactWith", $functionValues );
    }

    /**
     * @Then the resulting array equals JSON
     */
    public function theResultingArrayEqualsJson( PyStringNode $string ) {
        $shouldBe = $this->jsonString2Array( $string );

        Assert::assertEqualsCanonicalizing( $shouldBe, static::$foundValue );
    }

    /**
     * helper function to convert string to JSON Array
     *
     * @param  string $string JSON String
     * @return array          result of json_decode
     */
    private function jsonString2Array( $string ) {

        $result = json_decode( $string, true );

        try {
            Assert::assertEquals( JSON_ERROR_NONE, json_last_error() );
        } catch ( ExpectationFailedException $e ) {
            throw new ExpectationFailedException( 'JSON Error: ' . json_last_error_msg() );
        }

        return $result;
    }

}
