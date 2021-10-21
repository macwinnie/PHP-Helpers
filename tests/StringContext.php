<?php

namespace macwinnie\PHPHelpersTests;

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
class StringContext implements Context {

    static protected $rnd_randoms   = [];
    static protected $rnd_curLen    = 16;
    static protected $rnd_alphabets = NULL;

    /**
     * @BeforeScenario
     */
    // prepare for scenario execution
    public static function prepareForTheScenario() {
        static::$rnd_randoms   = [];
        static::$rnd_curLen    = 16;
        static::$rnd_alphabets = NULL;
    }

    /**
     * generate random – helper function
     *
     * @return void
     */
    private function generateRandom() {
        $l1 = count( static::$rnd_randoms );
        $string = randomString( static::$rnd_curLen, static::$rnd_alphabets );
        Assert::assertEquals( static::$rnd_curLen, strlen( $string ) );
        static::$rnd_randoms[] = $string;
        $l2 = count( static::$rnd_randoms );
        Assert::assertEquals( $l1 + 1, $l2 );
    }

    /**
     * @Given I generate a random string of length :len
     */
    public function iGenerateARandomStringOfLength( $len = 16 ) {
        static::$rnd_curLen    = $len;
        $this->generateRandom();
    }

    /**
     * @When I generate another random string of same length
     */
    public function iGenerateAnotherRandomStringOfSameLength() {
        $this->generateRandom();
    }

    /**
     * @Then both strings have to differ
     */
    public function bothStringsHaveToDiffer() {
        $strings = static::$rnd_randoms;
        foreach ( $strings as $key => $s1 ) {
            unset( $strings[ $key ] );
            foreach ( $strings as $s2 ) {
                Assert::assertNotEquals( $s1, $s2 );
            }
        }
    }

    /**
     * @Then both strings have to be of given length
     */
    public function bothStringsHaveToBeOfGivenLength() {
        Assert::assertEquals( 2, count( static::$rnd_randoms ) );
        foreach ( static::$rnd_randoms as $string ) {
            Assert::assertEquals( static::$rnd_curLen, strlen( $string ) );
        }
    }

    /**
     * @Given the alphabets
     */
    public function theAlphabets( TableNode $table ) {
        $alphabets = $table->getHash();
        if ( count( $alphabets ) > 1 ) {
            static::$rnd_alphabets = [];
            foreach ( $alphabets as $alph ) {
                static::$rnd_alphabets[] = $alph['alphabet'];
            }
        }
        else {
            static::$rnd_alphabets = $alphabets[0]['alphabet'];
        }
    }

    /**
     * @Then there is only one alphabet given
     */
    public function thereIsOnlyOneAlphabetGiven() {
        Assert::assertIsString( static::$rnd_alphabets );
    }

    /**
     * @Then the generated string matches each RegEx
     */
    public function theGeneratedStringMatchesEachRegex( TableNode $table ) {
        Assert::assertEquals( 1, count( static::$rnd_randoms ) );
        $pattern = $table->getHash();
        foreach ( $pattern as $rx ) {
            Assert::assertMatchesRegularExpression( $rx[ 'RegEx' ], static::$rnd_randoms[0] );
        }
    }
}
