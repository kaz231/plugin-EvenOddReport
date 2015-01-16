<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\EvenOddReporting;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugin\API as BaseAPI;

/**
 * API for plugin EvenOddReporting
 *
 * @method static API getInstance()
 */
class API extends BaseAPI
{
    /**
     * Another example method that returns a data table.
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @param bool $expanded
     * @throws \Exception
     * @return DataTable
     */
    public function getEvenAndOddVisits($idSite, $period, $date, $segment = false, $expanded = false)
    {
        /** @var DataTable $dataTable */
        $dataTable = Archive::getDataTableFromArchive(
            Archiver::EVEN_ODD_REPORTING_RECORD,
            $idSite,
            $period,
            $date,
            $segment,
            $expanded
        );
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->applyQueuedFilters();

        $columns = $dataTable->getFirstRow()->getColumns();

        return $dataTable;
    }
}
