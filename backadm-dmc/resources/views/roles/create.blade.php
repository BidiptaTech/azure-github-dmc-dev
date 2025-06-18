@extends('layouts.layout')
@section('content')
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Role
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('roles.store') }}" method="POST" class="card-body">
                @csrf <!-- CSRF token for protection -->

                <!-- Name Field -->
                <div class="mb-3">
                    <label for="name" class="form-label"><strong>Role Name:</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <input type="text" id="name" name="name" placeholder="Enter Role Name" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- User Type Select -->
                <div class="mb-3">
                    <label for="inputUserType" class="form-label"><strong>User Type:</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <select class="form-select @error('user_type') is-invalid @enderror" id="inputUserType" name="user_type" required>
                        <option selected disabled value>Choose User Type...</option>
                        @foreach($userTypes as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('user_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Permissions Checkbox Group (Optional) -->
                <!--
                <div class="mb-3">
                    <label class="form-label"><strong>Permissions:</strong></label>
                    <div class="row">
                        @foreach($permission as $value)
                            <div class="col-sm-6 col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permission-{{ $value->id }}" name="permission[]" value="{{ $value->id }}">
                                    <label class="form-check-label" for="permission-{{ $value->id }}">
                                        {{ $value->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                -->

                <!-- Status -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="role_status" value="0">
                            <input class="form-check-input" name="role_status" type="checkbox" id="role_status"
                                value="1">
                            <label for="role_status" class="form-check-label"><strong>Status</strong></label>
                        </div>
                    </div>
                </div>
                <!-- Submit Button -->
                <div class="row justify-content-center mt-4">
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-2">Save</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection
