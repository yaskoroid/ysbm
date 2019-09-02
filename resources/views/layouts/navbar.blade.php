<!-- navbar -->
<header class="navbar navbar-inverse" role="banner">
    <div class="navbar-header pull-left">
        <a class="navbar-brand" href="{{ URL::route('index') }}">{{ trans('general.ysbm_app') }}</a>
    </div>
    <ul class="nav navbar-nav pull-right">
        <li class="settings{{ (Request::is('shipments') ? ' active' : '') }}">
            <a href="{{ URL::route('shipments') }}" role="button">
                <span class="title pull-left hidden-xs hidden-sm">{{ trans('general.shipments') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ URL::route('logout') }}" role="button">
                <span class="title pull-left hidden-xs hidden-sm">{{ trans('general.logout') }}</span>
            </a>
        </li>
    </ul>
</header>
<!-- end navbar -->