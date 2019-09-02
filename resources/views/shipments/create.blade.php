@extends("layouts/default")

{{-- Web site Title --}}
@section('title')
    Shipment create
@stop

@section("layout")
    <h1>Shipment create</h1>
    <div class="col-md-6 col-md-offset-3">
        {{ Form::open(['route' => ['shipmentCreate'], 'autocomplete' => 'on', 'method' => 'post']) }}
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('Id') }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::text('id', $id, ['class' => 'input pull-left']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('Name') }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::text('name', $name, ['class' => 'input pull-left']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::label($message) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::submit('Submit', ['class' => 'btn bnt-success']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@stop