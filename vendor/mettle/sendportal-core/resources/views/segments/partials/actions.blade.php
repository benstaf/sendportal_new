<form action="{{ route('sendportal.segments.destroy', $segment->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <a href="{{ route('sendportal.segments.edit', $segment->id) }}"
       class="btn btn-sm btn-light">{{ __('Edit') }}</a>
    <button type="submit" class="btn btn-sm btn-light" onclick="return confirm('Are you sure you want to delete this segment?')"   >{{ __('Delete') }}</button>
</form>
