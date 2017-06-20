<?php

namespace Edujugon\TableDiff;


use Edujugon\TableDiff\Events\MergeDone;
use Edujugon\TableDiff\Exceptions\TableDiffException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class TableDiff
{

    /**
     * Table names
     *
     * @var array
     */
    protected $tables = [];

    /**
     * Base table - Main table
     * @var string
     */
    protected $baseTable = '';

    /**
     * Base table records as Collection.
     *
     * @var Collection
     */
    protected $baseCollection;


    /**
     * Table to be merged
     *
     * @var string
     */
    protected $mergeTable = '';

    /**
     * Merge table records as Collection
     *
     * @var Collection
     */
    protected $mergeCollection;


    /**
     * Associative array with base table pivot => merge table pivot
     * The key is the base table pivot and the value is the merge table pivot
     *
     * @var array
     */
    protected $pivots = [];

    /**
     * Associative array with base table columns => merge table columns
     * The key is the base column and the value is the merge table column
     *
     * If empty, it will search by column name matching.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Object with columns as properties.
     * each property will be an array with old and new values. (oldValue => newValue)
     *
     * @var MyReport
     */
    protected $report;


    /**
     * Primary Key. this column won't be overwritten except if passed as column
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Event payload
     *
     * @var array
     */
    protected $eventPayload = [];

    //
    //API METHODS
    //

    /**
     * Set table names
     *
     * @param string $baseTable
     * @param string $mergeTable
     * @return $this
     */
    public function tables($baseTable, $mergeTable)
    {
        $this->validateTables([$baseTable, $mergeTable]);

        $this->setTableNameValues([$baseTable, $mergeTable]);

        return $this;
    }

    /**
     * Set pivot columns
     *
     * @param string $basePivot
     * @param string $mergePivot
     * @return $this
     */
    public function pivots($basePivot, $mergePivot)
    {
        return $this->multiPivots([$basePivot => $mergePivot]);

    }

    /**
     * Assign multi pivots.
     * Passed associative array with basePivot => mergePivot
     *
     * @param array $pivots
     * @return $this
     */
    public function multiPivots(array $pivots)
    {

        $this->validateColumns($pivots);

        $this->setPivots($pivots);

        return $this;
    }

    /**
     * Set same value for base and merge table pivots
     *
     * @param string $pivot
     * @return $this
     */
    public function pivot($pivot)
    {
        return $this->pivots($pivot, $pivot);
    }

    /**
     * Load collections from tables
     * Get new elements from merge table and base table.
     * Get differences between both tables.
     *
     * @return $this
     */
    public function run()
    {
        $this->report = new MyReport();

        $this->passRequirements('run', ['baseTable', 'mergeTable', 'pivots']);

        $this->loadCollections();

        $this->loadMatchUnMatchedCollection();

        $this->initReport();

        return $this;
    }

    /**
     * Set same value for base and merge table column
     *
     * @param string $name
     * @return $this
     */
    public function column($name)
    {
        return $this->columns([$name => $name]);
    }

    /**
     * Load the columns of base table and merge table
     *
     * base table column1 => merge table column 10
     * base table column20 => merge table column 4
     * ...
     *
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns)
    {
        $this->columns = $columns;

        $this->validateColumns($columns);

        return $this;
    }

    /**
     * Get the report
     *
     * @return MyReport
     */
    public function withReport()
    {
        return $this->report;
    }

    /**
     * Get the report
     *
     * @return MyReport
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Get basic report.
     * Name of the column and amount of changes.
     *
     * @return array
     */
    public function withBasicReport()
    {
        return $this->basicReport();
    }

    /**
     * Alias for WithBasicReport
     *
     * @return array
     */
    public function getBasicReport()
    {
        return $this->basicReport();
    }

    /**
     * Update Base table items with Merge table values.
     * Also Insert new values from merge table to base table.
     *
     * @param callable $callback
     * @param callable $preCallBack
     * @return $this
     */
    public function merge($callback = null,$preCallBack = null)
    {

        $this->forgotRun();

        $this->mergeMatched($callback,$preCallBack);
        $this->mergeUnMatched();

        return $this;
    }

    /**
     * Insert new values that are in merge table but not in base table.
     *
     * @param callable $callback
     * @param callable $preCallback
     * @return $this
     */
    public function mergeUnMatched($callback = null,$preCallback = null)
    {
        $this->forgotRun();

        $this->createNewColumnsInBaseTable();

        $this->insertUnMatched($callback,$preCallback);

        Event::fire(new MergeDone('unmatched',$this->report,$this->eventPayload));


        return $this;
    }

    /**
     * Update Base table values with Matched Merge table values.
     * !Notice it won't update the new items found in merge that are not in base table.
     *
     * @param callable $callback
     * @param callable $preCallback
     * @return $this
     */
    public function mergeMatched($callback = null,$preCallback = null)
    {
        $this->forgotRun();

        $this->createNewColumnsInBaseTable();

        $this->updateBaseTable($callback,$preCallback);

        Event::fire(new MergeDone('matched',$this->report,$this->eventPayload));

        return $this;
    }


    /**
     * Set the extra payload for the event
     *
     * @param array $payload
     * @return $this
     */
    public function eventPayload($payload)
    {
        $this->eventPayload = $payload;

        return $this;
    }

    /**
     * Get the event payload
     *
     * @return array
     */
    public function getEventPayload()
    {
        return $this->eventPayload;
    }

    /**
     * Set the primary key of the base table.
     *
     * @param $key
     * @return $this
     */
    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    //
    //PRIVATE METHODS
    //


    /**
     * Execute run method if forgotten
     */
    private function forgotRun()
    {
        //If no executed run method then run it now.
        if (!$this->mergeCollection && !$this->baseCollection)
            $this->run();
    }

    /**
     * Set associative properties.
     *
     * @param array $pivots
     */
    private function setPivots(array $pivots)
    {
        $this->pivots = $pivots;
    }


    /**
     * Get an array of column's name and amount of changes.
     *
     * @return array
     */
    private function basicReport()
    {
        return $this->report->count();
    }

    /**
     * Create columns in a table if columns don't exist
     *
     */
    private function createNewColumnsInBaseTable()
    {

        Schema::table($this->baseTable, function (Blueprint $table) {

            //If no associative columns, the use same column name as merge column.
            if (empty($this->columns)) {
                $columns = Schema::getColumnListing($this->mergeTable);

                foreach ($columns as $key) {
                    if (!Schema::hasColumn($table->getTable(), $key)) {
                        $table->text($key)->nullable();
                    }
                }
                //Otherwise create only the table columns in baseTable which appears in array.
            } else {
                foreach ($this->columns as $baseColumn => $mergeColumn) {
                    if (!Schema::hasColumn($table->getTable(), $baseColumn)) {
                        $table->text($baseColumn)->nullable();
                    }
                }
            }

        });
    }


    /**
     * Update values based on the matched collection
     *
     * @param callable $callback
     * @param callable $preCallback
     */
    private function updateBaseTable($callback = null,$preCallback = null)
    {
        foreach ($this->mergeCollection as $key => $item) {
            $query = DB::table($this->baseTable);

            foreach ($this->pivots as $basePivot => $mergePivot) {
                $query = $query->whereIn($basePivot, [$item->$mergePivot]);
            }

            if (!in_array($this->primaryKey, $this->columns) && !in_array($this->primaryKey, $this->pivots))
                unset($item->{$this->primaryKey});

            if (!empty($this->columns)) {

                $newItem = new \stdClass();

                foreach ($this->columns as $baseColumn => $mergeColumn) {
                    $newItem->$baseColumn = $item->$mergeColumn;
                }

                $item = $newItem;
            }

            if(is_callable($preCallback))
                $preCallback($item);

            $updated = $query->update(get_object_vars($item));

            if($updated)
            {
                $this->report->addUpdatedRecords($updated);

                $collection = $query->get();

                if(is_callable($callback))
                    $callback($collection,$item);

            }else{

                //Remove from the report those values that haven't been updated

                $noUpdated = $query->get();

                $diff = $this->getReport()->diff();

                $noUpdated->each(function ($baseItem) use($diff){

                    foreach ($diff as $prop => $el){
                        if(array_key_exists($baseItem->{$this->primaryKey},$el)){
                            unset($diff->{$prop}[array_keys($el)[0]]);

                            if(empty($diff->{$prop})){
                                unset($diff->{$prop});
                            }
                        }
                    }

                });
            }
        }

    }

    /**
     * Insert new elements into base table
     *
     * @param callable $callback
     * @param null $preCallback
     * @param int $chunk
     */
    private function insertUnMatched($callback = null,$preCallback = null,$chunk = 10)
    {

        $this->report->unmatched()->chunk($chunk)->each(function ($collect) use($callback,$preCallback) {
            $newElements = $collect->map(function ($item,$preCallback) {

                //Unset primary key property because it can create Integrity constraint violation: Duplicate ID
                if (!in_array($this->primaryKey, $this->columns) && !in_array($this->primaryKey, $this->pivots))
                    unset($item->{$this->primaryKey});

                if (!empty($this->columns)) {
                    $newItem = new \stdClass();
                    foreach ($this->columns as $baseColumn => $mergeColumn) {
                        $newItem->$baseColumn = $item->$mergeColumn;
                    }

                    $item = $newItem;
                }

                if(is_callable($preCallback))
                    $preCallback($item);

                return get_object_vars($item);

            });

            if (!$newElements->isEmpty()){
                $inserted = DB::table($this->baseTable)->insert($newElements->toArray());

                if($inserted)
                {
                    $this->report->addInsertedRecords(count($newElements));

                    if(is_callable($callback))
                        $callback($newElements);
                }

            }

        });

    }

    /**
     * Start reporting process
     */
    private function initReport()
    {
        $this->report->matched()->each(function ($item) {

            $newElement = $this->findMeIn($this->mergeCollection, $item);

            //Unset primary key property if it's not in associative pivots because it can create Integrity constraint violation: Duplicate Primary key
            if (!in_array($this->primaryKey, $this->columns) && !in_array($this->primaryKey, $this->pivots))
                unset($newElement->{$this->primaryKey});

            //If found element
            if ($newElement)
                $this->fillReport($newElement, $item);
        });
    }

    /**
     * get an item from a passed collection where match with the passed item
     * All based on the associative pivot list
     *
     * @param Collection $collection
     * @param object $search
     * @return bool|object
     */
    private function findMeIn($collection, $search)
    {
        foreach ($collection as $key => $item) {
            $found = true;
            foreach ($this->pivots as $basePivot => $mergePivot) {
                if ($search->$basePivot != $item->$mergePivot) {
                    $found = false;
                    continue 2;
                }
            }

            if ($found) {
                return $item;
            }

        }

        return false;
    }

    /**
     * Fill Report Property.
     *
     * @param $newElement
     * @param $oldElement
     */
    private function fillReport($newElement, $oldElement)
    {

        if($newElement !== $oldElement){

            // It will search by column name matching
            if(empty($this->columns))
                $this->allColumns($newElement,$oldElement);
            else
                $this->byColumns($newElement,$oldElement);
        }
    }

    /**
     * Get value from base table column and value from merge table column
     * Based on the associative array with columns.
     *
     * @param $newElement
     * @param $oldElement
     */
    private function byColumns($newElement, $oldElement)
    {

        foreach ($this->columns as $baseColumn => $mergeColumn){

            if(!property_exists($oldElement,$baseColumn)){

                $this->report->diff()->$baseColumn[$oldElement->{$this->primaryKey}] = ['null' => $newElement->$mergeColumn];

                continue;
            }

            if($newElement->$mergeColumn != $oldElement->$baseColumn){

                $this->report->diff()->$baseColumn[$oldElement->{$this->primaryKey}] = [$oldElement->$baseColumn => $newElement->$mergeColumn];
            }

        }
    }

    /**
     * Based on column name matching
     *
     * @param $newElement
     * @param $oldElement
     */
    private function allColumns($newElement, $oldElement)
    {

        foreach (get_object_vars($newElement) as $key => $newValue){

            if(!property_exists($oldElement,$key)){
                $this->report->diff()->$key[$oldElement->{$this->primaryKey}] = ['null' => $newValue];

                continue;
            }

            if($newValue != $oldElement->$key)
                $this->report->diff()->$key[$oldElement->{$this->primaryKey}] = [$oldElement->$key => $newValue];
        }
    }


    /**
     * Create matched and unmatched collections
     *
     * @return void
     */
    private function loadMatchUnMatchedCollection()
    {
        foreach ($this->mergeCollection as $key => $item) {
            $search = $this->baseCollection;
            foreach ($this->pivots as $basePivot => $mergePivot) {
                $search = $search->whereIn($basePivot, $item->$mergePivot);
            }

            $found = $search->first();

            if (!$found) {
                $this->report->unmatched()->push($item);
            } else {
                $this->report->matched()->push($found);
            }

        }
    }

    /**
     *Load Collections.
     */
    private function loadCollections()
    {
        $this->mergeCollection = $this->loadCollection($this->mergeTable);
        $this->baseCollection = $this->joinWhereIn(DB::table($this->baseTable))->get();

    }


    /**
     * Add whereIn closures to the DB Query based on associative pivots.
     *
     * @param $query
     * @return mixed
     */
    private function joinWhereIn($query)
    {
        foreach ($this->pivots as $basePivot => $mergePivot) {
            $query = $query->whereIn($basePivot, $this->mergeCollection->pluck($mergePivot)->toArray());
        }

        return $query;
    }

    /**
     * Get collection from a passed table name.
     *
     * @param $tableName
     * @return mixed
     */
    private function loadCollection($tableName)
    {
        return DB::table($tableName)->get();
    }

    /**
     * @param $array
     */
    private function setTableNameValues($array)
    {
        $this->tables = $array;

        $this->baseTable = $array[0];
        $this->mergeTable = $array[1];
    }


    /**
     * Check table names exist
     *
     * @param array $names
     */
    private function validateTables($names)
    {
        foreach ($names as $name) {
            $this->tableExist($name);
        }

    }

    /**
     * Check columns exist
     *
     * @param array $columns
     */
    private function validateColumns($columns)
    {
        foreach ($columns as $baseColumn => $mergeColumn) {
            $this->existsInTable($this->mergeTable, $mergeColumn);
            $this->existsInTable($this->baseTable, $baseColumn);
        }

    }

    /**
     * Confirm pivot name exists in tables columns
     *
     * @param $table
     * @param $pivot
     * @return bool
     * @throws TableDiffException
     */
    private function existsInTable($table, $pivot)
    {
        $this->passRequirements('pivots', ['baseTable', 'mergeTable']);

        if (!Schema::hasColumn($table, $pivot))
            throw new TableDiffException('Column not Found');

        return true;

    }

    /**
     * Check if the table exists in the schema
     * If no exists, throw an exception.
     *
     * @param $name
     * @return $this
     * @throws TableDiffException
     */
    private function tableExist($name)
    {
        if (!Schema::hasTable($name))
            throw new TableDiffException("Table '$name' not found");
    }


    /**
     * Check min requirements for a method
     *
     * @param string $method
     * @param array $requirements
     * @throws TableDiffException
     */
    protected function passRequirements($method = null, $requirements)
    {
        $unProvided = $this->minRequiredData($requirements);
        $message = $method ? "Required data for '$method' method: " : "Required data: ";

        if ($unProvided !== true)
            throw new TableDiffException($message . implode(', ', $unProvided));
    }

    /**
     * Check if the data has been provided.
     *
     * @param mixed $data
     * @return bool|array
     */
    protected function minRequiredData($data)
    {
        $data = is_array($data) ? $data : func_get_args();

        $noProvided = [];
        foreach ($data as $prop) {
            if (!$this->$prop)
                $noProvided[] = $prop;
        }

        if (empty($noProvided))
            return true;

        return $noProvided;
    }
}