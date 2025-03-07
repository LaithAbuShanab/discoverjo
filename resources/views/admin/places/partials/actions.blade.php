<a class="btn btn-outline-warning btn-sm edit" title="Edit" href="{{ route('admin.places.edit', $place['id']) }}">
    <i class="fas fa-pencil-alt" title="Edit"></i>
</a>

<a class="btn btn-outline-primary btn-sm" title="Show" href="{{ route('admin.places.show', $place['id']) }}">
    <i class="fas fa-eye" title="show"></i>
</a>

<form method="post" action="{{ route('admin.places.destroy', $place['id']) }}" style="display:inline;">
    @csrf
    @method('delete')
    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete" style="padding-bottom: 1px;" onclick="return confirm('Are you sure you want to delete?')">
        <i class="ri-delete-bin-line" title="Edit"></i>
    </button>
</form>
