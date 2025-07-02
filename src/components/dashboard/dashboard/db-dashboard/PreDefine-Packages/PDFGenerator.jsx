import React, { useEffect } from 'react';

// PDF Generator Component - Store booking data globally for PDF access
let globalBookingData = null;

const PDFGenerator = ({ children, bookingData }) => {
  // Store booking data globally so PDF functions can access it
  useEffect(() => {
    if (bookingData) {
      globalBookingData = bookingData;
    }
  }, [bookingData]);

  // Add print styles when component mounts
  useEffect(() => {
    const style = document.createElement('style');
    style.id = 'print-styles';
    style.textContent = `
      @media print {
        @page {
          margin: 1.2in 0.3in 0.3in 0.3in;
          size: A4;
        }
        @page :first {
          margin-top: 0.3in;
        }
        
        /* Hide everything by default */
        body > * {
          display: none !important;
        }
        
        /* Show only the dialog content */
        .MuiDialog-root {
          display: block !important;
          position: static !important;
        }
        
        /* Show custom PDF content */
        .custom-pdf-content,
        .custom-pdf-content * {
          display: block !important;
          visibility: visible !important;
        }
        
        body {
          background: white !important;
          -webkit-print-color-adjust: exact !important;
          color-adjust: exact !important;
        }
      }
    `;
    document.head.appendChild(style);
    
    return () => {
      const existingStyle = document.getElementById('print-styles');
      if (existingStyle) {
        existingStyle.remove();
      }
    };
  }, []);

  return children;
};

// Helper function to parse JSON safely (same as in BookingViewModal)
const parseJsonSafely = (jsonString) => {
  if (!jsonString) return null;
  if (typeof jsonString === 'object') return jsonString;
  
  try {
    return JSON.parse(jsonString);
  } catch (error) {
    console.error("Error parsing JSON:", error);
    return null;
  }
};

// Helper function to extract service name from service object
const extractServiceName = (service) => {
  if (!service || typeof service !== 'object') return 'Unknown Service';
  
  let serviceName = '';
  
  // Handle the specific format from BookingViewModal
  if (service.service_type && service.service_name) {
    serviceName = service.service_name;
  } else if (service.type) {
    serviceName = service.name || service.title || service.hotel_name || 
           service.attraction_name || service.restaurant_name || 'Unknown Service';
  } else {
    // Try to find any name-like property
    for (const key of Object.keys(service)) {
      if (key.includes('name') || key.includes('title')) {
        serviceName = service[key];
        break;
      }
    }
  }
  
  // Add transport information for attractions if available
  if (service.attraction_with_transfer) {
    const transferValue = parseInt(service.attraction_with_transfer);
    if (transferValue === 1) {
      serviceName += ' (One-Way Transfer)';
    } else if (transferValue === 2) {
      serviceName += ' (Round-Trip Transfer)';
    }
  }
  
  // Add entry/exit transport information if available
  if (service.entry_port === 1 || service.entry_port === true) {
    serviceName += ' (Arrival Transport)';
  }
  if (service.exit_port === 1 || service.exit_port === true) {
    serviceName += ' (Departure Transport)';
  }
  
  return serviceName || 'Unknown Service';
};

