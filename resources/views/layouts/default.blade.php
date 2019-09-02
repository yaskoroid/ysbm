@extends("layouts.main")

@section("body")
    <div id="wrap">

        @include("layouts.navbar")

        @yield("layout")
        @yield("content")
    </div>
    <div id="footer">
        <div class="text-center">
            <div id="copyright-date" style="padding-top: 50px;">
                <span class="text-muted">{{ trans('general.footer.copyright_date') }} — {{ date('Y') }}</span>
            </div>
        </div>
    </div>
    {{ Html::script('/js/global.js') }}
@stop