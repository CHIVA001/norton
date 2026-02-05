@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Add Stock') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stocks.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span
                                    class="text-danger">*</span></label>
                            <select name="name" id="name" class="form-select @error('name') is-invalid @enderror"
                                required>
                                <option value="">{{ __('Select Name') }}</option>
                                @foreach ($names ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('name') === $value)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="code_tire" class="form-label">{{ __('Code') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="code_tire" id="code_tire"
                                class="form-control @error('code_tire') is-invalid @enderror" value="{{ old('code_tire') }}"
                                required>
                            @error('code_tire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">{{ __('Qty') }} <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="qty" id="qty"
                                class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty', 0) }}"
                                min="0" required>
                            @error('qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">{{ __('Price') }} <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="price" id="price"
                                class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0) }}"
                                min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            <a href="{{ route('stocks.index') }}"
                                class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
