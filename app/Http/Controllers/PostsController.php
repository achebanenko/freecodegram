<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\Post;

class PostsController extends Controller
{
    // Hide if is an unauthorized visit
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $users = auth()->user()->following()->pluck('profiles.user_id');

        // $posts = Post::whereIn('user_id', $users)->orderBy('created_at', 'DESC')->get();
        // $posts = Post::whereIn('user_id', $users)->latest()->get();
        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate('2');

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store()
    {
        // dd(request()->all());
        $data = request()->validate([
            'caption' => 'required',
            // 'image' => 'required|image'
            'image' => ['required', 'image'],
            // To bring fields into data add with empty string
            // 'something' => '',
        ]);

        $imagePath = request('image')->store('uploads', 'public');

        $image = Image::make(public_path("/storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();

        // Can't use this because also need user_if field
        // \App\Models\Post::create($data);
        // Creating post through a relationship
        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);

        return redirect('/profile/' . auth()->user()->id);
    }

    public function show(\App\Models\Post $post)
    {
        // return view('posts.show', [
        //     'post' => $post,
        // ]);
        
        // short way
        return view('posts.show', compact('post'));
    }
}
