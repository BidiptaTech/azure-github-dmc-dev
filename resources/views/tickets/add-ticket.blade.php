@extends('layouts.layout')
@section('content')
@extends('layouts.datatablecss')


@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
    <div class="page-content">
    <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('attraction.edit') ? 'active' : '' }}" 
                   href="{{ route('attraction.edit', $attraction->attraction_id) }}" 
                   role="tab">
                    Attraction
                </a>
            </li>
            
            
            <li class="nav-item" role="presentation">
                
                <a class="nav-link {{ request()->routeIs('tickets.add_ticket') ? 'active' : '' }}" 
                   href="{{ route('tickets.add_ticket', $attraction->attraction_id) }}" 
                   role="tab">
                    Ticket
                </a>
            </li>
        </ul>
        <x-alert />
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Create New Ticket</h4>
                                @if(auth()->user()->role_id == '11')
                                    <a href="{{ route('tickets.bulk_upload_for_attraction', $attraction->attraction_id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="ri-upload-cloud-2-line me-1"></i>Bulk Upload Tickets
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('tickets.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="attraction_id" value="{{ $attraction->attraction_id }}">
                                    <!-- Ticket Name -->
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="form-label"><strong>Ticket Name</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Ticket Name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_price" class="form-label"><strong>Child Price(local)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="child_price" name="child_price" placeholder="Enter Child Price" value="{{ old('child_price') }}" required>
                                        @error('child_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price" class="form-label"><strong>Adult Price(local)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="adult_price" name="adult_price" placeholder="Enter Adult Price" value="{{ old('adult_price') }}" required>
                                        @error('adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>                                    
                                    
                                    <!-- Senior Adult Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price" class="form-label"><strong>Senior Citizen Price(local)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="senior_adult_price" name="senior_adult_price" placeholder="Enter Senior Citizen Price" value="{{ old('senior_adult_price') }}" required>
                                        @error('senior_adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Price NRI -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_price_nri" class="form-label"><strong>Child Price(foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="child_price_nri" name="child_price_nri" placeholder="Enter Child Price" value="{{ old('child_price_nri') }}" required>
                                        @error('child_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price_nri" class="form-label"><strong>Adult Price(foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="adult_price_nri" name="adult_price_nri" placeholder="Enter Adult Price" value="{{ old('adult_price_nri') }}" required>
                                        @error('adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>                                    
                                    
                                    <!-- Senior Adult Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price_nri" class="form-label"><strong>Senior Citizen Price(foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="senior_adult_price_nri" name="senior_adult_price_nri" placeholder="Enter Senior Citizen Price" value="{{ old('senior_adult_price_nri') }}" required>
                                        @error('senior_adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label"><strong>Important Notes</strong><span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter Description">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Remarks -->
                                    <div class="col-md-12 mb-3">
                                        <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                                        <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)"></textarea>
                                        @error('remarks')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Terms & Conditions -->
                                    <div class="col-md-12 mb-3">
                                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                                        <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..."></textarea>
                                        @error('terms_conditions')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status"><strong>Active</strong></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Create Ticket</button>
                                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Tickets</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                

                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Ticket ID</th>
                            <th>Name</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Senior Adult Price</th>
                            <th>Status</th>
                            <th>Action</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $key => $ticket)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $ticket->ticket_id }}</td>
                                <td>{{ $ticket->name }}</td>
                                <td>{{ $ticket->adult_price }}</td>
                                <td>{{ $ticket->child_price ?? 'N/A' }}</td>
                                <td>{{ $ticket->senior_adult_price ?? 'N/A' }}</td>
                                <td>
                                    @if($ticket->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- View Button -->
                                    
                                    <a href="{{ route('tickets.show', $ticket->id) }}" 
                                    class="btn btn-info btn-sm rounded-circle" 
                                    style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 0 576 512" width="16px" fill="#ffffff">
                                            <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
                                        </svg>
                                    </a>
                                    

                                    <!-- Edit Button -->
                                   
                                    <a href="{{ route('tickets.edit', $ticket->id) }}" 
                                    class="btn btn-primary btn-sm rounded-circle" 
                                    style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    

                                    <!-- Delete Button -->
                                    
                                    <button type="button" 
                                            class="btn btn-danger btn-sm rounded-circle" 
                                            style="width: 28px; height: 28px; padding: 0;" 
                                            data-toggle="modal" 
                                            data-target="#deleteModal" 
                                            onclick="setDeleteForm('{{ route('tickets.destroy', $ticket->id) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    
                                </td>
                                
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
@endsection 
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $('#description').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
        });
        $('#remarks').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter any remarks or notes (optional)...', 
        });
        $('#terms_conditions').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter terms and conditions...', 
        });
        // Initialize Select2 for city
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            tags: true,
            width: '100%'
        });
    });
</script>
@endsection