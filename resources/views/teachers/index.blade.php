@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Teachers</h1>
    <a href="{{ route('teachers.create') }}" class="btn btn-primary mb-3">Add Teacher</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Degree</th>
                <th>Tel</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $teacher)
                <tr>
                    <td>{{ $teacher->tid }}</td>
                    <td>{{ $teacher->full_name }}</td>
                    <td>{{ $teacher->gender }}</td>
                    <td>{{ $teacher->degree }}</td>
                    <td>{{ $teacher->tel }}</td>
                    <td>
                        <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $teachers->links() }}
</div>
@endsection
