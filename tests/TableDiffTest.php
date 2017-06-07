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
        $matchedCountry = \Edujugon\TableDiffTest\Models\MainArea::first()->country;

        $this->assertNotEquals($country, $matchedCountry);
    }

    /** @test */
    public function throw_an_exception_if_no_tables()
    {
        $this->setExpectedException(\Edujugon\TableDiff\Exceptions\TableDiffException::class);
        $this->diff->pivots('main_areas', 'sub_areas')->run();
    }

    /** @test */
    public function throw_an_exception_if_no_pivots()
    {
        $this->setExpectedException(\Edujugon\TableDiff\Exceptions\TableDiffException::class);
        $this->diff->tables('main_areas', 'sub_areas')->run();
    }

    /** @test */
    public function get_report_of_changes()
    {
        $this->diff->tables('main_areas','sub_areas');
        $this->diff->pivot('id');
        $this->diff->run();
        $report = $this->diff->withReport();

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
    public function check_unmatched_and_matched()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->run()
            ->withReport();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->unmatched());
        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->matched());
    }

    /** @test */
    public function set_a_different_primary_key()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->setPrimaryKey('name')
            ->pivot('name')
            ->run()
            ->withReport();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->unmatched());
        $this->assertInstanceOf(\Illuminate\Support\Collection::class,$report->matched());

        $this->diff->setPrimaryKey('id');
    }

    /** @test */
    public function using_callback_in_merge_matched()
    {
        $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->mergeMatched(function($collection,$data){
                $subArea = \Edujugon\TableDiffTest\Models\SubArea::where('id',$collection->first()->id)->first();

                $this->assertEquals($data->city,$subArea->city);
            });
    }

    /** @test */
    public function using_callback_in_merge()
    {
        $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->merge(function($collection,$data){
                $subArea = \Edujugon\TableDiffTest\Models\SubArea::where('id',$collection->first()->id)->first();

                $this->assertEquals($data->city,$subArea->city);
            });
    }


    /** @test */
    public function using_callback_in_merge_unmatched()
    {
        $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->mergeUnMatched(function($list){

                $this->assertCount(2,$list);
                $this->assertArrayHasKey('city',$list[0]);
                $this->assertArrayNotHasKey('id',$list[0]);
            });
    }

    /** @test */
    public function inserted_and_updated_records_have_been_increased()
    {
        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->merge()
            ->withReport();

        $this->assertEquals(3,$report->updatedRecords());
        $this->assertEquals(2,$report->insertedRecords());
    }

    /** @test */
    public function casting_data_before_merge()
    {
        $city = '';

        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->merge(function($collection,$data) use($city){
                $this->assertNotEquals($city,$data->city);
            },function(&$data) use($city){

                $city = $data->city;
                $data->city = strtoupper($data->city);
            });
    }

    /** @test */
    public function using_callback_after_adding_new_elements()
    {
        $new = \Edujugon\TableDiffTest\Models\SubArea::whereNotIn('id',\Edujugon\TableDiffTest\Models\MainArea::all('id')->toArray())
                ->pluck('city')
                ->toArray();

        $report = $this->diff->tables('main_areas','sub_areas')
            ->pivot('id')
            ->column('city')
            ->mergeUnMatched(function($collection) use($new){
                $collection->each(function ($item,$key) use($new){

                    $this->assertEquals($item['city'],$new[$key]);
                });

            });
    }

    /** @test */
    public function add_payload_to_the_event()
    {
        $this->diff->eventPayload(['user' => 'John Doe']);

        $this->assertArrayHasKey('user',$this->diff->getEventPayload());
        $this->assertEquals('John Doe',$this->diff->getEventPayload()['user']);
    }
}