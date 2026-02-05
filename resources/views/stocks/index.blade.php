@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ __('Stock Management') }}</h5>
                    <a href="{{ route('stocks.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('Add Tire') }}
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row g-2 align-items-end flex-wrap">
                            <div class="col-auto">
                                <label for="filter_by" class="form-label small mb-0">{{ __('Filter by') }}</label>
                                <select name="filter_by" id="filter_by" class="form-select form-select-sm"
                                    style="min-width: 140px;">
                                    <option value="name" {{ ($filterBy ?? 'name') === 'name' ? 'selected' : '' }}>
                                        {{ __('Name') }}</option>
                                    <option value="brand" {{ ($filterBy ?? '') === 'brand' ? 'selected' : '' }}>
                                        {{ __('Brand') }}</option>
                                    <option value="category" {{ ($filterBy ?? '') === 'category' ? 'selected' : '' }}>
                                        {{ __('Category') }}</option>
                                </select>
                            </div>
                            <div class="col-auto" id="filter-search-wrap">
                                <label for="search" class="form-label small mb-0 d-block">&nbsp;</label>
                                <input type="text" name="search" id="search" class="form-control form-control-sm"
                                    placeholder="{{ __('Search by name or code...') }}" value="{{ request('search') }}"
                                    style="min-width: 180px;">
                            </div>
                            <div class="col-auto d-none" id="filter-category-wrap">
                                <label for="category_id" class="form-label small mb-0">{{ __('Category') }}</label>
                                <select name="category_id" id="category_id" class="form-select form-select-sm"
                                    style="min-width: 180px;">
                                    <option value="">{{ __('All categories') }}</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary btn-sm" type="submit"><i
                                        class="fas fa-search me-1"></i>{{ __('Search') }}</button>
                            </div>
                        </div>
                    </form>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var filterBy = document.getElementById('filter_by');
                            var searchWrap = document.getElementById('filter-search-wrap');
                            var categoryWrap = document.getElementById('filter-category-wrap');

                            function toggleFilter() {
                                var v = filterBy.value;
                                if (v === 'category') {
                                    searchWrap.classList.add('d-none');
                                    categoryWrap.classList.remove('d-none');
                                } else {
                                    searchWrap.classList.remove('d-none');
                                    categoryWrap.classList.add('d-none');
                                }
                            }
                            filterBy.addEventListener('change', toggleFilter);
                            toggleFilter();
                        });
                    </script>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>

                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->id }}</td>
                                        <td>{{ $stock->name }}</td>

                                        <td>{{ $stock->code_tire }}</td>
                                        <td class="text-bold">{{ $stock->qty }}</td>
                                        <td>{{ number_format($stock->price) }} $</td>
                                        <td class="text-end">
                                            <a href="{{ route('stocks.edit', $stock) }}"
                                                class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('stocks.destroy', $stock) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this stock?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">{{ __('No stocks found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($stocks->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $stocks->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
