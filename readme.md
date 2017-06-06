# TableDiff

Comparing DB tables data for Laravel.

## Installation

type in console:

```
composer require edujugon/tablediff
```

Register TableDiff service by adding it to the providers array.
```
'providers' => array(
        ...
        Edujugon\TableDiff\Providers\TableDiffServiceProvider::class
    )
```

Let's add the Alias facade, add it to the aliases array.
```
'aliases' => array(
        ...
        'TableDiff' => Edujugon\TableDiff\Facades\TableDiff::class,
    )
```

##  Usage samples

Instance the main TableDiff class:

```php
$diff = new \Edujugon\TableDiff\TableDiff();
```

Add tables to be compared

```php
$diff->tables('base_table', 'merge_table');
```

Set the pivots

```php
$diff->pivots('base_table_pivot', 'merge_table_pivot');
```

If the columns have the same name you could do it like follows:

```php
$diff->pivot('pivot_name');
```

Now, we can run the comparison and get the report
 
```php
$report = $diff->run()->withReport();
```

Of course, all those methods can be chained

```php
$report = $diff->tables('base_table','merge_table')
            ->pivot('pivot_name')
            ->run()
            ->withReport();
```

> Notice if you don't use `column` method, it will look for all columns with same name in both tables.

### Merging

The simplest way yo do a merge is like follows 

```php
$diff->tables('base_table','merge_table')
    ->pivot('id')
    ->column('column_to_update')
    ->merge();
```

The above code snippet will update the `column_to_update` column values from base_table 
with the `column_to_update` column values of merge_table in matched ids.
  
> Notice that `merge` method will update the matched records and also add those records that are new for base table.

Just merging matched records

```php
diff->tables('base_table','merge_table')
    ->pivot('id')
    ->column('column_to_update')
    ->mergeMatched();
```

Now, let's insert the new records.

```php
diff->tables('base_table','merge_table')
    ->pivot('id')
    ->column('column_to_update')
    ->mergeUnMatched();
```

`merge`, `mergeMatched` and `mergeUnMatched` methods accept callbacks before doing the merge and just after each update.


Before callback is perfect for casting data like. It takes the data which will be added.

```php
$ddiff->tables('base_table','merge_table')
            ->pivot('id')
            ->column('column_to_update')
            ->merge(null,function(&$data){
                // HERE your code
                $data->column_to_update = (float) $data->column_to_update;
            });
```

The first callback is call each time the db is updated. It takes the collection to be updated and the data with the new values 
 
```php
$diff->tables('base_table','merge_table')
    ->pivot('id')
    ->column('column_to_update')
    ->merge(function($collection,$data){
        //HERE your code
    });
```

In case of `mergeUnMatched`, the first callback takes the new elements to be added and is called for each chunk (By default 10)

```php
$diff->tables('base_table','merge_table')
    ->pivot('id')
    ->column('column_to_update')
    ->mergeUnMatched(function($list){
        //HERE your code
    });
```