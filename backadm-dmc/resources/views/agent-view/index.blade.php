@extends('layouts.layout')
@section('title', 'Verified Agent Registrations')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page header -->
    <div class="card bg-gradient-primary mb-4 text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="fw-bold mb-0 text-white"><i class="fas fa-check-circle me-2"></i> Verify Agent Registrations</h4>
            <p class="mb-0 opacity-75">View and manage agent registration data</p>
          </div>
          <div class="bg-white bg-opacity-25 p-3 rounded-circle">
            <i class="fas fa-user-check fa-2x text-white"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Content card -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <!-- Search Form with animated focus -->
          <form action="{{ route('registered-agents.index') }}" method="GET" class="d-flex">
            <div class="input-group input-group-merge shadow-sm border rounded">
              <span class="input-group-text border-0 bg-transparent"><i class="fas fa-search text-muted"></i></span>
              <input type="text" class="form-control border-0 search-input" name="search" 
                placeholder="Search registrations..." value="{{ $search ?? '' }}">
              <button type="submit" class="btn btn-primary rounded-end">
                Search
              </button>
            </div>
          </form>
          
          <!-- Export Dropdown Button -->
          <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center" type="button" id="exportDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-2"></i> Export Data
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="exportDropdown">
              <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" id="exportCopy">
                <i class="fas fa-copy text-primary me-2"></i> Copy to clipboard
              </a></li>
              <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" id="exportCSV">
                <i class="fas fa-file-csv text-success me-2"></i> CSV file
              </a></li>
              <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" id="exportExcel">
                <i class="fas fa-file-excel text-success me-2"></i> Excel file
              </a></li>
              <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" id="exportPDF">
                <i class="fas fa-file-pdf text-danger me-2"></i> PDF file
              </a></li>
              <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" id="exportPrint">
                <i class="fas fa-print text-primary me-2"></i> Print
              </a></li>
            </ul>
          </div>
        </div>
        
        @if(session('success'))
          <div class="alert alert-success d-flex align-items-center fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        
        @if(session('error'))
          <div class="alert alert-danger d-flex align-items-center fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        
        <!-- Stats cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card bg-light h-100">
              <div class="card-body d-flex align-items-center">
                <div class="avatar bg-primary me-3 p-2 rounded">
                  <i class="fas fa-user-check text-white"></i>
                </div>
                <div>
                  <span class="d-block text-muted small">Total Registrations</span>
                  <h5 class="mb-0">{{ $otpVerifications->total() }}</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-light h-100">
              <div class="card-body d-flex align-items-center">
                <div class="avatar bg-success me-3 p-2 rounded">
                  <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <div>
                  <span class="d-block text-muted small">Latest Registration</span>
                  <h5 class="mb-0">{{ $otpVerifications->count() > 0 ? $otpVerifications->first()->created_at->format('M d') : 'N/A' }}</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-light h-100">
              <div class="card-body d-flex align-items-center">
                <div class="avatar bg-info me-3 p-2 rounded">
                  <i class="fas fa-building text-white"></i>
                </div>
                <div>
                  <span class="d-block text-muted small">Companies</span>
                  <h5 class="mb-0">{{ $otpVerifications->unique('email')->count() }}</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-light h-100">
              <div class="card-body d-flex align-items-center">
                <div class="avatar bg-warning me-3 p-2 rounded">
                  <i class="fas fa-globe-asia text-white"></i>
                </div>
                <div>
                  <span class="d-block text-muted small">Countries</span>
                  <h5 class="mb-0">{{ $otpVerifications->count() > 0 ? 'Multiple' : '0' }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="table-responsive">
          <table class="table table-hover border-top">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th><i class="fas fa-building text-primary me-2"></i>Agency</th>
                <th><i class="fas fa-user text-info me-2"></i>Name</th>
                <th><i class="fas fa-envelope text-warning me-2"></i>Email</th>
                <th><i class="fas fa-phone text-success me-2"></i>Phone</th>
                <th><i class="fas fa-map-marker-alt text-danger me-2"></i>Location</th>
                <th><i class="fas fa-calendar-check text-primary me-2"></i>Verified On</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($otpVerifications as $key => $verification)
                <tr class="align-middle agent-row">
                  <td class="text-center fw-bold">{{ $otpVerifications->firstItem() + $key }}</td>
                  <td class="fw-semibold">{{ $verification->company_name ?? 'N/A' }}</td>
                  <td>{{ $verification->name ?? 'N/A' }}</td>
                  <td>
                    <span class="d-inline-block text-truncate" style="max-width: 150px;">
                      {{ $verification->email }}
                    </span>
                  </td>
                  <td>+{{ $verification->code ?? '' }} {{ $verification->phone ?? 'N/A' }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-xs bg-light-primary me-2 flex-shrink-0">
                        <span class="avatar-initial rounded-circle">{{ substr($verification->user_country ?? 'N/A', 0, 1) }}</span>
                      </div>
                      <div class="text-truncate" style="max-width: 120px;">
                        <span class="fw-medium text-primary d-block">{{ $verification->user_country ?? 'N/A' }}</span>
                        <small class="text-muted">{{ $verification->city ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light-success text-success">
                      <i class="fas fa-check-circle me-1"></i> 
                      {{ $verification->updated_at->format('M d, Y') }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group">
                      <a href="{{ route('registered-agents.show', $verification->id) }}" 
                        class="btn btn-sm btn-primary" 
                        data-bs-toggle="tooltip" title="View Details">
                        <i class="fas fa-eye"></i> View
                      </a>
                      <button type="button" 
                        class="btn btn-sm btn-success verify-agent-btn" 
                        data-id="{{ $verification->id }}" 
                        data-email="{{ $verification->email }}" 
                        data-bs-toggle="tooltip" title="Verify Agent">
                        <i class="fas fa-user-check"></i> Verify
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="empty-state">
                      <img src="{{ asset('assets/images/illustrations/empty-search.svg') }}" alt="No Data" width="120" class="mb-3" 
                         onerror="this.src='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/icons/inbox.svg'; this.onerror=null; this.width='80';">
                      <h5>No verified registrations found</h5>
                      <p class="text-muted">There are no verified agent registrations matching your criteria.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $otpVerifications->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="fas fa-check-circle me-2"></i> Operation completed successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<!-- Verification Confirmation Modal -->
<div class="modal fade" id="verifyAgentModal" tabindex="-1" aria-labelledby="verifyAgentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="verifyAgentModalLabel">Verify Agent</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="avatar bg-light-primary p-3 mx-auto rounded-circle mb-3">
            <i class="fas fa-user-check fa-2x text-primary"></i>
          </div>
          <h5>Confirm Agent Verification</h5>
          <p class="text-muted">Are you sure you want to verify this agent? This will change their status to active.</p>
        </div>
        <div class="agent-info p-3 bg-light rounded">
          <div class="d-flex justify-content-between mb-2">
            <span class="fw-bold">Agent:</span>
            <span id="modal-agent-name"></span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Email:</span>
            <span id="modal-agent-email"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmVerifyBtn">
          <i class="fas fa-check me-2"></i> Verify Agent
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="mb-0">Processing</h5>
        <p class="text-muted mb-0">Please wait while we verify the agent...</p>
      </div>
    </div>
  </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">Verification Failed</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <div class="avatar bg-light-danger p-3 mx-auto rounded-circle mb-3">
            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
          </div>
          <h5>Error</h5>
          <p class="text-muted error-message">An error occurred during verification.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Add hover effect to table rows
        $('.agent-row').hover(function() {
            $(this).addClass('bg-light-hover');
        }, function() {
            $(this).removeClass('bg-light-hover');
        });
        
        // Variables to store the current button being clicked
        var activeButton = null;
        var agentId = null;
        var agentEmail = null;
        
        // Initialize modals
        var verifyModal = new bootstrap.Modal(document.getElementById('verifyAgentModal'));
        var processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        
        // Verify agent button click handler - Open confirmation modal
        $('.verify-agent-btn').click(function() {
            activeButton = $(this);
            agentId = activeButton.data('id');
            agentEmail = activeButton.data('email');
            
            // Get agent name from the row for display in the modal
            var agentName = activeButton.closest('tr').find('td:eq(2)').text().trim();
            
            // Update modal with agent information
            $('#modal-agent-name').text(agentName);
            $('#modal-agent-email').text(agentEmail);
            
            // Show the confirmation modal
            verifyModal.show();
        });
        
        // Confirm verify button click handler
        $('#confirmVerifyBtn').click(function() {
            // Hide the confirmation modal and show the processing modal
            verifyModal.hide();
            processingModal.show();
            
            // Make AJAX request to verify the agent
            $.ajax({
                url: "{{ route('registered-agents.verify') }}",
                type: "POST",
                data: {
                    id: agentId,
                    email: agentEmail,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    // Hide processing modal
                    processingModal.hide();
                    
                    if (response.success) {
                        // Show success toast
                        showToast(response.message);
                        
                        // Update the button to show verified state
                        activeButton.removeClass('btn-success').addClass('btn-secondary');
                        activeButton.html('<i class="fas fa-check-circle"></i> Verified');
                        activeButton.prop('disabled', true);
                        
                        // Add a visual indicator that the row was updated
                        activeButton.closest('tr').addClass('bg-light-success');
                        
                        // Refresh the page after 2 seconds to show updated data
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Show error modal
                        $('.error-message').text(response.message || 'Verification failed');
                        errorModal.show();
                        
                        // Reset the button
                        activeButton.html('<i class="fas fa-user-check"></i> Verify');
                        activeButton.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    // Hide processing modal
                    processingModal.hide();
                    
                    console.error(xhr);
                    var errorMessage = 'Verification failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Show error modal
                    $('.error-message').text(errorMessage);
                    errorModal.show();
                    
                    // Reset the button
                    activeButton.html('<i class="fas fa-user-check"></i> Verify');
                    activeButton.prop('disabled', false);
                }
            });
        });
        
        // Simple table sorting
        $('th').click(function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
            this.asc = !this.asc;
            if (!this.asc) {
                rows = rows.reverse();
            }
            for (var i = 0; i < rows.length; i++) {
                table.append(rows[i]);
            }
            
            // Visual feedback for sort
            $('th').removeClass('sorted-asc sorted-desc');
            $(this).addClass(this.asc ? 'sorted-asc' : 'sorted-desc');
        });
        
        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index);
                var valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
            }
        }
        
        function getCellValue(row, index) {
            return $(row).children('td').eq(index).text();
        }
        
        // Export functions
        $('#exportCopy').on('click', function() {
            copyToClipboard();
            showToast("Table content copied to clipboard!");
        });
        
        $('#exportCSV').on('click', function() {
            exportTableToCSV('verified_agent_registrations.csv');
            showToast("CSV file downloaded!");
        });
        
        $('#exportExcel').on('click', function() {
            exportTableToExcel('verified_agent_registrations.xlsx');
            showToast("Excel file downloaded!");
        });
        
        $('#exportPDF').on('click', function() {
            alert('PDF export would require a PDF library');
        });
        
        $('#exportPrint').on('click', function() {
            window.print();
        });
        
        function showToast(message) {
            $('.toast-body').html('<i class="fas fa-check-circle me-2"></i> ' + message);
            var toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        }
        
        // Helper functions for export
        function copyToClipboard() {
            var range = document.createRange();
            range.selectNode(document.querySelector('table'));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        }
        
        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll('table tr');
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (var j = 0; j < cols.length; j++) 
                    row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                
                csv.push(row.join(','));
            }
            
            // Download CSV file
            downloadCSV(csv.join('\n'), filename);
        }
        
        function downloadCSV(csv, filename) {
            var csvFile = new Blob([csv], {type: "text/csv"});
            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
        
        function exportTableToExcel(filename) {
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.querySelector('table').outerHTML;
            
            // Create download link element
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableSelect], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', ' + encodeURIComponent(tableSelect);
                downloadLink.download = filename;
                downloadLink.click();
            }
        }

        // Search input animation
        $('.search-input').focus(function() {
            $(this).parent().addClass('search-focused');
        }).blur(function() {
            $(this).parent().removeClass('search-focused');
        });
    });
</script>
<style>
    /* Custom styles for this page */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .bg-light-hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .search-focused {
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25) !important;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
    
    .bg-light-primary {
        background-color: rgba(78, 115, 223, 0.1);
    }
    
    .bg-light-success {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .sorted-asc::after {
        content: " ↑";
        color: #4e73df;
    }
    
    .sorted-desc::after {
        content: " ↓";
        color: #4e73df;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 1rem;
    }
    
    .agent-row {
        transition: all 0.2s ease;
    }
    
    .agent-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        z-index: 1;
    }
    
    /* Print styles */
    @media print {
        .btn, form, .card-header, .empty-state img, .pagination {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        
        .table {
            width: 100% !important;
        }
    }
</style>
@endsection