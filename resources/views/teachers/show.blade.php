@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Teacher Details</h1>
        <div class="card">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $teacher->tid }}</p>
                <p><strong>Full Name:</strong> {{ $teacher->full_name }}</p>
                <p><strong>Gender:</strong> {{ $teacher->gender }}</p>
                <p><strong>Degree:</strong> {{ $teacher->degree }}</p>
                <p><strong>Tel:</strong> {{ $teacher->tel }}</p>
            </div>
        </div>
        <a href="{{ route('teachers.index') }}" class="btn btn-primary mt-3">Back</a>
    </div>
@endsection
