<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.general
 * @name        Com_THM_OrganizerInstallerScript
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class for the execution of processes during changes to the component itself.
 *
 * @category    Joomla.Component.General
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.general
 */
class Com_THM_OrganizerInstallerScript
{
	/**
	 * Method to install the component. For some unknown reason Joomla will not resolve text constants in this function.
	 * All text constants have been replaced by hard coded English texts. :(
	 *
	 * It also seems that under 3.x this function is ignored if the method is upgrade even if no prior installation
	 * existed.
	 *
	 * @param object $parent the class calling this method
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function install($parent)
	{
		$dirCreated = $this->createImageDirectory();
		if ($dirCreated)
		{
			$dirColor   = 'green';
			$dirStatus  = 'The directory /images/thm_organizer has been created.';
			$instColor  = 'green';
			$instStatus = 'THM Organizer was successfully installed.';
			$status     = 'com_thm_organizer_success';
		}
		else
		{
			$dirColor   = 'red';
			$dirStatus  = 'The directory /images/thm_organizer could not be created.';
			$instColor  = 'yellow';
			$instStatus = 'Problems have occured while installing THM Organizer.';
			$status     = 'com_thm_organizer_failure';
		}
		?>
        <style>
            .com_thm_organizer_success {
                box-shadow: -5px -5px 25px green inset;
                transition-property: box-shadow;
                transition-duration: 3s;
            }

            .com_thm_organizer_failure {
                box-shadow: -5px -5px 25px red inset;
                transition-property: box-shadow;
                transition-duration: 3s;
            }
        </style>
        <script>
            var status = "<?php echo $status; ?>";
            window.addEvent('domready', function ()
            {
                $('com_thm_organizer_fieldset').addClass(status);
            });
        </script>
        <fieldset id="com_thm_organizer_fieldset" style="border-radius:10px;">
            <legend>
                <img style="float:none;" src="../media/com_thm_organizer/images/thm_organizer.png"
                     alt="THM Organizer Logo"/>
            </legend>
            <div style="padding-left:17px;">
                <div style="color:#146295; font-size: 1.182em; font-weight:bold; padding-bottom: 17px">
                    THM Organizer is a component designed to handle the scheduling and planning needs of the
                    University of Applied Sciences Central Hessen in Giessen, Germany.
                </div>
                <div style="width: 100%;">
                    Released under the terms and conditions of the
                    <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
                </div>
                <table style="border-radius: 5px; border-style: dashed; margin-top: 17px;">
                    <tbody>
                    <tr>
                        <td>Directory Status</td>
                        <td><span style='color:
							<?php echo $dirColor; ?>
                                    '>
            <?php echo $dirStatus; ?>
            </span></td>
                    </tr>
                    <tr>
                        <td>Installation Status</td>
                        <td><span style='color:
							<?php echo $instColor; ?>
                                    '>
            <?php echo $instStatus; ?>
            </span></td>
                    </tr>
                    </tbody>
                </table>
				<?php
				if ($dirCreated)
				{
					?>
                    <h4>Please ensure that THM Organizer has write access to the directory mentioned above.</h4>
					<?php
				}
				else
				{
					?>
                    <h4>Please check the /images/thm_organizer Directory.</h4>
                    If it does not exist, please create this directory, and ensure THM - Organizer has write access to it.
                    <br/>
                    Failure to do so will prevent THM - Organizer from being able use images.
					<?php
				}
				?>
            </div>
        </fieldset>
		<?php
	}

	/**
	 * Method returning the administrative compontent path
	 *
	 * @return  string  path to the component's sql directory
	 */
	private function SQLPath()
	{
		return JPATH_SITE . '/administrator/components/com_thm_organizer/sql';
	}

	/**
	 * Creates the directory for images used by the component
	 *
	 * @return  boolean true if the directory exists, otherwise false
	 */
	private function createImageDirectory()
	{
		$exists = JFolder::exists(JPATH_SITE . '/images/thm_organizer');
		if ($exists)
		{
			return true;
		}

		return JFolder::create(JPATH_SITE . '/images/thm_organizer');
	}

	/**
	 * Method to uninstall the component
	 *
	 * @param object $parent the class calling this method
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function uninstall($parent)
	{
		$dirDeleted = JFolder::delete(JPATH_SITE . '/images/thm_organizer');
		if (!$dirDeleted)
		{
			echo JText::_('The directory located at "/images/thm_organizer" could not be removed.');
		}
	}

	/**
	 * Provides an output once Joomla! has finished the update process.
	 *
	 * @param Object $parent JInstallerComponent
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$logoURL     = 'media/com_thm_organizer/images/thm_organizer.png';
		$licenseLink = '<a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>';
		$version     = (string) $parent->get('manifest')->version;

		$dirSpan    = '';
		$imagePath  = '/images/thm_organizer';
		$dirCreated = $this->createImageDirectory();
		if (!$dirCreated)
		{
			$dirSpan .= '<span style="color:red" >' . JText::sprintf('COM_THM_ORGANIZER_MESSAGE_IMAGE_FOLDER_FAIL', $imagePath) . "</span>";
		}
		?>
        <div class="span5 form-vertical">
			<?php echo JHtml::_('image', $logoURL, JText::_('COM_THM_ORGANIZER')); ?>
            <br/>
            <p><?php echo JText::sprintf('COM_THM_ORGANIZER_MESSAGE_UPDATE', $version, $licenseLink) . ' ' . $dirSpan; ?></p>
            <br/>
        </div>
		<?php
	}
}
