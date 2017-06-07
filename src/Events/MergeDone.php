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
     * Extra payload provided by user
     *
     * @var array
     */
    private $extra;

    /**
     * MergeDone constructor.
     *
     * @param string $type
     * @param MyReport $report
     * @param array $extra
     */
    function __construct($type,$report,$extra = [])
    {
        $this->report = $report;
        $this->type = $type;
        $this->extra = $extra;
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