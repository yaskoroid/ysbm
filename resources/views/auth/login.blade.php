@extends("layouts/default")

{{-- Web site Title --}}
@section('title')
    Login
@stop

@section('style')
@stop

@section('js')
    <script type="text/javascript">
        document.getElementById("email").focus();
    </script>
@stop

@section("layout")
    <div class="login-wrapper">
        <a href="{{ URL::route('index') }}" class="visible-xs">
            <h1>{{ trans('general.home') }}</h1>
        </a>
        <div class="box">
            <div class="content-wrap">
                <h6>{{ trans('general.log_in.log_in') }}</h6>
                @if ($error = $errors->first())
                    <div class="alert alert-danger">
                        {{ $error }}
                    </div>
                @endif
                {{ Form::open(["route" => "login", "autocomplete" => "off"]) }}
                {{ Form::text("email", Request::get("email"), ["class" => "form-control", "id" => "email", "placeholder" => trans('general.login')]) }}
                {{ Form::password("password", ["class" => "form-control", "placeholder" => trans('general.password')]) }}
                <br/>
                {{ Form::submit(trans('general.log_in.log_in'), ["class" => "btn-glow success login"]) }}
                {{ Form::close() }}
            </div>
        </div>
        <br/>
    </div>
    <br>
@stop