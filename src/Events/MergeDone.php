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
     * Type of merge
     *
     * @var string
     */
    private $type;

    /**
     * MergeDone constructor.
     *
     * @param string $type
     * @param MyReport $report
     */
    function __construct($type,$report)
    {
        $this->report = $report;
        $this->type = $type;
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

    /**
     * Get type of report
     *
     * @return string
     */
    function getType()
    {
        return $this->type;
    }
}