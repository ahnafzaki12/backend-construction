<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('created_at', 'DESC')->get();
        return $posts;
    }

    public function latestposts(Request $request){
        $posts = Post::take($request->get('limit'))->orderBy('created_at', 'DESC')->get();
        return $posts;
    }
}