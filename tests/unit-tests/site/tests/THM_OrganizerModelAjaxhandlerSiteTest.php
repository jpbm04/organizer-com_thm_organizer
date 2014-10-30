<?php
/**
 * @package    THM_Organizer.UnitTest
 * @author     Wolf Rost <Wolf.Rost@mni.thm.de>
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Include the SUT class
require_once JPATH_BASE . '/components/com_thm_organizer/models/ajaxhandler.php';

/**
 * Class THM_OrganizerModelConsumptionSiteTest
 *
 * @package             com_thm_organizer
 * @coversDefaultClass  THM_OrganizerModelConsumption
 *
 * @requires            extension sqlite3
 */
class THM_OrganizerModelAjaxhandlerSiteTest extends TestCaseDatabase
{
    /**
     * @var THM_OrganizerModelConsumption
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     *
     * @return  null
     */
    protected function setUp()
    {
        parent::setup();

        //$this->saveFactoryState();

        JFactory::$application = $this->getMockWeb();

        $connect = parent::getConnection();
        $assets = $this->getDataSet();
        $this->_db = JFactory::getDbo();
        $this->object = new THM_OrganizerModelAjaxhandler;
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     * @since   3.2
     */
    protected function tearDown()
    {
        $this->restoreFactoryState();

        $this->object = null;

        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        $dataSet = $this->createXMLDataSet(JPATH_TEST_DATABASE . '/jos_thm_organizer_schedules.xml');

        return $dataSet;
    }


    /**
     * Method to test the executeTask function
     *
     * @return null
     */
    public function testexecuteTask_THMEvents(){

        $_JDA = new THM_OrganizerDataAbstraction;
        $_CFG = new mySchedConfig($_JDA);

        JFactory::$application->expects($this->exactly(1))
            ->method('getCfg')
            ->will($this->returnValue("just a string"));

        // TEST Events.load
        require_once JPATH_COMPONENT . "/assets/classes/Events.php";
        $class = new THMEvents($_JDA, $_CFG);

        // Set model include path
        JModelLegacy::addIncludePath(JPATH_COMPONENT . '/models/');

        // Mock the getEvents method for $class and $this->object
        $class = $this->getMockBuilder(get_class($class))->setConstructorArgs(array($_JDA, $_CFG))->setMethods(array("load"))->getMock();

        $class->expects($this->exactly(2))
            ->method('load')
            ->will($this->returnValue(array("success" => true,"data" => array())));


        $this->object = $this->getMockBuilder(get_class($this->object))->setConstructorArgs(array($_JDA, $_CFG))->setMethods(array("getClass"))->getMock();

        $this->object->expects($this->exactly(1))
            ->method('getClass')
            ->with('THMEvents')
            ->will($this->returnValue($class));

        $expected = $class->load();

        $actual = $this->object->executeTask("Events.load");

        $this->assertEquals($expected, $actual);

    }

    /**
     * Method to test the executeTask function
     *
     * @return null
     */
    public function testexecuteTask_THMUserSchedules(){

        $_JDA = new THM_OrganizerDataAbstraction;
        $_CFG = new mySchedConfig($_JDA);

        // TEST UserSchedule.load
        require_once JPATH_COMPONENT . "/assets/classes/UserSchedule.php";
        $class = new THMUserSchedule($_JDA, $_CFG);

        // Mock the getEvents method for $class and $this->object
        $class = $this->getMockBuilder(get_class($class))->setConstructorArgs(array($_JDA, $_CFG))->setMethods(array("load"))->getMock();

        $class->expects($this->exactly(2))
            ->method('load')
            ->will($this->returnValue(array("success" => true,"data" => array())));


        $this->object = $this->getMockBuilder(get_class($this->object))->setConstructorArgs(array($_JDA, $_CFG))->setMethods(array("getClass"))->getMock();

        $this->object->expects($this->exactly(1))
            ->method('getClass')
            ->with('THMUserSchedule')
            ->will($this->returnValue($class));

        $expected = $class->load();
        $actual = $this->object->executeTask("UserSchedule.load", array("username" => "respChanges"));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Method to test the executeTask function
     *
     * @return null
     */
    public function testexecuteTask_ErrorHandling()
    {
        // ERROR Handling tests
        $expected = array("success" => false, "data" => "Unknown task!");

        $actual = $this->object->executeTask("FOO");
        $this->assertEquals($expected, $actual);

        $actual = $this->object->executeTask(123);
        $this->assertEquals($expected, $actual);

        $expected = array("success" => false, "data" => "Error while perfoming the task.");

        $actual = $this->object->executeTask("FOO.bar");
        $this->assertEquals($expected, $actual);

    }

}