// Helper function to categorize service type
const categorizeService = (service) => {
  if (!service || typeof service !== 'object') return 'other';
  
  const serviceType = (service.service_type || service.type || '').toLowerCase();
  const serviceName = (service.service_name || service.name || '').toLowerCase();
  
  // Check service type first
  if (serviceType.includes('hotel') || serviceType.includes('accommodation')) return 'hotel';
  if (serviceType.includes('attraction') || serviceType.includes('sightseeing')) return 'attraction';
  if (serviceType.includes('restaurant') || serviceType.includes('dining')) return 'restaurant';
  if (serviceType.includes('guide') || serviceType.includes('tour guide')) return 'guide';
  if (serviceType.includes('transport') || serviceType.includes('transfer')) return 'transport';
  
  // Check service name if type is not clear
  if (serviceName.includes('hotel') || serviceName.includes('accommodation')) return 'hotel';
  if (serviceName.includes('attraction') || serviceName.includes('sightseeing') || serviceName.includes('visit')) return 'attraction';
  if (serviceName.includes('restaurant') || serviceName.includes('dining') || serviceName.includes('lunch') || serviceName.includes('dinner')) return 'restaurant';
  if (serviceName.includes('guide') || serviceName.includes('tour guide')) return 'guide';
  if (serviceName.includes('pickup') || serviceName.includes('transfer') || serviceName.includes('transport')) return 'transport';
  
  // Check for specific service IDs
  if (service.hotel_id || service.hotel_name) return 'hotel';
  if (service.attraction_id || service.attraction_name) return 'attraction';
  if (service.restaurant_id || service.restaurant_name) return 'restaurant';
  
  // Check for attraction_with_transfer to identify attractions
  if (service.attraction_with_transfer) return 'attraction';
  
  return 'other';
};

// Helper function to extract service time
const extractServiceTime = (service) => {
  if (!service || typeof service !== 'object') return '09:00 AM';
  
  // Check for time-related properties
  if (service.time) return service.time;
  if (service.start_time) return service.start_time;
  if (service.scheduled_time) return service.scheduled_time;
  
  // Default times based on service category
  const category = categorizeService(service);
  switch (category) {
    case 'hotel': return '03:00 PM'; // Check-in time
    case 'restaurant': return '12:00 PM'; // Lunch time
    case 'attraction': return '10:00 AM'; // Morning visit
    case 'guide': return '09:00 AM'; // Tour start
    case 'transport': 
      // Use 9 AM for pickup/arrival, 7 PM for departure
      const serviceName = (service.activity || '').toLowerCase();
      if (serviceName.includes('pickup') || serviceName.includes('arrival')) {
        return '09:00 AM';
      } else if (serviceName.includes('departure') || serviceName.includes('drop')) {
        return '07:00 PM';
      }
      return '09:00 AM'; // Default for transport
    default: return '09:00 AM';
  }
};

