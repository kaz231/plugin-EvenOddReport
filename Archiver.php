<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\EvenOddReporting;

use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Archiver as BaseArchiver;
use Zend_Db_Statement;

/**
 * Class Archiver
 * @package Piwik\Plugins\EvenOddReporting
 *
 * Archiver is class processing raw data into ready ro read reports.
 * It must implement two methods for aggregating daily reports
 * aggregateDayReport() and other for summing daily reports into periods
 * like week, month, year or custom range aggregateMultipleReports().
 *
 * For more detailed information about Archiver please visit Piwik developer guide
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Archiver
 *
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const EVEN_ODD_REPORTING_RECORD = "EvenOddReporting_EvenOddVisits";
    const EVEN_ODD_REPORTING_EVEN_RECORD_NAME = "EvenOddReporting_Even";
    const EVEN_ODD_REPORTING_ODD_RECORD_NAME = "EvenOddReporting_Odd";

    const EVEN_ODD_REPORTING_EVEN_METRIC = 'sum(case %s when 0 then 1 else 0 end) as `%s`';
    const EVEN_ODD_REPORTING_ODD_METRIC = 'sum(case %s when 1 then 1 else 0 end) as `%s`';

    const EVEN_ODD_REPORTING_IS_ODD_EXPRESSION = 'mod(hour(%s.visit_first_action_time), 2)';

    const EVEN_ODD_REPORTING_EVEN_METRIC_NAME = 'nb_even';
    const EVEN_ODD_REPORTING_ODD_METRIC_NAME = 'nb_odd';

    protected $maximumRowsInDataTable;

    public function __construct(ArchiveProcessor $processor)
    {
        parent::__construct($processor);
        $this->maximumRowsInDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_referrers'];
    }

    public function aggregateDayReport()
    {
        $logAggregator = $this->getLogAggregator();

        /** @var Zend_Db_Statement $stmt */
        $stmt = $logAggregator->queryVisitsByDimension(
            array(),
            false,
            $this->getAdditionalSelects(),
            array(
                self::EVEN_ODD_REPORTING_EVEN_METRIC_NAME,
                self::EVEN_ODD_REPORTING_ODD_METRIC_NAME
            )
        );

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray($stmt->fetch());

        $archiveProcessor = $this->getProcessor();
        $archiveProcessor->insertBlobRecord(self::EVEN_ODD_REPORTING_RECORD, $dataTable->getSerialized());
    }

    public function aggregateMultipleReports()
    {
        $this->getProcessor()->aggregateDataTableRecords(self::EVEN_ODD_REPORTING_RECORD);
    }

    /**
     * @return array
     */
    protected function getAdditionalSelects()
    {
        $isOddExpr = sprintf(self::EVEN_ODD_REPORTING_IS_ODD_EXPRESSION, LogAggregator::LOG_VISIT_TABLE);

        return array(
            sprintf(self::EVEN_ODD_REPORTING_EVEN_METRIC, $isOddExpr, self::EVEN_ODD_REPORTING_EVEN_METRIC_NAME),
            sprintf(self::EVEN_ODD_REPORTING_ODD_METRIC, $isOddExpr, self::EVEN_ODD_REPORTING_ODD_METRIC_NAME)
        );
    }
}
