<?php
/**
 * Project: TableDiff.
 * User: Edujugon
 * Email: edujugon@gmail.com
 * Date: 7/6/17
 * Time: 9:34
 */

namespace Edujugon\TableDiff\Events;


use Edujugon\TableDiff\MyReport;

class MergeDone
{

    /**
     * Merge report
     *
     * @var MyReport
     */
    protected $report;

    /**
     * MergeDone constructor.
     *
     * @param $report
     */
    function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * Get report
     *
     * @return MyReport
     */
    function getReport()
    {
        return $this->report;
    }
}