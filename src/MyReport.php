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
     * Matched records (base table)
     *
     * @var Collection
     */
    protected $matched;

    /**
     * Unmatched records (merge table)
     *
     * @var Collection
     */
    protected $unMatched;

    /**
     * Collection
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
        $this->unMatched = collect();
        $this->diff = new \stdClass();
    }

    /**
     * Get matched records.
     *
     * @return Collection
     */
    public function matched()
    {
        return $this->matched;
    }

    /**
     * Get unmatched records
     *
     * @return Collection
     */
    public function unMatched()
    {
        return $this->unMatched;
    }

    /**
     * Get the differences between base and merge tables.
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