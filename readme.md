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
$report = $diff->tables('base_table_pivot','merge_table_pivot')
            ->pivot('pivot_name')
            ->run()
            ->withReport();
```