<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    public function showCreateForm() {
        return view('create-post');
    }

    public function createPost(Request $request) {
        $incomingFields = $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail([
            "sendTo"=>auth()->user()->email,
            'name' => auth()->user()->username,
            'title'=>strip_tags($incomingFields['title']),
        ]));

        return redirect("/post/{$newPost->id}")->with('success', 'Post Created');
    }

    public function createPostAPI(Request $request) {
        $incomingFields = $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);
    
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();
    
        $newPost = Post::create($incomingFields);
    
        dispatch(new SendNewPostEmail([
            "sendTo"=>auth()->user()->email,
            'name' => auth()->user()->username,
            'title'=>strip_tags($incomingFields['title']),
        ]));
    
        return $newPost->id;
    }

    public function viewSinglePost(Post $post) {
        $html = Str::markdown($post->body);
        $post['body'] = $html;
        return view('single-post',['post'=>$post]);
    }

    public function deletePost(Post $post) {
        if (auth()->user()->cannot('delete', $post)) {
            return redirect("/post/{$post->id}")->with('failure', 'You cannot do that');
        }
        $post->delete();
        return redirect("/profile/{$post->user->username}")->with('success', 'Successfully deleted post');
    }

    public function deletePostAPI(Post $post) {
        if (auth()->user()->cannot('delete', $post)) {
            return redirect("/post/{$post->id}")->with('failure', 'You cannot do that');
        }
        $post->delete();
        return true;
    }

    public function showEditForm(Post $post) {
        if (auth()->user()->cannot('update', $post)) {
            return redirect("/post/{$post->id}")->with('failure', 'You cannot do that');
        }

        return view('edit-post', ['post'=>$post]);
    }

    public function updatePost(Post $post, Request $request) {
        if (auth()->user()->cannot('update', $post)) {
            return redirect("/post/{$post->id}")->with('failure', 'You cannot do that');
        }

        $incomingFields = $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return redirect("/post/{$post->id}")->with('success','Successfully edited post');
    }

    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }
}
