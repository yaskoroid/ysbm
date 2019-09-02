@extends("layouts/default")

{{-- Web site Title --}}
@section('title')
    Shipments
@stop

@section("layout")
    <h1>Shipments</h1>
    @if ( !empty($shipments))
        @foreach($shipments as $key => $shipment)
            <div>Id: {{ $shipment['id'] }}</div>
            <div>Name: {{ $shipment['name'] }}</div>
            <div>Is send: {{ $shipment['is_send'] }}</div>
                @if ( !empty($shipment['items']))
                    <div style="margin-left: 30px;">
                        <div>Items:</div>
                        @foreach($shipment['items'] as $item)
                            <div>Item id: {{ $item['id'] }}</div>
                            <div>Item name: {{ $item['name'] }}</div>
                            <div>Item code: {{ $item['code'] }}</div>
                            <div style="margin-left: 60px;">
                                <a href="{{ URL::route('itemDelete', $item['id']) }}">Item delete</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span style="margin-left: 30px;">Nothing here yet.</span>
                @endif
            <div style="margin-left: 30px;">
                <a href="{{ URL::route('itemCreate', $shipment['id']) }}">Item create</a>
            </div>
        @endforeach
    @else
        <span>Nothing here yet.</span>
    @endif
    <div>
        <a href="{{ URL::route('shipmentCreate') }}">Shipment create</a>
    </div>
@stop