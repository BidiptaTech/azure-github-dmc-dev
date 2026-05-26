@extends('layouts.layout')
@section('title', 'Profile')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="row">

            {{-- LEFT PROFILE CARD --}}
            <div class="col-lg-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            @php
                            $profileImg = asset('assets/img/avatars/1.png');
                            if (!empty($user->profile_image)) {
                                $profileImg = str_starts_with($user->profile_image, 'http') ? $user->profile_image : asset('storage/' . $user->profile_image);
                            } else {
                                $profileImg = $user->logo ?? asset('assets/img/avatars/1.png');
                            }
                            @endphp
                            <img id="profile-image-preview" 
                                 src="{{ $profileImg }}" 
                                 class="rounded-circle shadow" 
                                 width="120" 
                                 height="120"
                                 style="object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-light border rounded-circle position-absolute bottom-0 end-0 shadow-sm profile-edit-img-btn" 
                                    style="width: 32px; height: 32px; padding: 0;" 
                                    title="Change photo">
                                <i class="ri-pencil-line"></i>
                            </button>
                        </div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-change-password" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="ri-lock-line me-1"></i> Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: PROFILE INFORMATION --}}
            <div class="col-lg-8">
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                    @csrf
                    <input type="file" id="profile_image_input" name="profile_image" accept="image/*" class="d-none">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Profile Information</h5>
                            <button type="submit" class="btn btn-primary btn-sm" id="profile-save-btn" style="display: none;">
                                <i class="ri-save-line me-1"></i> Save changes
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach([
                                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => $user->name],
                                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'value' => $user->phone ?? ''],
                                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'value' => $user->country ?? ''],
                                    ['name' => 'city', 'label' => 'City', 'type' => 'text', 'value' => $user->city ?? ''],
                                    ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'value' => $user->address ?? '', 'full' => true],
                                ] as $field)
                                <div class="{{ !empty($field['full']) ? 'col-12' : 'col-md-6' }}">
                                    <label class="form-label">{{ $field['label'] }}</label>
                                    <div class="input-group input-group-merge">
                                        <input type="{{ $field['type'] }}" 
                                               name="{{ $field['name'] }}" 
                                               class="form-control profile-field" 
                                               value="{{ $field['value'] }}" 
                                               readonly 
                                               data-field="{{ $field['name'] }}">
                                        <button type="button" class="btn btn-outline-secondary profile-edit-btn" data-target="{{ $field['name'] }}" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ACCOUNT DETAILS (read-only with edit icon for consistency) --}}
                    
                </form>
            </div>

        </div>

    </div>
</div>

{{-- Change Password Modal --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="ri-lock-password-line me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.password.update') }}" method="POST" id="change-password-form">
                @csrf
                <div class="modal-body">
                    @if($errors->has('current_password') || $errors->has('password'))
                        <div class="alert alert-danger py-2 mb-3">
                            <ul class="mb-0 list-unstyled small">
                                @foreach($errors->get('current_password') as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                                @foreach($errors->get('password') as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   value="{{ old('current_password') }}"
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Enter current password">
                            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="current_password" title="Show password" aria-label="Toggle password visibility">
                                <i class="ri-eye-line password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password"
                                   placeholder="Min 8 characters">
                            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password" title="Show password" aria-label="Toggle password visibility">
                                <i class="ri-eye-line password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password"
                                   placeholder="Confirm new password">
                            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password_confirmation" title="Show password" aria-label="Toggle password visibility">
                                <i class="ri-eye-line password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make field editable when edit button is clicked
    document.querySelectorAll('.profile-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var name = this.getAttribute('data-target');
            var input = document.querySelector('.profile-field[data-field="' + name + '"]');
            if (!input) return;
            input.removeAttribute('readonly');
            input.focus();
            document.getElementById('profile-save-btn').style.display = 'inline-block';
        });
    });

    // Profile image: click edit to open file picker
    document.querySelector('.profile-edit-img-btn').addEventListener('click', function() {
        document.getElementById('profile_image_input').click();
    });
    document.getElementById('profile_image_input').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('profile-image-preview').src = ev.target.result;
            };
            reader.readAsDataURL(file);
            document.getElementById('profile-save-btn').style.display = 'inline-block';
        }
    });

    // Show Save when any profile field is focused after edit
    document.querySelectorAll('.profile-field').forEach(function(input) {
        input.addEventListener('focus', function() {
            if (!this.hasAttribute('readonly')) {
                document.getElementById('profile-save-btn').style.display = 'inline-block';
            }
        });
    });

    // Password show/hide toggle in Change Password modal
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-target');
            var input = document.getElementById(id);
            var icon = this.querySelector('.password-toggle-icon');
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
                this.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
                this.setAttribute('title', 'Show password');
            }
        });
    });

    // Open Change Password modal when redirected back with validation errors
    @if(session('open_password_modal') || $errors->has('current_password') || $errors->has('password'))
    (function() {
        var modalEl = document.getElementById('changePasswordModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    })();
    @endif
});
</script>
@endpush
@endsection