// Extract booking data using the actual booking data from props
const extractBookingData = () => {
  if (!globalBookingData) {
    console.log('No global booking data available');
    return null;
  }

  const bookingData = globalBookingData;
  console.log('Using booking data:', bookingData);

  // Parse booking_details and travel_dates if present
  const bookingDetails = parseJsonSafely(bookingData.booking_details);
  const travelDates = parseJsonSafely(bookingData.travel_dates);

  // Extract basic information
  const bookingId = bookingData.bookingId || bookingData.booking_id || 'N/A';
  const customerName = bookingData.customerName || 'N/A';
  const destination = bookingData.destination || 'N/A';
  const startDate = bookingData.startDate || 'N/A';
  const endDate = bookingData.endDate || 'N/A';
  
  // Extract payment amount
  const paymentAmount = bookingDetails?.total_price ? 
    `SGD ${bookingDetails.total_price}` : 
    (bookingData.payment ? `SGD ${bookingData.payment}` : 'N/A');

  // Extract guest information
  let guests = 'N/A';
  if (bookingDetails) {
    const totalGuests = (bookingDetails.adult_count || 0) + (bookingDetails.child_count || 0);
    if (totalGuests > 0) {
      guests = `${totalGuests} person(s)`;
      if (bookingDetails.adult_count > 0 || bookingDetails.child_count > 0) {
        const guestDetails = [];
        if (bookingDetails.adult_count > 0) guestDetails.push(`${bookingDetails.adult_count} adults`);
        if (bookingDetails.child_count > 0) guestDetails.push(`${bookingDetails.child_count} children`);
        guests += ` (${guestDetails.join(', ')})`;
      }
    }
  } else if (bookingData.pax) {
    guests = `${bookingData.pax} person(s)`;
  }

  // Extract itinerary data
  const itinerary = [];
  const itineraryData = bookingDetails?.itinerary || [];
  
  if (itineraryData.length > 0) {
    itineraryData.forEach((day, index) => {
      const dayTitle = `Day ${day.day || index + 1}: ${day.date || 'Date TBD'}`;
      const services = [];
      
      // Add transport information if available (with correct times)
      if (day.arrival_pickup === 1 || day.arrival_pickup === true) {
        services.push({
          time: '09:00 AM',
          activity: 'Arrival Pickup Service',
          category: 'transport'
        });
      }
      
      // Extract services from the day with proper categorization
      if (day.services && Array.isArray(day.services)) {
        day.services.forEach(service => {
          const serviceName = extractServiceName(service);
          const serviceCategory = categorizeService(service);
          const serviceTime = extractServiceTime(service);
          
          // Check if this service has transport information that should be highlighted
          const hasAttractionTransfer = service.attraction_with_transfer && 
            (parseInt(service.attraction_with_transfer) === 1 || parseInt(service.attraction_with_transfer) === 2);
          const hasEntryTransport = service.entry_port === 1 || service.entry_port === true;
          const hasExitTransport = service.exit_port === 1 || service.exit_port === true;
          
          services.push({
            time: serviceTime,
            activity: serviceName,
            category: serviceCategory,
            hasAttractionTransfer,
            hasEntryTransport,
            hasExitTransport
          });
        });
      }
      
      if (day.departure_service === 1 || day.departure_service === true) {
        services.push({
          time: '07:00 PM',
          activity: 'Departure Service',
          category: 'transport'
        });
      }
      
      // Default service if no activities found
      if (services.length === 0) {
        services.push({
          time: '09:00 AM',
          activity: 'Free time / Leisure activities',
          category: 'other'
        });
      }

      itinerary.push({
        day: dayTitle,
        services: services
      });
    });
  }

  // If no itinerary found, create a basic one based on dates
  if (itinerary.length === 0) {
    const startDateObj = new Date(startDate);
    const endDateObj = new Date(endDate);
    const dayCount = Math.ceil((endDateObj - startDateObj) / (1000 * 60 * 60 * 24)) + 1;
    
    for (let i = 1; i <= Math.max(dayCount, 1); i++) {
      itinerary.push({
        day: `Day ${i}`,
        services: [{
          time: '09:00 AM',
          activity: 'Scheduled activities and sightseeing',
          category: 'other'
        }]
      });
    }
  }

  const result = {
    bookingId,
    customerName,
    destination,
    startDate,
    endDate,
    paymentAmount,
    guests,
    itinerary
  };

  console.log('Extracted booking data:', result);
  
  // Debug: Log detailed service information
  if (process.env.NODE_ENV === 'development') {
    console.log('Detailed service extraction:');
    result.itinerary.forEach((day, dayIndex) => {
      console.log(`Day ${dayIndex + 1}:`, day.day);
      day.services.forEach((service, serviceIndex) => {
        console.log(`  Service ${serviceIndex + 1}:`, {
          activity: service.activity,
          category: service.category,
          hasAttractionTransfer: service.hasAttractionTransfer,
          hasEntryTransport: service.hasEntryTransport,
          hasExitTransport: service.hasExitTransport
        });
      });
    });
  }
  
  return result;
};

