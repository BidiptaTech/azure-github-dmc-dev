@extends('layouts.layout')

@section('content')
<!-- Main Content Section -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Role
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <!-- Start of the form -->
            <form action="{{ route('roles.update', $role->id) }}" method="POST" class="card-body">
                @csrf <!-- CSRF token for protection -->
                @method('PATCH') <!-- Specify the method for update -->

                <!-- Role Name Field -->
                <div class="mb-3">
                    <label for="name" class="form-label"><strong>Role Name:</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Role Name"
                        value="{{ old('name', $role->name) }}" required>
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
                            <option value="{{ $key }}" @if(old('user_type', $role->user_type) == $key) selected @endif>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Permissions Checkbox Group -->
                <!-- <div class="mb-3">
                    <label class="form-label"><strong>Permissions:</strong></label>
                    <div class="row">
                        @foreach($permissions as $permission)
                            @if(in_array($permission->id, $assignedRolePermissions)) 
                                <div class="col-sm-6 col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permission-{{ $permission->id }}" name="permission[]"
                                            value="{{ $permission->id }}" @if(in_array($permission->id, $rolePermissions)) checked @endif>
                                        <label class="form-check-label" for="permission-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div> -->

                <!-- Status -->
                <div class="mt-2 form-check form-switch">
                    <label for="role_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$role->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="role_status" type="checkbox" id="role_status"
                        value="1">
                    <label class="form-check-label"></label>
                    @error('role_status')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="row justify-content-center mt-4">
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-2">Update Role</button>
                    </div>
                </div>

            </form>
            <!-- End of the form -->
        </div>
    </div>
</div>

@endsection
