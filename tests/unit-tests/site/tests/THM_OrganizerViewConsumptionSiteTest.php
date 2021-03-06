<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    JoomlaFramework
 */

// Complusoft JoomlaTeam - Support: JoomlaTeam@Complusoft.es
require_once JPATH_BASE . '/components/com_thm_organizer/views/consumption/view.html.php';
require_once JPATH_BASE . '/components/com_thm_organizer/models/consumption.php';
/**
 * Test class for THM_OrganizerViewConsumption
 *
 * @package  thm_organizer
 */
class THM_OrganizerViewConsumptionSiteTest extends TestCaseDatabase
{
    /**
     *
     * @var THM_OrganizerViewConsumption
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

        if (!defined('JPATH_COMPONENT'))
        {
            define('JPATH_COMPONENT', JPATH_BASE . '/components/com_thm_organizer');
        }

        JFactory::$application = $this->getMockCmsApp();
        JFactory::$document = $this->getMockDocument();

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '';

        $this->object = new THM_OrganizerViewConsumption;
        $consumptionModel = new THM_OrganizerModelConsumption;
        $this->object->addTemplatePath(JPATH_BASE . '/components/com_thm_organizer/views/consumption/tmpl');
        $this->object->setModel($consumptionModel, true);
    }

    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(JPATH_TEST_DATABASE . '/jos_thm_organizer_schedules.xml');
    }

    /**
     * Method to test the getSchedulesFromDB function
     *
     * @return null
     */
    public function testdisplay()
    {
        $this->markTestSkipped(
            'Solution for getParams needed.'
        );
        JFactory::$application->expects($this->any())->method('getParams')->will($this->returnValue(TestMockMenu::create($this)));
        $this->object->display();

        $actual = ob_get_contents();

        ob_clean();

        $matcher = array(
            'tag'     => 'form',
            'child'   => array(
                'tag'   => 'select'
            )
        );

        // $this->assertTag($matcher, $actual);

        $matcher = array(
            'tag'     => 'form',
            'child'   => array(
                'tag'   => 'input'
            )
        );

        // $this->assertTag($matcher, $actual);

        $matcher = array(
            'tag'     => 'form',
            'child'   => array(
                'tag'       => 'select',
                'children'  => array(
                    'greater_than' => 1,
                    'only'         => array('tag' => 'option')
                )
            )
        );

        // $this->assertTag($matcher, $actual);
    }
}
