{{-- Tail scripts (package total, observers, helpers) — was outside @section before refactor --}}
<script>
    // Initialize package total price display when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Update total price display initially
        updatePackageTotalPriceDisplay();
        
        // Also update when any service data changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    // Update total price when DOM changes
                    setTimeout(updatePackageTotalPriceDisplay, 100);
                }
            });
        });
        
        // Observe changes to the service data fields
        const serviceDataFields = [
            'hotel_data',
            'attraction_data', 
            'restaurant_data',
            'guide_data',
            'transport_data',
            'entry_port_data',
            'exit_port_data'
        ];
        
        serviceDataFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                observer.observe(field, {
                    attributes: true,
                    childList: true,
                    subtree: true
                });
            }
        });
    });

    // Function to check and fix guide hidden fields
    window.fixGuideHiddenFields = function() {
        console.log('=== FIXING GUIDE HIDDEN FIELDS ===');
        
        document.querySelectorAll('.guide-select').forEach((select, index) => {
            const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
            if (nameMatch) {
                const day = nameMatch[1];
                const guideIndex = nameMatch[2];
                
                console.log(`Checking guide ${index + 1} (Day ${day}, Index ${guideIndex})`);
                
                // Check if hidden fields exist
                const basePriceField = document.getElementById(`day${day}_guide_${guideIndex}_base_price`);
                const hoursField = document.getElementById(`day${day}_guide_${guideIndex}_hours`);
                const surchargeField = document.getElementById(`day${day}_guide_${guideIndex}_surcharge`);
                const totalPriceField = document.getElementById(`day${day}_guide_${guideIndex}_total_price`);
                
                console.log('Hidden fields status:', {
                    basePriceField: !!basePriceField,
                    hoursField: !!hoursField,
                    surchargeField: !!surchargeField,
                    totalPriceField: !!totalPriceField
                });
                
                // If any field is missing, create it
                if (!basePriceField || !hoursField || !surchargeField || !totalPriceField) {
                    console.log('Creating missing hidden fields...');
                    
                    const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                    if (packageSelect) {
                        // Remove any existing hidden fields first
                        const existingHiddenFields = packageSelect.parentNode.querySelectorAll('input[type="hidden"][id*="_guide_"]');
                        existingHiddenFields.forEach(field => field.remove());
                        
                        // Add hidden fields after the package select
                        const hiddenFieldsHTML = `
                            <input type="hidden" id="day${day}_guide_${guideIndex}_base_price" name="day${day}_guide_${guideIndex}_base_price" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_hours" name="day${day}_guide_${guideIndex}_hours" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_surcharge" name="day${day}_guide_${guideIndex}_surcharge" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_total_price" name="day${day}_guide_${guideIndex}_total_price" value="0">
                        `;
                        packageSelect.insertAdjacentHTML('afterend', hiddenFieldsHTML);
                        console.log('Hidden fields created successfully');
                    }
                }
            }
        });
        
        console.log('Guide hidden fields fix completed');
    };

    // Simple function to test guide pricing - call this from console
    window.testGuidePricing = function(day = 1, index = 1) {
        console.log(`Testing guide pricing for Day ${day}, Index ${index}`);
        
        const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
        const guideSelect = document.getElementById(`day${day}_guide_${index}`);
        
        console.log('Elements found:', {
            packageSelect: !!packageSelect,
            guideSelect: !!guideSelect,
            packageValue: packageSelect?.value,
            guideValue: guideSelect?.value
        });
        
        if (packageSelect && guideSelect && packageSelect.value && guideSelect.value) {
            console.log('Calling updateGuidePricing...');
            updateGuidePricing(day, index);
        } else {
            console.log('Cannot test - missing elements or values');
        }
    };

    // Test function to check multiple restaurant pricing
    window.testMultipleRestaurantPricing = function() {
        console.log('=== TESTING MULTIPLE RESTAURANT PRICING ===');
        
        // Check all restaurant forms for hidden fields
        const restaurantItems = document.querySelectorAll('.restaurant-item');
        console.log(`Found ${restaurantItems.length} restaurant forms`);
        
        restaurantItems.forEach((item, index) => {
            const restaurantIndex = index + 1;
            const totalPriceField = document.getElementById(`day1_restaurant_${restaurantIndex}_total_price`);
            const mealIdField = document.getElementById(`day1_restaurant_${restaurantIndex}_meal_id`);
            const dishNameField = document.getElementById(`day1_restaurant_${restaurantIndex}_dish_name`);
            
            console.log(`Restaurant ${restaurantIndex} hidden fields:`);
            console.log(`- Total Price: $${totalPriceField?.value || 'Not found'}`);
            console.log(`- Meal ID: ${mealIdField?.value || 'Not found'}`);
            console.log(`- Dish Name: ${dishNameField?.value || 'Not found'}`);
        });
        
        // Test the data collection function
        console.log('Calling updateRestaurantDataField()...');
        updateRestaurantDataField();
        
        // Check the collected data
        const restaurantData = document.getElementById('restaurant_data')?.value;
        
        console.log('Collected restaurant data:');
        if (restaurantData) {
            const parsedData = JSON.parse(restaurantData);
            console.log('Restaurant Data:', parsedData);
            
            // Check if all restaurants have correct pricing
            parsedData.forEach((restaurant, index) => {
                console.log(`Restaurant ${index + 1}:`);
                console.log(`- Total Price: $${restaurant.totalPrice}`);
                console.log(`- Meal Price: $${restaurant.mealPrice}`);
                console.log(`- Meal Description Price: $${restaurant.MealDescription[0]?.price || 'N/A'}`);
            });
        } else {
            console.log('No restaurant data found');
        }
    };

    // Test function to check restaurant data collection
    window.testRestaurantDataCollection = function() {
        console.log('=== TESTING RESTAURANT DATA COLLECTION ===');
        
        // Check if hidden fields exist and have values
        const totalPriceField = document.getElementById('day1_restaurant_1_total_price');
        const mealIdField = document.getElementById('day1_restaurant_1_meal_id');
        const dishNameField = document.getElementById('day1_restaurant_1_dish_name');
        
        console.log('Restaurant hidden fields:');
        console.log(`- Total Price: $${totalPriceField?.value || 'Not found'}`);
        console.log(`- Meal ID: ${mealIdField?.value || 'Not found'}`);
        console.log(`- Dish Name: ${dishNameField?.value || 'Not found'}`);
        
        // Test the data collection function
        console.log('Calling updateRestaurantDataField()...');
        updateRestaurantDataField();
        
        // Check the collected data
        const restaurantData = document.getElementById('restaurant_data')?.value;
        
        console.log('Collected restaurant data:');
        if (restaurantData) {
            console.log('Restaurant Data:', JSON.parse(restaurantData));
        } else {
            console.log('No restaurant data found');
        }
    };

    // Test function to check transport data collection
    window.testTransportDataCollection = function() {
        console.log('=== TESTING TRANSPORT DATA COLLECTION ===');
        
        // Check if hidden fields exist and have values
        const sections = ['entry', 'exit', 'transport'];
        
        sections.forEach(section => {
            const basePriceField = document.getElementById(`day1_${section}_base_price`);
            const totalPriceField = document.getElementById(`day1_${section}_total_price`);
            const serviceTypeField = document.getElementById(`day1_${section}_service_type`);
            
            if (basePriceField && totalPriceField && serviceTypeField) {
                console.log(`${section.toUpperCase()} PORT - Hidden fields:`);
                console.log(`- Base Price: $${basePriceField.value}`);
                console.log(`- Total Price: $${totalPriceField.value}`);
                console.log(`- Service Type: ${serviceTypeField.value}`);
            } else {
                console.log(`${section.toUpperCase()} PORT - Hidden fields not found`);
            }
        });
        
        // Test the data collection function
        console.log('Calling updateTransportDataField()...');
        updateTransportDataField();
        
        // Check the collected data
        const entryPortData = document.getElementById('entry_port_data')?.value;
        const exitPortData = document.getElementById('exit_port_data')?.value;
        const transportData = document.getElementById('transport_data')?.value;
        
        console.log('Collected data:');
        if (entryPortData) {
            console.log('Entry Port Data:', JSON.parse(entryPortData));
        }
        if (exitPortData) {
            console.log('Exit Port Data:', JSON.parse(exitPortData));
        }
        if (transportData) {
            console.log('Transport Data:', JSON.parse(transportData));
        }
        
        // Test form submission
        console.log('Testing form submission...');
        saveAllBookings();
    };
    
    // Quick test function for Google Maps coordinates
    window.testGoogleMapsCoordinates = function() {
        console.log('=== TESTING GOOGLE MAPS COORDINATES ===');
        
        // Check all coordinate fields
        const coordinateFields = document.querySelectorAll('input[id*="_lat"], input[id*="_lng"]');
        console.log(`Found ${coordinateFields.length} coordinate fields:`);
        
        coordinateFields.forEach(field => {
            if (field.value) {
                console.log(`${field.id}: ${field.value}`);
            }
        });
        
        // Test coordinate retrieval for all sections
        const sections = ['entry', 'exit', 'transport'];
        const days = [1, 2, 3]; // Adjust based on your form
        
        sections.forEach(section => {
            days.forEach(day => {
                const testCoords = {
                    pickup: {
                        lat: document.getElementById(`day${day}_${section}_pickup_lat`)?.value || 'not found',
                        lng: document.getElementById(`day${day}_${section}_pickup_lng`)?.value || 'not found'
                    },
                    dropoff: {
                        lat: document.getElementById(`day${day}_${section}_dropoff_lat`)?.value || 'not found',
                        lng: document.getElementById(`day${day}_${section}_dropoff_lng`)?.value || 'not found'
                    }
                };
                
                if (testCoords.pickup.lat !== 'not found' || testCoords.dropoff.lat !== 'not found') {
                    console.log(`Day ${day} ${section} coordinates:`, testCoords);
                }
            });
        });
    };
    
    // Comprehensive test function for transport data
    window.testAllTransportData = function() {
        console.log('=== COMPREHENSIVE TRANSPORT DATA TEST ===');
        
        // Test Google Maps coordinates
        testGoogleMapsCoordinates();
        
        // Test transport data collection
        console.log('Calling updateTransportDataField()...');
        updateTransportDataField();
        
        // Check all transport data fields
        const transportFields = ['transport_data', 'entry_port_data', 'exit_port_data'];
        
        transportFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && field.value) {
                try {
                    const data = JSON.parse(field.value);
                    console.log(`${fieldName}:`, data);
                    console.log(`Number of items in ${fieldName}:`, data.length);
                    
                    data.forEach((item, index) => {
                        console.log(`${fieldName}[${index}]:`, {
                            id: item.id,
                            vehicles_name: item.vehicles_name,
                            travel_type: item.travel_type,
                            type: item.type,
                            PickupPlaceid: item.PickupPlaceid,
                            DropoffPlaceid: item.DropoffPlaceid,
                            totalPrice: item.totalPrice
                        });
                    });
                } catch (error) {
                    console.error(`Error parsing ${fieldName}:`, error);
                }
            } else {
                console.log(`${fieldName}: No data or field not found`);
            }
        });
        
        // Test form submission
        console.log('Testing form submission...');
        saveAllBookings();
    };

    // Test function to check entry port pricing
    window.testEntryPortPricing = function(day = 1) {
        console.log(`=== TESTING ENTRY PORT PRICING FOR DAY ${day} ===`);
        
        // Check if hidden fields exist
        const basePriceField = document.getElementById(`day${day}_entry_base_price`);
        const totalPriceField = document.getElementById(`day${day}_entry_total_price`);
        const serviceTypeField = document.getElementById(`day${day}_entry_service_type`);
        const guestCountField = document.getElementById(`day${day}_entry_guest_count`);
        
        console.log('Hidden fields found:', {
            basePriceField: !!basePriceField,
            totalPriceField: !!totalPriceField,
            serviceTypeField: !!serviceTypeField,
            guestCountField: !!guestCountField
        });
        
        if (basePriceField && totalPriceField && serviceTypeField && guestCountField) {
            console.log('Current hidden field values:');
            console.log(`- Base Price: $${basePriceField.value}`);
            console.log(`- Total Price: $${totalPriceField.value}`);
            console.log(`- Service Type: ${serviceTypeField.value}`);
            console.log(`- Guest Count: ${guestCountField.value}`);
        }
        
        // Check if vehicle and service type are selected
        const vehicleSelect = document.querySelector(`select[name="day${day}_entry_vehicle_id"]`);
        const serviceTypeSelect = document.querySelector(`select[name="day${day}_entry_service_type"]`);
        
        if (vehicleSelect && serviceTypeSelect) {
            console.log('Current selections:');
            console.log(`- Vehicle: ${vehicleSelect.value} (${vehicleSelect.options[vehicleSelect.selectedIndex]?.text || 'None'})`);
            console.log(`- Service Type: ${serviceTypeSelect.value}`);
            
            if (vehicleSelect.value && serviceTypeSelect.value) {
                console.log('✅ Vehicle and service type selected - pricing should be calculated');
                // Trigger pricing update
                updatePricing(day, 'entry');
            } else {
                console.log('❌ Please select both vehicle and service type first');
            }
        } else {
            console.log('❌ Vehicle or service type selectors not found');
        }
    };

    // Test function to check if all required functions are available
    window.testAllFunctions = function() {
        console.log('=== TESTING ALL REQUIRED FUNCTIONS ===');
        
        const requiredFunctions = [
            'updateGuidePricing',
            'updateAttractionPricing', 
            'loadGuideDetails',
            'loadAttractionDetails',
            'loadRestaurantDetails',
            'updateVehicleDetails',
            'updatePricing',
            'updatePackagePrices',
            'updatePackagePricesForTime',
            'calculateAllGuidePricing',
            'fixGuideHiddenFields',
            'quickFixGuidePricing'
        ];
        
        const results = {};
        
        requiredFunctions.forEach(funcName => {
            const isAvailable = typeof window[funcName] === 'function';
            results[funcName] = isAvailable;
            console.log(`${funcName}: ${isAvailable ? '✅ AVAILABLE' : '❌ MISSING'}`);
        });
        
        const missingFunctions = Object.keys(results).filter(func => !results[func]);
        
        if (missingFunctions.length === 0) {
            console.log('🎉 ALL FUNCTIONS ARE AVAILABLE!');
        } else {
            console.log('❌ MISSING FUNCTIONS:', missingFunctions);
        }
        
        return results;
    };

    // Quick fix function - run this to immediately calculate all guide pricing
    window.quickFixGuidePricing = function() {
        console.log('=== QUICK FIX: CALCULATING ALL GUIDE PRICING ===');
        
        // First, ensure hidden fields exist
        fixGuideHiddenFields();
        
        // Then calculate pricing for all guides
        calculateAllGuidePricing();
        
        // Finally, update the guide data field
        updateGuideDataField();
        
        console.log('Quick fix completed! Check the guide_data field for updated pricing.');
    };

    // Simple function to test guide pricing - call this from console
    window.testGuidePricing = function(day = 1, index = 1) {
        console.log(`Testing guide pricing for Day ${day}, Index ${index}`);
        
        const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
        const guideSelect = document.getElementById(`day${day}_guide_${index}`);
        
        console.log('Elements found:', {
            packageSelect: !!packageSelect,
            guideSelect: !!guideSelect,
            packageValue: packageSelect?.value,
            guideValue: guideSelect?.value
        });
        
        if (packageSelect && guideSelect && packageSelect.value && guideSelect.value) {
            console.log('Calling updateGuidePricing...');
            updateGuidePricing(day, index);
        } else {
            console.log('Cannot test - missing elements or values');
        }
    };

                 
                 // Function to initialize transport service type styling
                 window.initializeTransportServiceTypeStyling = function() {
                     console.log('Initializing transport service type styling...');
                     
                     // Find all transport service type radio buttons
                     const radioButtons = document.querySelectorAll('.transport-service-type');
                     
                     radioButtons.forEach(radio => {
                         if (radio.checked) {
                             const dayMatch = radio.id.match(/day(\d+)_transport/);
                             const typeMatch = radio.id.match(/_(point_to_point|hourly|local_transfer)/);
                             const indexMatch = radio.id.match(/transport_(\d+)_/);
                             
                             if (dayMatch && typeMatch) {
                                 const day = dayMatch[1];
                                 const type = typeMatch[1];
                                 const index = indexMatch ? indexMatch[1] : 1;
                                 
                                 console.log(`Initializing: Day ${day}, Type: ${type}, Index: ${index}`);
                                 updateTransportServiceTypeStyling(day, type, index);
                             }
                         }
                     });
                 };
                 
                 // Function to update visual styling for transport service type radio buttons
                 window.updateTransportServiceTypeStyling = function(day, selectedType, index = 1) {
                     const types = ['point_to_point', 'hourly', 'local_transfer'];
                     
                     types.forEach(type => {
                         const radioId = index === 1 ? 
                             `day${day}_transport_${type}` : 
                             `day${day}_transport_${index}_${type}`;
                         const radio = document.getElementById(radioId);
                         const label = radio ? radio.nextElementSibling : null;
                         
                         if (radio && label) {
                             if (type === selectedType) {
                                 // Selected state
                                 label.classList.add('text-success', 'fw-bold');
                                 label.classList.remove('text-muted');
                                 
                                 // Add specific styling for each type
                                 if (type === 'point_to_point') {
                                     label.classList.add('text-danger');
                                 } else if (type === 'hourly') {
                                     label.classList.add('text-primary');
                                 } else if (type === 'local_transfer') {
                                     label.classList.add('text-success');
                                 }
                             } else {
                                 // Unselected state
                                 label.classList.remove('text-success', 'fw-bold', 'text-danger', 'text-primary');
                                 label.classList.add('text-muted');
                             }
                         }
                     });
                 };
                 
                 // Function to calculate pricing for all guides and store in hidden fields
                 window.calculateAllGuidePricing = function() {
                     console.log('=== CALCULATING ALL GUIDE PRICING ===');
                     
                     document.querySelectorAll('.guide-select').forEach((select, index) => {
                         if (select.value) {
                             const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                             if (nameMatch) {
                                 const day = nameMatch[1];
                                 const guideIndex = nameMatch[2];
                                 
                                 const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                                 if (packageSelect && packageSelect.value) {
                                     console.log(`Calculating pricing for Day ${day}, Guide ${guideIndex}`);
                                     
                                     const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                                     const selectedGuide = select.options[select.selectedIndex];
                                     
                                     if (selectedPackage && selectedGuide && selectedPackage.dataset) {
                                         const basePrice = parseFloat(selectedPackage.dataset.price) || 0;
                                         const hours = parseInt(selectedPackage.dataset.hours) || 0;
                                         
                                         // Calculate surcharge based on pickup time
                                         const pickupTime = document.getElementById(`day${day}_guide_${guideIndex}_pickup_time`)?.value || '';
                                         let surcharge = 0;
                                         
                                         if (pickupTime) {
                                             const pickupHour = parseInt(pickupTime.split(':')[0]);
                                             const nightStartTime = selectedGuide.dataset.nightStartTime;
                                             const nightEndTime = selectedGuide.dataset.nightEndTime;
                                             
                                             if (nightStartTime && nightEndTime) {
                                                 const nightStart = parseInt(nightStartTime.split(':')[0]);
                                                 const nightEnd = parseInt(nightEndTime.split(':')[0]) - 1;
                                                 
                                                 const isNightTime = (pickupHour >= nightStart && pickupHour <= nightEnd) || 
                                                                    (nightStart > nightEnd && (pickupHour >= nightStart || pickupHour <= nightEnd));
                                                 
                                                 if (isNightTime) {
                                                     surcharge = parseFloat(selectedGuide.dataset.nightSurcharge) || 0;
                                                 }
                                             }
                                         }
                                         
                                         const totalPrice = basePrice + surcharge;
                                         
                                         // Update hidden fields
                                         const basePriceField = document.getElementById(`day${day}_guide_${guideIndex}_base_price`);
                                         const hoursField = document.getElementById(`day${day}_guide_${guideIndex}_hours`);
                                         const surchargeField = document.getElementById(`day${day}_guide_${guideIndex}_surcharge`);
                                         const totalPriceField = document.getElementById(`day${day}_guide_${guideIndex}_total_price`);
                                         
                                         if (basePriceField) basePriceField.value = basePrice.toFixed(2);
                                         if (hoursField) hoursField.value = hours.toString();
                                         if (surchargeField) surchargeField.value = surcharge.toFixed(2);
                                         if (totalPriceField) totalPriceField.value = totalPrice.toFixed(2);
                                         
                                         console.log(`Pricing calculated for Day ${day}, Guide ${guideIndex}:`, {
                                             basePrice: basePrice.toFixed(2),
                                             hours: hours,
                                             surcharge: surcharge.toFixed(2),
                                             totalPrice: totalPrice.toFixed(2)
                                         });
                                     }
                                 }
                             }
                         }
                     });
                     
                     console.log('All guide pricing calculated');
                 }

                 // Function to manually trigger pricing for all guides
                 window.triggerAllGuidePricing = function() {
                     console.log('=== TRIGGERING ALL GUIDE PRICING ===');
        
                     document.querySelectorAll('.guide-select').forEach((select, index) => {
                         if (select.value) {
                             const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                             if (nameMatch) {
                                 const day = nameMatch[1];
                                 const guideIndex = nameMatch[2];
                                 
                                 const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                                 if (packageSelect && packageSelect.value) {
                                     console.log(`Triggering pricing for Day ${day}, Guide ${guideIndex}`);
                                     updateGuidePricing(day, guideIndex);
                                 }
                             }
                         }
                     });
                     
                     console.log('All guide pricing triggered');
                 };

                 // Google Maps Autocomplete Functionality
                 window.initializeGoogleMapsAutocomplete = function() {
                     console.log('Initializing Google Maps Autocomplete...');
                     
                     // Get selected country for location bias
                     const selectedCountry = document.getElementById('user_country')?.value || '';
                     const selectedCity = ''; // City will be selected per service section
                     
                     // Create location bias for better search results
                     let locationBias = null;
                     if (selectedCountry && selectedCity) {
                         // Use the city as the center point for location bias
                         locationBias = selectedCity + ', ' + selectedCountry;
                     } else if (selectedCountry) {
                         locationBias = selectedCountry;
                     }
                     
                     // Initialize autocomplete for all transport location inputs
                     document.querySelectorAll('.google-maps-autocomplete').forEach(input => {
                         if (input && !input.hasAttribute('data-autocomplete-initialized')) {
                             console.log('Initializing autocomplete for:', input.id);
                             
                             // Create autocomplete instance
                             const autocomplete = new google.maps.places.Autocomplete(input, {
                                 types: ['establishment', 'geocode'],
                                 componentRestrictions: { country: getCountryCode(selectedCountry) },
                                 fields: ['place_id', 'geometry', 'formatted_address', 'name', 'address_components']
                             });
                             
                             // Add place_changed event listener
                             autocomplete.addListener('place_changed', function() {
                                 const place = autocomplete.getPlace();
                                 
                                 if (!place.geometry) {
                                     console.log('No geometry found for selected place');
                                     return;
                                 }
                                 
                                 console.log('Place selected:', place);
                                 
                                 // Extract coordinates
                                 const lat = place.geometry.location.lat();
                                 const lng = place.geometry.location.lng();
                                 
                                 // Update hidden fields based on input ID
                                 const inputId = input.id;
                                 
                                 // Determine field type (pickup/dropoff) and day/index
                                 if (inputId.includes('pickup')) {
                                     const latField = document.getElementById(inputId.replace('_location', '_lat'));
                                     const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                                     const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                                     
                                     if (latField) latField.value = lat;
                                     if (lngField) lngField.value = lng;
                                     if (placeIdField) placeIdField.value = place.place_id;
                                     
                                     console.log(`Updated pickup coordinates: ${lat}, ${lng}`);
                                 } else if (inputId.includes('dropoff')) {
                                     const latField = document.getElementById(inputId.replace('_location', '_lat'));
                                     const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                                     const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                                     
                                     if (latField) latField.value = lat;
                                     if (lngField) lngField.value = lng;
                                     if (placeIdField) placeIdField.value = place.place_id;
                                     
                                     console.log(`Updated dropoff coordinates: ${lat}, ${lng}`);
                                 }
                                 
                                 // Update input value with formatted address
                                 input.value = place.formatted_address || place.name || input.value;
                                 
                                 // Validate point-to-point search button after Google Maps autocomplete selection
                                 if (inputId && inputId.includes('transport') && (inputId.includes('pickup_location') || inputId.includes('dropoff_location'))) {
                                     const dayMatch = inputId.match(/day(\d+)_transport/);
                                     if (dayMatch) {
                                         const day = parseInt(dayMatch[1]);
                                         
                                         // Check if it's hourly transport
                                         if (inputId.includes('hourly_pickup_location')) {
                                             // Hourly transport - extract index from pattern: dayX_transport_Y_hourly_pickup_location or dayX_transport_hourly_pickup_location
                                             if (inputId.match(/day\d+_transport_hourly_pickup_location$/)) {
                                                 // Initial transport (index 0) - pattern: dayX_transport_hourly_pickup_location
                                                 setTimeout(() => {
                                                     enableSearchButton(day, 'transport_hourly', 0);
                                                 }, 50);
                                             } else {
                                                 // Dynamic transport - extract index from pattern: dayX_transport_Y_hourly_pickup_location
                                                 const indexMatch = inputId.match(/day\d+_transport_(\d+)_hourly_pickup_location/);
                                                 if (indexMatch) {
                                                     const index = parseInt(indexMatch[1]);
                                                     setTimeout(() => {
                                                         enableSearchButton(day, `transport_${index}_hourly`, index);
                                                     }, 50);
                                                 }
                                             }
                                         } else {
                                             // Point-to-point transport
                                             // Check if it's the initial transport (no index in field name) or dynamic transport
                                             if (inputId.match(/day\d+_transport_(pickup|dropoff)_location$/)) {
                                                 // Initial transport (index 0) - pattern: dayX_transport_pickup_location or dayX_transport_dropoff_location
                                                 setTimeout(() => {
                                                     enableSearchButton(day, 'transport_additional', 0);
                                                 }, 50);
                                             } else {
                                                 // Dynamic transport - extract index from pattern: dayX_transport_Y_pickup_location
                                                 const indexMatch = inputId.match(/day\d+_transport_(\d+)_(pickup|dropoff)_location/);
                                                 if (indexMatch) {
                                                     const index = parseInt(indexMatch[1]);
                                                     setTimeout(() => {
                                                         enableSearchButton(day, `transport_${index}_additional`, index);
                                                     }, 50);
                                                 }
                                             }
                                         }
                                     }
                                 }
                             });
                             
                             // Mark as initialized
                             input.setAttribute('data-autocomplete-initialized', 'true');
                         }
                     });
                 };
                 
                 // Helper function to get country code from country name
                 window.getCountryCode = function(countryName) {
                     const countryCodes = {
                         'India': 'IN',
                         'United States': 'US',
                         'United Kingdom': 'GB',
                         'Canada': 'CA',
                         'Australia': 'AU',
                         'Germany': 'DE',
                         'France': 'FR',
                         'Italy': 'IT',
                         'Spain': 'ES',
                         'Netherlands': 'NL',
                         'Belgium': 'BE',
                         'Switzerland': 'CH',
                         'Austria': 'AT',
                         'Sweden': 'SE',
                         'Norway': 'NO',
                         'Denmark': 'DK',
                         'Finland': 'FI',
                         'Poland': 'PL',
                         'Czech Republic': 'CZ',
                         'Hungary': 'HU',
                         'Slovakia': 'SK',
                         'Slovenia': 'SI',
                         'Croatia': 'HR',
                         'Serbia': 'RS',
                         'Bosnia and Herzegovina': 'BA',
                         'Montenegro': 'ME',
                         'Albania': 'AL',
                         'North Macedonia': 'MK',
                         'Bulgaria': 'BG',
                         'Romania': 'RO',
                         'Greece': 'GR',
                         'Turkey': 'TR',
                         'Cyprus': 'CY',
                         'Malta': 'MT',
                         'Portugal': 'PT',
                         'Ireland': 'IE',
                         'Iceland': 'IS',
                         'Luxembourg': 'LU',
                         'Liechtenstein': 'LI',
                         'Monaco': 'MC',
                         'Andorra': 'AD',
                         'San Marino': 'SM',
                         'Vatican City': 'VA',
                         'Japan': 'JP',
                         'South Korea': 'KR',
                         'China': 'CN',
                         'Singapore': 'SG',
                         'Thailand': 'TH',
                         'Malaysia': 'MY',
                         'Indonesia': 'ID',
                         'Philippines': 'PH',
                         'Vietnam': 'VN',
                         'Cambodia': 'KH',
                         'Laos': 'LA',
                         'Myanmar': 'MM',
                         'Brunei': 'BN',
                         'East Timor': 'TL',
                         'New Zealand': 'NZ',
                         'Fiji': 'FJ',
                         'Papua New Guinea': 'PG',
                         'Solomon Islands': 'SB',
                         'Vanuatu': 'VU',
                         'New Caledonia': 'NC',
                         'French Polynesia': 'PF',
                         'Samoa': 'WS',
                         'Tonga': 'TO',
                         'Kiribati': 'KI',
                         'Tuvalu': 'TV',
                         'Nauru': 'NR',
                         'Palau': 'PW',
                         'Marshall Islands': 'MH',
                         'Micronesia': 'FM',
                         'Brazil': 'BR',
                         'Argentina': 'AR',
                         'Chile': 'CL',
                         'Peru': 'PE',
                         'Colombia': 'CO',
                         'Venezuela': 'VE',
                         'Ecuador': 'EC',
                         'Bolivia': 'BO',
                         'Paraguay': 'PY',
                         'Uruguay': 'UY',
                         'Guyana': 'GY',
                         'Suriname': 'SR',
                         'French Guiana': 'GF',
                         'Mexico': 'MX',
                         'Guatemala': 'GT',
                         'Belize': 'BZ',
                         'El Salvador': 'SV',
                         'Honduras': 'HN',
                         'Nicaragua': 'NI',
                         'Costa Rica': 'CR',
                         'Panama': 'PA',
                         'Cuba': 'CU',
                         'Jamaica': 'JM',
                         'Haiti': 'HT',
                         'Dominican Republic': 'DO',
                         'Puerto Rico': 'PR',
                         'Bahamas': 'BS',
                         'Barbados': 'BB',
                         'Trinidad and Tobago': 'TT',
                         'Grenada': 'GD',
                         'Saint Vincent and the Grenadines': 'VC',
                         'Saint Lucia': 'LC',
                         'Dominica': 'DM',
                         'Antigua and Barbuda': 'AG',
                         'Saint Kitts and Nevis': 'KN',
                         'South Africa': 'ZA',
                         'Egypt': 'EG',
                         'Morocco': 'MA',
                         'Algeria': 'DZ',
                         'Tunisia': 'TN',
                         'Libya': 'LY',
                         'Sudan': 'SD',
                         'South Sudan': 'SS',
                         'Ethiopia': 'ET',
                         'Somalia': 'SO',
                         'Kenya': 'KE',
                         'Tanzania': 'TZ',
                         'Uganda': 'UG',
                         'Rwanda': 'RW',
                         'Burundi': 'BI',
                         'Democratic Republic of the Congo': 'CD',
                         'Republic of the Congo': 'CG',
                         'Central African Republic': 'CF',
                         'Cameroon': 'CM',
                         'Chad': 'TD',
                         'Niger': 'NE',
                         'Nigeria': 'NG',
                         'Benin': 'BJ',
                         'Togo': 'TG',
                         'Ghana': 'GH',
                         'Ivory Coast': 'CI',
                         'Liberia': 'LR',
                         'Sierra Leone': 'SL',
                         'Guinea': 'GN',
                         'Guinea-Bissau': 'GW',
                         'Senegal': 'SN',
                         'The Gambia': 'GM',
                         'Mauritania': 'MR',
                         'Mali': 'ML',
                         'Burkina Faso': 'BF',
                         'Cape Verde': 'CV',
                         'Sao Tome and Principe': 'ST',
                         'Equatorial Guinea': 'GQ',
                         'Gabon': 'GA',
                         'Angola': 'AO',
                         'Zambia': 'ZM',
                         'Zimbabwe': 'ZW',
                         'Botswana': 'BW',
                         'Namibia': 'NA',
                         'Lesotho': 'LS',
                         'Eswatini': 'SZ',
                         'Mozambique': 'MZ',
                         'Madagascar': 'MG',
                         'Comoros': 'KM',
                         'Mauritius': 'MU',
                         'Seychelles': 'SC',
                         'Russia': 'RU',
                         'Ukraine': 'UA',
                         'Belarus': 'BY',
                         'Lithuania': 'LT',
                         'Latvia': 'LV',
                         'Estonia': 'EE',
                         'Moldova': 'MD',
                         'Georgia': 'GE',
                         'Armenia': 'AM',
                         'Azerbaijan': 'AZ',
                         'Kazakhstan': 'KZ',
                         'Uzbekistan': 'UZ',
                         'Turkmenistan': 'TM',
                         'Kyrgyzstan': 'KG',
                         'Tajikistan': 'TJ',
                         'Afghanistan': 'AF',
                         'Pakistan': 'PK',
                         'Nepal': 'NP',
                         'Bhutan': 'BT',
                         'Bangladesh': 'BD',
                         'Sri Lanka': 'LK',
                         'Maldives': 'MV',
                         'Iran': 'IR',
                         'Iraq': 'IQ',
                         'Kuwait': 'KW',
                         'Saudi Arabia': 'SA',
                         'Yemen': 'YE',
                         'Oman': 'OM',
                         'United Arab Emirates': 'AE',
                         'Qatar': 'QA',
                         'Bahrain': 'BH',
                         'Israel': 'IL',
                         'Palestine': 'PS',
                         'Jordan': 'JO',
                         'Lebanon': 'LB',
                         'Syria': 'SY',
                         'Kuwait': 'KW',
                         'Qatar': 'QA',
                         'Bahrain': 'BH',
                         'Oman': 'OM',
                         'Yemen': 'YE'
                     };
                     
                     return countryCodes[countryName] || '';
                 };
                 
                 // Function to reinitialize autocomplete when country/city changes
                 window.reinitializeAutocomplete = function() {
                     console.log('Reinitializing autocomplete due to country/city change...');
                     
                     // Remove existing autocomplete instances
                     document.querySelectorAll('.google-maps-autocomplete').forEach(input => {
                         input.removeAttribute('data-autocomplete-initialized');
                     });
                     
                     // Reinitialize after a short delay
                     setTimeout(() => {
                         initializeGoogleMapsAutocomplete();
                     }, 500);
                 };
                 
                 // Initialize autocomplete when page loads
                 document.addEventListener('DOMContentLoaded', function() {
                     // Wait for Google Maps API to load
                     if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                         initializeGoogleMapsAutocomplete();
                     } else {
                         // Wait for Google Maps API to load
                         window.addEventListener('load', function() {
                             if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                                 initializeGoogleMapsAutocomplete();
                             }
                         });
                     }
                 });
                 
                 // Reinitialize when country or city changes
                 document.addEventListener('change', function(e) {
                     if (e.target.id === 'user_country' || e.target.id === 'city') {
                         setTimeout(() => {
                             reinitializeAutocomplete();
                         }, 1000); // Wait for city options to load
                     }
                 });
                 
                 // Reinitialize when transport service type changes (for newly shown fields)
                 document.addEventListener('change', function(e) {
                     if (e.target.classList.contains('transport-service-type')) {
                         setTimeout(() => {
                             initializeGoogleMapsAutocomplete();
                         }, 100);
                     }
                 });  
                 
                 // Test function for Google Maps integration
                 window.testGoogleMapsIntegration = function() {
                     console.log('=== TESTING GOOGLE MAPS INTEGRATION ===');
                     
                     // Check if Google Maps API is loaded
                     if (typeof google === 'undefined') {
                         console.error('Google Maps API not loaded');
                         return false;
                     }
                     
                     if (!google.maps || !google.maps.places) {
                         console.error('Google Maps Places API not loaded');
                         return false;
                     }
                     
                     console.log('Google Maps API loaded successfully');
                     
                     // Check for autocomplete inputs
                     const autocompleteInputs = document.querySelectorAll('.google-maps-autocomplete');
                     console.log(`Found ${autocompleteInputs.length} autocomplete inputs`);
                     
                     autocompleteInputs.forEach((input, index) => {
                         console.log(`Input ${index + 1}:`, {
                             id: input.id,
                             name: input.name,
                             placeholder: input.placeholder,
                             initialized: input.hasAttribute('data-autocomplete-initialized')
                         });
                     });
                     
                     // Test country/city selection
                     const selectedCountry = document.getElementById('user_country')?.value || '';
                     const selectedCity = ''; // City will be selected per service section
                     console.log('Current selection:', {
                         country: selectedCountry,
                         city: selectedCity,
                         countryCode: getCountryCode(selectedCountry)
                     });
                     
                     return true;
                 };
                 
                // Function to manually trigger autocomplete initialization
                window.manualInitAutocomplete = function() {
                    console.log('Manually initializing Google Maps autocomplete...');
                    initializeGoogleMapsAutocomplete();
                };

                // Function to search vehicles for Point-to-Point entry/exit ports
                window.searchVehiclesPointToPoint = function(day, section) {
                    console.log(`Searching Point-to-Point vehicles for day ${day}, section ${section}`);
                    
                    const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
                    // Handle both old and new field naming patterns for primary ports
                    console.log(`DEBUG: Searching for vehicle select element for day ${day}, section ${section}`);
                    let vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
                    console.log(`DEBUG: First attempt (old pattern): day${day}_${section}_vehicle_id - Found:`, !!vehicleSelect);
                    
                    if (!vehicleSelect && section === 'exit') {
                        vehicleSelect = document.querySelector(`select[name="day${day}_exit_0_vehicle_id"]`);
                        console.log(`DEBUG: Second attempt (new pattern): day${day}_exit_0_vehicle_id - Found:`, !!vehicleSelect);
                        if (vehicleSelect) {
                            console.log(`Found exit port vehicle select using new pattern: day${day}_exit_0_vehicle_id`);
                        }
                    }
                    if (!vehicleSelect && section === 'entry') {
                        vehicleSelect = document.querySelector(`select[name="day${day}_entry_0_vehicle_id"]`);
                        console.log(`DEBUG: Second attempt (new pattern): day${day}_entry_0_vehicle_id - Found:`, !!vehicleSelect);
                        if (vehicleSelect) {
                            console.log(`Found entry port vehicle select using new pattern: day${day}_entry_0_vehicle_id`);
                        }
                    }
                    
                    console.log(`Vehicle select element found for ${section}:`, vehicleSelect ? vehicleSelect.name : 'NOT FOUND');
                    
                    // Additional debugging for exit port
                    if (section === 'exit') {
                        console.log(`DEBUG: All exit port vehicle selects found:`, document.querySelectorAll('select[name*="_exit_"][name*="_vehicle_id"]'));
                        console.log(`DEBUG: All select elements with 'exit' in name:`, document.querySelectorAll('select[name*="exit"]'));
                    }
                    const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
                    
                    // Get city from the transport city select for this day
                    const transportCitySelect = document.getElementById(`day${day}_transport_city_1`);
                    const exitport_city = document.getElementById("modal_exit_city");
                    const entryport_city = document.getElementById("modal_local_transfer_city");

                    const transportCity = transportCitySelect?.value?.trim() || "";
                    const exitportCity = exitport_city?.value?.trim() || "";
                    const entryportCity = entryport_city?.value?.trim() || "";
                    if ((!transportCitySelect || !transportCity) && (!exitport_city || !exitportCity ) && (!entryport_city || !entryportCity)) {
                        alert('Please select a city in the transport section first');
                        return;
                    }
                    let city = "";
                    if(section === 'exit'){
                        city = exitportCity;
                    }else if(section === 'entry'){
                        city = entryportCity;
                    }else{
                        city = transportCity;
                    }
                    console.log('Using Point-to-Point city-based endpoint for city:', city);

                    // Show loading state
                    searchBtn.innerHTML = 'Searching...';
                    searchBtn.disabled = true;

                    fetch(`{{ url(route('fetch-vehicles-by-city-dmc')) }}?city=${encodeURIComponent(city)}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Point-to-Point vehicle search response for exit port:', data);
                            if (data.success && data.vehicles && data.vehicles.length > 0) {
                                // Populate vehicle dropdown
                                console.log(`DEBUG: About to populate vehicles. vehicleSelect found:`, !!vehicleSelect);
                                console.log(`DEBUG: vehicleSelect element:`, vehicleSelect);
                                if (vehicleSelect) {
                                    console.log(`DEBUG: vehicleSelect name:`, vehicleSelect.name);
                                    console.log(`DEBUG: vehicleSelect id:`, vehicleSelect.id);
                                    vehicleSelect.innerHTML = '<option value="">Choose your vehicle</option>';
                                    data.vehicles.forEach(vehicle => {
                                        const entryOrExitMaxPax = ((section === 'entry' || section === 'exit') && (vehicle.city_tour_seating_capacity != null && vehicle.city_tour_seating_capacity !== '')) ? (vehicle.city_tour_seating_capacity || vehicle.seating_capacity) : (vehicle.seating_capacity || '');
                                        const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                                    vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                                            data-vehicle-name="${vehicle.vehicle_name}" 
                                            data-vehicle-type="${vehicle.vehicle_type}" 
                                            data-seating-capacity="${entryOrExitMaxPax}"
                                            data-seatingCapacity="${entryOrExitMaxPax}"
                                            data-private-price="${vehicle.private_price || ''}"
                                            data-shared-price="${vehicle.shared_price || ''}"
                                            data-service-type="${vehicle.service_type || ''}"
                                            data-cost-per-hour="${vehicle.cost_per_hour || ''}"
                                            data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}"
                                            data-sharable="${vehicle.sharable || '0'}"
                                            data-image="${vehicle.image || ''}">
                                            ${vehicleInfo}
                                        </option>`;
                                    });
                                    vehicleSelect.disabled = false;
                                    
                                    console.log(`DEBUG: After population, vehicleSelect options count:`, vehicleSelect.options.length);
                                    console.log(`DEBUG: vehicleSelect innerHTML length:`, vehicleSelect.innerHTML.length);
                                    console.log(`Populated ${data.vehicles.length} vehicles for Point-to-Point ${section} port`);
                                } else {
                                    console.error(`DEBUG: vehicleSelect element NOT FOUND for section ${section}!`);
                                    console.error(`DEBUG: Searched for: day${day}_${section}_vehicle_id`);
                                    console.error(`DEBUG: Available select elements:`, document.querySelectorAll('select[name*="_exit_"][name*="_vehicle_id"]'));
                                }
                                
                                // Show vehicle results section
                                if (vehicleResultsDiv) {
                                    vehicleResultsDiv.style.display = 'block';
                                    vehicleResultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                }
                                // Show "Add More Vehicles" for entry/exit (only after search)
                                if (section === 'entry') {
                                    const entryAddMore = document.getElementById(`day${day}_entry_add_more_section`);
                                    if (entryAddMore) entryAddMore.style.display = 'block';
                                } else if (section === 'exit') {
                                    const exitAddMore = document.getElementById(`day${day}_exit_add_more_section`);
                                    if (exitAddMore) exitAddMore.style.display = 'block';
                                }

                                // Show custom price field and set service type to Private for entry ports when zone_on = 0 (Point-to-Point mode)
                                if (section === 'entry' || section.startsWith('entry_')) {
                                    console.log(`Point-to-Point mode (zone=0) detected for entry port (${section}) - showing custom price field and setting service type to Private`);
                                    
                                    // Determine the correct section name for element IDs
                                    let entrySection = section === 'entry' ? 'entry_0' : section;
                                    
                                    // Show custom price field
                                    const entryPriceField = document.getElementById(`day${day}_${entrySection}_price_field`);
                                    if (entryPriceField) {
                                        entryPriceField.style.display = 'block';
                                        console.log(`Showing custom price field for entry port (zone=0): day${day}_${entrySection}_price_field`);
                                    }
                                    
                                    // Set service type to Private and make it readonly
                                    const serviceTypeSelect = document.getElementById(`day${day}_${entrySection}_service_type`);
                                    if (serviceTypeSelect) {
                                        serviceTypeSelect.value = 'Private';
                                        serviceTypeSelect.disabled = true;
                                        serviceTypeSelect.style.backgroundColor = '#f8f9fa';
                                        serviceTypeSelect.style.cursor = 'not-allowed';
                                        console.log(`Set entry port service type to Private (readonly) for zone=0: day${day}_${entrySection}_service_type`);
                                    }
                                }
                                
                                // Show custom price field and set service type to Private for exit ports when zone_on = 0 (Point-to-Point mode)
                                if (section === 'exit' || section.startsWith('exit_')) {
                                    console.log(`Point-to-Point mode (zone=0) detected for exit port (${section}) - showing custom price field and setting service type to Private`);
                                    
                                    // Determine the correct section name for element IDs
                                    let exitSection = section === 'exit' ? 'exit_0' : section;
                                    
                                    // Show custom price field
                                    const exitPriceField = document.getElementById(`day${day}_${exitSection}_price_field`);
                                    if (exitPriceField) {
                                        exitPriceField.style.display = 'block';
                                        console.log(`Showing custom price field for exit port (zone=0): day${day}_${exitSection}_price_field`);
                                    }
                                    
                                    // Set service type to Private and make it readonly
                                    const serviceTypeSelect = document.getElementById(`day${day}_${exitSection}_service_type`);
                                    if (serviceTypeSelect) {
                                        serviceTypeSelect.value = 'Private';
                                        serviceTypeSelect.disabled = true;
                                        serviceTypeSelect.style.backgroundColor = '#f8f9fa';
                                        serviceTypeSelect.style.cursor = 'not-allowed';
                                        console.log(`Set exit port service type to Private (readonly) for zone=0: day${day}_${exitSection}_service_type`);
                                    }
                                }

                                // Reset search button
                                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                                searchBtn.disabled = false;
                                
                                console.log(`Populated ${data.vehicles.length} vehicles in dropdown (Point-to-Point ${section} port)`);
                            } else {
                                alert('No vehicles available for this city. Please try a different city.');
                                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                                searchBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error searching Point-to-Point vehicles:', error);
                            alert('Error searching vehicles. Please try again.');
                            searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                            searchBtn.disabled = false;
                        });
                };

                // Agency-Agent Dependent Dropdown Functionality
                // Handle agency change to load agents (using Select2 event)
                document.addEventListener('DOMContentLoaded', function() {
                    // Wait for jQuery to be available
                    if (typeof jQuery === 'undefined') {
                        console.error('jQuery is not loaded');
                        return;
                    }
                    
                    const agentSelect = document.getElementById('agent_id');
                    const $agentSelect = jQuery('#agent_id');
                    const isAgentDisabled = agentSelect && agentSelect.hasAttribute('disabled');
                    
                    // Handle agency change using Select2 event
                    jQuery('#agency_id').on('change', function() {
                        const agencyId = jQuery(this).val();
                        
                        if (!agentSelect) return;
                        
                        // Clear agent dropdown
                        agentSelect.innerHTML = '<option value="">Choose agent...</option>';
                        $agentSelect.val(null).trigger('change');
                        
                        if (agencyId) {
                            // Show loading state
                            agentSelect.innerHTML = '<option value="">Loading agents...</option>';
                            $agentSelect.prop('disabled', true);
                            
                            // Make AJAX request to get agents by agency
                            fetch(`{{ url(route('fetch-agents-by-agency')) }}?agency_id=${agencyId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Clear loading state
                                agentSelect.innerHTML = '<option value="">Choose agent...</option>';
                                
                                if (data.success && data.agents && data.agents.length > 0) {
                                    // Populate agent dropdown
                                    data.agents.forEach(agent => {
                                        const option = document.createElement('option');
                                        option.value = agent.agent_id;
                                        option.textContent = agent.name;
                                        agentSelect.appendChild(option);
                                    });
                                    
                                    console.log(`Loaded ${data.agents.length} agents for agency ${agencyId}`);
                                } else {
                                    console.log('No agents found for selected agency');
                                    agentSelect.innerHTML = '<option value="">No agents found</option>';
                                }
                                
                                // Destroy and reinitialize Select2 to recognize new options
                                $agentSelect.select2('destroy');
                                jQuery('#agent_id').select2({
                                    placeholder: "Choose agent...",
                                    allowClear: true,
                                    width: '100%'
                                });
                                
                                // Re-enable agent select (unless it was originally disabled due to enquiry)
                                if (!isAgentDisabled) {
                                    $agentSelect.prop('disabled', false);
                                } else {
                                    $agentSelect.prop('disabled', true);
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching agents:', error);
                                agentSelect.innerHTML = '<option value="">Error loading agents</option>';
                                
                                // Destroy and reinitialize Select2
                                $agentSelect.select2('destroy');
                                $agentSelect.select2({
                                    placeholder: "Choose agent...",
                                    allowClear: true,
                                    width: '100%'
                                });
                                
                                // Re-enable agent select (unless it was originally disabled due to enquiry)
                                if (!isAgentDisabled) {
                                    $agentSelect.prop('disabled', false);
                                } else {
                                    $agentSelect.prop('disabled', true);
                                }
                                
                                // Show user-friendly error message
                                alert('Error loading agents. Please try again.');
                            });
                        } else {
                            // Reset to default state when no agency is selected
                            // Destroy and reinitialize Select2
                            $agentSelect.select2('destroy');
                            jQuery('#agent_id').select2({
                                placeholder: "Choose agent...",
                                allowClear: true,
                                width: '100%'
                            });
                            
                            // Re-enable agent select (unless it was originally disabled due to enquiry)
                            if (!isAgentDisabled) {
                                $agentSelect.prop('disabled', false);
                            } else {
                                $agentSelect.prop('disabled', true);
                            }
                        }
                    });
                });

// Transport Service Type Handling Functions
// Function to set up event listeners for hourly transport fields
window.setupHourlyFieldListeners = function(day, index = 0) {
    console.log(`Setting up hourly field listeners for day ${day}, index ${index}`);
    
    // Determine field index pattern - for static transport (index 0 or 1), use 0; for dynamic, use the index
    const fieldIndex = (index === 0 || index === 1) ? 0 : index;
    
    // Determine section name for enableSearchButton
    const sectionName = fieldIndex === 0 ? 'transport_hourly' : `transport_${fieldIndex}_hourly`;
    
    // City select - check if it exists and add listener
    // For static transport, city select ID is day${day}_transport_city_0
    // For dynamic transport, city select ID is day${day}_transport_city_${fieldIndex}
    const citySelect = document.getElementById(`day${day}_transport_city_${fieldIndex}`);
    if (citySelect) {
        // Store the original onchange handler if it exists (for loadTransportZonesForCity)
        const originalOnChange = citySelect.getAttribute('onchange');
        
        // Add our validation listener
        citySelect.addEventListener('change', function() {
            console.log(`City changed for hourly transport day ${day}, index ${fieldIndex}, value: ${this.value}`);
            // Call validation after a short delay to ensure other handlers have run
            setTimeout(() => {
                enableSearchButton(day, sectionName, fieldIndex);
            }, 150);
        });
    } else {
        console.warn(`City select not found for day ${day}, index ${fieldIndex}: day${day}_transport_city_${fieldIndex}`);
    }
    
    // Pickup location input - listen for changes and clearing
    let pickupLocationField;
    if (fieldIndex === 0) {
        pickupLocationField = document.getElementById(`day${day}_transport_hourly_pickup_location`);
    } else {
        pickupLocationField = document.getElementById(`day${day}_transport_${fieldIndex}_hourly_pickup_location`);
    }
    
    if (pickupLocationField) {
        // Listen for input changes (when user types or clears)
        pickupLocationField.addEventListener('input', function() {
            console.log(`Pickup location input changed for hourly transport day ${day}, index ${fieldIndex}`);
            enableSearchButton(day, sectionName, fieldIndex);
        });
        
        // Listen for blur (when user leaves the field)
        pickupLocationField.addEventListener('blur', function() {
            console.log(`Pickup location blur for hourly transport day ${day}, index ${fieldIndex}`);
            enableSearchButton(day, sectionName, fieldIndex);
        });
    }
    
    // Pickup time capsule (input + AM/PM)
    let pickupTimeInput, pickupTimeAmpm;
    if (fieldIndex === 0) {
        pickupTimeInput = document.getElementById(`day${day}_transport_hourly_pickup_time_input`);
        pickupTimeAmpm = document.getElementById(`day${day}_transport_hourly_pickup_time_ampm`);
    } else {
        pickupTimeInput = document.getElementById(`day${day}_transport_${fieldIndex}_hourly_pickup_time_input`);
        pickupTimeAmpm = document.getElementById(`day${day}_transport_${fieldIndex}_hourly_pickup_time_ampm`);
    }
    
    if (pickupTimeInput) {
        pickupTimeInput.addEventListener('input', function() {
            console.log(`Pickup time input changed for hourly transport day ${day}, index ${fieldIndex}`);
            formatTimeInput(pickupTimeInput);
            syncTransportHourlyPickupTime(day);
            enableSearchButton(day, sectionName, fieldIndex);
        });
        pickupTimeInput.addEventListener('change', function() {
            syncTransportHourlyPickupTime(day);
            enableSearchButton(day, sectionName, fieldIndex);
        });
    }
    if (pickupTimeAmpm) {
        pickupTimeAmpm.addEventListener('change', function() {
            syncTransportHourlyPickupTime(day);
            enableSearchButton(day, sectionName, fieldIndex);
        });
    }
    
    // Selected hours select
    let selectedHoursSelect;
    if (fieldIndex === 0) {
        selectedHoursSelect = document.querySelector(`select[name="day${day}_transport_hourly_selected_hours"]`);
    } else {
        selectedHoursSelect = document.querySelector(`select[name="day${day}_transport_${fieldIndex}_hourly_selected_hours"]`);
    }
    
    if (selectedHoursSelect) {
        selectedHoursSelect.addEventListener('change', function() {
            console.log(`Selected hours changed for hourly transport day ${day}, index ${fieldIndex}`);
            enableSearchButton(day, sectionName, fieldIndex);
        });
    }
    
    // Monitor hidden coordinate fields for changes (when Google Maps autocomplete updates them)
    let pickupLatField, pickupLngField;
    if (fieldIndex === 0) {
        pickupLatField = document.getElementById(`day${day}_transport_hourly_pickup_lat`);
        pickupLngField = document.getElementById(`day${day}_transport_hourly_pickup_lng`);
    } else {
        pickupLatField = document.getElementById(`day${day}_transport_${fieldIndex}_hourly_pickup_lat`);
        pickupLngField = document.getElementById(`day${day}_transport_${fieldIndex}_hourly_pickup_lng`);
    }
    
    if (pickupLatField && pickupLngField) {
        // Poll for value changes in hidden fields (since they're updated programmatically)
        let lastLatValue = pickupLatField.value || '';
        let lastLngValue = pickupLngField.value || '';
        
        const checkCoordinates = setInterval(function() {
            const currentLat = pickupLatField.value || '';
            const currentLng = pickupLngField.value || '';
            
            if (currentLat !== lastLatValue || currentLng !== lastLngValue) {
                lastLatValue = currentLat;
                lastLngValue = currentLng;
                console.log(`Coordinate fields changed for hourly transport day ${day}, index ${fieldIndex}`);
                enableSearchButton(day, sectionName, fieldIndex);
            }
        }, 200);
        
        // Store interval ID for cleanup if needed
        if (pickupLatField) {
            pickupLatField.setAttribute('data-coordinate-check-interval', checkCoordinates);
        }
    }
    
    console.log(`Hourly field listeners set up for day ${day}, index ${fieldIndex}`);
};

function handleTransportServiceTypeChange(day, serviceType, index = 0) {
    console.log(`Transport service type changed to: ${serviceType} for day ${day}, index ${index}`);
    
    // Get field containers for this specific transport section
    const transportContainer = document.querySelector(`[data-transport-index="${index}"]`);
    
    if (!transportContainer) {
        console.error(`Transport container not found for day ${day}, index ${index}`);
        return;
    }
    
    const localTransferFields = transportContainer.querySelectorAll(`.local-transfer-field`);
    const pointToPointFields = transportContainer.querySelectorAll(`.point-to-point-field`);
    const hourlyFields = transportContainer.querySelectorAll(`.hourly-field`);
    
    // Hide vehicle results section for this specific transport
    // For the main transport section (index 1), use the transport_vehicle_results ID without index
    // For additional transport sections, use the indexed ID
    let vehicleResultsSectionId;
    if (index === 1) {
        vehicleResultsSectionId = `day${day}_transport_vehicle_results`;
    } else {
        vehicleResultsSectionId = `day${day}_transport_${index}_vehicle_results`;
    }
    
    const vehicleResultsSection = document.getElementById(vehicleResultsSectionId);
    if (vehicleResultsSection) {
        vehicleResultsSection.style.display = 'none';
    }
    
    // Get the price field for point-to-point for this specific transport
    const priceField = document.getElementById(`day${day}_transport_${index}_price_field`);
    // Get the service type field to hide/show it based on service type
    const serviceTypeField = document.getElementById(`day${day}_transport_${index}_service_type_field`);
    // Get the vehicle field to adjust its width
    const vehicleField = document.querySelector(`#day${day}_transport_${index}_vehicle_results .vehicle-field`);
    
    // Clear Google Maps location fields when switching service types
    const clearGoogleMapsFields = () => {
        // Determine field ID pattern based on index
        let fieldIdPattern;
        if (index === 1) {
            // Main transport section uses different ID pattern
            fieldIdPattern = `day${day}_transport`;
        } else {
            // Additional transport sections use indexed pattern
            fieldIdPattern = `day${day}_transport_${index}`;
        }
        
        // Clear point-to-point location fields
        const pickupLocationField = document.getElementById(`${fieldIdPattern}_pickup_location`);
        const dropoffLocationField = document.getElementById(`${fieldIdPattern}_dropoff_location`);
        const pickupLatField = document.getElementById(`${fieldIdPattern}_pickup_lat`);
        const pickupLngField = document.getElementById(`${fieldIdPattern}_pickup_lng`);
        const pickupPlaceIdField = document.getElementById(`${fieldIdPattern}_pickup_place_id`);
        const dropoffLatField = document.getElementById(`${fieldIdPattern}_dropoff_lat`);
        const dropoffLngField = document.getElementById(`${fieldIdPattern}_dropoff_lng`);
        const dropoffPlaceIdField = document.getElementById(`${fieldIdPattern}_dropoff_place_id`);
        
        // Clear hourly location fields
        const hourlyPickupLocationField = document.getElementById(`${fieldIdPattern}_hourly_pickup_location`);
        const hourlyPickupLatField = document.getElementById(`${fieldIdPattern}_hourly_pickup_lat`);
        const hourlyPickupLngField = document.getElementById(`${fieldIdPattern}_hourly_pickup_lng`);
        const hourlyPickupPlaceIdField = document.getElementById(`${fieldIdPattern}_hourly_pickup_place_id`);
        
        // Clear all fields
        if (pickupLocationField) pickupLocationField.value = '';
        if (dropoffLocationField) dropoffLocationField.value = '';
        if (pickupLatField) pickupLatField.value = '';
        if (pickupLngField) pickupLngField.value = '';
        if (pickupPlaceIdField) pickupPlaceIdField.value = '';
        if (dropoffLatField) dropoffLatField.value = '';
        if (dropoffLngField) dropoffLngField.value = '';
        if (dropoffPlaceIdField) dropoffPlaceIdField.value = '';
        if (hourlyPickupLocationField) hourlyPickupLocationField.value = '';
        if (hourlyPickupLatField) hourlyPickupLatField.value = '';
        if (hourlyPickupLngField) hourlyPickupLngField.value = '';
        if (hourlyPickupPlaceIdField) hourlyPickupPlaceIdField.value = '';
        
        console.log(`Cleared Google Maps location fields for day ${day}, index ${index} using pattern: ${fieldIdPattern}`);
    };
    
    // Clear location fields when switching service types
    clearGoogleMapsFields();
    
    // When switching away from Local Transfer, clear its selected values (do not disable controls)
    if (serviceType !== 'local_transfer') {
        const pickupZoneSelect = transportContainer.querySelector('.pickup-zone-select');
        const dropoffZoneSelect = transportContainer.querySelector('.dropoff-zone-select');
        const pickupTimeField = transportContainer.querySelector('[name$="_transport_pickup_time"]');
        const searchButton = transportContainer.querySelector('button[id$="_transport_search_btn"]');
        if (pickupZoneSelect) {
            pickupZoneSelect.value = '';
        }
        if (dropoffZoneSelect) {
            dropoffZoneSelect.value = '';
        }
        if (pickupTimeField) {
            pickupTimeField.value = '';
        }
        if (searchButton) {
            searchButton.innerHTML = '<i class="ri-search-line me-2"></i>Search';
        }
    }
    
    // Show/hide fields based on selected service type
    if (serviceType === 'local_transfer') {
        localTransferFields.forEach(field => field.style.display = 'block');
        pointToPointFields.forEach(field => field.style.display = 'none');
        hourlyFields.forEach(field => field.style.display = 'none');
        
        // Show service type field for local transfer and make it editable
        if (serviceTypeField) {
            serviceTypeField.style.display = 'block';
            
            // Handle both static and dynamic service type selects
            let serviceTypeSelect;
            if (index === 0 || index === 1) {
                // Static transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_service_type`);
            } else {
                // Dynamic transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_${index}_service_type_select`);
            }
            
            if (serviceTypeSelect) {
                // Restore all options for local transfer
                serviceTypeSelect.innerHTML = '<option value="">Select service type</option><option value="Shared">Shared</option><option value="Private">Private</option>';
                serviceTypeSelect.disabled = false; // Make it editable
                serviceTypeSelect.style.backgroundColor = ''; // Reset background
                serviceTypeSelect.style.cursor = '';
                serviceTypeSelect.value = ''; // Reset value
            }
        }
        
        // Adjust vehicle field width to col-md-8 when service type is shown
        if (vehicleField) {
            vehicleField.className = vehicleField.className.replace('col-md-12', 'col-md-8');
        }
        
        // Hide price field for local transfer
        if (priceField) {
            priceField.style.display = 'none';
        }
    } else if (serviceType === 'point_to_point') {
        pointToPointFields.forEach(field => field.style.display = 'block');
        localTransferFields.forEach(field => field.style.display = 'none');
        hourlyFields.forEach(field => field.style.display = 'none');
        
        // Show service type field for point-to-point but make it readonly and set to Private
        if (serviceTypeField) {
            serviceTypeField.style.display = 'block';
            
            // Handle both static and dynamic service type selects
            let serviceTypeSelect;
            if (index === 0 || index === 1) {
                // Static transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_service_type`);
            } else {
                // Dynamic transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_${index}_service_type_select`);
            }
            
            if (serviceTypeSelect) {
                // For point-to-point, only show Private option
                serviceTypeSelect.innerHTML = '<option value="">Select service type</option><option value="Private">Private</option>';
                serviceTypeSelect.value = 'Private';
                serviceTypeSelect.disabled = true; // Make it readonly
                serviceTypeSelect.style.backgroundColor = '#f8f9fa'; // Light gray background to indicate disabled
                serviceTypeSelect.style.cursor = 'not-allowed';
            }
        }
        
        // Keep vehicle field width to col-md-8 when service type is shown
        if (vehicleField) {
            vehicleField.className = vehicleField.className.replace('col-md-12', 'col-md-8');
        }
        
        // Show price field immediately for point-to-point service (but only after vehicle results are shown)
        // The price field will be shown when vehicles are searched and results are displayed
    } else if (serviceType === 'hourly') {
        hourlyFields.forEach(field => field.style.display = 'block');
        localTransferFields.forEach(field => field.style.display = 'none');
        pointToPointFields.forEach(field => field.style.display = 'none');
        
        // Show service type field for hourly but make it readonly and set to Private
        // Remove Shared option and only show Private for hourly transport
        if (serviceTypeField) {
            serviceTypeField.style.display = 'block';
            
            // Handle both static and dynamic service type selects
            let serviceTypeSelect;
            if (index === 0 || index === 1) {
                // Static transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_service_type`);
            } else {
                // Dynamic transport field
                serviceTypeSelect = document.getElementById(`day${day}_transport_${index}_service_type_select`);
            }
            
            if (serviceTypeSelect) {
                // Clear existing options and only add Private option for hourly transport
                serviceTypeSelect.innerHTML = '<option value="">Select service type</option><option value="Private">Private</option>';
                serviceTypeSelect.disabled = false; // Keep it enabled for hourly
                serviceTypeSelect.style.backgroundColor = ''; // Reset background
                serviceTypeSelect.style.cursor = ''; // Reset cursor
                console.log(`Set hourly service type to show only Private option (enabled)`);
            }
        }
        
        // Adjust vehicle field width to col-md-8 when service type is shown
        if (vehicleField) {
            vehicleField.className = vehicleField.className.replace('col-md-12', 'col-md-8');
        }
        
        
        // Hide point-to-point price field
        if (priceField) {
            priceField.style.display = 'none';
        }
        
        // Set up event listeners for hourly fields
        setupHourlyFieldListeners(day, index);
    }
}

// Vehicle search functions for different transport types
function searchPointToPointVehicles(day, section) {
    const pickupLocation = document.querySelector(`input[name="day${day}_transport_pickup_location"]`);
    const dropoffLocation = document.querySelector(`input[name="day${day}_transport_dropoff_location"]`);
    const pickupTime = document.querySelector(`[name="day${day}_transport_additional_pickup_time"]`);
    const pickupDate = document.querySelector(`input[name="day${day}_transport_additional_date"]`);
    
    if (!pickupLocation || !dropoffLocation || !pickupTime || !pickupDate || 
        !pickupLocation.value || !dropoffLocation.value || !pickupTime.value || !pickupDate.value) {
        showNotification('Please fill in all required fields', 'warning');
        return;
    }
    
    console.log('Searching vehicles for point to point:', {
        pickup: pickupLocation.value,
        dropoff: dropoffLocation.value,
        time: pickupTime.value,
        date: pickupDate.value
    });
    
    // Get city from the transport city select
    const citySelect = document.getElementById(`day${day}_transport_city_1`);
    if (!citySelect || !citySelect.value) {
        showNotification('Please select a city first', 'error');
        return;
    }
    
    const city = citySelect.value;
    const searchBtn = document.getElementById(`day${day}_transport_additional_search_btn`);
    
    // Show loading state
    if (searchBtn) {
        searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
        searchBtn.disabled = true;
    }
    
    fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?city=${encodeURIComponent(city)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Point-to-Point vehicle search response:', data);
            populateVehicleDropdown(day, 'transport', data.vehicles);
            
            // Reset search button
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error searching vehicles:', error);
            showNotification('Error searching vehicles. Please try again.', 'error');
            
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        });
}

function searchHourlyVehicles(day, section) {
    const pickupLocation = document.querySelector(`input[name="day${day}_transport_hourly_pickup_location"]`);
    const pickupTimeField = document.querySelector(`[name="day${day}_transport_hourly_pickup_time"]`);
    const pickupDate = document.querySelector(`input[name="day${day}_transport_hourly_date"]`);
    
    if (!pickupLocation || !pickupTimeField || !pickupDate || 
        !pickupLocation.value || !pickupTimeField.value || !pickupDate.value) {
        showNotification('Please fill in all required fields', 'warning');
        return;
    }
    
    console.log('Searching vehicles for hourly service:', {
        pickup: pickupLocation.value,
        time: pickupTimeField.value,
        date: pickupDate.value
    });
    
    // Get city from the transport city select
    const citySelect = document.getElementById(`day${day}_transport_city_1`);
    if (!citySelect || !citySelect.value) {
        showNotification('Please select a city first', 'error');
        return;
    }
    
    const city = citySelect.value;
    const searchBtn = document.getElementById(`day${day}_transport_hourly_search_btn`);
    
    // Show loading state
    if (searchBtn) {
        searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
        searchBtn.disabled = true;
    }
    
    fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?city=${encodeURIComponent(city)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Hourly vehicle search response:', data);
            populateVehicleDropdown(day, 'transport', data.vehicles);
            
            // Reset search button
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error searching vehicles:', error);
            showNotification('Error searching vehicles. Please try again.', 'error');
            
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        });
}

function searchLocalTransferVehicles(day, section) {
    const pickupZoneSelect = document.querySelector(`select[name="day${day}_transport_pickup_zone_id"]`);
    const dropoffZoneSelect = document.querySelector(`select[name="day${day}_transport_dropoff_zone_id"]`);
    const pickupTime = document.querySelector(`[name="day${day}_transport_pickup_time"]`);
    const pickupDate = document.querySelector(`input[name="day${day}_transport_date"]`);
    
    if (!pickupZoneSelect || !dropoffZoneSelect || !pickupTime || !pickupDate ||
        !pickupZoneSelect.value || !dropoffZoneSelect.value || !pickupTime.value || !pickupDate.value) {
        showNotification('Please fill in all required fields', 'warning');
        return;
    }
    
    const fromZoneId = pickupZoneSelect.value;
    const toZoneId = dropoffZoneSelect.value;
    const searchBtn = document.getElementById(`day${day}_transport_search_btn`);
    
    console.log('Searching vehicles for local transfer:', { fromZoneId, toZoneId, time: pickupTime.value, date: pickupDate.value });
    
    // Get location types from the selected options
    const pickupOption = pickupZoneSelect.options[pickupZoneSelect.selectedIndex];
    const dropoffOption = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex];
    
    const fromZoneType = pickupOption?.dataset?.type || 'port';
    const toZoneType = dropoffOption?.dataset?.type || 'attraction';
    
    // Get the actual zone IDs based on type
    let actualFromZoneId = fromZoneId;
    let actualToZoneId = toZoneId;
    
    // For ports, use port_id from data attribute if available
    if (fromZoneType === 'port') {
        actualFromZoneId = pickupOption?.dataset?.portId || fromZoneId;
    }
    
    if (toZoneType === 'port') {
        actualToZoneId = dropoffOption?.dataset?.portId || toZoneId;
    }
    
    console.log('Zone mapping for local transfer:', {
        originalFromZoneId: fromZoneId,
        originalToZoneId: toZoneId,
        actualFromZoneId: actualFromZoneId,
        actualToZoneId: actualToZoneId,
        fromZoneType: fromZoneType,
        toZoneType: toZoneType
    });
    
    // Show loading state
    if (searchBtn) {
        searchBtn.innerHTML = 'Searching...';
        searchBtn.disabled = true;
    }
    
    // Get zone status from DMC user data
    const user_dmc = window.UserDmc;
    const zone_status = user_dmc ? user_dmc.zone_on : 1;
    
    // Make POST request with data in body
    fetch(`{{ route('fetch-vehicles-by-zones') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            from_zone_id: actualFromZoneId,
            to_zone_id: actualToZoneId,
            from_zone_type: zone_status == 1 ? fromZoneType : '',
            to_zone_type: zone_status == 1 ? toZoneType : '',
            zone_status: zone_status
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Local transfer vehicle search response:', data);
            populateVehicleDropdown(day, 'transport', data.vehicles);
            
            // Reset search button
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error searching vehicles:', error);
            showNotification('Error searching vehicles. Please try again.', 'error');
            
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search';
                searchBtn.disabled = false;
            }
        });
}

// Helper function to populate vehicle dropdown
function populateVehicleDropdown(day, section, vehicles) {
    const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
    const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
    
    if (!vehicleSelect || !vehicleResultsDiv) {
        console.error('Vehicle select or results div not found');
        return;
    }
    
    if (vehicles && vehicles.length > 0) {
        vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
        const isEntryOrExit = (section === 'entry' || String(section).startsWith('entry_') || section === 'exit' || String(section).startsWith('exit_'));
        vehicles.forEach(vehicle => {
            const entryOrExitMaxPax = isEntryOrExit && (vehicle.city_tour_seating_capacity != null && vehicle.city_tour_seating_capacity !== '') ? (vehicle.city_tour_seating_capacity || vehicle.seating_capacity) : (vehicle.seating_capacity || '');
            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
            
        vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                data-private-price="${vehicle.private_price || ''}" 
                data-shared-price="${vehicle.shared_price || ''}"
                data-service-type="${vehicle.service_type || ''}"
                data-cost-per-hour="${vehicle.cost_per_hour || ''}"
                data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}"
                data-vehicle-name="${vehicle.vehicle_name || ''}"
                data-vehicle-type="${vehicle.vehicle_type || ''}"
                data-seatingCapacity="${entryOrExitMaxPax}"
                data-sharable="${vehicle.sharable || ''}"
                data-image="${vehicle.image || ''}">
                ${vehicleInfo}
            </option>`;
        });
        
        // Enable the vehicle select
        vehicleSelect.disabled = false;
        
        // Show results section
        vehicleResultsDiv.style.display = 'block';
        vehicleResultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        // Show "Add More Vehicles" for entry/exit (only after search)
        if (section === 'entry') {
            const entryAddMore = document.getElementById(`day${day}_entry_add_more_section`);
            if (entryAddMore) entryAddMore.style.display = 'block';
            // Do not pre-fill adults/children here; they stay at default until user chooses a vehicle (applyEntryPortPaxFromVehicleAndTour)
        } else if (section === 'exit') {
            const exitAddMore = document.getElementById(`day${day}_exit_add_more_section`);
            if (exitAddMore) exitAddMore.style.display = 'block';
        }
        
        console.log(`Populated ${vehicles.length} vehicles in dropdown`);
    } else {
        showNotification('No vehicles available for this route. Please try different locations.', 'warning');
    }
}

