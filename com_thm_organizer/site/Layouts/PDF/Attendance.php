<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\PDF;

use Organizer\Helpers;

/**
 * Displays a list of participant entries.
 */
class Attendance extends BaseLayout
{
	use CourseContext;

	private $headers;

	private $widths = [
		'index'      => 10,
		'name'       => 55,
		'department' => 25,
		'program'    => 85,
		'room'       => 15
	];

	/**
	 * Performs initial construction of the TCPDF Object.
	 *
	 * @param   int  $courseID  the id of the course for which the badges are valid
	 */
	public function __construct($courseID)
	{
		parent::__construct();

		$this->setCourse($courseID);

		$documentName = "$this->name - $this->term - " . Helpers\Languages::_('ORGANIZER_PARTICIPANTS');
		$this->setNames($documentName);
		$this->margins(10, 30, -1, 0, 8, 10);
		$this->setHeader();
		$this->showPrintOverhead(true);

		$this->headers = [
			'index'      => '#',
			'name'       => 'Name',
			'department' => Helpers\Languages::_('ORGANIZER_DEPARTMENT'),
			'program'    => Helpers\Languages::_('ORGANIZER_PROGRAM'),
			'room'       => Helpers\Languages::_('ORGANIZER_ROOM')
		];

		// Adjust for more information
		if ($this->fee)
		{
			$this->headers['paid'] = Helpers\Languages::_('ORGANIZER_PAID');
			$this->widths['name']  = 42;
			$this->widths['paid']  = 14;
			$this->widths['room']  = 14;
		}
	}

	/**
	 * Adds a new page to the document and creates the column headers for the table
	 *
	 * @return void
	 */
	private function addAttendancePage()
	{
		$this->AddPage();

		// create the column headers for the page
		$this->SetFillColor(210);
		$this->changeSize(10);
		$initial = true;
		foreach (array_keys($this->headers) as $column)
		{
			$border = [];
			if ($initial)
			{
				$border['BLRT'] = $this->border;
				$this->renderCell($this->widths[$column], 7, $this->headers[$column], self::CENTER, 'BLRT', 1);
				$initial = false;
				continue;
			}
			$border['BRT'] = $this->border;
			$this->renderCell($this->widths[$column], 7, $this->headers[$column], self::CENTER, 'BRT', 1);
		}
		$this->Ln();

		// reset styles
		$this->SetFillColor(255);
		$this->changeSize(8);
	}

	/**
	 * Renders the document.
	 *
	 * @param   array  $participants  the course participants
	 *
	 * @return void renders the document and closes the application
	 */
	public function fill($participants)
	{
		$this->addAttendancePage();

		$itemNo = 1;

		foreach ($participants as $participant)
		{
			// Get the starting coordinates for later use with borders
			$startX = $this->GetX();
			$startY = $this->GetY();

			$maxLength = 0;

			foreach (array_keys($this->headers) as $columnName)
			{
				switch ($columnName)
				{
					case 'index':
						$value = $itemNo;
						break;
					case 'name':
						$value = empty($participant['forename']) ?
							$participant['surname'] : "{$participant['surname']},  {$participant['forename']}";
						break;
					case 'department':
						$value = $participant['departmentName'];
						break;
					case 'program':
						$value = $participant['programName'];
						break;
					default:
						$value = '';
						break;
				}

				$length = $this->renderMultiCell($this->widths[$columnName], 5, $value);
				if ($length > $maxLength)
				{
					$maxLength = $length;
				}
			}

			// Reset for borders
			$this->changePosition($startX, $startY);

			foreach ($this->widths as $index => $width)
			{
				$border = $index === 'index' ? ['BLR' => $this->border] : ['BR' => $this->border];
				$this->renderMultiCell($width, $maxLength * 5, '', self::LEFT, $border);
			}

			$this->Ln();

			if ($this->getY() > 275)
			{
				$this->addAttendancePage();
			}

			$itemNo++;
		}
	}

	/**
	 * Set header items.
	 *
	 * @return void
	 */
	public function setHeader()
	{
		$header    = "$this->name $this->term";
		$subHeader = "{$this->campus} {$this->dates}";

		$this->setHeaderData('thm_logo_pdf_header.png', '55', $header, $subHeader, self::BLACK, self::WHITE);
		$this->setFooterData(self::BLACK, self::WHITE);
		parent::setHeader();
	}
}
