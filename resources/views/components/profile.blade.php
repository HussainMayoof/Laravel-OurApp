<x-layout :doctitle="$doctitle">
    <div class="container py-md-5 container--narrow">
        <h2>
            <img class="avatar-small" src="{{$sharedData['user']->avatar}}"/> {{$sharedData['user']->username}}
            @auth
            @if (!$sharedData['isFollowing'] AND ((auth()->user()->id) != ($sharedData['user']->id)))
            <form class="ml-2 d-inline" action="/create-follow/{{$sharedData['user']->username}}" method="POST">
            @csrf
            <button class="btn btn-primary btn-sm">Follow <i class="fas fa-user-plus"></i></button>
            </form>
            @endif

            @if ($sharedData['isFollowing'])
            <form class="ml-2 d-inline" action="/remove-follow/{{$sharedData['user']->username}}" method="POST">
            @csrf
            <button class="btn btn-danger btn-sm">Stop Following <i class="fas fa-user-times"></i></button>
            </form>
            @endif

            @if((auth()->user()->username) == $sharedData['user']->username)
            <a href="/manage-avatar" class="btn btn-secondary btn-sm">Manage Avatar</a>
            @endif
            @endauth
        </h2>

        <div class="profile-nav nav nav-tabs pt-2 mb-4">
            <a href="/profile/{{$sharedData['user']->username}}" class="profile-nav-link nav-item nav-link {{Request::segment(3)=='' ? 'active' : ''}}">Posts: {{$sharedData['postCount']}}</a>
            <a href="/profile/{{$sharedData['user']->username}}/followers" class="profile-nav-link nav-item nav-link {{Request::segment(3)=='followers' ? 'active' : ''}}">Followers: {{$sharedData['followerCount']}}</a>
            <a href="/profile/{{$sharedData['user']->username}}/following" class="profile-nav-link nav-item nav-link {{Request::segment(3)=='following' ? 'active' : ''}}">Following: {{$sharedData['followingCount']}}</a>
        </div>

        <div class="profile-slot-content">
            {{$slot}}
        </div>
    </div>
</x-layout>