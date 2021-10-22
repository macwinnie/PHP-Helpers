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
class AnyContext implements Context {

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
}
