
@extends('layout.main')


@section('content')
    <div>
        <p>@translate('test2', ['test_parameter' => 'Test'])</p>
        <p>@translate('test')</p>

        <p>@translate('test')</p>
        <p>@translate('greeting', ['name' => 'John'])</p>
        <p>@translate('multi_parameter_test',   [
                                                    'name' => 'John',
                                                    'surname' => 'Doe',
                                                    'test' => '<i>hallo</i>'
                                                ])</p>
        <p>@translate('test')</p>
        <p>@translate('dump_translator')</p>
    </div>
@endsection
