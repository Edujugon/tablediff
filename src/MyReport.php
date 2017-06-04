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
     * Collection of records to be updated (base table)
     *
     * @var Collection
     */
    protected $updated;

    /**
     * Collection of records to be added (merge table)
     *
     * @var Collection
     */
    protected $added;

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
        $this->updated = collect();
        $this->added = collect();
        $this->diff = new \stdClass();
    }

    /**
     * Get a collection of records to be updated
     *
     * @return Collection
     */
    public function updated()
    {
        return $this->updated;
    }

    /**
     * Get a collection of records to be added
     *
     * @return Collection
     */
    public function added()
    {
        return $this->added;
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