// Function to validate passenger capacity against vehicle seating capacity
function validatePassengerCapacity(day, section) {
    // Handle different field naming patterns for entry/exit ports vs regular transport
    let passengerInput, vehicleSelect;
    
    if (section.includes('entry_') || section.includes('exit_')) {
        // For entry/exit ports with index (e.g., 'entry_0', 'entry_1', 'exit_1')
        passengerInput = document.getElementById(`day${day}_${section}_passengers`);
        vehicleSelect = document.getElementById(`day${day}_${section}_vehicle_id`);
    } else if (section === 'entry') {
        // For main entry port (uses _0 suffix)
        passengerInput = document.getElementById(`day${day}_${section}_0_passengers`);
        vehicleSelect = document.getElementById(`day${day}_${section}_0_vehicle_id`);
    } else if (section === 'exit') {
        // For main exit port (no _0 suffix)
        passengerInput = document.getElementById(`day${day}_${section}_passengers`);
        vehicleSelect = document.getElementById(`day${day}_${section}_vehicle_id`);
    } else {
        // For regular transport sections
        passengerInput = document.getElementById(`day${day}_${section}_passengers`);
        vehicleSelect = document.getElementById(`day${day}_${section}_vehicle_id`);
    }
    
    console.log('Validating passenger capacity for:', {
        day: day,
        section: section,
        passengerInput: passengerInput ? passengerInput.id : 'NOT_FOUND',
        vehicleSelect: vehicleSelect ? vehicleSelect.id : 'NOT_FOUND'
    });
    
    if (!passengerInput || !vehicleSelect) {
        console.error('Passenger input or vehicle select not found!');
        return true; // Don't block if elements not found
    }
    
    const passengerCount = parseInt(passengerInput.value) || 0;
    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    const vehicleCapacity = parseInt(selectedOption.dataset.seatingCapacity) || parseInt(selectedOption.getAttribute('data-seatingCapacity')) || 0;
    
    console.log('Passenger capacity validation:', {
        passengerCount: passengerCount,
        vehicleCapacity: vehicleCapacity,
        selectedOption: selectedOption,
        dataAttributes: selectedOption ? Object.fromEntries([...selectedOption.attributes].map(attr => [attr.name, attr.value])) : 'NO_OPTION'
    });
    if (passengerCount > vehicleCapacity) {
        // Show error and reset to max capacity
        showNotification(`Passenger count (${passengerCount}) exceeds vehicle capacity (${vehicleCapacity} seats). Please select a larger vehicle or reduce passenger count.`, 'error');
        passengerInput.value = vehicleCapacity;
        // Keep entry_0 adults/children in sync (visual): sum = vehicleCapacity
        if (section === 'entry_0') {
            const adultsEl = document.getElementById('day' + day + '_entry_0_adults');
            const childrenEl = document.getElementById('day' + day + '_entry_0_children');
            if (adultsEl) adultsEl.value = vehicleCapacity;
            if (childrenEl) childrenEl.value = 0;
        }
        if (section.startsWith('exit_')) {
            const adultsEl = document.getElementById('day' + day + '_' + section + '_adults');
            const childrenEl = document.getElementById('day' + day + '_' + section + '_children');
            if (adultsEl) adultsEl.value = vehicleCapacity;
            if (childrenEl) childrenEl.value = 0;
        }
        if (section === 'transport') {
            const adultsEl = document.getElementById('day' + day + '_transport_adults');
            const childrenEl = document.getElementById('day' + day + '_transport_children');
            if (adultsEl) adultsEl.value = vehicleCapacity;
            if (childrenEl) childrenEl.value = 0;
            if (typeof window.updatePricing === 'function') window.updatePricing(day, 'transport');
        }
        if (section.startsWith('transport_')) {
            const adultsEl = document.getElementById('day' + day + '_' + section + '_adults');
            const childrenEl = document.getElementById('day' + day + '_' + section + '_children');
            if (adultsEl) adultsEl.value = vehicleCapacity;
            if (childrenEl) childrenEl.value = 0;
            if (typeof window.updatePricing === 'function') window.updatePricing(day, section);
        }
        passengerInput.focus();
        return false;
    }

    // Clear any previous error styling
    passengerInput.classList.remove('is-invalid');
    return true;
}

