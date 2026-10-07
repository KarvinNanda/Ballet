@if (session()->has('msg'))
    <div class="alert alert-success py-2" role="status">{{ session('msg') }}</div>
@endif
