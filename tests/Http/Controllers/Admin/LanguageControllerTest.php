<?php

class LanguageControllerTest extends ExpendableTestCase {

    public function setUp()
    {
        parent::setUp();


        \Distilleries\Expendable\Models\Role::create([
            'libelle'            => 'admin',
            'initials'           => '@a',
            'overide_permission' => true,
        ]);

        \Distilleries\Expendable\Models\Service::create([
            'action' => 'test',
        ]);

        $faker = Faker\Factory::create();
        $email = $faker->email;
        $user  = \Distilleries\Expendable\Models\User::create([
            'email'    => $email,
            'password' => \Hash::make('test'),
            'status'   => true,
            'role_id'  => 1,
        ]);

        \Distilleries\Expendable\Models\Permission::create([
            'role_id'    => 1,
            'service_id' => 1,
        ]);

        $this->be($user);

    }


    public function testDatatable()
    {

        $response = $this->call('GET', action('Admin\LanguageController@getIndex'));
        $this->assertResponseOk();

        $this->assertContains(trans('expendable::datatable.id'), $response->getContent());
        $this->assertContains(trans('expendable::datatable.libelle'), $response->getContent());
        $this->assertContains(trans('expendable::datatable.iso'), $response->getContent());

    }


    public function testDatatableApi()
    {

        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);

        $response = $this->call('GET', action('Admin\LanguageController@getDatatable'));
        $this->assertResponseOk();
        $result = json_decode($response->getContent());

