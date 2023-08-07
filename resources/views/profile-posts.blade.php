<x-profile :sharedData="$sharedData" doctitle="{{$sharedData['user']->username}}'s Profile">
  @include('profile-posts-only')
</x-profile>