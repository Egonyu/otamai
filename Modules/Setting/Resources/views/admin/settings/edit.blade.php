@extends('admin::layout')

@component('admin::include.page.header')
    @slot('title', clean(trans('setting::settings.settings')))
    
    <li class="nav-item"> {{ clean(trans('setting::settings.settings')) }}</li>
@endcomponent

@section('content')
<div class="row">
    <div class="col-md-12">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="form-horizontal" id="settings-edit-form" novalidate>
            {{ csrf_field() }}
            {{ method_field('put') }}

            {!! $tabs->render(compact('settings')) !!}
        </form>
    </div>
</div>
@endsection
