@extends('layouts.main')

@section('content')
<div class="container py-4">
    <h3>Create User</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="staff" selected>staff</option>
                <option value="cashier">cashier</option>
                <option value="admin">admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Password (optional)</label>
            <input type="text" name="password" class="form-control" placeholder="leave blank for default">
            <small class="text-muted">Default password is <strong>password123</strong> if left blank.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Photo (optional)</label>
            <input type="file" name="avatar" accept="image/*" class="form-control">
        </div>
        <button class="btn btn-primary">Create User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>
@endsection
