<?php

include_once '../apps/docking/lib/utils/StructureViewerConfigLib.php';

/**
 * ConfigLib test case.
 */
class ConfigLibTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var ConfigLib
     */
    private $configLib;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated ConfigLibTest::setUp()
        
        $this->configLib = new StructureViewerConfigLib(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated ConfigLibTest::tearDown()
        $this->configLib = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests ConfigLib->isAppEnabled()
     */
    public function testIsAppEnabled()
    {
        $result = $this->configLib->getStructureViewerType();
        $this->assertTrue($result!=null && $result!='');
    }
}

