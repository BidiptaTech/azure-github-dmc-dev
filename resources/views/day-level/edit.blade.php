@extends('layouts.layout')

@section('title', 'Edit Day Level')

@section('content')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit Day Level</h4>
            <a href="{{ route('day-level.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>

        <x-alert />

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('day-level.update', $dayLevel->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="city_id">City</label>
                            <select id="city_id" name="city_id" class="form-select" required>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ (int)$dayLevel->city_id === (int)$city->id ? 'selected' : '' }}>
                                        {{ $city->name }}@if($city->country), {{ $city->country }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="country">Country</label>
                            <input id="country" name="country" class="form-control" value="{{ old('country', $dayLevel->country) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="days">Days</label>
                            <input id="days" name="days" type="number" min="1" max="365" class="form-control" value="{{ old('days', (int)$dayLevel->days) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="hotels_json">Hotels JSON</label>
                            <textarea id="hotels_json" name="hotels_json" rows="8" class="form-control">{{ old('hotels_json', json_encode($dayLevel->hotels ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="activities_json">Activities JSON</label>
                            <textarea id="activities_json" name="activities_json" rows="8" class="form-control">{{ old('activities_json', json_encode($dayLevel->activities ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="inter_json">Inter City JSON</label>
                            <textarea id="inter_json" name="inter_json" rows="8" class="form-control">{{ old('inter_json', json_encode($dayLevel->inter_city ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('day-level.show', $dayLevel->id) }}" class="btn btn-outline-info">Preview</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