// When vehicle is chosen for transport: set adults & children to min(tour pax, vehicle capacity). Max shown = that limit.
window.applyTransportPaxFromVehicleAndTour = function(day, section) {
    const sectionId = (section === 'transport' || section === undefined) ? 'transport' : section;
    const adultsEl = document.getElementById('day' + day + '_' + sectionId + '_adults');
    const childrenEl = document.getElementById('day' + day + '_' + sectionId + '_children');
    const passengersEl = document.getElementById('day' + day + '_' + sectionId + '_passengers');
    const vehicleSelect = document.getElementById('day' + day + '_' + sectionId + '_vehicle_id');
    if (!adultsEl || !childrenEl || !passengersEl || !vehicleSelect || !vehicleSelect.value) return;
    const tourAdults = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
    const tourChildren = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    const vehicleCapacity = parseInt(selectedOption.dataset.seatingCapacity || selectedOption.getAttribute('data-seatingCapacity') || selectedOption.getAttribute('data-seating-capacity')) || 0;
    if (vehicleCapacity <= 0) return;
    const tourTotal = tourAdults + tourChildren;
    const totalPax = Math.min(tourTotal, vehicleCapacity);
    var a = Math.min(totalPax, tourAdults);
    var c = totalPax - a;
    if (c > tourChildren) {
        c = tourChildren;
        a = totalPax - c;
    }
    a = Math.max(0, a);
    c = Math.max(0, c);
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = (a + c) || 1;
    if (typeof window.syncTransportPaxAndUpdate === 'function') window.syncTransportPaxAndUpdate(day, sectionId);
    if (typeof window.updatePricing === 'function') window.updatePricing(day, sectionId);
}

