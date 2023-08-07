<x-profile :sharedData="$sharedData" doctitle="{{$sharedData['user']->username}}'s Following">
  @include('profile-following-only')
</x-profile>