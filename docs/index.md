# TableDiff Laravel Package

Laravel package for comparing Database Tables.

##  Installation

##### Type in console:

```
composer require edujugon/laravel-tablediff
```

##### Register the TableDiff service by adding it to the providers array.

```
'providers' => array(
        ...
        Edujugon\TableDiff\Providers\TableDiffServiceProvider::class
    )
```

##### Let's add the Alias facade, add it to the aliases array.

```
'aliases' => array(
        ...
        'TableDiff' => Edujugon\TableDiff\Facades\TableDiff::class,
    )
```

##  API Documentation

[Full API List](https://edujugon.github.io/laravel-tablediff/API-Documentation)

##  Usage samples

Let's get a report of the differences between 2 tables.

```php
$report = TableDiff::tables('base_table_name','new_table')
                    ->pivots('base_table_primary_key','new_table_primary_key')
                    ->run()
                    ->withReport();
```

The above code will returns an object with the "base_table_name" columns as properties which are arrays.
Where the "base_table_name" column value as the array key and the "new_table" column value as the array value.  