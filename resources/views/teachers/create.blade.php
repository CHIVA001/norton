@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Teacher</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('teachers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}">
        </div>
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Degree</label>
            <input type="text" name="degree" class="form-control" value="{{ old('degree') }}">
        </div>
        <div class="mb-3">
            <label>Tel</label>
            <input type="text" name="tel" class="form-control" value="{{ old('tel') }}">
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
