<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewGroups
 * @description THM_CurriculumViewGroups component site view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class THM_CurriculumViewGroups for component com_thm_organizer
 *
 * Class provides methods to display the groups view
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewGroups extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		JHTML::script('joomla.javascript.js', 'includes/js/');

		$document = & JFactory::getDocument();
		$model = & $this->getModel();
		$app = JFactory::getApplication();
		$state = $this->get('State');

		// Attach the components css file to the main page
		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/css/curriculum.css');

		// Get the parameters of the current view
		$params = &$state->params;
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$this->params = $menu->params;

		// Set the default language to german
		$this->session = & JFactory::getSession();

		$this->lang = JRequest::getVar('lang');

		// Assign the data to the template
		$this->pagetitle = $menu->params->get('page_title');
		$this->groups = $model->getGroups($menu->params->get('lsf_query'));

		$this->langLink = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->langLink);

		parent::display($tpl);
	}

	/**
	 * Method to build the link for the language switcher button
	 *
	 * @param   String  $langLink  Language link
	 *
	 * @return  String
	 */
	public function languageSwitcher($langLink)
	{
		$uri = JUri::base();
		$itemid = JRequest::getVar('Itemid');
		$group = JRequest::getVar('view');
		$u = & JURI::getInstance('index.php');
		$params = array('option' => 'com_thm_organizer',
				'view' => $group,
				'Itemid' => $itemid,
				'lang' => $langLink
		);
		$params = array_merge($u->getQuery(true), $params);
		$query = $u->buildQuery($params);
		$u->setQuery($query);

		return $u->toString();
	}
}