// Create custom PDF HTML with professional styling
const createCustomPDFHTML = (bookingData) => {
  if (!bookingData) return '<div>No booking data available</div>';

  const itineraryHTML = bookingData.itinerary.map((day, index) => {
    // Organize services by category
    const hotels = day.services.filter(s => s.category === 'hotel' || s.activity.toLowerCase().includes('hotel') || s.activity.toLowerCase().includes('accommodation'));
    const attractions = day.services.filter(s => s.category === 'attraction' || s.activity.toLowerCase().includes('attraction') || s.activity.toLowerCase().includes('sightseeing') || s.activity.toLowerCase().includes('visit'));
    const restaurants = day.services.filter(s => s.category === 'restaurant' || s.activity.toLowerCase().includes('restaurant') || s.activity.toLowerCase().includes('dining') || s.activity.toLowerCase().includes('lunch') || s.activity.toLowerCase().includes('dinner'));
    const guides = day.services.filter(s => s.category === 'guide' || s.activity.toLowerCase().includes('guide') || s.activity.toLowerCase().includes('tour guide'));
    
    // Only include general transport services (arrival/departure), NOT attraction transfers
    // Attraction transfers should only appear under the attraction itself, not as separate transport
    const transport = day.services.filter(s => {
      const isTransport = s.category === 'transport' || s.activity.toLowerCase().includes('pickup') || s.activity.toLowerCase().includes('transfer') || s.activity.toLowerCase().includes('transport');
      const isAttractionTransfer = s.hasAttractionTransfer; // This indicates it's an attraction-specific transfer
      
      // Only include if it's transport but NOT an attraction transfer
      return isTransport && !isAttractionTransfer;
    });
    
    const others = day.services.filter(s => !hotels.includes(s) && !attractions.includes(s) && !restaurants.includes(s) && !guides.includes(s) && !transport.includes(s));

    // Debug: Log service categorization (only in development)
    if (process.env.NODE_ENV === 'development') {
      console.log(`Day ${index + 1} service categorization:`, {
        hotels: hotels.length,
        attractions: attractions.length,
        restaurants: restaurants.length,
        guides: guides.length,
        transport: transport.length,
        others: others.length,
        attractionTransfers: day.services.filter(s => s.hasAttractionTransfer).length
      });
    }

    // Create service sections with headings
    const createServiceSection = (services, title, icon, color) => {
      if (services.length === 0) return '';
      
      const servicesHTML = services.map(service => {
        // Create transport indicators - show attraction transfers only for attraction services
        let transportIndicators = '';
        if (service.hasAttractionTransfer || service.hasEntryTransport || service.hasExitTransport) {
          const indicators = [];
          
          // Only show entry/exit transport for hotel services
          if ((title === 'Accommodation') && (service.hasEntryTransport || service.hasExitTransport)) {
            if (service.hasEntryTransport) {
              indicators.push('<span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 4px;">🚗 Arrival</span>');
            }
            if (service.hasExitTransport) {
              indicators.push('<span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 4px;">🚗 Departure</span>');
            }
          }
          
          // Only show attraction transfers for attraction services
          if ((title === 'Attractions & Experiences') && service.hasAttractionTransfer) {
            // Try to get more specific transfer information from the service name
            let transferType = 'Transfer';
            if (service.activity && service.activity.includes('One-Way Transfer')) {
              transferType = 'One-Way Transfer';
            } else if (service.activity && service.activity.includes('Round-Trip Transfer')) {
              transferType = 'Round-Trip Transfer';
            }
            indicators.push(`<span style="background: #e8f5e8; color: #2e7d32; padding: 2px 6px; border-radius: 4px; font-size: 10px;">🚗 ${transferType}</span>`);
          }
          
          if (indicators.length > 0) {
            transportIndicators = `<div style="margin-top: 4px;">${indicators.join('')}</div>`;
          }
        }
        
        return `
          <tr style="border-bottom: 1px solid #f8f9fa;">
           
            <td style="padding: 12px 20px; color: #333; font-size: 14px; line-height: 1.5; vertical-align: top;">
              <div style="font-weight: 500;">${service.activity}</div>
              ${transportIndicators}
            </td>
          </tr>
        `;
      }).join('');

      return `
        <tr>
          <td colspan="2" style="padding: 16px 20px 8px 20px;">
            <div style="display: flex; align-items: center; margin-bottom: 8px;">
              <span style="font-size: 16px; margin-right: 8px;">${icon}</span>
              <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1a202c; text-transform: uppercase; letter-spacing: 0.5px;">${title}</h4>
            </div>
          </td>
        </tr>
        ${servicesHTML}
      `;
    };

    // Create transportation section separately (only for general transport like arrival/departure)
    const transportSection = transport.length > 0 ? `
      <tr>
        <td colspan="2" style="padding: 16px 20px 8px 20px; background: #f8f9fa;">
          <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 16px; margin-right: 8px;">🚗</span>
            <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1a202c; text-transform: uppercase; letter-spacing: 0.5px;">Transportation</h4>
          </div>
        </td>
      </tr>
      ${transport.map(service => {
        // Only show general transport indicators (entry/exit), not attraction transfers
        let transportIndicators = '';
        if (service.hasEntryTransport || service.hasExitTransport) {
          const indicators = [];
          if (service.hasEntryTransport) {
            indicators.push('<span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 4px;">🚗 Arrival</span>');
          }
          if (service.hasExitTransport) {
            indicators.push('<span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 4px;">🚗 Departure</span>');
          }
          transportIndicators = `<div style="margin-top: 4px;">${indicators.join('')}</div>`;
        }
        
        return `
          <tr style="border-bottom: 1px solid #e9ecef; background: #f8f9fa;">
           
            <td style="padding: 12px 20px; color: #333; font-size: 14px; line-height: 1.5; vertical-align: top;">
              <div style="font-weight: 500;">${service.activity}</div>
              ${transportIndicators}
            </td>
          </tr>
        `;
      }).join('')}
    ` : '';

    const servicesHTML = [
      createServiceSection(hotels, 'Accommodation', '🏨', '#667eea'),
      createServiceSection(attractions, 'Attractions & Experiences', '🎯', '#f093fb'),
      createServiceSection(restaurants, 'Dining', '🍽️', '#f5576c'),
      createServiceSection(guides, 'Tour Guide', '👨‍🏫', '#764ba2'),
      createServiceSection(others, 'Other Activities', '📋', '#a8edea')
    ].filter(section => section !== '').join('');

    return `
      <div style="margin-bottom: 32px; break-inside: avoid; page-break-inside: avoid; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #7ed6fb 0%, #b2f7ef 100%); color: #222; padding: 20px 24px; position: relative;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
              <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px;">
                <span style="color:rgb(10, 9, 9); font-weight: bold; font-size: 14px;">${index + 1}</span>
              </div>
              <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">${day.day}</h3>
              </div>
            </div>
            <div style="opacity: 0.7; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
              ${day.services.length} Activities
            </div>
          </div>
        </div>
        <div style="background: white;">
          <table style="width: 100%; border-collapse: collapse;">
            <tbody>
              ${transportSection}
              ${servicesHTML}
            </tbody>
          </table>
        </div>
      </div>
    `;

    return `
      <div style="margin-bottom: 32px; break-inside: avoid; page-break-inside: avoid; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #7ed6fb 0%, #b2f7ef 100%); color: #222; padding: 20px 24px; position: relative;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
              <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px;">
                <span style="color:rgb(21, 10, 10); font-weight: bold; font-size: 14px;">${index + 1}</span>
              </div>
              <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">${day.day}</h3>
              </div>
            </div>
            <div style="opacity: 0.7; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
              ${day.services.length} Activities
            </div>
          </div>
        </div>
        <div style="background: white;">
          <table style="width: 100%; border-collapse: collapse;">
            <tbody>
              ${servicesHTML}
            </tbody>
          </table>
        </div>
      </div>
    `;
  }).join('');

  return `
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; max-width: 850px; margin: 0 auto; background: white; line-height: 1.5;">
      
      <!-- Header Section -->
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 32px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.3;"></div>
        <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 2;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div>
              <h1 style="margin: 0 0 8px 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">Trip Itinerary</h1>
              <p style="margin: 0; font-size: 16px; opacity: 0.9; font-weight: 400; color: black;">Complete Travel Plan & Schedule</p>
            </div>
            <div style="text-align: right;">
              <div style="background: rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 20px; display: inline-block;">
                <span style="font-size: 14px; font-weight: 500;">Booking #${bookingData.bookingId}</span>
              </div>
            </div>
          </div>
          
          <!-- Key Trip Info -->
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 32px;">
            <div style="text-align: center; padding: 16px; background: rgba(255,255,255,0.1); border-radius: 12px;">
              <div style="font-size: 24px; margin-bottom: 8px;">📅</div>
              <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Duration</div>
              <div style="font-size: 14px; font-weight: 600;">${bookingData.startDate} - ${bookingData.endDate}</div>
            </div>
            <div style="text-align: center; padding: 16px; background: rgba(255,255,255,0.1); border-radius: 12px;">
              <div style="font-size: 24px; margin-bottom: 8px;">📍</div>
              <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Destination</div>
              <div style="font-size: 14px; font-weight: 600;">${bookingData.destination}</div>
            </div>
            <div style="text-align: center; padding: 16px; background: rgba(255,255,255,0.1); border-radius: 12px;">
              <div style="font-size: 24px; margin-bottom: 8px;">👥</div>
              <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Travelers</div>
              <div style="font-size: 14px; font-weight: 600;">${bookingData.guests}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Information Section -->
      <div style="padding: 32px; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 600px; margin: 0 auto;">
          <h2 style="margin: 0 0 24px 0; font-size: 20px; font-weight: 600; color: white; text-align: center;">Booking Details</h2>
          
          <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
              <div style="text-align: center; padding: 16px; border-right: 1px solid #e2e8f0;">
                <div style="color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px; letter-spacing: 1px;">Customer Name</div>
                <div style="color: #1a202c; font-size: 16px; font-weight: 600;">${bookingData.customerName}</div>
              </div>
              <div style="text-align: center; padding: 16px;">
                <div style="color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px; letter-spacing: 1px;">Total Amount</div>
                <div style="color: #059669; font-size: 16px; font-weight: 700;">${bookingData.paymentAmount}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Itinerary Section -->
      <div style="padding: 40px 32px; ">
        <div style="text-align: center; margin-bottom: 40px;">
          <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 700; color: white;">Daily Itinerary</h2>
          <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 14px;">Your complete day-by-day travel schedule</p>
        </div>
        
        ${itineraryHTML}
      </div>

      <!-- Footer Section -->
      <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
        <div style="margin-bottom: 16px;">
          <div style="display: inline-flex; align-items: center; background: white; padding: 8px 16px; border-radius: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <span style="color: #64748b; font-size: 12px; font-weight: 500;">Generated on ${new Date().toLocaleDateString('en-GB', { 
              weekday: 'long', 
              day: 'numeric', 
              month: 'long', 
              year: 'numeric' 
            })}</span>
          </div>
        </div>
        <div style="color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
          Travel Document • Booking Reference: #${bookingData.bookingId}
        </div>
      </div>
    </div>
  `;
};

