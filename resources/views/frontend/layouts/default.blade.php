<!doctype html>
<html lang="en">
    <head>
        @include('frontend.inc.head')
        @yield('styles')
    </head>

    <body>
        @include('frontend.inc.header')
        <main>
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif
            @yield('content')
        </main>
        @include('frontend.inc.footer')
        @yield('scripts')
    </body>
</html>
