<x-profile :sharedData="$sharedData" doctitle="{{$sharedData['user']->username}}'s Followers">
  @include('profile-followers-only')
</x-profile>