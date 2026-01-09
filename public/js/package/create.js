$(document).ready(function() {
    // Initialize Select2
    // $('.hotel-select').select2({
    //     placeholder: 'Search for hotel...',
    //     allowClear: true,
    //     width: '100%'
    // });

    // Initialize date inputs with restrictions
    const startDateInput = $('input[name="start_date"]');
    const endDateInput = $('input[name="end_date"]');
    
    // Set minimum date to today
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    // Initialize date inputs
    startDateInput.attr('min', formatDate(today));
    endDateInput.attr('min', formatDate(tomorrow));

    // Start date change handler
    startDateInput.on('change', function() {
        const selectedStart = new Date(this.value);
        const minEndDate = new Date(selectedStart);
        minEndDate.setDate(selectedStart.getDate() + 1);

        // Update end date minimum
        endDateInput.attr('min', formatDate(minEndDate));

        // If end date is before new start date + 1, reset it
        if (endDateInput.val() && new Date(endDateInput.val()) < minEndDate) {
            endDateInput.val('');
        }

        updateDateDependentSections();
    });

    // End date change handler
    endDateInput.on('change', function() {
        updateDateDependentSections();
    });

    // Function to update date-dependent sections
    function updateDateDependentSections() {
        if (!startDateInput.val() || !endDateInput.val()) return;
        
        const startDate = new Date(startDateInput.val());
        const endDate = new Date(endDateInput.val());

        // Calculate number of nights
        const nights = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
        const days = nights + 1;

        // Update duration display
        $('#duration-display').text(`${nights} Nights ${days} Days`);

        // Update hotel nights for all rows
        $('.hotel-row').each(function() {
            updateHotelNights($(this), startDate, nights);
        });
    }

    // Function to update hotel nights for a row
    function updateHotelNights(row, startDate, nights) {
        const container = row.find('.hotel-nights-container');
        container.empty();

        for (let i = 0; i < nights; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(startDate.getDate() + i);
            const dateStr = formatDate(currentDate);
            
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            container.append(`
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input hotel-night-checkbox" type="checkbox" 
                               name="hotel_nights[]" value="${i + 1}" 
                               data-date="${dateStr}" checked>
                        <label class="form-check-label">
                            ${i + 1}${getNumberSuffix(i + 1)} Night 
                            (${dayNames[currentDate.getDay()]} ${currentDate.getDate()} ${monthNames[currentDate.getMonth()]})
                        </label>
                    </div>
                </div>
            `);
        }
    }

    // Helper function for number suffix
    function getNumberSuffix(num) {
        if (num >= 11 && num <= 13) return 'th';
        switch (num % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    // Handle hotel selection
    $(document).on('change', '.hotel-select', function() {
        var $row = $(this).closest('tr');
        var $selected = $(this).find('option:selected');
        
        if ($selected.val()) {
            var name = $selected.data('name');
            var location = $selected.data('location');
            var starRating = parseInt($selected.data('star-rating')) || 0;
            var rooms = $selected.data('rooms') || [];
            
            // Create stars HTML
            var stars = '';
            for (var i = 0; i < starRating; i++) {
                stars += '<i class="ri-star-fill text-warning"></i>';
            }
            
            // Update hotel info
            var hotelInfo = name + ' • ' + location + ' • ' + stars;
            $row.find('.selected-hotel-info').html(hotelInfo);
            
            // Populate room select
            var $roomSelect = $row.find('.room-select');
            $roomSelect.empty().append('<option value="">Select room type...</option>');
            
            rooms.forEach(function(room) {
                $roomSelect.append(
                    $('<option>', {
                        value: room.id,
                        text: room.room_type,
                        'data-beds': JSON.stringify(room.beds || []),
                        'data-max-occupancy': room.max_occupancy || 0
                    })
                );
            });

            // Clear bed configuration
            $row.find('.bed-configuration-container').html('<div class="text-muted small">Select a room type to view bed options</div>');
            $row.find('.room-info').empty();
        } else {
            $row.find('.selected-hotel-info').empty();
            $row.find('.room-select').empty().append('<option value="">Select room type...</option>');
            $row.find('.bed-configuration-container').html('<div class="text-muted small">Select a room type to view bed options</div>');
            $row.find('.room-info').empty();
        }
        
        updateRoomDistribution();
    });

    // Handle room type selection
    $(document).on('change', '.room-select', function() {
        var $row = $(this).closest('tr');
        var $selected = $(this).find('option:selected');
        if ($selected.val()) {
            var bedsStr = $selected.attr('data-beds');
            var beds = [];
            try {
                beds = JSON.parse(bedsStr || '[]');
                // Convert single object to array if needed
                if (!Array.isArray(beds)) {
                    beds = [beds];
                }
            } catch (e) {
                console.warn('Failed to parse beds data:', e);
                beds = [];
            }
            
            var maxOccupancy = parseInt($selected.data('max-occupancy')) || 0;
            
            // Update room info
            $row.find('.room-info').html(`Max Occupancy: ${maxOccupancy} persons`);
            
            // Update bed configuration
            var $bedContainer = $row.find('.bed-configuration-container');
            $bedContainer.empty();
            
            // Check if beds exists and has data
            if (beds && beds.length > 0) {
                beds.forEach(function(bed, index) {
                    $bedContainer.append(`
                        <div class="bed-config mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">${bed.room_type}</span>
                                <select name="bed_occupancy_${index}[]" class="form-select bed-occupancy-select" data-max="${bed.no_of_rooms}">
                                    ${generateOccupancyOptions(bed.no_of_rooms)}
                                </select>
                            </div>
                        </div>
                    `);
                });
            } else {
                console.warn('No bed configuration data available:', beds);
                $bedContainer.html('<div class="text-muted small">No bed configuration available</div>');
            }
        } else {
            $row.find('.bed-configuration-container').html('<div class="text-muted small">Select a room type to view bed options</div>');
            $row.find('.room-info').empty();
        }
        
        updateRoomDistribution();
    });

    function generateOccupancyOptions(maxOccupancy) {
        let options = '<option value="0">Empty</option>';
        for (let i = 1; i <= maxOccupancy; i++) {
            options += `<option value="${i}">${i} person${i > 1 ? 's' : ''}</option>`;
        }
        return options;
    }

    // Handle bed occupancy changes
    $(document).on('change', '.bed-occupancy-select', function() {
        updateRoomDistribution();
    });

    function updateRoomDistribution() {
        let totalAssigned = 0;
        let distribution = [];
        
        $('.hotel-row').each(function() {
            const hotelName = $(this).find('.hotel-select option:selected').data('name') || '';
            const roomType = $(this).find('.room-select option:selected').text() || '';
            
            if (hotelName && roomType !== 'Select room type...') {
                let roomOccupancy = 0;
                $(this).find('.bed-occupancy-select').each(function() {
                    roomOccupancy += parseInt($(this).val()) || 0;
                });
                
                if (roomOccupancy > 0) {
                    distribution.push(`${hotelName} - ${roomType}: ${roomOccupancy} person(s)`);
                    totalAssigned += roomOccupancy;
                }
            }
        });

        const totalPax = parseInt($('#total_pax').val()) || 0;
        const remaining = totalPax - totalAssigned;

        let summaryHtml = '';
        if (distribution.length > 0) {
            summaryHtml = `
                <div class="mb-2">
                    ${distribution.map(d => `<div>${d}</div>`).join('')}
                </div>
                <div class="mt-2 ${remaining < 0 ? 'text-danger' : remaining > 0 ? 'text-warning' : 'text-success'}">
                    <strong>Status:</strong> ${
                        remaining < 0 ? `Over-assigned by ${Math.abs(remaining)} person(s)` :
                        remaining > 0 ? `${remaining} person(s) still need rooms` :
                        'All guests assigned to rooms'
                    }
                </div>
            `;
        } else {
            summaryHtml = '<div class="text-muted small">Select hotels and rooms to view distribution</div>';
        }

        $('#room-distribution-summary').html(summaryHtml);
    }

    // Add PAX change handler
    $('#total_pax, #adult_pax, #child_pax').on('change', function() {
        updateRoomDistribution();
    });

    // Initialize room select with Select2
    // $('.room-select').select2({
    //     placeholder: 'Select room type...',
    //     allowClear: true,
    //     width: '100%'
    // });

    // Handle delete hotel row
    $(document).on('click', '.delete-hotel', function() {
        var $row = $(this).closest('tr');
        if ($('.hotel-row').length > 1) {
            $row.remove();
        }
    });

    // Add new hotel row
    $('#add-hotel').on('click', function() {
        // Clone the first row
        var $firstRow = $('.hotel-row:first');
        var $newRow = $firstRow.clone();
        
        // Reset all fields
        $newRow.find('select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).val('');
        });
        
        $newRow.find('.selected-hotel-info').empty();
        $newRow.find('.hotel-nights-container').empty();
        
        // Initialize Select2 for hotel select
        // $newRow.find('.hotel-select').select2({
        //     placeholder: 'Search for hotel...',
        //     allowClear: true,
        //     width: '100%'
        // });
        
        // Initialize Select2 for room select
        // $newRow.find('.room-select').select2({
        //     placeholder: 'Select room type...',
        //     allowClear: true,
        //     width: '100%'
        // }).empty().append('<option value="">Select room type...</option>');
        
        // Show delete button
        $newRow.find('.delete-hotel').show();
        
        // Update nights if dates are set
        if ($('input[name="start_date"]').val() && $('input[name="end_date"]').val()) {
            const startDate = new Date($('input[name="start_date"]').val());
            const endDate = new Date($('input[name="end_date"]').val());
            const nights = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
            updateHotelNights($newRow, startDate, nights);
        }
        
        // Append the new row
        $('#hotels-container').append($newRow);
        
        // Initialize tooltips for the new row
        $newRow.find('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Add Hotel Service button click handler
    $('#add-hotel-service').on('click', function() {
        // Get all hotels from the first hotel select
        const hotelOptions = $('.hotel-select:first option').clone();
        
        const serviceHtml = `
            <div class="hotel-service-row mb-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-hotel-line"></i></span>
                            <select name="service_hotel[]" class="form-select service-hotel-select" required>
                                <option value="">Select Hotel</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-service-line"></i></span>
                            <input type="text" name="service_name[]" class="form-control" placeholder="Service name" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">SGD</span>
                            <input type="number" name="service_cost[]" class="form-control" placeholder="Cost" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-message-2-line"></i></span>
                            <input type="text" name="service_remarks[]" class="form-control" placeholder="Remarks">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm delete-hotel-service">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add the new service row
        const newRow = $(serviceHtml);
        
        // Add hotel options to the new select
        const newSelect = newRow.find('.service-hotel-select');
        hotelOptions.each(function() {
            newSelect.append($(this).clone());
        });
        
        // Add the row to container
        $('#hotel-services-container').append(newRow);

        // Initialize Select2 for the new hotel select
        newSelect.select2({
            placeholder: "Select Hotel",
            allowClear: true,
            width: '100%'
        });
    });

    // Delete Hotel Service button click handler
    $(document).on('click', '.delete-hotel-service', function() {
        $(this).closest('.hotel-service-row').remove();
    });

    // PAX Management
    const totalPaxInput = $('#total_pax');
    const adultPaxInput = $('#adult_pax');
    const childPaxInput = $('#child_pax');
    const infantPaxInput = $('#infant_pax');

    // Handle +/- buttons for PAX
    $('.pax-minus, .pax-plus').on('click', function() {
        const target = $(this).data('target');
        const input = $(`#${target}`);
        const currentVal = parseInt(input.val()) || 0;
        
        if ($(this).hasClass('pax-minus')) {
            if (target === 'adult_pax' && currentVal <= 1) return;
            if ((target === 'child_pax' || target === 'infant_pax') && currentVal <= 0) return;
            if (target === 'total_pax' && currentVal <= 1) return;
            input.val(currentVal - 1).trigger('change');
        } else {
            input.val(currentVal + 1).trigger('change');
        }
    });

    // Handle PAX input changes
    totalPaxInput.on('change', function() {
        validateAndUpdatePax('total');
    });

    adultPaxInput.on('change', function() {
        validateAndUpdatePax('adult');
    });

    childPaxInput.on('change', function() {
        validateAndUpdatePax('child');
    });

    infantPaxInput.on('change', function() {
        validateAndUpdatePax('infant');
    });

    function validateAndUpdatePax(source) {
        let total = Math.max(1, parseInt(totalPaxInput.val()) || 0);
        let adults = Math.max(1, parseInt(adultPaxInput.val()) || 0);
        let children = Math.max(0, parseInt(childPaxInput.val()) || 0);
        let infants = Math.max(0, parseInt(infantPaxInput.val()) || 0);

        switch(source) {
            case 'total':
                if (children > total - 1) {
                    children = Math.max(0, total - 1);
                    childPaxInput.val(children);
                }
                adults = total - children;
                adultPaxInput.val(adults);
                break;
            case 'adult':
                if (adults + children > total) {
                    children = Math.max(0, total - adults);
                    childPaxInput.val(children);
                }
                total = adults + children;
                totalPaxInput.val(total);
                break;
            case 'child':
                if (adults + children > total) {
                    adults = Math.max(1, total - children);
                    adultPaxInput.val(adults);
                }
                total = adults + children;
                totalPaxInput.val(total);
                break;
            case 'infant':
                // Infants don't affect total PAX
                break;
        }

        updateChildAges();
        calculateRooms();
    }

    function updateChildAges() {
        const childCount = parseInt(childPaxInput.val()) || 0;
        const container = $('#child-ages');
        container.empty();
        
        if (childCount === 0) {
            container.html('<div class="small text-muted fst-italic" id="no-children-msg">No children added</div>');
            return;
        }
        
        for (let i = 0; i < childCount; i++) {
            container.append(`
                <div class="child-age-input">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Child ${i + 1}</span>
                        <input type="number" name="child_ages[]" class="form-control" min="0" max="17" 
                               placeholder="Age" style="width: 60px;" required>
                        <span class="input-group-text">yrs</span>
                    </div>
                </div>
            `);
        }
    }

    // Calculate rooms needed
    function calculateRooms() {
        const totalPax = parseInt($('#total_pax').val()) || 0;
        const paxPerRoom = parseInt($('#pax_per_room').val()) || 1;
        
        if (totalPax > 0 && paxPerRoom > 0) {
            const roomsNeeded = Math.ceil(totalPax / paxPerRoom);
            $('#number_of_rooms').val(roomsNeeded);
        }
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initial PAX validation
    validateAndUpdatePax('total');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle "Same Cab Type for All" checkbox
    $('#sameCabType').on('change', function() {
        // Your existing cab type logic
    });

    // Handle "Refresh Days" button
    $('#refresh-days').on('click', function() {
        // Get the start and end dates
        const startDate = $('input[name="start_date"]').val();
        const endDate = $('input[name="end_date"]').val();

        if (!startDate || !endDate) {
            alert('Please select both start and end dates first.');
            return;
        }

        // Calculate number of days
        const start = new Date(startDate);
        const end = new Date(endDate);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

        // Clear existing container
        $('.transport-activities-container').empty();

        // Generate day sections
        for (let i = 0; i < days; i++) {
            const currentDate = new Date(start);
            currentDate.setDate(start.getDate() + i);
            
            const dayHtml = `
                <div class="day-section mb-4" data-day="${i + 1}">
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Day ${i + 1} - ${formatDate(currentDate)}</h6>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary add-transport">
                                    <i class="ri-car-line"></i> Add Transport
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success add-activity">
                                    <i class="ri-map-pin-line"></i> Add Activity
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="transport-list mb-3">
                                <!-- Transport items will be added here -->
                            </div>
                            <div class="activity-list">
                                <!-- Activity items will be added here -->
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('.transport-activities-container').append(dayHtml);
        }
    });

    // Handle "Add Transport" button
    $(document).on('click', '.add-transport', function() {
        const daySection = $(this).closest('.day-section');
        const transportList = daySection.find('.transport-list');
        
        const transportHtml = `
            <div class="transport-item mb-3">
                <div class="card border">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Transport Type</label>
                                <select class="form-select transport-type">
                                    <option value="">Select Transport...</option>
                                    <option value="airport">Airport Transfer</option>
                                    <option value="city">City Transfer</option>
                                    <option value="sightseeing">Sightseeing</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vehicle Type</label>
                                <select class="form-select vehicle-type">
                                    <option value="">Select Vehicle...</option>
                                    <option value="sedan">Sedan (4 Seater)</option>
                                    <option value="suv">SUV (6 Seater)</option>
                                    <option value="van">Van (12 Seater)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cost (SGD)</label>
                                <input type="number" class="form-control transport-cost" min="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-transport">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        transportList.append(transportHtml);
    });

    // Handle "Add Activity" button
    $(document).on('click', '.add-activity', function() {
        const daySection = $(this).closest('.day-section');
        const activityList = daySection.find('.activity-list');
        
        const activityHtml = `
            <div class="activity-item mb-3">
                <div class="card border">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Activity Name</label>
                                <input type="text" class="form-control activity-name" placeholder="Enter activity name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Duration</label>
                                <select class="form-select activity-duration">
                                    <option value="half">Half Day</option>
                                    <option value="full">Full Day</option>
                                    <option value="evening">Evening</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pax</label>
                                <input type="number" class="form-control activity-pax" min="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cost (SGD)</label>
                                <input type="number" class="form-control activity-cost" min="0">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-activity">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        activityList.append(activityHtml);
    });

    // Handle remove buttons
    $(document).on('click', '.remove-transport', function() {
        $(this).closest('.transport-item').remove();
        updateTotalCost();
    });

    $(document).on('click', '.remove-activity', function() {
        $(this).closest('.activity-item').remove();
        updateTotalCost();
    });

    // Helper function to format date
    function formatDate(date) {
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]}`;
    }

    // Function to update total costs
    function updateTotalCost() {
        let transportTotal = 0;
        let activityTotal = 0;

        // Calculate transport total
        $('.transport-cost').each(function() {
            transportTotal += parseFloat($(this).val()) || 0;
        });

        // Calculate activity total
        $('.activity-cost').each(function() {
            activityTotal += parseFloat($(this).val()) || 0;
        });

        // Update the display
        $('.transport-total').text(`SGD ${transportTotal}`);
        $('.activity-total').text(`SGD ${activityTotal}`);
        $('.services-grand-total').text(`SGD ${transportTotal + activityTotal}`);
    }

    // Handle cost input changes
    $(document).on('change', '.transport-cost, .activity-cost', function() {
        updateTotalCost();
    });

    // Package Management Functions
    window.createPackage = function() {
        $('#packageModalTitle').text('Create New Package');
        $('#packageForm')[0].reset();
        $('#packageModal').modal('show');
    };

    window.editPackage = function(id) {
        $('#packageModalTitle').text('Edit Package');
        
        // Load package data via AJAX
        $.get(`/packages/${id}/edit`, function(data) {
            // Populate form with package data
            // This would be implemented when you have actual database
            console.log('Loading package data for editing:', id);
        }).fail(function() {
            alert('Error loading package data');
        });
        
        $('#packageModal').modal('show');
    };

    window.viewPackage = function(id) {
        // Redirect to package details page
        window.location.href = `/packages/${id}`;
    };

    window.deletePackage = function(id) {
        if (confirm('Are you sure you want to delete this package?')) {
            $.ajax({
                url: `/packages/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Error deleting package');
                }
            });
        }
    };

    window.savePackage = function() {
        const formData = new FormData($('#packageForm')[0]);
        const url = $('#packageForm').data('edit-mode') ? 
                   `/packages/${$('#packageForm').data('package-id')}` : 
                   '/packages';
        const method = $('#packageForm').data('edit-mode') ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#packageModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMessage = 'Validation errors:\n';
                    Object.keys(errors).forEach(key => {
                        errorMessage += `${key}: ${errors[key].join(', ')}\n`;
                    });
                    alert(errorMessage);
                } else {
                    alert('Error saving package');
                }
            }
        });
    };

    window.exportPackages = function() {
        const filters = getFilterParams();
        
        $.get('/packages-export', filters, function(response) {
            if (response.success) {
                alert(`Export prepared: ${response.count} packages in ${response.format} format`);
                // Here you would typically trigger a download
            }
        }).fail(function() {
            alert('Error exporting packages');
        });
    };

    window.togglePackageStatus = function(id) {
        $.post(`/packages/${id}/toggle-status`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        }).fail(function() {
            alert('Error updating package status');
        });
    };

    window.togglePackageFeatured = function(id) {
        $.post(`/packages/${id}/toggle-featured`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        }).fail(function() {
            alert('Error updating featured status');
        });
    };

    window.duplicatePackage = function(id) {
        if (confirm('Are you sure you want to duplicate this package?')) {
            $.post(`/packages/${id}/duplicate`, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(response) {
                location.reload();
            }).fail(function() {
                alert('Error duplicating package');
            });
        }
    };

    // Filter Functions
    window.clearFilters = function() {
        $('#searchInput').val('');
        $('#destinationFilter').val('');
        $('#categoryFilter').val('');
        $('#durationFilter').val('');
        $('#priceFilter').val('');
        $('#statusFilter').val('all');
        $('#featuredFilter').val('');
        filterPackages();
    };

    function getFilterParams() {
        return {
            search: $('#searchInput').val(),
            destination: $('#destinationFilter').val(),
            category: $('#categoryFilter').val(),
            duration: $('#durationFilter').val(),
            price_range: $('#priceFilter').val(),
            status: $('#statusFilter').val(),
            featured: $('#featuredFilter').val()
        };
    }

    function filterPackages() {
        const filters = getFilterParams();
        
        $.get('/packages-filtered', filters, function(response) {
            if (response.html) {
                $('#packagesContainer').html(response.html);
                $('#resultsCount').text(response.total);
            }
        }).fail(function() {
            console.error('Error filtering packages');
        });
    }

    // Debounced search
    let searchTimeout;
    function debouncedFilter() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterPackages, 300);
    }

    // View Toggle
    $('#gridView, #listView').on('click', function() {
        $('#gridView, #listView').removeClass('active');
        $(this).addClass('active');

        if ($(this).attr('id') === 'listView') {
            $('.package-card').addClass('list-view col-12').removeClass('col-lg-4 col-md-6');
        } else {
            $('.package-card').removeClass('list-view col-12').addClass('col-lg-4 col-md-6');
        }
    });

    // Event Listeners
    $('#searchInput').on('keyup', debouncedFilter);
    $('#destinationFilter, #categoryFilter, #durationFilter, #priceFilter, #statusFilter, #featuredFilter').on('change', filterPackages);

    // Package card hover effects
    $(document).on('mouseenter', '.package-item', function() {
        $(this).addClass('shadow-lg');
    }).on('mouseleave', '.package-item', function() {
        $(this).removeClass('shadow-lg');
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Package form validation
    $('#packageForm').on('submit', function(e) {
        e.preventDefault();
        savePackage();
    });

    // Auto-calculate duration display
    $('#packageForm input[name="duration_days"], #packageForm input[name="duration_nights"]').on('change', function() {
        const days = $('#packageForm input[name="duration_days"]').val();
        const nights = $('#packageForm input[name="duration_nights"]').val();
        
        if (days && nights) {
            $('#durationDisplay').text(`${days}D/${nights}N`);
        }
    });

    // Price formatting
    $('#packageForm input[name="price_adult"], #packageForm input[name="price_child"]').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });

    // Image preview functionality
    $('#packageForm input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Bulk actions
    $('#selectAll').on('change', function() {
        $('.package-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });

    $(document).on('change', '.package-checkbox', function() {
        updateBulkActions();
    });

    function updateBulkActions() {
        const checkedCount = $('.package-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').show();
            $('#bulkCount').text(checkedCount);
        } else {
            $('#bulkActions').hide();
        }
    }

    // Bulk delete
    window.bulkDelete = function() {
        const selectedIds = $('.package-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select packages to delete');
            return;
        }

        if (confirm(`Are you sure you want to delete ${selectedIds.length} packages?`)) {
            $.ajax({
                url: '/packages/bulk-delete',
                type: 'POST',
                data: {
                    ids: selectedIds,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Error deleting packages');
                }
            });
        }
    };

    // Bulk status update
    window.bulkUpdateStatus = function(status) {
        const selectedIds = $('.package-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select packages to update');
            return;
        }

        $.ajax({
            url: '/packages/bulk-status',
            type: 'POST',
            data: {
                ids: selectedIds,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Error updating package status');
            }
        });
    };

    // Advanced search modal
    window.showAdvancedSearch = function() {
        $('#advancedSearchModal').modal('show');
    };

    // Package statistics
    function loadPackageStats() {
        $.get('/packages/stats', function(data) {
            $('#totalPackages').text(data.total);
            $('#activePackages').text(data.active);
            $('#featuredPackages').text(data.featured);
            $('#draftPackages').text(data.draft);
        });
    }

    // Load stats on page load
    loadPackageStats();

    // Auto-refresh every 30 seconds
    setInterval(loadPackageStats, 30000);

    // Package quick actions
    $(document).on('click', '.quick-edit', function(e) {
        e.stopPropagation();
        const packageId = $(this).data('package-id');
        editPackage(packageId);
    });

    $(document).on('click', '.quick-duplicate', function(e) {
        e.stopPropagation();
        const packageId = $(this).data('package-id');
        duplicatePackage(packageId);
    });

    $(document).on('click', '.quick-toggle-status', function(e) {
        e.stopPropagation();
        const packageId = $(this).data('package-id');
        togglePackageStatus(packageId);
    });

    $(document).on('click', '.quick-toggle-featured', function(e) {
        e.stopPropagation();
        const packageId = $(this).data('package-id');
        togglePackageFeatured(packageId);
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+N for new package
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            createPackage();
        }
        
        // Ctrl+F for search focus
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            $('#searchInput').focus();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });

    // Package sorting
    $('.sort-option').on('click', function() {
        const sortBy = $(this).data('sort');
        const sortOrder = $(this).hasClass('asc') ? 'desc' : 'asc';
        
        $('.sort-option').removeClass('asc desc');
        $(this).addClass(sortOrder);
        
        const filters = getFilterParams();
        filters.sort_by = sortBy;
        filters.sort_order = sortOrder;
        
        $.get('/packages-filtered', filters, function(response) {
            if (response.html) {
                $('#packagesContainer').html(response.html);
            }
        });
    });

    // Initialize perfect scrollbar for sidebar
    if (typeof PerfectScrollbar !== 'undefined') {
        new PerfectScrollbar('.sidebar-content');
    }

    console.log('Predefined Packages Admin Interface Initialized');
}); 

