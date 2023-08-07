<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user) {

        if ($user->id == auth()->user()->id){
            return back()->with('failure', 'You cannot follow yourself');
        }

        $isAlreadyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();

        if ($isAlreadyFollowing) {
            return back()->with('failure', "You are already following {$user->username}");
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return redirect("/profile/{$user->username}")->with('success', "You are now following {$user->username}");
    }

    public function removeFollow(User $user) {

        if ($user->id == auth()->user()->id){
            return back()->with('failure', 'You cannot unfollow yourself');
        }

        $isAlreadyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();

        if (!$isAlreadyFollowing) {
            return back()->with('failure', "You are not following {$user->username}");
        }

        Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->delete();

        return redirect("/profile/{$user->username}")->with('success', "You have unfollowed {$user->username}");
    }
}
