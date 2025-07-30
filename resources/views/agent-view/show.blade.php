@extends('layouts.layout')
@section('title', 'Agent Registration Details')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header with background -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-primary text-white shadow-lg border-0 overflow-hidden">
          <div class="position-absolute top-0 end-0 shape-decorator">
            <svg width="140" height="140" viewBox="0 0 140 140" fill="none">
              <circle cx="70" cy="70" r="70" fill="rgba(255,255,255,0.1)" />
              <circle cx="70" cy="70" r="50" fill="rgba(255,255,255,0.1)" />
              <circle cx="70" cy="70" r="30" fill="rgba(255,255,255,0.1)" />
            </svg>
          </div>
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md bg-white p-1 rounded-circle shadow">
                <span class="avatar-initial rounded-circle bg-primary text-white">
                  {{ substr($agentData['name'] ?? 'A', 0, 1) }}
                </span>
              </div>
              <div>
                <h4 class="mb-0 text-white fw-bold">{{ $agentData['name'] ?? 'N/A' }}</h4>
                <p class="mb-0 text-white opacity-75">{{ $agentData['company_name'] ?? 'N/A' }}</p>
              </div>
            </div>
          </div>
          <div class="card-footer bg-primary-dark border-0 py-3">
            <div class="d-flex justify-content-between">
              <a href="{{ route('registered-agents.index') }}" class="btn btn-light d-flex align-items-center gap-2 btn-hover-translate">
                <i class="fas fa-arrow-left"></i> Back to Registrations
              </a>
              
              <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 btn-hover-translate" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-cog"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 slideIn" aria-labelledby="dropdownMenuButton">
                  <li><a class="dropdown-item verify-agent-btn" href="#" data-id="{{ $verification->id }}" data-email="{{ $verification->email }}">
                    <i class="fas fa-user-check me-2 text-success"></i> Verify Agent
                  </a></li>
                  <li><a class="dropdown-item" href="#" onclick="window.print()"><i class="fas fa-print me-2 text-primary"></i> Print Details</a></li>
                  <li><a class="dropdown-item" href="#" id="exportPDF"><i class="fas fa-file-pdf me-2 text-danger"></i> Export as PDF</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#" id="convertToAgent"><i class="fas fa-user-plus me-2 text-success"></i> Convert to Active Agent</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Registration timeline with animation -->
    <div class="row mb-4 fadeInUp">
      <div class="col-12">
        <div class="card bg-light border-0 shadow-sm">
          <div class="card-body p-0">
            <div class="timeline-steps">
              <div class="timeline-step active">
                <div class="timeline-icon bg-primary shadow pulse">
                  <i class="fas fa-user-plus text-white"></i>
                </div>
                <div class="timeline-content">
                  <h6 class="mb-1 fw-bold">Registration Submitted</h6>
                  <p class="mb-0 text-muted">{{ $verification->created_at->format('M d, Y H:i') }}</p>
                </div>
              </div>
              <div class="timeline-step active">
                <div class="timeline-icon bg-success shadow pulse">
                  <i class="fas fa-check text-white"></i>
                </div>
                <div class="timeline-content">
                  <h6 class="mb-1 fw-bold">OTP Verified</h6>
                  <p class="mb-0 text-muted">{{ $verification->updated_at->format('M d, Y H:i') }}</p>
                </div>
              </div>
              <div class="timeline-step">
                <div class="timeline-icon bg-warning shadow">
                  <i class="fas fa-user-check text-white"></i>
                </div>
                <div class="timeline-content">
                  <h6 class="mb-1 fw-bold">Account Activation</h6>
                  <p class="mb-0 text-muted">Pending</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <!-- Left column: Agent details -->
      <div class="col-lg-8 col-md-7 order-md-1">
        <div class="card mb-4 shadow-lg border-0 fadeInLeft">
          <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 d-flex align-items-center">
              <span class="header-icon bg-primary text-white rounded-circle p-2 me-2">
                <i class="fas fa-user-circle"></i>
              </span>
              Agent Registration Details
            </h5>
            <span class="badge bg-success py-2 px-3 rounded-pill">
              <i class="fas fa-check-circle me-1"></i> Verified
            </span>
          </div>
          
          <div class="card-body">
            <div class="row g-4">
              <!-- Company Information Section -->
              <div class="col-md-12 info-section">
                <h6 class="section-title">
                  <span class="icon-circle bg-primary"><i class="fas fa-building text-white"></i></span>
                  Company Information
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-primary-subtle text-primary">
                        <i class="fas fa-building"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Company Name</div>
                        <div class="fw-bold">{{ $agentData['company_name'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-success-subtle text-success">
                        <i class="fas fa-map-marked-alt"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Address</div>
                        <div class="fw-bold">{{ $agentData['agent_address'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Personal Information Section -->
              <div class="col-md-12 info-section">
                <h6 class="section-title">
                  <span class="icon-circle bg-info"><i class="fas fa-user text-white"></i></span>
                  Personal Information
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-info-subtle text-info">
                        <i class="fas fa-user"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Full Name</div>
                        <div class="fw-bold">{{ $agentData['salutation'] ?? '' }} {{ $agentData['name'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-warning-subtle text-warning">
                        <i class="fas fa-envelope"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Email Address</div>
                        <div class="fw-bold">{{ $verification->email }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-success-subtle text-success">
                        <i class="fas fa-phone-alt"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Phone Number</div>
                        <div class="fw-bold">+{{ $agentData['code'] ?? '' }} {{ $agentData['phone'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-danger-subtle text-danger">
                        <i class="fas fa-id-card"></i>
                      </div>
                      <div>
                        <div class="text-muted small">ID Card</div>
                        <div class="fw-bold">{{ $agentData['id_card'] ?? 'N/A' }} - {{ $agentData['card_number'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Location Information Section -->
              <div class="col-md-12 info-section">
                <h6 class="section-title">
                  <span class="icon-circle bg-warning"><i class="fas fa-globe-asia text-white"></i></span>
                  Location Information
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-primary-subtle text-primary">
                        <i class="fas fa-map-marker-alt"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Location</div>
                        <div class="fw-bold">{{ $agentData['city'] ?? 'N/A' }}, {{ $agentData['user_country'] ?? 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="info-card">
                      <div class="info-card-icon bg-info-subtle text-info">
                        <i class="fas fa-globe"></i>
                      </div>
                      <div>
                        <div class="text-muted small">Service Countries</div>
                        <div class="fw-bold country-tags">
                          @if(isset($agentData['country']) && is_array($agentData['country']))
                            @foreach($agentData['country'] as $country)
                              <span class="badge bg-primary-subtle text-primary me-1 mb-1 country-tag">
                                <i class="fas fa-flag me-1"></i> {{ $country }}
                              </span>
                            @endforeach
                          @elseif(isset($agentData['country']))
                            <span class="badge bg-primary-subtle text-primary country-tag">
                              <i class="fas fa-flag me-1"></i> {{ $agentData['country'] }}
                            </span>
                          @else
                            N/A
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right column: Status and actions -->
      <div class="col-lg-4 col-md-5 order-md-2">
        <!-- Profile Card -->
        <div class="card mb-4 shadow-lg border-0 profile-card fadeInRight">
          <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0 d-flex align-items-center">
              <i class="fas fa-info-circle me-2"></i> Status Information
            </h5>
          </div>
          <div class="card-body text-center position-relative">
            <div class="avatar-profile mb-3 bg-gradient-primary p-2 mx-auto rounded-circle pulse-slow">
              <span class="avatar-text text-white">{{ substr($agentData['name'] ?? 'A', 0, 1) }}</span>
            </div>
            <div class="profile-status-indicator active"></div>
            <h5 class="mb-1">{{ $agentData['name'] ?? 'N/A' }}</h5>
            <p class="text-muted small mb-3">{{ $agentData['company_name'] ?? 'N/A' }}</p>
            
            <div class="mb-3">
              <span class="badge bg-success py-2 px-3 rounded-pill glow-success">
                <i class="fas fa-check-circle me-1"></i> Verification Complete
              </span>
            </div>
            
            <hr>
            
            <div class="verification-info mb-3">
              <div class="info-item">
                <div class="info-label">
                  <i class="far fa-calendar-alt text-primary me-2"></i> Registration Date
                </div>
                <div class="info-value">{{ $verification->created_at->format('M d, Y') }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">
                  <i class="far fa-calendar-check text-success me-2"></i> Verification Date
                </div>
                <div class="info-value">{{ $verification->updated_at->format('M d, Y') }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">
                  <i class="fas fa-fingerprint text-info me-2"></i> Reference ID
                </div>
                <div class="info-value">{{ substr(md5($verification->id), 0, 8) }}</div>
              </div>
            </div>
          </div>
          <div class="card-footer bg-light">
            <div class="d-grid">
              <button id="convertToAgentBtn" class="btn btn-primary btn-hover-float">
                <i class="fas fa-user-plus me-2"></i> Convert to Active Agent
              </button>
            </div>
          </div>
        </div>
        
        <!-- Note Card -->
        <div class="card mb-4 shadow-lg border-0 note-card border-start border-warning border-4 fadeInRight animation-delay-200">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-shrink-0">
                <div class="note-icon-container">
                  <div class="note-icon-bg bg-warning"></div>
                  <i class="fas fa-lightbulb note-icon text-warning"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h5 class="fw-bold text-dark">Important Note</h5>
                <p class="mb-0 text-muted">
                  This registration has been verified but not yet converted to an active agent account. To create an active agent account, use the "Convert to Active Agent" button below or from the actions menu above.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Convert to Agent Modal -->
<div class="modal fade" id="convertModal" tabindex="-1" aria-labelledby="convertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-success text-white border-0">
        <h5 class="modal-title" id="convertModalLabel">Convert to Active Agent</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="avatar-icon bg-success-subtle p-3 mx-auto rounded-circle mb-3 pulse">
            <i class="fas fa-user-check fa-2x text-success"></i>
          </div>
          <h5>Convert Registration to Active Agent Account</h5>
          <p class="text-muted">This will create a new agent account using the verified registration information.</p>
        </div>
        <div class="alert alert-info d-flex align-items-center mb-0 border-0 bg-info-subtle">
          <i class="fas fa-info-circle text-info me-2 fa-lg"></i>
          <span>This feature will be implemented in the future. For now, please create an agent manually.</span>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success rounded-pill btn-hover-float">
          <i class="fas fa-check me-2"></i> Convert to Agent
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
    // Handler for "Convert to Agent" action - both from card and dropdown
    $('#convertToAgentBtn, #convertToAgent').on('click', function(e) {
      e.preventDefault();
      var convertModal = new bootstrap.Modal(document.getElementById('convertModal'));
      convertModal.show();
    });
    
    // Handler for "Export as PDF" action
    $('#exportPDF').on('click', function(e) {
      e.preventDefault();
      alert('PDF export functionality would be implemented using a PDF library');
    });
    
    // Verify agent button click handler
    $('.verify-agent-btn').click(function(e) {
      e.preventDefault();
      var button = $(this);
      var id = button.data('id');
      var email = button.data('email');
      
      // Show confirmation modal with nicer styling
      Swal.fire({
        title: 'Verify Agent?',
        text: "Are you sure you want to verify this agent?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, verify it!'
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading state
          Swal.fire({
            title: 'Verifying...',
            html: 'Please wait while we verify the agent',
            timerProgressBar: true,
            didOpen: () => {
              Swal.showLoading();
              
              // Make AJAX request to verify the agent
              $.ajax({
                url: "{{ route('registered-agents.verify') }}",
                type: "POST",
                data: {
                  id: id,
                  email: email,
                  _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                  if (response.success) {
                    // Show success message
                    Swal.fire({
                      icon: 'success',
                      title: 'Verified!',
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500
                    });
                    
                    // Update the button text
                    button.html('<i class="fas fa-check-circle me-2 text-success"></i> Agent Verified');
                    button.addClass('disabled');
                    
                    // Update timeline - change the last step to active
                    $('.timeline-step:last-child').addClass('active');
                    $('.timeline-step:last-child .timeline-icon').addClass('pulse');
                    
                    // Update status badge in the profile card
                    $('.verification-info').prepend('<div class="alert alert-success border-0 bg-success-subtle text-success py-2 mb-3">Agent successfully verified in the database!</div>');
                    
                    // Update the profile status indicator with a checkmark
                    $('.profile-status-indicator').html('<i class="fas fa-check"></i>');
                  } else {
                    // Show error message
                    Swal.fire({
                      icon: 'error',
                      title: 'Verification Failed',
                      text: response.message || 'Failed to verify agent',
                    });
                    button.html('<i class="fas fa-user-check me-2 text-success"></i> Verify Agent');
                    button.removeClass('disabled');
                  }
                },
                error: function(xhr) {
                  console.error(xhr);
                  var errorMessage = 'Verification failed';
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                  }
                  
                  Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: errorMessage,
                  });
                  button.html('<i class="fas fa-user-check me-2 text-success"></i> Verify Agent');
                  button.removeClass('disabled');
                }
              });
            }
          });
        }
      });
    });

    // Add animations when scrolling to sections
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 0.1
    };
    
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, observerOptions);
    
    document.querySelectorAll('.fadeInLeft, .fadeInRight, .fadeInUp').forEach(el => {
      observer.observe(el);
    });
  });
</script>
@endsection

@section('styles')
<style>
  /* Custom Styles */
  /* Base styling */
  .card {
    border-radius: 12px;
    transition: all 0.3s ease;
  }
  
  .card-header {
    border-bottom: none;
    border-radius: 12px 12px 0 0 !important;
  }
  
  .badge {
    font-weight: 500;
  }
  
  /* Timeline styling */
  .timeline-steps {
    display: flex;
    justify-content: space-between;
    padding: 20px 40px;
  }
  
  .timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
  }
  
  .timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px;
    width: calc(100% - 40px);
    left: calc(50% + 20px);
    height: 3px;
    background-color: #e9ecef;
    z-index: 1;
  }
  
  .timeline-step.active:not(:last-child)::after {
    background-color: #4e73df;
  }
  
  .timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    position: relative;
    margin-bottom: 15px;
  }
  
  .timeline-content {
    text-align: center;
  }
  
  /* Information section styling */
  .info-section {
    margin-bottom: 10px;
    animation: fadeIn 0.5s ease-in-out;
  }
  
  .section-title {
    position: relative;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #495057;
    display: flex;
    align-items: center;
  }
  
  .icon-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
  }
  
  .info-card {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
  }
  
  .info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border-color: rgba(78, 115, 223, 0.2);
  }
  
  .info-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 1.2rem;
  }
  
  .country-tags {
    display: flex;
    flex-wrap: wrap;
  }
  
  .country-tag {
    border-radius: 20px;
    padding: 6px 12px;
    font-weight: 500;
    animation: pulseScale 2s infinite;
    animation-delay: calc(var(--tag-index, 0) * 0.2s);
  }
  
  /* Profile card styling */
  .profile-card {
    overflow: hidden;
  }
  
  .bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
  }
  
  .bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
  }
  
  .avatar-profile {
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .avatar-text {
    font-size: 42px;
    font-weight: bold;
  }
  
  .profile-status-indicator {
    position: absolute;
    top: 45px;
    right: 105px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #1cc88a;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #1cc88a;
  }
  
  .profile-status-indicator.active {
    animation: pulse 1.5s infinite;
  }
  
  .profile-status-indicator i {
    color: white;
    font-size: 10px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  
  .verification-info .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed #e9ecef;
  }
  
  .verification-info .info-label {
    font-size: 0.9rem;
    color: #6c757d;
  }
  
  .verification-info .info-value {
    font-weight: 600;
    color: #495057;
  }
  
  /* Note card styling */
  .note-card {
    overflow: hidden;
    position: relative;
  }
  
  .note-icon-container {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .note-icon-bg {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 10px;
    opacity: 0.2;
    transform: rotate(10deg);
  }
  
  .note-icon {
    font-size: 1.5rem;
    position: relative;
  }
  
  /* Header styling */
  .header-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .shape-decorator {
    opacity: 0.2;
    transform: rotate(10deg);
  }
  
  /* Button effects */
  .btn-hover-translate {
    transition: transform 0.2s ease;
  }
  
  .btn-hover-translate:hover {
    transform: translateY(-3px);
  }
  
  .btn-hover-float {
    transition: all 0.3s ease;
  }
  
  .btn-hover-float:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }
  
  /* Animations */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0.6); }
    70% { box-shadow: 0 0 0 10px rgba(28, 200, 138, 0); }
    100% { box-shadow: 0 0 0 0 rgba(28, 200, 138, 0); }
  }
  
  @keyframes pulseScale {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
  }
  
  .pulse {
    animation: pulse 1.5s infinite;
  }
  
  .pulse-slow {
    animation: pulse 3s infinite;
  }
  
  .glow-success {
    animation: glowSuccess 1.5s infinite alternate;
  }
  
  @keyframes glowSuccess {
    from { box-shadow: 0 0 5px rgba(28, 200, 138, 0.5); }
    to { box-shadow: 0 0 15px rgba(28, 200, 138, 0.8); }
  }
  
  /* Fade in animations */
  .fadeInLeft, .fadeInRight, .fadeInUp {
    opacity: 0;
    transition: all 1s ease;
  }
  
  .fadeInLeft {
    transform: translateX(-30px);
  }
  
  .fadeInRight {
    transform: translateX(30px);
  }
  
  .fadeInUp {
    transform: translateY(30px);
  }
  
  .fadeInLeft.visible, .fadeInRight.visible, .fadeInUp.visible {
    opacity: 1;
    transform: translateX(0) translateY(0);
  }
  
  .animation-delay-200 {
    transition-delay: 0.2s;
  }
  
  /* Dropdown animation */
  .slideIn {
    animation: slideInAnimation 0.3s ease;
    transform-origin: top;
  }
  
  @keyframes slideInAnimation {
    from {
      opacity: 0;
      transform: translateY(-10px) scaleY(0.95);
    }
    to {
      opacity: 1;
      transform: translateY(0) scaleY(1);
    }
  }
  
  /* Bootstrap 5 color variants */
  .bg-primary-subtle {
    background-color: rgba(78, 115, 223, 0.15);
  }
  
  .bg-success-subtle {
    background-color: rgba(28, 200, 138, 0.15);
  }
  
  .bg-warning-subtle {
    background-color: rgba(246, 194, 62, 0.15);
  }
  
  .bg-danger-subtle {
    background-color: rgba(231, 74, 59, 0.15);
  }
  
  .bg-info-subtle {
    background-color: rgba(54, 185, 204, 0.15);
  }
  
  .bg-primary-dark {
    background-color: #224abe;
  }
  
  /* Print styles */
  @media print {
    .btn, .dropdown, .modal, .timeline-icon::after {
      display: none !important;
    }
    
    .card {
      box-shadow: none !important;
      border: 1px solid #ddd !important;
    }
    
    .bg-gradient-primary {
      background: white !important;
      color: #000 !important;
    }
    
    .text-white {
      color: #000 !important;
    }
    
    .profile-status-indicator,
    .pulse, .pulse-slow, .glow-success {
      animation: none !important;
      box-shadow: none !important;
    }
  }
</style>
@endsection 