// PDF Print Button Component
export const PDFPrintButton = ({ variant = "contained", color = "primary", size = "small", sx = {}, children = "Download PDF" }) => {
  const handlePrint = () => {
    // Extract booking data
    const bookingData = extractBookingData();
    
    // Create custom PDF content
    const customHTML = createCustomPDFHTML(bookingData);
    
    // Create a temporary container
    const tempContainer = document.createElement('div');
    tempContainer.className = 'custom-pdf-content';
    tempContainer.innerHTML = customHTML;
    tempContainer.style.cssText = `
      position: absolute;
      top: -9999px;
      left: -9999px;
      width: 800px;
      background: white;
    `;
    
    document.body.appendChild(tempContainer);
    
    // Add print styles
    const printStyle = document.createElement('style');
    printStyle.id = 'temp-print-styles';
    printStyle.textContent = `
      @media print {
        @page {
          margin: 0.3in;
          size: A4;
        }
        
        /* Hide everything */
        body * {
          visibility: hidden !important;
        }
        
        /* Show only the custom PDF content */
        .custom-pdf-content,
        .custom-pdf-content * {
          visibility: visible !important;
        }
        
        /* Position the content */
        .custom-pdf-content {
          position: absolute !important;
          top: 0 !important;
          left: 0 !important;
          width: 100% !important;
          margin: 0 !important;
          padding: 0 !important;
          background: white !important;
        }
        
        body {
          background: white !important;
          -webkit-print-color-adjust: exact !important;
          color-adjust: exact !important;
          print-color-adjust: exact !important;
        }
        
        /* Ensure proper table styling */
        table {
          border-collapse: collapse !important;
        }
        
        /* Ensure colors print correctly */
        * {
          -webkit-print-color-adjust: exact !important;
          color-adjust: exact !important;
          print-color-adjust: exact !important;
        }
      }
    `;
    
    document.head.appendChild(printStyle);
    
    // Print
    setTimeout(() => {
      window.print();
      
      // Clean up
      setTimeout(() => {
        document.body.removeChild(tempContainer);
        const tempStyle = document.getElementById('temp-print-styles');
        if (tempStyle) {
          tempStyle.remove();
        }
      }, 1000);
    }, 100);
  };

  return (
    <button
      onClick={handlePrint}
      style={{
        padding: size === 'small' ? '6px 16px' : '8px 22px',
        fontSize: size === 'small' ? '0.875rem' : '1rem',
        fontWeight: 500,
        borderRadius: '4px',
        border: 'none',
        cursor: 'pointer',
        backgroundColor: color === 'primary' ? '#1976d2' : '#dc3545',
        color: 'white',
        ...sx
      }}
    >
      {children}
    </button>
  );
};