// Sync transport hidden passengers from adults + children; set max to min(tour pax, vehicle capacity) and clamp values.
window.syncTransportPaxAndUpdate = function(day, section) {
    const sectionId = (section === 'transport' || section === undefined) ? 'transport' : section;
    const adultsEl = document.getElementById('day' + day + '_' + sectionId + '_adults');
    const childrenEl = document.getElementById('day' + day + '_' + sectionId + '_children');
    const passengersEl = document.getElementById('day' + day + '_' + sectionId + '_passengers');
    const vehicleSelect = document.getElementById('day' + day + '_' + sectionId + '_vehicle_id');
    if (!adultsEl || !childrenEl || !passengersEl) return;
    const tourAdults = parseInt(document.getElementById('adults')?.value) || 0;
    const tourChildren = parseInt(document.getElementById('children')?.value) || 0;
    var maxAdults = Math.max(0, tourAdults);
    var maxChildren = Math.max(0, tourChildren);
    var maxTotal = maxAdults + maxChildren;
    if (vehicleSelect && vehicleSelect.value) {
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const vehicleCapacity = parseInt(selectedOption.dataset.seatingCapacity || selectedOption.getAttribute('data-seatingCapacity') || selectedOption.getAttribute('data-seating-capacity')) || 0;
        if (vehicleCapacity > 0) maxTotal = Math.min(maxTotal, vehicleCapacity);
    }
    adultsEl.setAttribute('max', maxAdults || 50);
    childrenEl.setAttribute('max', maxChildren || 50);
    var maxAdultsSpan = document.getElementById('day' + day + '_' + sectionId + '_max_adults');
    var maxChildrenSpan = document.getElementById('day' + day + '_' + sectionId + '_max_children');
    if (maxAdultsSpan) maxAdultsSpan.textContent = maxAdults || '—';
    if (maxChildrenSpan) maxChildrenSpan.textContent = maxChildren || '—';
    var a = parseInt(adultsEl.value) || 0;
    var c = parseInt(childrenEl.value) || 0;
    a = Math.min(Math.max(0, a), maxAdults || 50);
    c = Math.min(Math.max(0, c), maxChildren || 50);
    if (a + c > maxTotal) {
        if (a >= maxTotal) {
            a = maxTotal;
            c = 0;
        } else {
            c = maxTotal - a;
            c = Math.min(c, maxChildren);
        }
    }
    const total = (a + c) || 1;
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = total;
    if (typeof window.updatePricing === 'function') window.updatePricing(day, sectionId);
}

