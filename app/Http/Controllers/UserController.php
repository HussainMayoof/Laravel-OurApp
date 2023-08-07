<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username'=>['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email'=>['required', 'email', Rule::unique('users', 'email')],
            'password'=>['required', 'min:8', 'confirmed']
        ]);
        
        $user = User::create($incomingFields);

        auth()->login($user);
        return redirect('/')->with('success', "Account Registered");
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername'=>['required'],
            'loginpassword'=>['required']
        ]);
        
        if (auth()->attempt(['username'=>$incomingFields['loginusername'], 'password'=>$incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', "Logged In");
        } else {
            return redirect('/')->with('failure', "Incorrect username or password");
        }

    }

    public function loginAPI(Request $request) {
        $incomingFields = $request->validate([
            'loginusername'=>['required'],
            'loginpassword'=>['required']
        ]);

        if (auth()->attempt(['username'=>$incomingFields['loginusername'], 'password'=>$incomingFields['loginpassword']])) {
            $user = User::where('username', $incomingFields['loginusername'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        } else {
            return redirect('/')->with('failure', "Incorrect username or password");
        }
    }

    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('home-feed', ['posts'=>auth()->user()->feedPosts()->latest()->paginate(4)]);
        } else {
            $postCount = Cache::remember('postCount', 20, function (){
                return Post::count();
            });
            return view('home', ['postCount'=>$postCount]);
        } 
    }

    public function logout() {
        auth()->logout();
        return redirect('/')->with('success', "Logged Out");
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

    public function addAvatar(Request $request) {
        $request->validate([
            'avatar'=> 'required|image|max:8000'
        ]);

        $user = auth()->user();
        $filename = $user->id."_".uniqid().".jpg";

        $avatar = Image::make($request->file('avatar'))->fit('120')->encode('jpg');
        Storage::put("public/avatars/".$filename, $avatar);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg"){
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return redirect("profile/{$user->username}");
    }

    private function sharedData(User $user) {
        $isFollowing = 0;

        if (auth()->check()){
            $isFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser', '=', $user->id]])->count();
        }

        View::share('sharedData', [
            'user'=>$user, 
            'isFollowing'=>$isFollowing, 
            'postCount'=>$postCount = $user->posts()->latest()->get()->count(),
            'followerCount'=>$followerCount = $user->followers()->latest()->get()->count(),
            'followingCount'=>$followingCount = $user->following()->latest()->get()->count()
        ]);
    }

    public function profile(User $user) {
        $this->sharedData($user);
        return view('profile-posts', ['posts'=>$posts = $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user) {
        return response()->json(['theHTML'=>view('profile-posts-only', ['posts'=>$user->posts()->latest()->get()])->render(), 'docTitle'=>$user->username."'s Profile"]);
    }

    public function followers(User $user) {
        $this->sharedData($user);
        return view('profile-followers', ['followers'=>$user->followers()->latest()->get()]);
    }

    public function followersRaw(User $user) {
        return response()->json(['theHTML'=>view('profile-followers-only', ['followers'=>$user->followers()->latest()->get()])->render(), 'docTitle'=>$user->username."'s Followers"]);
    }

    public function following(User $user) {
        $this->sharedData($user);
        return view('profile-following', ['following'=>$user->following()->latest()->get()]);
    }

    public function followingRaw(User $user) {
        return response()->json(['theHTML'=>view('profile-following-only', ['following'=>$user->following()->latest()->get()])->render(), 'docTitle'=>$user->username."'s Following"]);
    }
}
