@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-8">
            <img class="w-100" src="/storage/{{ $post->image }}" alt="">
        </div>
        <div class="col-4">
            <div class="d-flex align-items-center">
                <div class="pr-3">
                    <img class="rounded-circle w-100" src="{{ $post->user->profile->profileImage() }}" alt=""
                        style="max-width:40px;">
                </div>
                <div>
                    <a class="font-weight-bold text-dark" href="/profile/{{ $post->user->id }}">{{
                        $post->user->username }}
                    </a>
                    <a class="pl-3" href="#">Follow</a>
                </div>
            </div>

            <hr />

            <p>
                <a class="font-weight-bold text-dark" href="/profile/{{ $post->user->id }}">
                    {{ $post->user->username }}
                </a>
                {{ $post->caption }}
            </p>
        </div>
    </div>
</div>
@endsection