// When vehicle is chosen: populate adults & children to the least of (tour pax, vehicle capacity). E.g. capacity 5 & pax 7 → show 5; capacity 5 & pax 2 → show 2.
window.applyEntryPortPaxFromVehicleAndTour = function(day, index) {
    const adultsEl = document.getElementById('day' + day + '_entry_' + index + '_adults');
    const childrenEl = document.getElementById('day' + day + '_entry_' + index + '_children');
    const passengersEl = document.getElementById('day' + day + '_entry_' + index + '_passengers');
    const vehicleSelect = document.getElementById('day' + day + '_entry_' + index + '_vehicle_id');
    if (!adultsEl || !childrenEl || !passengersEl || !vehicleSelect || !vehicleSelect.value) return;
    const tourAdults = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
    const tourChildren = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    const vehicleCapacity = parseInt(selectedOption.dataset.seatingCapacity || selectedOption.getAttribute('data-seatingCapacity') || selectedOption.getAttribute('data-seating-capacity')) || 0;
    if (vehicleCapacity <= 0) return;
    const tourTotal = tourAdults + tourChildren;
    const totalPax = Math.min(tourTotal, vehicleCapacity);
    var a = Math.min(totalPax, tourAdults);
    var c = totalPax - a;
    if (c > tourChildren) {
        c = tourChildren;
        a = totalPax - c;
    }
    a = Math.max(0, a);
    c = Math.max(0, c);
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = (a + c) || 1;
    if (typeof window.updatePricing === 'function') window.updatePricing(day, 'entry_' + index);
}

