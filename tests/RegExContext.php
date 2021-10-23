<?php

namespace macwinnie\PHPHelpersTests;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class RegExContext implements Context {

    /**
     * @Given the input string
     */
    public function theInputString( PyStringNode $string ) {
        $this->results[ 'fkt_input' ] = $string;
    }

    /**
     * @When running the :fkt function
     */
    public function runningTheFunction( $fkt ) {
        if ( is_array( $this->results[ 'fkt_input' ] ) ) {
            $this->results[ 'fkt_result' ] = call_user_func_array( $fkt, $this->results[ 'fkt_input' ] );
        }
        else {
            $this->results[ 'fkt_result' ] = $fkt( $this->results[ 'fkt_input' ] );
        }
    }

    /**
     * @When fetching values by generated RegEx from :string
     */
    public function fetchingValuesByGeneratedRegexFrom( $string ) {
        preg_match( $this->results[ 'fkt_result' ], $string, $matches );
        $this->results[ 'matches' ] = $matches;
    }

    /**
     * @Then I should get :count values
     */
    public function iShouldGetValues( $count ) {
        Assert::assertEquals( $count, count( $this->results[ 'matches' ] ) - 1 );
    }

    /**
     * @Then :val is matched value :no
     */
    public function isMatchedValue( $val, $no ) {
        Assert::assertEquals( $val, $this->results[ 'matches' ][ $no ] );
    }

    /**
     * @AfterScenario
     */
    public function cleanup ( AfterScenarioScope $scope ) {
        // only run on getRegexOccurences.feature
        if ( strpos( $scope->getFeature()->getFile(), 'getRegexOccurences.feature' ) !== false ) {
            $this->results = [];
        }
    }

    /**
     * @Given the occurences RegEx :regex
     */
    public function theOccurencesRegex ( $regex ) {
        $template = $this->results[ 'fkt_input' ]->getRaw();
        $this->results[ 'fkt_input' ]   = [];
        $this->results[ 'fkt_input' ][] = $regex;
        $this->results[ 'fkt_input' ][] = $template;
    }

    /**
     * @Given the additional occurences group :groupname with commaseparated groups :list
     */
    public function theAdditionalOccurencesGroupWithCommaseparatedGroups( $groupname, $list ) {
        $this->results[ 'fkt_input' ][ 2 ] = [];
        $this->results[ 'fkt_input' ][ 2 ][ $groupname ] = explode(',', $list);
    }

    /**
     * @Then I should get :count match
     */
    public function iShouldGetMatch( $count ) {
        Assert::assertEquals( $count, count( $this->results[ 'fkt_result' ] ) );
    }

    /**
     * @Then :key matches :match for variable :c
     */
    public function matchesForVariable( $key, $match, $c ) {
        $escaped = preg_quote( $match );
        Assert::assertRegExp( '/\s*(\'' . $escaped . '\'|\"' . $escaped . '\")\s*/m', $this->results[ 'fkt_result' ][ $c ][ $key ] );
    }
}
