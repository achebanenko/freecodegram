@extends('layouts.app')

@section('content')
<div class="container">
    @foreach($posts as $post)
    <div class="row py-3">
        <div class="col-6 offset-3">
            <a href="/p/{{ $post->id }}">
                <img class="w-100" src="/storage/{{ $post->image }}" alt="">
            </a>
            <p class="pt-3">
                <a class="font-weight-bold text-dark" href="/profile/{{ $post->user->id }}">
                    {{ $post->user->username }}
                </a>
                {{ $post->caption }}
            </p>
        </div>
    </div>
    @endforeach

    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection