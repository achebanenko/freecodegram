
# Laravel PHP Framework Tutorial - Full Course for Beginners (2019)

https://www.youtube.com/watch?v=ImtZ5yENzgE

Изучение Laravel / #1 - Что такое фреймворк Laravel?
https://www.youtube.com/watch?v=i98TUvjQZyw


## Installation

xampp (for php)
https://www.apachefriends.org/ru/index.html

composer
https://getcomposer.org/

node w/ npm
https://nodejs.org/en/

Global installation laravel's installer package.
$ composer global require laravel/installer
$ laravel new freecodegram

Starting laravel development server.
$ cd freecodegram
$ php artisan serve


## Auth layout via Vue

$ composer require laravel/ui
$ php artisan ui vue --auth
$ npm install && npm run dev


## DB

Leave only one DB variable in the .env file

.env
```
...
DB_CONNECTION=sqlite
...
```

Migrating default tables (users, password_resets).
$ php artisan migrate


## Register user

http://localhost:8000/register 
name test (test@test.com)
pwd testtest


## Add username to the registration flow

views/auth/register.blade.php
```
...
<div class="form-group row">
    <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

    <div class="col-md-6">
        <input id="username" type="text"
            class="form-control @error('username') is-invalid @enderror" name="username"
            value="{{ old('username') }}" required autocomplete="username">

        @error('username')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
...
```

Controllers/Auth/RegisterController.php
```
...
protected function validator(array $data)
{
    return Validator::make($data, [
        ...
        'username' => ['required', 'string', 'max:255','unique:users'],
    ]);
}

protected function create(array $data)
    {
        return User::create([
            ...
            'username' => $data['username'],
        ]);
    }
...
```

database/migrations/..._create_users_table.php
```
...
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        ...
        $table->string('username')->unique();
    });
}
...
```

Models/User.php
```
...
protected $fillable = [
    ...
    'username',
];
...
```

Drop all tables and create new ones.
$ php artisan migrate:fresh

Register user again now with username.

$ php artisan tinker
$ User::all();

$ exit


## Creating the Profiles controller

$ php artisan make:controller ProfilesController

Controllers/ProfileControllers.php
```
...
use App\Models\User;

class ProfilesController extends Controller
{
    public function index($user) {
        // dd(User::find($user));
        $user = User::find($user);
        return view('profiles.index', [
            'user' => $user,
        ]);
    }
}
```

routes/web.php
```
...
Route::get('/profile/{user}', [App\Http\Controllers\ProfilesController::class, 'index'])->name('profile.show');
```

views/profiles/index.blade.php
```
...
<h1>{{ $user->username }}</h1>
...
```


## Adding the Profile model and migration

$ php artisan help make:model # read about command and options
$ php artisan make:model Profile -m # shorthand for --migration

database/migrations/..._create_profiles_table.php
```
...
public function up()
{
    Schema::create('profiles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('title')->nullable();
        $table->text('description')->nullable();
        $table->string('url')->nullable();
        $table->string('image')->nullable();
        $table->timestamps();

        $table->index('user_id');
    });
}
...
```

$ php artisan migrate


## Adding Eloquent relationships