        $this->assertEquals(1, $result->iTotalRecords);
        $this->assertEquals($language->id, $result->aaData[0]->{0});
        $this->assertEquals($language->libelle, $result->aaData[0]->{1});
    }


    public function testGetChangeLang()
    {
        $this->call('GET', action('Admin\LanguageController@getChangeLang', [
            'local' => 'es'
        ]));
        $this->assertEquals('es', $this->app->getLocale());

    }

    public function testView()
    {
        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);

        $response = $this->call('GET', action('Admin\LanguageController@getView', [
            'id' => $language->id
        ]));

        $this->assertResponseOk();
        $this->assertContains($data['libelle'], $response->getContent());
        $this->assertContains($data['iso'], $response->getContent());
    }

    public function testEdit()
    {
        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);
        $response = $this->call('GET', action('Admin\LanguageController@getEdit', [
            'id' => $language->id
        ]));

        $this->assertResponseOk();
        $this->assertContains($data['libelle'], $response->getContent());
        $this->assertContains($data['iso'], $response->getContent());

    }


    public function testSaveError()
    {
        $this->call('POST', action('Admin\LanguageController@postEdit'));
        $this->assertSessionHasErrors();
        $this->assertHasOldInput();
    }

    public function testSave()
    {
        $faker = Faker\Factory::create();
        $data  = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => 0,
            'is_default'  => 1,
            'status'      => 1,
        ];

        $this->call('POST', action('Admin\LanguageController@postEdit'), $data);
        $total = \Distilleries\Expendable\Models\Language::where('libelle', $data['libelle'])->where('iso', $data['iso'])->count();

        $this->assertEquals(1, $total);

    }

    public function testSearch()
    {
        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => 0,
            'is_default'  => 1,
            'status'      => 1,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);
        $response = $this->call('POST', action('Admin\LanguageController@postSearch'), [
            'ids' => [$language->id]
        ]);

        $result = json_decode($response->getContent());
        $this->assertEquals($language->id, $result[0]->id);
        $this->assertEquals($language->libelle, $result[0]->libelle);

        $response = $this->call('POST', action('Admin\LanguageController@postSearch'), [
        ]);

        $result = json_decode($response->getContent());
        $this->assertEquals(0, $result->total);
    }


    public function testSearchWithException()
    {
        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => 0,
            'is_default'  => 1,
            'status'      => 1,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);
        $response = $this->call('POST', action('Admin\LanguageController@postSearch'), [
            'ids' => [$language->id]
        ]);

        $result = json_decode($response->getContent());
        $this->assertEquals($language->id, $result[0]->id);
        $this->assertEquals($language->libelle, $result[0]->libelle);

        try
        {
            $response = $this->call('POST', action('Admin\LanguageController@postSearch'), [
                'term' => $data['iso'].$data['iso'].$data['iso']
            ]);

            $result = json_decode($response->getContent());
            $this->assertEquals(0, $result->total);

        } catch (\Exception $e)
        {
            $this->assertEquals('Database driver not supported: sqlite', $e->getMessage());
        }


    }

    public function testDestroyNoId()
    {

        $this->call('PUT', action('Admin\LanguageController@putDestroy'));
        $this->assertSessionHasErrors();
        $this->assertHasOldInput();

    }

    public function testDestroy()
    {
        $faker    = Faker\Factory::create();
        $data     = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        $language = \Distilleries\Expendable\Models\Language::create($data);
        $this->call('PUT', action('Admin\LanguageController@putDestroy'), [
            'id' => $language->id
        ]);
        $newLanguage = \Distilleries\Expendable\Models\Language::find($language->id);

        $this->assertEquals(null, $newLanguage);

    }


    public function testExport()
    {

        $response = $this->call('GET', action('Admin\LanguageController@getExport'));
        $this->assertResponseOk();

        $this->assertContains(trans('expendable::form.date'), $response->getContent());
        $this->assertContains(trans('expendable::form.type'), $response->getContent());
    }

    public function testExportError()
    {

        $this->call('POST', action('Admin\LanguageController@postExport'));
        $this->assertSessionHasErrors();
        $this->assertHasOldInput();
    }

    public function testExportCsv()
    {

        $faker = Faker\Factory::create();
        $data  = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        \Distilleries\Expendable\Models\Language::create($data);

        \File::delete(storage_path('exports'));
        $dateBegin = date('Y-m-d', time() - (24 * 60 * 60));
        $dateEnd   = date('Y-m-d', time() + (24 * 60 * 60));

        try
        {
            $this->call('POST', action('Admin\LanguageController@postExport'), [
                'range' => [
                    'start' => $dateBegin,
                    'end'   => $dateEnd
                ],
                'type'  => 'Distilleries\Expendable\Contracts\CsvExporterContract'
            ]);

        } catch (\Maatwebsite\Excel\Exceptions\LaravelExcelException $e)
        {
            $this->assertEquals("[ERROR]: Headers already sent", $e->getMessage());
            $this->assertFileExists(storage_path('exports/'.$dateBegin.' '.$dateEnd.'.csv'));
        }

    }

    public function testExportXls()
    {

        $faker = Faker\Factory::create();
        $data  = [
            'libelle'     => str_replace('\'', '', $faker->country),
            'iso'         => $faker->countryCode,
            'not_visible' => false,
            'is_default'  => true,
            'status'      => true,
        ];
        \Distilleries\Expendable\Models\Language::create($data);

        $dateBegin = date('Y-m-d', time() - (24 * 60 * 60));
        $dateEnd   = date('Y-m-d', time() + (24 * 60 * 60));
        \File::delete(storage_path('exports'));
        try
        {
            $this->call('POST', action('Admin\LanguageController@postExport'), [
                'range' => [
                    'start' => $dateBegin,
                    'end'   => $dateEnd
                ],
                'type'  => 'Distilleries\Expendable\Contracts\ExcelExporterContract'
            ]);

        } catch (\Maatwebsite\Excel\Exceptions\LaravelExcelException $e)
        {
            $this->assertEquals("[ERROR]: Headers already sent", $e->getMessage());
            $this->assertFileExists(storage_path('exports/'.$dateBegin.' '.$dateEnd.'.xls'));
        }

    }

    public function testImport()
    {


        $response = $this->call('GET', action('Admin\LanguageController@getImport'));
        $this->assertResponseOk();
        $this->assertContains(trans('expendable::form.file_import'), $response->getContent());
    }


    public function testImportNoFileCsv()
    {
        $this->call('POST', action('Admin\LanguageController@postImport'), [
            'file' => storage_path('test.csv')
        ]);

        $this->assertSessionHas(\Distilleries\Expendable\Formatter\Message::WARNING);
    }

    public function testImportCsv()
    {

        \DB::table('languages')->truncate();
        copy(realpath(__DIR__.'/../../../data/exports/2015-03-17 2015-03-19.csv'), storage_path('2015-03-17 2015-03-19.csv'));
        $this->call('POST', action('Admin\LanguageController@postImport'), [
            'file' => storage_path('2015-03-17 2015-03-19.csv')
        ]);

        $total = \Distilleries\Expendable\Models\Language::count();
        $this->assertSessionHas(\Distilleries\Expendable\Formatter\Message::MESSAGE);
        $this->assertEquals(1, $total);
    }

    public function testImportXls()
    {

        \DB::table('languages')->truncate();
        copy(realpath(__DIR__.'/../../../data/exports/2015-03-17 2015-03-19.csv'), storage_path('2015-03-17 2015-03-19.xls'));
        $this->call('POST', action('Admin\LanguageController@postImport'), [
            'file' => storage_path('2015-03-17 2015-03-19.xls')
        ]);

        $total = \Distilleries\Expendable\Models\Language::count();
        $this->assertSessionHas(\Distilleries\Expendable\Formatter\Message::MESSAGE);
        $this->assertEquals(1, $total);
    }

}

