# {{ $exception->class() }} - {!! $exception->title() !!}

{!! $exception->message() !!}

PHP {{ PHP_VERSION }}
Laravel {{ app()->version() }}
{{ $exception->request()->httpHost() }}

## Stack Trace

@foreach ($exception->frames() as $index => $frame)
{{ $index }} - {{ $frame->file() }}:{{ $frame->line() }}
@endforeach

## Request

{{ $exception->request()->method() }} {{ \Illuminate\Support\Str::start($exception->request()->path(), '/') }}

## Headers

@php($requestHeaders = $exception->requestHeaders())
@if (count($requestHeaders) > 0)
@foreach ($requestHeaders as $key => $value)
* **{{ $key }}**: {!! $value !!}
@endforeach
@else
No header data available.
@endif

## Route Context

@php($routeContext = $exception->applicationRouteContext())
@if (count($routeContext) > 0)
@foreach ($routeContext as $name => $value)
{{ $name }}: {!! $value !!}
@endforeach
@else
No routing data available.
@endif

## Route Parameters

@php($routeParametersContext = $exception->applicationRouteParametersContext())
@if ($routeParametersContext)
{!! $routeParametersContext !!}
@else
No route parameter data available.
@endif

## Database Queries

@php($applicationQueries = $exception->applicationQueries())
@if (count($applicationQueries) > 0)
@foreach ($applicationQueries as $query)
* {{ $query['connectionName'] }} - {!! $query['sql'] !!} ({{ $query['time'] }} ms)
@endforeach
@else
No database queries detected.
@endif