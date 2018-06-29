@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="page-header">
                <h1>
                    {{ $profileUser->name }}
                </h1>
            </div>

            @foreach ($activities as $date => $activities_of_date)
                <h3 class="page-header">{{ $date }}</h3>

                @foreach ($activities_of_date as $activity)
                    @include ("profiles.activities.{$activity->type}")
                @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection
