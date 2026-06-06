@extends('layouts.main')

@section('content')
<div class="container py-4">
    <h3>Edit User</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>staff</option>
                <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>cashier</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input type="text" name="password" class="form-control" placeholder="leave blank to keep current">
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Photo (optional)</label>
            @if($user->avatar)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" class="rounded" style="width:72px;height:72px;object-fit:cover;">
                </div>
            @endif
            <input type="file" name="avatar" accept="image/*" class="form-control">
        </div>
        <button class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">Back</a>
    </form>

    <hr>

    <h5>Danger Zone</h5>
    <form method="POST" action="{{ route('admin.users.reset', $user) }}" data-confirm="Reset password for this user?" class="d-inline">
        @csrf
        <button class="btn btn-warning">Reset Password (generate)</button>
    </form>

    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete this user?" class="d-inline ms-2">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger">Delete User</button>
    </form>
</div>
@endsection
