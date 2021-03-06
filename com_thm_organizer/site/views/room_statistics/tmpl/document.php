<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
jimport('phpexcel.library.PHPExcel');

/**
 * Class generates the room statistics XLS file.
 */
class THM_OrganizerTemplateRoom_Statistics_XLS
{

    public $endDate;

    public $endDoW;

    private $headerFill;

    private $lightBorder;

    public $metaData;

    private $rightBorder;

    public $rooms;

    public $roomTypes;

    public $roomTypeMap;

    public $roomData;

    public $startDate;

    public $startDoW;

    /**
     * THM_OrganizerTemplateRoom_Statistics_XLS constructor.
     *
     * @param object &$model the model containing the data for the room statistics
     */
    public function __construct(&$model)
    {
        $this->endDate  = $model->endDate;
        $this->endDoW   = $model->endDoW;
        $this->metaData = $model->metaData;

        $this->rooms = [];
        foreach ($model->rooms as $roomName => $roomData) {
            $this->rooms[$roomData['id']] = $roomName;
        }

        $this->roomTypes   = $model->roomTypes;
        $this->roomTypeMap = $model->roomTypeMap;
        $this->roomData    = $model->roomData;
        $this->startDate   = $model->startDate;
        $this->startDoW    = $model->startDoW;
        unset ($model);

        $this->spreadSheet = new PHPExcel();

        $userName    = JFactory::getUser()->name;
        $startDate   = THM_OrganizerHelperComponent::formatDate($this->startDate);
        $endDate     = THM_OrganizerHelperComponent::formatDate($this->endDate);
        $description = sprintf(JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_DESCRIPTION'), $startDate, $endDate);
        $this->spreadSheet->getProperties()->setCreator("THM Organizer")
            ->setLastModifiedBy($userName)
            ->setTitle(JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_TITLE'))
            ->setDescription($description);

        $this->headerFill = [
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'DFE5E6']
        ];

        $this->rightBorder = [
            'left'   => ['style' => PHPExcel_Style_Border::BORDER_NONE],
            'right'  => [
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => ['rgb' => '394A59']
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'top'    => ['style' => PHPExcel_Style_Border::BORDER_NONE]
        ];

        $this->lightBorder = [
            'left'   => ['style' => PHPExcel_Style_Border::BORDER_NONE],
            'right'  => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_HAIR,
                'color' => ['rgb' => 'DFE5E6']
            ],
            'top'    => ['style' => PHPExcel_Style_Border::BORDER_NONE]
        ];

        $this->spreadSheet->getDefaultStyle()->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $this->spreadSheet->getDefaultStyle()->applyFromArray(array(
            'font' => [
                'name'  => 'arial',
                'size'  => 12,
                'color' => ['rgb' => '394A59']
            ]
        ));

        $this->addSummarySheet();
        $this->addWeekSheet();
        $this->addGlossarySheet();

        // Reset the active sheet to the first item
        $this->spreadSheet->setActiveSheetIndex(0);
    }

    /**
     * Creates a glossary sheet
     *
     * @return void
     */
    private function addGlossarySheet()
    {
        $this->spreadSheet->createSheet();
        $this->spreadSheet->setActiveSheetIndex(2);
        $this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
        $this->spreadSheet->getActiveSheet()->setTitle(JTEXT::_('COM_THM_ORGANIZER_GLOSSARY'));
        $this->spreadSheet->getActiveSheet()->mergeCells("A1:F1");
        $this->spreadSheet->getActiveSheet()->setCellValue('A1', JTEXT::_('COM_THM_ORGANIZER_GLOSSARY'));
        $this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

        $this->spreadSheet->getActiveSheet()->mergeCells("A3:B3");
        $this->spreadSheet->getActiveSheet()->setCellValue('A3', JText::_('COM_THM_ORGANIZER_COLUMN_EXPLANATIONS'));
        $this->spreadSheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);
        $this->spreadSheet->getActiveSheet()->setCellValue("A4", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->setCellValue("B4", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TIP'));
        $this->spreadSheet->getActiveSheet()->setCellValue("A5", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->setCellValue("B5", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TIP'));
        $this->spreadSheet->getActiveSheet()->setCellValue("A6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->setCellValue("B6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TIP'));
        $this->spreadSheet->getActiveSheet()->setCellValue("A7", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->setCellValue("B7", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TIP'));

        $this->spreadSheet->getActiveSheet()->mergeCells("A9:B9");
        $this->spreadSheet->getActiveSheet()->setCellValue('A9', JText::_('COM_THM_ORGANIZER_ROOM_TYPES'));
        $this->spreadSheet->getActiveSheet()->getStyle('A9')->getFont()->setSize(14);
        $rowNumber = 9;

        foreach ($this->roomTypes as $typeData) {
            $rowNumber++;
            $this->spreadSheet->getActiveSheet()->setCellValue("A$rowNumber", $typeData['name']);
            $this->spreadSheet->getActiveSheet()->setCellValue("B$rowNumber", $typeData['description']);
        }

        $this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    }

    /**
     * Creates a room data summary row
     *
     * @param int   $rowNo     the row number
     * @param int   $roomID    the room id
     * @param array $weeksData the utilization data grouped by week number
     *
     * @return array and array with the total and adjusted totals for the room being iterated
     */
    private function addSummaryDataRow($rowNo, $roomID, $weeksData)
    {
        $this->spreadSheet->getActiveSheet()->setCellValue("A{$rowNo}", $this->rooms[$roomID]);
        $this->spreadSheet->getActiveSheet()->getStyle("A{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
        $roomType = (empty($this->roomTypeMap[$roomID]) or empty($this->roomTypes[$this->roomTypeMap[$roomID]])) ?
            '' : $this->roomTypes[$this->roomTypeMap[$roomID]]['name'];
        $this->spreadSheet->getActiveSheet()->setCellValue("B{$rowNo}", $roomType);
        $this->spreadSheet->getActiveSheet()->getStyle("B{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

        $total         = 0;
        $adjustedTotal = 0;
        $use           = 0;
        $adjustedUse   = 0;

        foreach ($weeksData as $weekData) {
            $total         += $weekData['total'];
            $adjustedTotal += $weekData['adjustedTotal'];
            $use           += $weekData['use'];
            $adjustedUse   += $weekData['adjustedUse'];
        }

        $this->spreadSheet->getActiveSheet()->setCellValue("C{$rowNo}", $use);
        $this->spreadSheet->getActiveSheet()->getStyle("C{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

        $sumValue = empty($total) ? 0 : $use / $total;
        $this->spreadSheet->getActiveSheet()->setCellValue("D{$rowNo}", $sumValue);
        $this->spreadSheet->getActiveSheet()->getStyle("D{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("D{$rowNo}")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);

        $this->spreadSheet->getActiveSheet()->setCellValue("E{$rowNo}", $adjustedUse);
        $this->spreadSheet->getActiveSheet()->getStyle("E{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

        $adjSumValue = empty($adjustedTotal) ? 0 : $adjustedUse / $adjustedTotal;
        $this->spreadSheet->getActiveSheet()->setCellValue("F{$rowNo}", $adjSumValue);
        $this->spreadSheet->getActiveSheet()->getStyle("F{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("F{$rowNo}")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);

        return ['total' => $total, 'adjustedTotal' => $adjustedTotal];
    }

    /**
     * Creates a summary sheet
     *
     * @return void
     */
    private function addSummarySheet()
    {
        $this->spreadSheet->setActiveSheetIndex(0);
        $this->spreadSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight('18');
        $this->spreadSheet->getActiveSheet()->setTitle(JTEXT::_('COM_THM_ORGANIZER_SUMMARY'));
        $this->spreadSheet->getActiveSheet()->mergeCells("A1:H1");
        $title = JText::_('COM_THM_ORGANIZER_SUMMARY') . ' - ' . $this->startDate . ' ' . JText::_('COM_THM_ORGANIZER_UNTIL') . ' ' . $this->endDate;
        $this->spreadSheet->getActiveSheet()->setCellValue('A1', $title);
        $this->spreadSheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

        // TableStartRow is
        $headerRow = 6;
        $firstRow  = $headerRow + 1;
        $lastRow   = $headerRow;

        foreach ($this->roomData as $roomID => $roomData) {
            $lastRow++;
            $totals = $this->addSummaryDataRow($lastRow, $roomID, $roomData['weeks']);
        }

        $this->spreadSheet->getActiveSheet()->getStyle("B3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("C3", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("C3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("D3", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("D3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("E3", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("E3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("F3", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("F3")->applyFromArray(['fill' => $this->headerFill]);


        $this->spreadSheet->getActiveSheet()->setCellValue('A6', JText::_('COM_THM_ORGANIZER_NAME'));
        $this->spreadSheet->getActiveSheet()->getStyle("A6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue('B6', JText::_('COM_THM_ORGANIZER_ROOM_TYPE'));
        $this->spreadSheet->getActiveSheet()->getStyle("B6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("C6", JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("C6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("D6", JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("D6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("E6", JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("E6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setCellValue("F6", JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("F6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->setAutoFilter("A6:F{$lastRow}");

        $this->spreadSheet->getActiveSheet()->setCellValue('B4', JText::_('COM_THM_ORGANIZER_SUMMARY'));
        $this->spreadSheet->getActiveSheet()->getStyle("B4")->applyFromArray(['fill' => $this->headerFill]);

        $this->spreadSheet->getActiveSheet()->setCellValue('C4', "=SUBTOTAL(109,C{$firstRow}:C{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("C4")->applyFromArray(['borders' => $this->lightBorder]);

        $this->spreadSheet->getActiveSheet()
            ->setCellValue('D4', "=IF(C4=0,0,C4/(SUBTOTAL(102,C{$firstRow}:C{$lastRow})*{$totals['total']}))");
        $this->spreadSheet->getActiveSheet()->getStyle("D4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle('D4')->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);

        $this->spreadSheet->getActiveSheet()->setCellValue('E4', "=SUBTOTAL(109,E{$firstRow}:E{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("E4")->applyFromArray(['borders' => $this->lightBorder]);

        $this->spreadSheet->getActiveSheet()
            ->setCellValue('F4', "=IF(E4=0,0,E4/(SUBTOTAL(102,E{$firstRow}:E{$lastRow})*{$totals['adjustedTotal']}))");
        $this->spreadSheet->getActiveSheet()->getStyle("F4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle('F4')->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);


        $this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setWidth(11.5);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $this->spreadSheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);

        $this->spreadSheet->getActiveSheet()->getPageSetup()->setColumnsToRepeatAtLeft('A,B');
        $this->spreadSheet->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 6);
    }

    /**
     * Creates a room data summary row
     *
     * @param int   $rowNo     the row number
     * @param int   $roomID    the room id
     * @param array $weeksData the utilization data grouped by week number
     *
     * @return string the last column name
     */
    private function addWeekDataRow($rowNo, $roomID, $weeksData)
    {
        $this->spreadSheet->getActiveSheet(1)->setCellValue("A{$rowNo}", $this->rooms[$roomID]);
        $this->spreadSheet->getActiveSheet()->getStyle("A{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("B{$rowNo}",
            $this->roomTypes[$this->roomTypeMap[$roomID]]['name']);
        $this->spreadSheet->getActiveSheet()->getStyle("B{$rowNo}")->applyFromArray(['borders' => $this->rightBorder]);

        $total         = 0;
        $adjustedTotal = 0;
        $use           = 0;
        $adjustedUse   = 0;

        $column = 'B';
        foreach ($weeksData as $weekData) {
            $total         += $weekData['total'];
            $adjustedTotal += $weekData['adjustedTotal'];
            $use           += $weekData['use'];
            $adjustedUse   += $weekData['adjustedUse'];

            ++$column;
            $this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $weekData['use']);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

            ++$column;
            $value = empty($weekData['total']) ? 0 : $weekData['use'] / $weekData['total'];
            $this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $value);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

            ++$column;
            $this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $weekData['adjustedUse']);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->lightBorder]);

            ++$column;
            $adjustedValue = empty($weekData['adjustedTotal']) ? 0 : $weekData['adjustedUse'] / $weekData['adjustedTotal'];
            $this->spreadSheet->getActiveSheet()->setCellValue("{$column}{$rowNo}", $adjustedValue);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->applyFromArray(['borders' => $this->rightBorder]);
            $this->spreadSheet->getActiveSheet()->getStyle("{$column}{$rowNo}")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        }

        return $column;
    }

    /**
     * Adds a header group consisting of a title row of 4 merged cells and a header row consisting of 4 header cells
     *
     * @param string $startColumn   the first column
     * @param string $groupTitle    the group header title
     * @param int    $firstRow      the first data row of the table
     * @param int    $lastRow       the last data row of the table
     * @param int    $total         the maximum number of reservable blocks for the given week
     * @param int    $adjustedTotal the adjusted maximum number of reservable blocks for the given week
     *
     * @return string the column name currently iterated to
     */
    private function addWeekHeaderGroup($startColumn, $groupTitle, $firstRow, $lastRow, $total, $adjustedTotal)
    {
        ++$startColumn;
        $currentColumn = $startColumn;
        $totalColumn   = $currentColumn;
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}4",
            "=SUBTOTAL(109,{$currentColumn}{$firstRow}:{$currentColumn}{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_RAW_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        ++$currentColumn;
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet(1)
            ->setCellValue(
                "{$currentColumn}4",
                "=IF({$totalColumn}4=0,0,{$totalColumn}4/(SUBTOTAL(102,{$totalColumn}{$firstRow}:{$totalColumn}{$lastRow})*{$total}))"
            );
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_RAW_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        ++$currentColumn;
        $adjTotalColumn = $currentColumn;
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}4",
            "=SUBTOTAL(109,{$currentColumn}{$firstRow}:{$currentColumn}{$lastRow})");
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->lightBorder]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_WEIGHTED_UTIL_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        ++$currentColumn;
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}3",
            JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}3")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet(1)
            ->setCellValue(
                "{$currentColumn}4",
                "=IF({$adjTotalColumn}4=0,0,{$adjTotalColumn}4/(SUBTOTAL(102,{$adjTotalColumn}{$firstRow}:{$adjTotalColumn}{$lastRow})*{$adjustedTotal}))"
            );
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}4")->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$currentColumn}7",
            JText::_('COM_THM_ORGANIZER_WEIGHTED_PERCENT_TEXT'));
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}7")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet()->getColumnDimension($currentColumn)->setWidth(10);

        $this->spreadSheet->getActiveSheet(1)->mergeCells("{$startColumn}6:{$currentColumn}6");
        $this->spreadSheet->getActiveSheet(1)->setCellValue("{$startColumn}6", $groupTitle);
        $this->spreadSheet->getActiveSheet()->getStyle("{$startColumn}6")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("{$currentColumn}6")->applyFromArray(['borders' => $this->rightBorder]);

        return $currentColumn;
    }

    /**
     * Adds column headers to the sheet
     *
     * @return void
     */
    private function addWeekSheet()
    {
        $this->spreadSheet->createSheet();
        $this->spreadSheet->setActiveSheetIndex(1);
        $this->spreadSheet->getActiveSheet(1)->getDefaultRowDimension()->setRowHeight('18');
        $this->spreadSheet->getActiveSheet(1)->setTitle(JTEXT::_('COM_THM_ORGANIZER_BY_WEEK'));
        $this->spreadSheet->getActiveSheet(1)->mergeCells("A1:H1");
        $this->spreadSheet->getActiveSheet(1)->setCellValue('A1', JText::_('COM_THM_ORGANIZER_BY_WEEK'));
        $this->spreadSheet->getActiveSheet(1)->getStyle('A1')->getFont()->setSize(16);
        $this->spreadSheet->getActiveSheet()->getStyle("B3")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue('B4', JText::_('COM_THM_ORGANIZER_SUMMARY'));
        $this->spreadSheet->getActiveSheet()->getStyle("B4")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);

        $startRow = 6;
        $firstRow = $startRow + 1;
        $lastRow  = $startRow;
        foreach ($this->roomData as $roomID => $roomData) {
            $lastRow++;
            $lastColumn = $this->addWeekDataRow($lastRow, $roomID, $roomData['weeks']);
        }

        $currentColumn = 'B';

        foreach ($this->metaData['weeks'] as $weekData) {
            $startDate          = THM_OrganizerHelperComponent::formatDate($weekData['startDate']);
            $endDate            = THM_OrganizerHelperComponent::formatDate($weekData['endDate']);
            $groupTitle         = "$startDate - $endDate";
            $singleRoomTotal    = $weekData['total'] / count($this->roomData);
            $singleRoomAdjTotal = $weekData['adjustedTotal'] / count($this->roomData);
            $currentColumn      = $this->addWeekHeaderGroup($currentColumn, $groupTitle, $firstRow, $lastRow,
                $singleRoomTotal, $singleRoomAdjTotal);
        }

        $this->spreadSheet->getActiveSheet(1)->setCellValue('A7', JText::_('COM_THM_ORGANIZER_NAME'));
        $this->spreadSheet->getActiveSheet()->getStyle("A7")->applyFromArray(['fill' => $this->headerFill]);
        $this->spreadSheet->getActiveSheet()->getStyle("B6")->applyFromArray(['borders' => $this->rightBorder]);
        $this->spreadSheet->getActiveSheet(1)->setCellValue('B7', JText::_('COM_THM_ORGANIZER_ROOM_TYPE'));
        $this->spreadSheet->getActiveSheet()->getStyle("B7")->applyFromArray([
            'fill'    => $this->headerFill,
            'borders' => $this->rightBorder
        ]);
        $this->spreadSheet->getActiveSheet(1)->setAutoFilter("A7:{$lastColumn}{$lastRow}");

        $this->spreadSheet->getActiveSheet()->getColumnDimension('A')->setWidth('11.5');
        $this->spreadSheet->getActiveSheet()->getColumnDimension('B')->setWidth('18');

        $this->spreadSheet->getActiveSheet()->getPageSetup()->setColumnsToRepeatAtLeftByStartAndEnd('A', 'B');
        $this->spreadSheet->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 7);
    }

    /**
     * Outputs the generated Excel file
     *
     * @return void
     */
    public function render()
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->spreadSheet, 'Excel2007');
        ob_end_clean();
        header('Content-type: application/vnd.ms-excel');
        $docTitle = JApplicationHelper::stringURLSafe(JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_EXPORT_TITLE') . '_' . date('Ymd'));
        header("Content-Disposition: attachment;filename=$docTitle.xlsx");
        $objWriter->save('php://output');
        exit();
    }
}