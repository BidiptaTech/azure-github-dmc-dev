@extends('layouts.layout')
@section('title', 'Mail Settings')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Mail Settings Form -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Mail Configuration Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('mail.settings.save') }}">
                            @csrf
                            <x-alert />

                            <!-- SMTP Configuration -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-mail-settings-line me-2"></i>SMTP Configuration</h6>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" placeholder="mail.example.com" value="{{ $settings->smtp_host ?? '' }}">
                                                        <label for="smtp_host">SMTP Host</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-floating mb-3">
                                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" placeholder="587" value="{{ $settings->smtp_port ?? 587 }}">
                                                        <label for="smtp_port">SMTP Port</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-floating mb-3">
                                                        <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                            <option value="tls" {{ isset($settings->smtp_encryption) && $settings->smtp_encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                                                            <option value="ssl" {{ isset($settings->smtp_encryption) && $settings->smtp_encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                            <option value="none" {{ isset($settings->smtp_encryption) && $settings->smtp_encryption == 'none' ? 'selected' : '' }}>None</option>
                                                        </select>
                                                        <label for="smtp_encryption">Encryption</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" placeholder="mail@example.com" value="{{ $settings->smtp_username ?? '' }}">
                                                        <label for="smtp_username">SMTP Username</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="Password" value="{{ $settings->smtp_password ?? '' }}">
                                                        <label for="smtp_password">SMTP Password</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Identity -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-at-line me-2"></i>Email Identity</h6>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="email" class="form-control" id="from_email" name="from_email" placeholder="noreply@example.com" value="{{ $settings->from_email ?? '' }}">
                                                        <label for="from_email">From Email</label>
                                                        <div class="form-text">The email address that will appear in the "From" field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="from_name" name="from_name" placeholder="Your Company Name" value="{{ $settings->from_name ?? '' }}">
                                                        <label for="from_name">From Name</label>
                                                        <div class="form-text">The name that will appear alongside the email address.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Support Contact Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-customer-service-2-line me-2"></i>Support Contact Information</h6>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="email" class="form-control" id="support_email" name="support_email" placeholder="support@example.com" value="{{ $settings->support_email ?? '' }}">
                                                        <label for="support_email">Support Email</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="support_phone" name="support_phone" placeholder="+1 (555) 123-4567" value="{{ $settings->support_phone ?? '' }}">
                                                        <label for="support_phone">Support Phone</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media Links -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-global-line me-2"></i>Social Media Links</h6>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/yourcompany" value="{{ $settings->facebook_url ?? '' }}">
                                                        <label for="facebook_url">Facebook URL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/yourcompany" value="{{ $settings->twitter_url ?? '' }}">
                                                        <label for="twitter_url">Twitter URL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/yourcompany" value="{{ $settings->instagram_url ?? '' }}">
                                                        <label for="instagram_url">Instagram URL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/company/yourcompany" value="{{ $settings->linkedin_url ?? '' }}">
                                                        <label for="linkedin_url">LinkedIn URL</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Footer Text -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-text me-2"></i>Email Footer Text</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="footer_text" name="footer_text" style="height: 100px">{{ $settings->footer_text ?? '' }}</textarea>
                                                <label for="footer_text">Footer Text</label>
                                                <div class="form-text">This text will appear in the footer of all emails.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Test Email Configuration -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card shadow-none bg-light border">
                                        <div class="card-header bg-transparent">
                                            <h6 class="mb-0"><i class="ri-speed-line me-2"></i>Test Email Configuration</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <div class="form-floating mb-3">
                                                        <input type="email" class="form-control" id="test_email" name="test_email" placeholder="youremail@example.com">
                                                        <label for="test_email">Test Email Address</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" id="send_test_email" class="btn btn-primary w-100 h-100">
                                                        <i class="ri-mail-send-line me-1"></i> Send Test Email
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="test_email_result" class="alert d-none mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line me-1"></i> Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Send test email functionality
        $('#send_test_email').on('click', function() {
            const testEmail = $('#test_email').val();
            if (!testEmail) {
                $('#test_email_result')
                    .removeClass('d-none alert-success alert-danger')
                    .addClass('alert-warning')
                    .html('<i class="ri-error-warning-line me-1"></i> Please enter an email address.');
                return;
            }
            
            // Show loading state
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
            btn.prop('disabled', true);
            
            // Clear previous result
            $('#test_email_result').addClass('d-none');
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("mail.test") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: testEmail,
                    smtp_host: $('#smtp_host').val(),
                    smtp_port: $('#smtp_port').val(),
                    smtp_encryption: $('#smtp_encryption').val(),
                    smtp_username: $('#smtp_username').val(),
                    smtp_password: $('#smtp_password').val(),
                    from_email: $('#from_email').val(),
                    from_name: $('#from_name').val()
                },
                success: function(response) {
                    $('#test_email_result')
                        .removeClass('d-none alert-warning alert-danger')
                        .addClass('alert-success')
                        .html('<i class="ri-check-line me-1"></i> ' + response.message);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    $('#test_email_result')
                        .removeClass('d-none alert-warning alert-success')
                        .addClass('alert-danger')
                        .html('<i class="ri-close-line me-1"></i> ' + (response.message || 'An error occurred. Please check your settings.'));
                },
                complete: function() {
                    // Restore button state
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
