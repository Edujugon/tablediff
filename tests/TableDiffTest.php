<?php

class TableDiffTest extends Orchestra\Testbench\TestCase
{

    private $diff;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->diff = new Edujugon\TableDiff\TableDiff();
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/migrations'),
        ]);

        $this->withFactories(__DIR__.'/factories');

        $this->addRecords();
    }

    protected function addRecords()
    {
        factory(Edujugon\TableDiffTest\Models\MainArea::class,3)->create(['name' => 'district 1']);
        factory(Edujugon\TableDiffTest\Models\SubArea::class,5)->create(['name' => 'district 1']);
    }

    protected function getPackageProviders($app)
    {
        return [\Orchestra\Database\ConsoleServiceProvider::class,
            Edujugon\TableDiff\Providers\TableDiffServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'TableDiff' => 'Edujugon\TableDiff\Facades\TableDiff'
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        $app['config']->set('database.default', 'testing');
    }

    /** @test */
    public function tables_methods_return_TableDiff_instance()
    {
        $this->assertInstanceOf(\Edujugon\TableDiff\TableDiff::class,$this->diff->tables('main_areas', 'sub_areas'));
    }

    /** @test */
    public function method_pivot_returns_tablediff_instance()
    {
        $diff = $this->diff->tables('main_areas', 'sub_areas')->pivot('name');

        $this->assertInstanceOf(\Edujugon\TableDiff\TableDiff::class,$diff);
    }

    /** @test */
    public function value_change_after_merge()
    {
        $country = \Edujugon\TableDiffTest\Models\MainArea::first()->country;

        $this->diff->tables('main_areas', 'sub_areas')
            ->pivot('id')
            ->columns(['country' => 'country'])
            ->merge();
        $updatedCountry = \Edujugon\TableDiffTest\Models\MainArea::first()->country;

        $this->assertNotEquals($country, $updatedCountry);
    }

    /** @test */
    public function throw_an_exception_if_no_tables()
    {
        $this->expectExceptionMessage("Required data for 'pivots' method: baseTable, mergeTable");
        $this->diff->pivots('main_areas', 'sub_areas')->run();
    }

    /** @test */
    public function throw_an_exception_if_no_pivots()
    {
        $this->expectExceptionMessage("Required data for 'run' method: pivots");
        $this->diff->tables('main_areas', 'sub_areas')->run();
    }

    /** @test */
    public function get_report_of_changes()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->run()
            ->withReport();

        $this->assertObjectHasAttribute('country',$report->diff());
        $this->assertObjectHasAttribute('description',$report->diff());
        $this->assertObjectHasAttribute('city',$report->diff());
        $this->assertObjectNotHasAttribute('name',$report->diff());
        $this->assertInstanceOf(\Edujugon\TableDiff\MyReport::class,$report);
    }

    /** @test */
    public function using_multi_pivots()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->multiPivots(['id' => 'id','name' => 'name'])
            ->run()
            ->withReport();

        $this->assertObjectHasAttribute('country',$report->diff());
        $this->assertObjectHasAttribute('description',$report->diff());
        $this->assertObjectHasAttribute('city',$report->diff());
        $this->assertObjectNotHasAttribute('name',$report->diff());
        $this->assertInstanceOf(\Edujugon\TableDiff\MyReport::class,$report);
    }

    /** @test */
    public function change_cities()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->columns(['city' => 'city'])
            ->run()
            ->withReport();

        $this->assertObjectNotHasAttribute('country',$report->diff());
        $this->assertObjectNotHasAttribute('description',$report->diff());
        $this->assertObjectHasAttribute('city',$report->diff());
        $this->assertObjectNotHasAttribute('name',$report->diff());
        $this->assertInstanceOf(\Edujugon\TableDiff\MyReport::class,$report);

    }

    /** @test */
    public function use_same_column_for_both_tables()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->run()
            ->withReport();

        $this->assertObjectNotHasAttribute('country',$report->diff());
        $this->assertObjectNotHasAttribute('description',$report->diff());
        $this->assertObjectHasAttribute('city',$report->diff());
        $this->assertObjectNotHasAttribute('name',$report->diff());
        $this->assertInstanceOf(\Edujugon\TableDiff\MyReport::class,$report);

    }

    /** @test */
    public function check_matched_and_unmatched()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->run()
            ->withReport();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->added());
        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->updated());
    }
}