// Sync entry port hidden passengers (stored) from adults + children (visual only). Clamp to tour max. Store unchanged: pax = adult + child.
window.syncEntryPortPaxAndUpdate = function(day, index) {
    const adultsEl = document.getElementById('day' + day + '_entry_' + index + '_adults');
    const childrenEl = document.getElementById('day' + day + '_entry_' + index + '_children');
    const passengersEl = document.getElementById('day' + day + '_entry_' + index + '_passengers');
    if (!adultsEl || !childrenEl || !passengersEl) return;
    const tourAdults = parseInt(document.getElementById('adults')?.value) || 0;
    const tourChildren = parseInt(document.getElementById('children')?.value) || 0;
    const maxAdults = Math.max(0, tourAdults);
    const maxChildren = Math.max(0, tourChildren);
    adultsEl.setAttribute('max', maxAdults || 50);
    childrenEl.setAttribute('max', maxChildren || 50);
    var maxAdultsSpan = document.getElementById('day' + day + '_entry_' + index + '_max_adults');
    var maxChildrenSpan = document.getElementById('day' + day + '_entry_' + index + '_max_children');
    if (maxAdultsSpan) maxAdultsSpan.textContent = maxAdults || '—';
    if (maxChildrenSpan) maxChildrenSpan.textContent = maxChildren || '—';
    var a = parseInt(adultsEl.value) || 0;
    var c = parseInt(childrenEl.value) || 0;
    a = Math.min(Math.max(0, a), maxAdults || 50);
    c = Math.min(Math.max(0, c), maxChildren || 50);
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = (a + c) || 1;
};

