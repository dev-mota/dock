<?php

require_once (__DIR__."/../../../../conf/globals-dockthor.php");


class BasicTest extends \PHPUnit_Framework_TestCase {

    public function testTrueIsTrue() {

        echo "\n\n################### testTrueIsTrue\n";
        $this->assertTrue(true);        
    }
}