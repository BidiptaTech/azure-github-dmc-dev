@extends('layouts.layout')
@section('title', 'Agent Registration Details')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Vibrant Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-vibrant text-white border-0 shadow-xl agent-header-vibrant overflow-hidden position-relative">
          <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
          </div>
          <div class="card-body p-4 position-relative">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="agent-avatar-vibrant me-3">
                  <span class="avatar-text-vibrant">{{ substr($agentData['name'] ?? 'A', 0, 1) }}</span>
                  <div class="avatar-glow"></div>
                </div>
                <div>
                  <h4 class="mb-1 text-white fw-bold agent-name-glow">{{ $agentData['name'] ?? 'N/A' }}</h4>
                  <p class="mb-0 text-white-75 fw-medium">{{ $agentData['company_name'] ?? 'N/A' }}</p>
                  
                </div>
              </div>
              <div class="d-flex gap-2">
                                 <a href="{{ route('registered-agents.index') }}" class="btn btn-white-glass btn-sm">
                   <i class="ri-arrow-left-line me-1 text-white"></i> <span class="text-white">Back</span>
                 </a>
                <div class="dropdown">
                                     <button class="btn btn-white-glass btn-sm dropdown-toggle text-primary" type="button" data-bs-toggle="dropdown">
                     <i class="ri-settings-3-line"></i>
                   </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 ">
                                         <li><a class="dropdown-item verify-agent-btn" href="#" data-id="{{ $verification->id }}" data-email="{{ $verification->email }}">
                       <i class="ri-user-check-line me-2 text-success"></i> Verify Agent
                     </a></li>
                     <li><a class="dropdown-item " href="#" onclick="window.print()"><i class="ri-printer-line me-2 text-primary"></i> Print</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
        <!-- Vibrant Status Bar -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="status-bar-vibrant">
          <div class="status-card">
                         <div class="status-icon-vibrant">
               <i class="ri-time-line"></i>
             </div>
            <div class="status-content-vibrant">
              <h6 class="status-title">Awaiting Verification</h6>
              <p class="status-desc">This agent registration is pending admin approval</p>
            </div>
            <div class="status-pulse"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="row g-3">
      <!-- Agent Information Cards -->
      <div class="col-lg-8">
        <!-- Personal & Contact Info -->
        <div class="vibrant-card mb-3">
          <div class="card-header-vibrant">
                         <div class="header-icon-container">
               <i class="ri-user-line"></i>
             </div>
            <h6 class="card-title-vibrant">Personal Information</h6>
          </div>
          <div class="card-body-vibrant">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-user-settings-line text-primary"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Full Name</label>
                    <div class="info-value-vibrant">{{ $agentData['salutation'] ?? '' }} {{ $agentData['name'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-mail-line text-warning"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Email Address</label>
                    <div class="info-value-vibrant">{{ $verification->email }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-phone-line text-success"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Phone Number</label>
                    <div class="info-value-vibrant">+{{ $agentData['code'] ?? '' }} {{ $agentData['phone'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-id-card-line text-danger"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">ID Card</label>
                    <div class="info-value-vibrant">{{ $agentData['id_card'] ?? 'N/A' }} - {{ $agentData['card_number'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Company & Location Info -->
        <div class="vibrant-card mb-3">
          <div class="card-header-vibrant">
                         <div class="header-icon-container">
               <i class="ri-building-line"></i>
             </div>
            <h6 class="card-title-vibrant">Company & Location</h6>
          </div>
          <div class="card-body-vibrant">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-building-line text-info"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Company Name</label>
                    <div class="info-value-vibrant">{{ $agentData['company_name'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-map-pin-line text-danger"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Location</label>
                    <div class="info-value-vibrant">{{ $agentData['city'] ?? 'N/A' }}, {{ $agentData['user_country'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-map-line text-primary"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Address</label>
                    <div class="info-value-vibrant">{{ $agentData['agent_address'] ?? 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="info-item-vibrant">
                                     <div class="info-icon">
                     <i class="ri-global-line text-success"></i>
                   </div>
                  <div class="info-content">
                    <label class="info-label-vibrant">Service Countries</label>
                    <div class="info-value-vibrant">
                      @if(isset($agentData['country']) && is_array($agentData['country']))
                        @foreach($agentData['country'] as $country)
                                                     <span class="badge-vibrant me-1 mb-1">
                             <i class="ri-flag-line me-1"></i>{{ $country }}
                           </span>
                        @endforeach
                      @elseif(isset($agentData['country']))
                                                 <span class="badge-vibrant">
                           <i class="ri-flag-line me-1"></i>{{ $agentData['country'] }}
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
      
      <!-- Right Sidebar -->
      <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="action-card-vibrant mb-3">
                     <div class="action-header">
             <i class="ri-flashlight-line"></i>
             <span>Quick Actions</span>
           </div>
          <div class="action-body">
            <button class="btn-vibrant btn-success-vibrant verify-agent-btn" data-id="{{ $verification->id }}" data-email="{{ $verification->email }}">
                             <div class="btn-content">
                 <i class="ri-user-check-line"></i>
                 <span>Verify Agent</span>
               </div>
              <div class="btn-glow"></div>
            </button>
            
          </div>
        </div>

        <!-- Status Details -->
        <div class="vibrant-card mb-3">
          <div class="card-header-vibrant">
                         <div class="header-icon-container">
               <i class="ri-line-chart-line"></i>
             </div>
            <h6 class="card-title-vibrant">Status Timeline</h6>
          </div>
          <div class="card-body-vibrant">
            <div class="timeline-vibrant">
              <div class="timeline-item-vibrant completed">
                                 <div class="timeline-icon-vibrant bg-success">
                   <i class="ri-calendar-add-line"></i>
                 </div>
                <div class="timeline-content-vibrant">
                  <h6>Registration</h6>
                  <small>{{ $verification->created_at->format('M d, Y') }}</small>
                </div>
              </div>
              
              <div class="timeline-item-vibrant current">
                                 <div class="timeline-icon-vibrant bg-warning pulse-animation">
                   <i class="ri-time-line"></i>
                 </div>
                <div class="timeline-content-vibrant">
                  <h6>Pending Verification</h6>
                  <small>Awaiting admin approval</small>
                </div>
              </div>
              
              <div class="timeline-item-vibrant">
                                 <div class="timeline-icon-vibrant bg-light">
                   <i class="ri-check-line"></i>
                 </div>
                <div class="timeline-content-vibrant">
                  <h6>Activation</h6>
                  <small>Not completed</small>
                </div>
              </div>
            </div>
            
            <div class="reference-id-vibrant mt-3">
                             <div class="ref-icon">
                 <i class="ri-fingerprint-line"></i>
               </div>
              <div class="ref-content">
                <small>Reference ID</small>
                <span class="ref-id">{{ substr(md5($verification->id), 0, 8) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Important Notice -->
        <div class="notice-card-vibrant">
                     <div class="notice-icon">
             <i class="ri-error-warning-line"></i>
           </div>
          <div class="notice-content">
            <h6>Action Required</h6>
            <p>This registration needs admin verification to proceed with account activation.</p>
          </div>
          <div class="notice-glow"></div>
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

@section('css')
<style>
  /* CSS Reset for Vibrant Styles */
  .agent-header-vibrant,
  .status-card,
  .vibrant-card,
  .action-card-vibrant,
  .notice-card-vibrant {
    all: unset;
    display: block;
  }
  
  /* Debug Styles - Remove after testing */
  .agent-header-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    min-height: 200px !important;
  }
  
  .status-card {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%) !important;
    min-height: 100px !important;
  }
  
  /* Vibrant Agent View Styles */
  
  /* Header Vibrant Styles */
  .agent-header-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 15px !important;
    position: relative !important;
    overflow: hidden !important;
    border: none !important;
  }
  
  .floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
  }
  
  .shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
  }
  
  .shape-1 {
    width: 80px;
    height: 80px;
    top: 20%;
    right: 10%;
    animation-delay: 0s;
  }
  
  .shape-2 {
    width: 60px;
    height: 60px;
    top: 60%;
    right: 20%;
    animation-delay: 2s;
  }
  
  .shape-3 {
    width: 40px;
    height: 40px;
    top: 40%;
    right: 5%;
    animation-delay: 4s;
  }
  
  .agent-avatar-vibrant {
    width: 60px !important;
    height: 60px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3) !important;
  }
  
  .avatar-text-vibrant {
    font-size: 24px;
    font-weight: bold;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }
  
  .avatar-glow {
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    z-index: -1;
    filter: blur(15px);
    opacity: 0.7;
  }
  
  .agent-name-glow {
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
  }
  
  .btn-white-glass {
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid rgba(255, 255, 255, 0.8);
    color: #667eea;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    font-weight: 600;
    text-shadow: none;
  }
  
  .btn-white-glass:hover {
    background: rgba(255, 255, 255, 1);
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.4);
  }
  
  /* Status Bar Vibrant */
  .status-bar-vibrant {
    padding: 0;
  }
  
  .status-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 15px !important;
    padding: 20px !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3) !important;
    border: none !important;
  }
  
  .status-icon-vibrant {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    margin-right: 15px;
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
  }
  
  .status-content-vibrant h6 {
    font-weight: bold;
    color: white;
    margin-bottom: 5px;
  }
  
  .status-content-vibrant p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-size: 14px;
  }
  
  .status-pulse {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 15px;
    height: 15px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    animation: pulse-glow 2s infinite;
  }
  
  /* Vibrant Cards */
  .vibrant-card {
    background: white !important;
    border-radius: 15px !important;
    border: none !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    overflow: hidden !important;
    transition: all 0.3s ease !important;
  }
  
  .vibrant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  }
  
  .card-header-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 15px 20px !important;
    display: flex !important;
    align-items: center !important;
    border: none !important;
  }
  
  .header-icon-container {
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 16px;
  }
  
  .card-title-vibrant {
    margin: 0;
    font-weight: 600;
    font-size: 16px;
  }
  
  .card-body-vibrant {
    padding: 20px;
  }
  
  /* Info Items Vibrant */
  .info-item-vibrant {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
  }
  
  .info-item-vibrant:hover {
    background: #e9ecef;
    border-left-color: #667eea;
    transform: translateX(5px);
  }
  
  .info-icon {
    width: 35px;
    height: 35px;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 16px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
  }
  
  .info-content {
    flex: 1;
  }
  
  .info-label-vibrant {
    font-size: 11px;
    font-weight: 700;
    color: #8e9aaf;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
    display: block;
  }
  
  .info-value-vibrant {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.4;
  }
  
  .badge-vibrant {
    background: #667eea;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
  }
  
  /* Action Card */
  .action-card-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 15px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3) !important;
    border: none !important;
  }
  
  .action-header {
    color: white;
    padding: 15px 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.1);
  }
  
  .action-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  .btn-vibrant {
    position: relative;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
    text-align: left;
  }
  
  .btn-success-vibrant {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4);
  }
  
  .btn-primary-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
  }
  
  .btn-content {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    z-index: 2;
  }
  
  .btn-glow {
    position: absolute;
    inset: 0;
    background: inherit;
    filter: blur(20px);
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .btn-vibrant:hover {
    transform: translateY(-3px);
  }
  
  .btn-vibrant:hover .btn-glow {
    opacity: 0.7;
  }
  
  /* Timeline Vibrant */
  .timeline-vibrant {
    position: relative;
  }
  
  .timeline-item-vibrant {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
  }
  
  .timeline-item-vibrant:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 22px;
    top: 45px;
    width: 2px;
    height: 25px;
    background: #e0e6ed;
  }
  
  .timeline-item-vibrant.completed::after {
    background: linear-gradient(to bottom, #11998e, #38ef7d);
  }
  
  .timeline-icon-vibrant {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    margin-right: 15px;
    flex-shrink: 0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  }
  
  .timeline-content-vibrant h6 {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
    font-size: 14px;
  }
  
  .timeline-content-vibrant small {
    color: #8e9aaf;
    font-size: 12px;
  }
  
  .reference-id-vibrant {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .ref-icon {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
  }
  
  .ref-content small {
    display: block;
    color: #8e9aaf;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
  }
  
  .ref-id {
    font-weight: 700;
    color: #2c3e50;
    font-family: monospace;
    font-size: 14px;
  }
  
  /* Notice Card */
  .notice-card-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
  }
  
  .notice-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    margin-bottom: 15px;
  }
  
  .notice-content h6 {
    font-weight: bold;
    color: white;
    margin-bottom: 8px;
  }
  
  .notice-content p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
  }
  
  .notice-glow {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 8px;
    height: 8px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    animation: pulse-glow 2s infinite;
  }
  
  /* Animations */
  @keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
  }
  
  @keyframes pulse-glow {
    0% { 
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
      transform: scale(1);
    }
    70% { 
      box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
      transform: scale(1.1);
    }
    100% { 
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
      transform: scale(1);
    }
  }
  
  .pulse-animation {
    animation: pulse-glow 2s infinite;
  }
  
  /* Gradients */
  .bg-gradient-vibrant {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  
  .shadow-xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }
  
  .text-white-75 {
    color: rgba(255, 255, 255, 0.75);
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .floating-shapes {
      display: none;
    }
    
    .agent-header-vibrant .d-flex {
      flex-direction: column;
      gap: 1rem;
      text-align: center;
    }
    
    .card-body-vibrant {
      padding: 15px;
    }
    
    .info-item-vibrant {
      flex-direction: column;
      text-align: center;
    }
    
    .info-icon {
      margin-right: 0;
      margin-bottom: 10px;
    }
  }
  
  /* Print Styles */
  @media print {
    .btn-vibrant, .dropdown, .floating-shapes, .notice-glow, .status-pulse {
      display: none !important;
    }
    
    .vibrant-card, .agent-header-vibrant {
      background: white !important;
      color: black !important;
      box-shadow: none !important;
    }
  }
</style>
@endsection 