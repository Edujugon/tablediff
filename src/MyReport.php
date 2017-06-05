<?php
/**
 * Project: TableDiff.
 * User: Edujugon
 * Email: edujugon@gmail.com
 * Date: 31/5/17
 * Time: 21:14
 */
namespace Edujugon\TableDiff;



namespace Edujugon\TableDiff;


use Illuminate\Support\Collection;

class MyReport
{

    /**
     * Collection of records that have matched (base table list)
     *
     * @var Collection
     */
    protected $matched;

    /**
     * Collection of records that haven't matched (merge table list)
     *
     * @var Collection
     */
    protected $unmatched;

    /**
     * Amount of updated records by matching after the merge
     *
     * @var int
     */
    protected $updatedRecords = 0;

    /**
     * Amount of inserted records
     *
     * @var int
     */
    protected $insertedRecords = 0;

    /**
     * Object with base table columns as properties which are arrays of key value pairs.
     * The array's key is the primary key of the base table record and
     * the arrays's value is an array with the old value and the new value.
     *
     * @var \stdClass
     */
    protected $diff;


    /**
     * Instance of MyReport
     *
     */
    function __construct()
    {
        $this->matched = collect();
        $this->unmatched = collect();
        $this->diff = new \stdClass();
    }

    /**
     * Add updated records
     *
     * @param int $amount
     * @return int
     */
    public function addUpdatedRecords($amount)
    {
        return $this->updatedRecords += $amount;
    }

    /**
     * Add inserted new records
     *
     * @param int $amount
     * @return int
     */
    public function addInsertedRecords($amount)
    {
        return $this->insertedRecords += $amount;
    }

    /**
     * Count updated records
     *
     * @return int
     */
    public function updatedRecords()
    {
        return $this->updatedRecords;
    }

    /**
     * Count inserted new records
     *
     * @return int
     */
    public function insertedRecords()
    {
        return $this->insertedRecords;
    }

    /**
     * Get matched collection from base table
     *
     * @return Collection
     */
    public function matched()
    {
        return $this->matched;
    }

    /**
     * Get unmatched collection from merge table
     *
     * @return Collection
     */
    public function unmatched()
    {
        return $this->unmatched;
    }

    /**
     * Get the differences between base and merge tables
     *
     * @return \stdClass
     */
    public function diff()
    {
        return $this->diff;
    }

    /**
     * Count amount of elements per property.
     * 
     * @return array
     */
    public function count()
    {
        $total = [];
        foreach (get_object_vars($this->diff()) as $key => $prop)
        {
            $total[$key] = count($this->diff->$key);
        }

        return $total;
    }
}