Models/Profile.php
```
...
class Profile extends Model {
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

Models/User.php
```
...
class User extends Authenticatable {
    ...
    public function profile() {
        return $this->hasOne(Profile::class);
    }
}
```

$ php artisan tinker
$profile = new App\Models\Profile();
$profile->title = 'Cool Title';
$profile->description = 'Description';
$profile->user_id = 1;
$profile->save();

$profile
$profile->user

$user = App\Models\User::find(1);
$user
$user->profile

views/home.blade.php
```
...
<div class="pt-4 font-weight-bold">{{ $user->profile->title }}</div>
<div>{{ $user->profile->description }}</div>
<div><a href="#">{{ $user->profile->url ?? 'N/A' }}</a></div>
...
```

$ php artisan tinker
$user = App\Models\User::find(1);
$user->profile->url = 'freecodecamp.org';
$user->push();


## Fetching the record from the database

controllers/ProfilesController.php
```
public function index($user) {
    $user = User::findOrFail($user);
    ...
}
```


## Adding posts to the database

$ php artisan make:model Post -m # --migration
$ php artisan migrate


database/migrations/..._create_posts_table.php


Models/User.php
```
...
class User extends Authenticatable
{
    ...
    public function posts() {
        return $this->hasMany(Post::class)->orderBy('created_at', 'DESC');
    }
}
```

Models/Post.php
```
...
class Post extends Model
{
    ...
    protected $guarded = [];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

$ php artisan make:controller PostsController

routes/web.php
```
...
Route::get('/p/create', 'App\Http\Controllers\PostsController@create');
Route::post('/p', 'App\Http\Controllers\PostsController@store');
```

Controllers/PostsController.php
```
...
class PostsController extends Controller
{
    // Hide if is an unauthorized visit
    public function __construct() {
        $this->middleware('auth');
    }

    public function create() {
        return view('posts.create');
    }

    public function store()
    {
        // dd(request()->all());
        $data = request()->validate([
            'caption' => 'required',
            // Another syntax
            // 'image' => 'required|image'
            'image' => ['required', 'image'],
            // To bring fields into data add with empty string
            // 'something' => '',
        ]);

        // Can't use this because also need user_if field
        // \App\Models\Post::create($data);

        // Creating through a relationship
        auth()->user()->posts()->create($data);
    }
}
```

views/posts/create.blade.php
```
@extends('layouts.app')

@section('content')
...
    <form action="/p" enctype="multipart/form-data" method="post">
        @csrf

        ...
        <label for="caption" class="col-md-4 col-form-label">Caption</label>

        <input id="caption" type="text" class="form-control @error('caption') is-invalid @enderror"
            name="caption" value="{{ old('caption') }}" autocomplete="caption" autofocus>

        @error('caption')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
        
        ...
        <label for="image" class="col-md-4 col-form-label">Image</label>
        <input type="file" class="form-control-file" id="image" name="image">

        @error('image')
        <!-- <span class="invalid-feedback" role="alert"> -->
        <strong>{{ $message }}</strong>
        <!-- </span> -->
        @enderror
        ...
        <button class="btn btn-primary">Add New Post</button>
        ...
    </form>
</div>
@endsection
```

$ php artisan tinker
Post::all();


## Uploading/Saving the image

Create a symbolic link from "public/storage" to "storage/app/public"
$ php artisan storage:link

Controllers/PostsController.php
```
...
class PostsController extends Controller
{
    ...
    public function store() {
        $data = request()->validate([
            'caption' => 'required',
            'image' => ['required', 'image'],
        ]);

        $imagePath = request('image')->store('uploads', 'public');

        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);

        return redirect('/profile/' . auth()->user()->id);
    }
}
```

$ php artisan tinker
Post::truncate()

views/posts/index.blade.php
```
...
<div class="pr-5"><strong>{{ $user->posts->count() }}</strong> posts</div>
...
<div class="row pt-5">
    @foreach($user->posts as $post)
    <div class="col-4 pb-4"><img class="w-100" src="/storage/{{ $post->image }}" alt=""></div>
    @endforeach
</div>
...
```


## Resizing images

$ composer require intervention/image

PostsController.php
```
...
use Intervention\Image\Facades\Image;

class PostsController extends Controller {
    ...
    public function store() {
        ...
        $image = Image::make(public_path("/storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();
        ...
    }
}
```


## Route Model Binding

views/profiles/index.blade.php
```
...
<a href="/p/{{ $post->id }}">
    <img class="w-100" src="/storage/{{ $post->image }}" alt="">
</a>
...
```

routes/web.php
```
...
Route::get('/p/{post}', 'App\Http\Controllers\PostsController@show');
...
```

PostsController.php
```
...
class PostsController extends Controller {
    ...
    public function show(\App\Models\Post $post)
    {
        // return view('posts.show', [
        //     'post' => $post,
        // ]);
        
        // short way
        return view('posts.show', compact('post'));
    }
}
```

views/posts/show.blade.php
```
@extends('layouts.app')

@section('content')
<div class="container"></div>
@endsection
```


## Editing profile

routes/web.php
```
...
Route::get('/profile/{user}/edit', [App\Http\Controllers\ProfilesController::class, 'edit'])->name('profile.edit');
Route::patch('/profile/{user}', [App\Http\Controllers\ProfilesController::class, 'update'])->name('profile.update');
```

views/profiles/index.blade.php
```
...
<a href="/profile/{{ $user->id }}/edit">Edit Profile</a>
...
```

views/profiles/edit.blade.php
```
...
<form action="/profile/{{ $user->id }}" enctype="multipart/form-data" method="post">
        @csrf
        @method('PATCH')

        ...
        <label for="title" class="col-md-4 col-form-label">Title</label>

        <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title"
            value="{{ old('title') ?? $user->profile->title }}" autocomplete="title" autofocus>
        ...
```

ProfilesController.php
```
...
class ProfilesController extends Controller
{
    ...
    public function edit(User $user) {
        return view('profiles.edit', compact('user'));
    }

    public function update(User $user) {
        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);

        auth()->user()->profile->update($data);
        
        return redirect("/profile/{$user->id}");
    }
}
```


## Model Policy

$ php artisan make:policy ProfilePolicy -m Profile

policies/ProfilePolicy.php
```
...
public function update(User $user, Profile $profile) {
    return $user->id == $profile->user_id;
}
...
```

ProfilesController.php
```
...
public function edit(User $user) {
    // Protect view via ProfilePolicy
    $this->authorize('update', $user->profile);

    return view('profiles.edit', compact('user'));
}

public function update(User $user) {
    $this->authorize('update', $user->profile);
    ...
}
...
```

views/profiles/index.blade.php
```
...
@can('update', $user->profile)
<a href="/p/create">Add new post</a>
@endcan
...
@can('update', $user->profile)
<a href="/profile/{{ $user->id }}/edit">Edit Profile</a>
@endcan
...
```


## Editing Profile Image

ProfilesController.php
```
...
use Intervention\Image\Facades\Image;

class ProfilesController extends Controller
{
    ...
    public function update(User $user)
    {
        $this->authorize('update', $user->profile);

        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);
        
        if (request('image')) {
            $imagePath = request('image')->store('profile', 'public');
            
            $image = Image::make(public_path("/storage/{$imagePath}"))->fit(1000, 1000);
            $image->save();

            $imageArray = ['image' => $imagePath];
        }

        auth()->user()->profile->update(array_merge(
            $data,
            $imageArray ?? []
        ));
        
        return redirect("/profile/{$user->id}");
    }
}
```

views/posts/show.blade.php
```
...
<img class="rounded-circle w-100" src="/storage/{{ $post->user->profile->image }}" alt="" style="max-width:40px;">
...
```


## Automatically creating a profile using Model Events

Models/User.php
```
...
protected static function boot()
{
    parent::boot();

    static::created(function ($user) {
        // $user->profile()->create();

        // With default title
        $user->profile()->create([
            'title' => $user->username
        ]);
    });
}
...
```


## Default Profile Image

Models/Profile.php
```
...
class Profile extends Model {
    ...
    public function profileImage()
    {
        // Path to empty image should be provided
        return ($this->image)
            ? '/storage/' . $this->image
            : '/storage/' . $this->image;
    }
}
...
```

views/profiles/index.blade.php
```
...
<img class="rounded-circle w-100" src="{{ $user->profile->profileImage() }}" />
...
```


## Follow/Unfollow using Vue

$ npm run watch

$ php artisan make:controller FollowsController

routes/web.php
```
...
// Route::post('/follow/{user}', function () {
//     return ['success'];
// });
Route::post('/follow/{user}', 'App\Http\Controllers\FollowsController@store');
...
```

FollowsController.php
```
...
class FollowsController extends Controller
{
    public function store(\App\Models\User $user)
    {
        // return $user->username;
        return auth()->user()->following()->toggle($user->profile);
    }
}
```

$ php artisan make:migration creates_profile_user_pivot_table --create profile_user

migrations/..._creates_rofile_user_pivot_table.php
```
...
$table->unsignedBigInteger('profile_id');
$table->unsignedBigInteger('user_id');
...
```

$ php artisan migrate

Models/User.php
```
...
public function following() {
    return $this->belongsToMany(Profile::class);
}
...
```

Models/Profile.php
```
...
public function followers() {
    return $this->belongsToMany(User::class);
}
...
```

ProfilesController.php
```
...
public function index(User $user)
{
    $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

    return view('profile.index', compact('user', 'follows'));
}
...
```

views/profiles/index.blade.php
```
...
<follow-button user-id="{{ $user->id }}" follows="{{ $follows }}"></follow-button>
...
```

js/app.js
```
...
Vue.component('follow-button', require('./components/FollowButton.vue').default);
...
```

js/components/FollowButton.vue
```
<template>
  <button
    class="btn btn-primary ml-4" @click="followUser" v-text="buttonText"></button>
</template>

<script>
export default {
  props: ["userId", "follows"],

  mounted() {
    console.log("Component mounted.");
  },

  data: function () {
    return {
      status: this.follows,
    };
  },

  methods: {
    followUser() {
      axios.post("/follow/" + this.userId).then((res) => {
        this.status = !this.status;
        console.log(res.data);
      });
    },
  },

  computed: {
    buttonText() {
      return this.status ? "Unfollow" : "Follow";
    },
  },
};
</script>
```

$ php artisan tinker
$user = User::find(1);
$user->following
$user->fresh()->following


## Calculate followers count and following count

views/profiles/index.blade.php
```
<div class="pr-5"><strong>{{ $user->profile->followers->count() }}</strong> followers</div>
<div class="pr-5"><strong>{{ $user->following->count() }}</strong> following</div>
```


## Laravel Telescope

$ composer require laravel/telescope
$ php artisan telescope:install
$ php artisan migrate


## Showing Posts from profiles the user is following

Auth/LoginController.php
```
...
// protected $redirectTo = RouteServiceProvider::HOME;
protected $redirectTo = '/';
...
```

routes/web.php
```
...
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', 'App\Http\Controllers\PostsController@index');
...
```

PostsController.php
```
...
use App\Models\Post;
...
public function index() {
    $users = auth()->user()->following()->pluck('profiles.user_id');

    // $posts = Post::whereIn('user_id', $users)->orderBy('created_at', 'DESC')->get();
    $posts = Post::whereIn('user_id', $users)->latest()->get();

    return view('posts.index', compact('posts'));
}
...
```

views/posts/index.blade.php
```
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
</div>
@endsection
```


## Pagination Eloquent

PostsController.php
```
...
// $posts = Post::whereIn('user_id', $users)->latest()->get();
$posts = Post::whereIn('user_id', $users)->latest()->paginate('2');
...
```

views/posts/index.blade.php
```
...
<div class="row">
    <div class="col-12 d-flex justify-content-center">
        {{ $posts->links() }}
    </div>
</div>
```


## N+1 Problem

PostsController.php
```
...
$posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate('2');
...
```

$ php artisan telescope:clear


## Make use of cache

views/profiles/index.blade.php
```
...
<div class="d-flex">
    <div class="pr-5"><strong>{{ $postsCount }}</strong> posts</div>
    <div class="pr-5"><strong>{{ $followersCount }}</strong> followers</div>
    <div class="pr-5"><strong>{{ $followingCount }}</strong> following</div>
</div>
...
```

ProfilesController.php
```
...
public function index(User $user) {
    $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

    // $postsCount = $user->posts->count();
    $postsCount = Cache::remember(
        'count.posts.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->posts->count();
        }
    );

    // $followersCount = $user->profile->followers->count();
    $followersCount = Cache::remember(
        'count.followers.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->profile->followers->count();
        }
    );

    // $followingCount = $user->following->count();
    $followingCount = Cache::remember(
        'count.following.' . $user->id,
        now()->addSeconds(30),
        function () use ($user) {
            return $user->following->count();
        }
    );

    return view('profiles.index', compact('user', 'follows', 'postsCount', 'followersCount', 'followingCount'));
}
...
```


## Sending Emails

mailtrap.io

.env
```
...
// MAIL_HOST=mailhog
// MAIL_PORT=1025
// MAIL_USERNAME=null
// MAIL_PASSWORD=null
// MAIL_FROM_ADDRESS=null

MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=4987c99bc52270
MAIL_PASSWORD=d9178f4d9da1a0
MAIL_FROM_ADDRESS=freecodegram@example.com
...
```

Stop and start process again
$ php artisan serve

$ php artisan help make:mail
$ php artisan make:mail NewUserWelcomeMail -m emails.welcome-email

views/emails/welcome-email.blade.php
```
@component('mail::message')
# Welcome to freeCodeGram

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

routes/web.php
```
...
// Temporary
Route::get('/email', function () {
    return new App\Mail\NewUserWelcomeMail();
});
...
```

Models/User.php
```
...
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserWelcomeMail;
...
protected static function boot() {
    parent::boot();

    static::created(function ($user) {
        ...
        Mail::to($user->email)->send(new NewUserWelcomeMail());
    });
}
...
```


## Moving project to another folder

$ rmdir public/storage # rm public/storage
$ php artisan storage:link


