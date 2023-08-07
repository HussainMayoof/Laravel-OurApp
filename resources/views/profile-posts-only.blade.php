<div class="list-group">
    @foreach ($posts as $post)
    <x-posts :post="$post" hideAuthor/>
    @endforeach
  </div>