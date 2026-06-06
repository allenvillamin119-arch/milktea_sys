@extends('layouts.main')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Users</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">Add User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="q" value="{{ old('q', $q ?? request('q')) }}" class="form-control" placeholder="Search name, email, or role">
            <button class="btn btn-outline-secondary">Search</button>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        @if($u->avatar)
                            <img src="{{ asset('storage/' . $u->avatar) }}" alt="avatar" class="rounded-circle me-2" style="width:36px;height:36px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        @endif
                        <span>{{ $u->name }}</span>
                    </div>
                </td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role }}</td>
                <td>{{ $u->created_at }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                    <form method="POST" action="{{ route('admin.users.reset', $u) }}" class="d-inline ms-1" data-confirm="Reset password for user?">
                        @csrf
                        <button class="btn btn-sm btn-outline-warning">Reset</button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline ms-1" data-confirm="Delete user?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
