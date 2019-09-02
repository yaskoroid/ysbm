@extends("layouts/default")

{{-- Web site Title --}}
@section('title')
    Item create
@stop

@section("layout")
    <h1>Item create</h1>
    <div class="col-md-6 col-md-offset-3">
        <div class="row">
            <div class="col-md-6">
                {{ $message }}
            </div>
        </div>
    </div>
@stop