// When vehicle is chosen for exit: populate adults & children to the least of (tour pax, vehicle capacity).
window.applyExitPortPaxFromVehicleAndTour = function(day, index) {
    const adultsEl = document.getElementById('day' + day + '_exit_' + index + '_adults');
    const childrenEl = document.getElementById('day' + day + '_exit_' + index + '_children');
    const passengersEl = document.getElementById('day' + day + '_exit_' + index + '_passengers');
    const vehicleSelect = document.getElementById('day' + day + '_exit_' + index + '_vehicle_id');
    if (!adultsEl || !childrenEl || !passengersEl || !vehicleSelect || !vehicleSelect.value) return;
    const tourAdults = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
    const tourChildren = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    const vehicleCapacity = parseInt(selectedOption.dataset.seatingCapacity || selectedOption.getAttribute('data-seatingCapacity') || selectedOption.getAttribute('data-seating-capacity')) || 0;
    if (vehicleCapacity <= 0) return;
    const tourTotal = tourAdults + tourChildren;
    const totalPax = Math.min(tourTotal, vehicleCapacity);
    var a = Math.min(totalPax, tourAdults);
    var c = totalPax - a;
    if (c > tourChildren) {
        c = tourChildren;
        a = totalPax - c;
    }
    a = Math.max(0, a);
    c = Math.max(0, c);
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = (a + c) || 1;
    if (typeof window.updatePricing === 'function') window.updatePricing(day, 'exit_' + index);
};

// Sync exit port hidden passengers (stored) from adults + children (visual only). Clamp to tour max.
window.syncExitPortPaxAndUpdate = function(day, index) {
    const adultsEl = document.getElementById('day' + day + '_exit_' + index + '_adults');
    const childrenEl = document.getElementById('day' + day + '_exit_' + index + '_children');
    const passengersEl = document.getElementById('day' + day + '_exit_' + index + '_passengers');
    if (!adultsEl || !childrenEl || !passengersEl) return;
    const tourAdults = parseInt(document.getElementById('adults')?.value) || 0;
    const tourChildren = parseInt(document.getElementById('children')?.value) || 0;
    const maxAdults = Math.max(0, tourAdults);
    const maxChildren = Math.max(0, tourChildren);
    adultsEl.setAttribute('max', maxAdults || 50);
    childrenEl.setAttribute('max', maxChildren || 50);
    var maxAdultsSpan = document.getElementById('day' + day + '_exit_' + index + '_max_adults');
    var maxChildrenSpan = document.getElementById('day' + day + '_exit_' + index + '_max_children');
    if (maxAdultsSpan) maxAdultsSpan.textContent = maxAdults || '—';
    if (maxChildrenSpan) maxChildrenSpan.textContent = maxChildren || '—';
    var a = parseInt(adultsEl.value) || 0;
    var c = parseInt(childrenEl.value) || 0;
    a = Math.min(Math.max(0, a), maxAdults || 50);
    c = Math.min(Math.max(0, c), maxChildren || 50);
    adultsEl.value = a;
    childrenEl.value = c;
    passengersEl.value = (a + c) || 1;
};

