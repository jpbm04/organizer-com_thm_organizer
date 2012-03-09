<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view thm organizer main menu
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');
class thm_organizersViewthm_organizers extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');

        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $pane = JPane::getInstance('sliders');
        $this->pane = $pane;

        $application = JFactory::getApplication("administrator");
        $this->option = $application->scope;

        JToolBarHelper::title( JText::_( 'COM_THM_ORGANIZER' ).": ".JText::_( "COM_THM_ORGANIZER_MAIN_TITLE" ), 'home.png' );

        $this->addViews();

	parent::display($tpl);
    }

    /**
     * addViews
     *
     * creates html elements for links to component views
     */
    private function addViews()
    {
        $linkStart = "<a href='index.php?option={$this->option}&view=VIEWTEXT' ";
        $linkStart .= "class='hasTip' title='TITLETEXT' >";
        $views = array( 'semester_manager' => array(),
                        'schedule_manager' => array(),
                        'virtual_schedule_manager' => array(),
                        'resource_manager' => array(),
                        'category_manager' => array(),
                        'monitor_manager' => array(),
                        'settings' => array());
        $views['semester_manager']['title'] = JText::_('COM_THM_ORGANIZER_SEM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SEM_TITLE')."::".JText::_('COM_THM_ORGANIZER_SEM_DESC');
        $views['semester_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        $views['schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SCH_TITLE')."::".JText::_('COM_THM_ORGANIZER_SCH_DESC');
        $views['schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        $views['virtual_schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_VSM_TITLE')."::".JText::_('COM_THM_ORGANIZER_VSM_DESC');
        $views['virtual_schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['resource_manager']['title'] = JText::_('COM_THM_ORGANIZER_RES_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_RES_TITLE')."::".JText::_('COM_THM_ORGANIZER_RES_DESC');
        $views['resource_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['category_manager']['title'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_CAT_TITLE')."::".JText::_('COM_THM_ORGANIZER_CAT_DESC');
        $views['category_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_MON_TITLE')."::".JText::_('COM_THM_ORGANIZER_MON_DESC');
        $views['monitor_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        $views['settings']['title'] = JText::_('COM_THM_ORGANIZER_COM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_COM_TITLE')."::".JText::_('COM_THM_ORGANIZER_RIA_DESC');
        $views['settings']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        foreach($views as $k => $view)
        {
            $views[$k]['link_start'] = str_replace("VIEWTEXT", $k, $views[$k]['link_start']);
            $views[$k]['image'] = JHTML::_("image",
                                           "components/com_thm_organizer/assets/images/$k.png",
                                           $view['title'],
                                           array( 'class' => 'thm_organizer_main_image'));
            $views[$k]['text'] = "<span>".$view['title']."</span>";
            $views[$k]['link_end'] = "</a>";
        }
        $this->views = $views;
    }
}
