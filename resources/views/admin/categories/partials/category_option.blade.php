<option value="{{ $category->id }}">
    {{ str_repeat('-', $depth * 2) }} {{ $category->name }}
</option>
@foreach($category->children as $child)
    @include('admin.categories.partials.category_option', ['category' => $child, 'depth' => $depth + 1])
@endforeach