// When any entry vehicle select changes (main or add-more, including Select2), populate adults/children from vehicle capacity.
$(document).off('change.entryVehiclePax').on('change.entryVehiclePax', 'select[id*="_entry_"][id*="_vehicle_id"]', function() {
    const el = this;
    const id = el.id || '';
    const match = id.match(/day(\d+)_entry_(\d+)_vehicle_id/);
    if (!match || !el.value) return;
    const day = parseInt(match[1], 10);
    const index = parseInt(match[2], 10);
    if (typeof window.applyEntryPortPaxFromVehicleAndTour === 'function') {
        window.applyEntryPortPaxFromVehicleAndTour(day, index);
    }
    if (typeof validatePassengerCapacity === 'function') validatePassengerCapacity(day, 'entry_' + index);
    if (typeof window.updatePricing === 'function') window.updatePricing(day, 'entry_' + index);
});

// When any exit vehicle select changes (main or add-more, including Select2), populate adults/children from vehicle capacity.
$(document).off('change.exitVehiclePax').on('change.exitVehiclePax', 'select[id*="_exit_"][id*="_vehicle_id"]', function() {
    const el = this;
    const id = el.id || '';
    const match = id.match(/day(\d+)_exit_(\d+)_vehicle_id/);
    if (!match || !el.value) return;
    const day = parseInt(match[1], 10);
    const index = parseInt(match[2], 10);
    if (typeof window.applyExitPortPaxFromVehicleAndTour === 'function') {
        window.applyExitPortPaxFromVehicleAndTour(day, index);
    }
    if (typeof validatePassengerCapacity === 'function') validatePassengerCapacity(day, 'exit_' + index);
    if (typeof window.updatePricing === 'function') window.updatePricing(day, 'exit_' + index);
});

// When main transport vehicle select changes: set adults/children from min(tour pax, vehicle capacity) and update pricing.
$(document).off('change.transportVehiclePax').on('change.transportVehiclePax', 'select[id*="_transport_vehicle_id"]', function() {
    const el = this;
    const id = el.id || '';
    const mainMatch = id.match(/day(\d+)_transport_vehicle_id$/);
    if (mainMatch && el.value) {
        const day = parseInt(mainMatch[1], 10);
        if (typeof window.applyTransportPaxFromVehicleAndTour === 'function') {
            window.applyTransportPaxFromVehicleAndTour(day, 'transport');
        }
        if (typeof validatePassengerCapacity === 'function') validatePassengerCapacity(day, 'transport');
        if (typeof window.updatePricing === 'function') window.updatePricing(day, 'transport');
    }
});

// Function to get customer information
function getCustomerInfo() {
    const fullName = document.getElementById('customerFullName')?.value || '';
    const email = document.getElementById('customerEmail')?.value || '';
    const phone = document.getElementById('customerPhone')?.value || '';
    const countryCode = document.getElementById('customerCountryCode')?.value || '';
    const address1 = document.getElementById('customerAddress1')?.value || '';
    const address2 = document.getElementById('customerAddress2')?.value || '';
    const state = document.getElementById('customerState')?.value || '';
    const zip = document.getElementById('customerZip')?.value || '';
    const specialRequests = document.getElementById('customerSpecialRequests')?.value || '';
    
    // For create form, get data from the tour package form
    const tourId = document.getElementById('tour_id')?.value || '';
    const adults = document.getElementById('adults')?.value || '1';
    const children = document.getElementById('children')?.value || '0';
    
    const customer_info = {
        fullName: fullName || 'Customer',
        email: email || 'customer@example.com',
        phone: phone || '1234567890',
        countryCode: countryCode || '+1',
        address1: address1 || 'Address',
        address2: address2 || '',
        state: state || 'State',
        zip: zip || '12345',
        specialRequests: specialRequests || '',
        tour_id: tourId,
        adults: adults,
        children: children
    };
    
    return customer_info;
}

// Function to save transport service
function saveTransportService(day, section, type) {
    console.log(`Saving transport service for day ${day}, section ${section}, type ${type}`);
    
    const saveBtn = document.getElementById(`day${day}_${section}_save_btn`);
    if (!saveBtn) {
        console.error('Save button not found');
        return;
    }
    
    // Get the current service type
    const pointToPointRadio = document.getElementById(`day${day}_transport_point_to_point`);
    const hourlyRadio = document.getElementById(`day${day}_transport_hourly`);
    const localTransferRadio = document.getElementById(`day${day}_transport_local_transfer`);
    
    let serviceCategory = 'local_transfer';
    if (pointToPointRadio && pointToPointRadio.checked) {
        serviceCategory = 'point_to_point';
    } else if (hourlyRadio && hourlyRadio.checked) {
        serviceCategory = 'hourly';
    }
    
    // Show loading state
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Saving...';
    saveBtn.disabled = true;
    
    // Prepare data based on service category
    let data = {};
    
    const vehicleSelectElement = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
    let selectedVehicleOption = vehicleSelectElement && vehicleSelectElement.selectedIndex >= 0
        ? vehicleSelectElement.options[vehicleSelectElement.selectedIndex]
        : null;

    if (serviceCategory === 'point_to_point') {
        const pickupLocation = document.querySelector(`input[name="day${day}_${section}_point_pickup_location"]`);
        const dropoffLocation = document.querySelector(`input[name="day${day}_${section}_point_dropoff_location"]`);
        const pickupTime = document.querySelector(`select[name="day${day}_${section}_point_pickup_time"]`);
        const pickupDate = document.querySelector(`input[name="day${day}_${section}_point_pickup_date"]`);
        const serviceType = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
        
        data = {
            service_category: 'point_to_point',
            pickup_location: pickupLocation?.value,
            dropoff_location: dropoffLocation?.value,
            pickup_time: pickupTime?.value,
            pickup_date: pickupDate?.value,
            vehicle_id: vehicleSelectElement?.value,
            service_type: serviceType?.value,
            day: day
        };
    } else if (serviceCategory === 'hourly') {
        const pickupLocation = document.querySelector(`input[name="day${day}_${section}_hourly_pickup_location"]`);
        const pickupTime = document.querySelector(`[name="day${day}_${section}_hourly_pickup_time"]`);
        const pickupDate = document.querySelector(`input[name="day${day}_${section}_hourly_pickup_date"]`);
        const serviceType = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
        
        data = {
            service_category: 'hourly',
            pickup_location: pickupLocation?.value,
            pickup_time: pickupTime?.value,
            pickup_date: pickupDate?.value,
            vehicle_id: vehicleSelectElement?.value,
            service_type: serviceType?.value,
            day: day
        };
    } else {
        // Local transfer
        const pickupZoneId = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
        const dropoffZoneId = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
        const pickupTime = document.querySelector(`[name="day${day}_${section}_pickup_time"]`);
        const pickupDate = document.querySelector(`input[name="day${day}_${section}_date"]`);
        const serviceType = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
        
        data = {
            service_category: 'local_transfer',
            pickup_zone_id: pickupZoneId?.value,
            dropoff_zone_id: dropoffZoneId?.value,
            pickup_time: pickupTime?.value,
            pickup_date: pickupDate?.value,
            vehicle_id: vehicleSelectElement?.value,
            service_type: serviceType?.value,
            day: day
        };
    }
    
    if (selectedVehicleOption) {
        data.image = selectedVehicleOption.dataset.image || '';
        data.vehicles_name = selectedVehicleOption.dataset.vehicleName || selectedVehicleOption.text || '';
        data.vehicle_type = selectedVehicleOption.dataset.vehicleType || '';
        data.vehicle_model = selectedVehicleOption.dataset.vehicleModel || '';
        data.seating_capacity = selectedVehicleOption.dataset.seatingcapacity || selectedVehicleOption.dataset.seatingCapacity || '';
    }
    
    // Get customer information
    const customerInfo = getCustomerInfo();
    data = { ...data, ...customerInfo };
    
    console.log('Transport service data:', data);
    
    // Submit to controller
    fetch("{{ route('orders.local-transfer.select') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            booking_data: JSON.stringify([data]),
            type: serviceCategory
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Transport service saved successfully!', 'success');
            
            // Reset form
            const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
            const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
            
            if (vehicleSelect) {
                vehicleSelect.value = '';
                vehicleSelect.disabled = true;
            }
            
            if (serviceTypeSelect) {
                serviceTypeSelect.value = '';
                serviceTypeSelect.disabled = true;
            }
            
            // Hide vehicle results
            const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
            if (vehicleResultsDiv) {
                vehicleResultsDiv.style.display = 'none';
            }
        } else {
            showNotification(data.message || 'Failed to save service.', 'error');
        }
        
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error saving service:', error);
        showNotification('An error occurred while saving the service.', 'error');
        
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Debug function to test child age dropdowns
window.testChildAgeDropdowns = function() {
    console.log('=== TESTING CHILD AGE DROPDOWNS ===');
    
    const childAgeSelects = document.querySelectorAll('.child-age-select');
    console.log(`Found ${childAgeSelects.length} child age dropdowns`);
    
    childAgeSelects.forEach((select, index) => {
        console.log(`Child ${index + 1} dropdown:`, {
            value: select.value || 'NO VALUE',
            selectedText: select.options[select.selectedIndex]?.text || 'NO SELECTED TEXT',
            totalOptions: select.options.length
        });
    });
    
    // Test the validation logic
    const children = parseInt(document.getElementById('children')?.value) || 0;
    const childAges = [];
    childAgeSelects.forEach(select => {
        if (select.value) {
            childAges.push(parseInt(select.value));
        }
    });
    
    console.log('Validation check:', {
        expectedChildren: children,
        selectedAges: childAges,
        selectedCount: childAges.length,
        validationPasses: children === 0 || childAges.length === children
    });
};

// Debug function to test create tour validation
window.debugCreateTourValidation = function() {
    console.log('=== DEBUGGING CREATE TOUR VALIDATION ===');
    
    const children = parseInt(document.getElementById('children')?.value) || 0;
    const childAgesData = document.getElementById('child_ages')?.value;
    const enquiry = @json($enquiry);
    
    console.log('Current state:', {
        children: children,
        childAgesData: childAgesData,
        enquiryExists: !!enquiry,
        enquiryChildAges: enquiry?.child_ages || 'N/A'
    });
    
    // Test child age selects in modal
    const childAgeSelects = document.querySelectorAll('.child-age-select');
    const selectedAges = [];
    childAgeSelects.forEach(select => {
        if (select.value) {
            selectedAges.push(parseInt(select.value));
        }
    });
    
    console.log('Modal child age selects:', {
        totalDropdowns: childAgeSelects.length,
        selectedAges: selectedAges,
        selectedCount: selectedAges.length
    });
    
    // Parse child ages data
    let parsedChildAges = [];
    if (childAgesData) {
        try {
            parsedChildAges = JSON.parse(childAgesData);
        } catch (e) {
            console.error('Error parsing child ages data:', e);
        }
    }
    
    console.log('Hidden field child ages:', {
        rawData: childAgesData,
        parsedData: parsedChildAges,
        parsedCount: parsedChildAges.length
    });
    
    console.log('Validation would:', {
        scenario: enquiry && enquiry.child_ages ? 'Use enquiry data' : 'Use guest selector data',
        expectedChildren: children,
        actualAgesCount: enquiry && enquiry.child_ages ? 
            (typeof enquiry.child_ages === 'string' ? enquiry.child_ages.split(',').length : enquiry.child_ages.length) :
            parsedChildAges.length,
        wouldPass: enquiry && enquiry.child_ages ? 
            (typeof enquiry.child_ages === 'string' ? enquiry.child_ages.split(',').length === children : enquiry.child_ages.length === children) :
            (parsedChildAges.length === children)
    });
};

function updatePassengerFeild(event, section, day) {
    console.log('Event:', event);
    selected_value = event.target.value;
    console.log('Selected value:', selected_value);
    console.log('Updating passenger feild for section:', section, 'and day:', day);
    const passengerInput = document.getElementById(`day${day}_${section}_passengers`);
    const vehicleSelect = document.getElementById(`day${day}_${section}_vehicle_id`);
}

</script>
