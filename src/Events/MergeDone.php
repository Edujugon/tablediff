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
    private $payload;

    /**
     * MergeDone constructor.
     *
     * @param string $type
     * @param MyReport $report
     * @param array $payload
     */
    function __construct($type,$report,$payload = [])
    {
        $this->report = $report;
        $this->type = $type;
        $this->extra = $payload;
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

    /**
     * Get the payload provided by the user
     *
     * @return array
     */
    function getPayload()
    {
        return $this->payload;
    }
}