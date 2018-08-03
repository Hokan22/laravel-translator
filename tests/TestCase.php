<?php

namespace Hokan22\LaravelTranslator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('view.paths', [__DIR__.'../src/resources/views']);
    }

    protected function getPackageAliases($app)
    {
        return [
            'Translator' => 'Hokan22\LaravelTranslator\TranslatorFacade'
        ];
    }
}