// PDF Generator Hook
export const usePDFGenerator = () => {
  const generatePDF = () => {
    // Extract booking data
    const bookingData = extractBookingData();
    
    // Create custom PDF content
    const customHTML = createCustomPDFHTML(bookingData);
    
    // Create a temporary container
    const tempContainer = document.createElement('div');
    tempContainer.className = 'custom-pdf-content';
    tempContainer.innerHTML = customHTML;
    tempContainer.style.cssText = `
      position: absolute;
      top: -9999px;
      left: -9999px;
      width: 800px;
      background: white;
    `;
    
    document.body.appendChild(tempContainer);
    
    // Add print styles
    const printStyle = document.createElement('style');
    printStyle.id = 'temp-print-styles';
    printStyle.textContent = `
      @media print {
        @page {
          margin: 0.3in;
          size: A4;
        }
        
        /* Hide everything */
        body * {
          visibility: hidden !important;
        }
        
        /* Show only the custom PDF content */
        .custom-pdf-content,
        .custom-pdf-content * {
          visibility: visible !important;
        }
        
        /* Position the content */
        .custom-pdf-content {
          position: absolute !important;
          top: 0 !important;
          left: 0 !important;
          width: 100% !important;
          margin: 0 !important;
          padding: 0 !important;
          background: white !important;
        }
        
        body {
          background: white !important;
          -webkit-print-color-adjust: exact !important;
          color-adjust: exact !important;
          print-color-adjust: exact !important;
        }
        
        /* Ensure proper table styling */
        table {
          border-collapse: collapse !important;
        }
        
        /* Ensure colors print correctly */
        * {
          -webkit-print-color-adjust: exact !important;
          color-adjust: exact !important;
          print-color-adjust: exact !important;
        }
      }
    `;
    
    document.head.appendChild(printStyle);
    
    // Print
    setTimeout(() => {
      window.print();
      
      // Clean up
      setTimeout(() => {
        document.body.removeChild(tempContainer);
        const tempStyle = document.getElementById('temp-print-styles');
        if (tempStyle) {
          tempStyle.remove();
        }
      }, 1000);
    }, 100);
  };

  return { generatePDF };
};

export default PDFGenerator; 