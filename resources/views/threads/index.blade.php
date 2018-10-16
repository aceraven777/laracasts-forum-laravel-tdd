@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            @include('threads._list')

            {{ $threads->render() }}
        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Search
                </div>

                <div class="panel-body">
                    <form method="GET" action="/threads/search">
                        <div class="form-group">
                            <input type="text" placeholder="Search for something..." name="q" value="{{ request('q') }}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <button class='btn btn-default' type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            @if (count($trending))
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Trending Threads
                    </div>

                    <div class="panel-body">
                        @if (count($trending))
                            <ul class='list-group'>
                                @foreach ($trending as $thread)
                                    <li class='list-group-item'>
                                        <a href="{{ url($thread->path) }}">{{ $thread->title }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No trending threads to show.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
