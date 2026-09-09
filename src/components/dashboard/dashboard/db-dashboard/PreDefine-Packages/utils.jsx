import dayjs from "dayjs";

// Helper function to calculate total amount with taxes
export const calculateTotalWithTaxes = (booking) => {
  // Base amount
  const baseAmount = Number(booking.total_amount || 0);
  
  // If base amount is 0 or invalid, return 0
  if (!baseAmount || baseAmount <= 0) {
    return 0;
  }

  // Helper function to safely convert to number
  const safeNumber = (value) => {
    const num = Number(value);
    return isNaN(num) ? 0 : num;
  };

  // Helper function to calculate nights
  const calculateNights = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return 0;
    try {
      const inDate = dayjs(checkIn);
      const outDate = dayjs(checkOut);
      const nights = outDate.diff(inDate, 'day');
      return Math.max(nights, 0);
    } catch (e) {
      return 0;
    }
  };

  // Parse taxes from JSON string or array
  let taxes = [];
  try {
    if (booking.taxes && typeof booking.taxes === 'string') {
      taxes = JSON.parse(booking.taxes);
    } else if (Array.isArray(booking.taxes)) {
      taxes = booking.taxes;
    }
  } catch (e) {
    console.error('Error parsing taxes for booking:', booking.booking_id, e);
    return Math.ceil(baseAmount);
  }

  if (!taxes || taxes.length === 0) {
    return Math.ceil(baseAmount);
  }

  // Initialize with base amount
  let total = baseAmount;
  
  // Calculate total pax
  const adultCount = safeNumber(booking.booking_details?.adult_count || 0);
  const childCount = safeNumber(booking.booking_details?.child_count || 0);
  const totalPax = adultCount + childCount;
  
  // Calculate nights
  const checkIn = booking.travel_dates?.check_in || booking.booking_details?.travel_dates?.check_in;
  const checkOut = booking.travel_dates?.check_out || booking.booking_details?.travel_dates?.check_out;
  const nights = calculateNights(checkIn, checkOut);

  // Store calculated amounts for each tax by tax_id (for cascading)
  const taxCalculations = {};

  // Step 1: Process taxes where calculate_on = "total"
  const totalTaxes = taxes.filter(tax => 
    tax.calculate_on && tax.calculate_on.toLowerCase() === 'total'
  );

  totalTaxes.forEach(tax => {
    let taxAmount = 0;
    const taxValue = safeNumber(tax.tax_value);

    if (tax.tax_type === 'percentage') {
      // Calculate percentage tax on BASE amount (not running total)
      taxAmount = (baseAmount * taxValue) / 100;
    } else if (tax.tax_type === 'fixed') {
      // Calculate fixed tax based on if_fixed type
      switch (tax.if_fixed) {
        case 'person':
        case 'per_person':
          taxAmount = totalPax * taxValue;
          break;
        case 'person_day':
        case 'per_person_per_day':
          taxAmount = totalPax * nights * taxValue;
          break;
        case 'per_day':
        case 'per_tour_per_day':
          taxAmount = nights * taxValue;
          break;
        case 'per_tour':
        case 'person_tour':
          taxAmount = taxValue;
          break;
        default:
          taxAmount = taxValue;
      }
    }

    // Validate taxAmount is not NaN
    if (isNaN(taxAmount)) {
      taxAmount = 0;
    }

    // Round the tax amount for consistency between display and calculation
    const roundedTaxAmount = Math.ceil(taxAmount);
    total += roundedTaxAmount;

    // Store the cumulative total after this tax by tax_id (for cascading lookups)
    taxCalculations[tax.tax_id] = {
      amount: total,
      taxAmount: roundedTaxAmount
    };
  });

  // Step 2: Process cascading taxes (where calculate_on != "total")
  // These taxes are calculated on (base amount + specific referenced tax amount)
  // NOT on the cumulative total
  const cascadingTaxes = taxes.filter(tax => 
    tax.calculate_on && tax.calculate_on.toLowerCase() !== 'total'
  );

  cascadingTaxes.forEach(tax => {
    let taxAmount = 0;
    const taxValue = safeNumber(tax.tax_value);

    // Find the tax amount from the referenced tax_id
    // calculate_on contains the tax_id (e.g., "12")
    const referencedTaxId = String(tax.calculate_on);
    const baseCalc = taxCalculations[referencedTaxId];
    
    // Calculate on (base amount + referenced tax amount)
    // Example: if base is 2550 and tax_id 12 added 600, 
    // then baseAmountForCascade = 2550 + 600 = 3150
    const referencedTaxAmount = baseCalc ? baseCalc.taxAmount : 0;
    const baseAmountForCascade = baseAmount + referencedTaxAmount;

    if (tax.tax_type === 'percentage') {
      // Calculate percentage tax on (base + referenced tax amount)
      taxAmount = (baseAmountForCascade * taxValue) / 100;
    } else if (tax.tax_type === 'fixed') {
      // Calculate fixed tax based on if_fixed type
      switch (tax.if_fixed) {
        case 'person':
        case 'per_person':
          taxAmount = totalPax * taxValue;
          break;
        case 'person_day':
        case 'per_person_per_day':
          taxAmount = totalPax * nights * taxValue;
          break;
        case 'per_day':
        case 'per_tour_per_day':
          taxAmount = nights * taxValue;
          break;
        case 'per_tour':
          taxAmount = taxValue;
          break;
        default:
          taxAmount = taxValue;
      }
    }

    // Validate taxAmount is not NaN
    if (isNaN(taxAmount)) {
      taxAmount = 0;
    }

    // Round the tax amount for consistency between display and calculation
    const roundedTaxAmount = Math.ceil(taxAmount);
    total += roundedTaxAmount;

    // Store the tax amount by tax_id for potential cascading
    taxCalculations[tax.tax_id] = {
      amount: total,
      taxAmount: roundedTaxAmount
    };
  });

  // Final validation - if total is NaN, fallback to baseAmount
  if (isNaN(total) || !isFinite(total)) {
    console.error('Total became NaN for booking:', booking.booking_id);
    return Math.ceil(baseAmount);
  }

  return Math.ceil(total);
};

// Function to sort data
export function getSorting(order, orderBy) {
  return order === 'desc'
    ? (a, b) => {
        if (orderBy === 'pax') {
          return a[orderBy] < b[orderBy] ? -1 : 1;
        } else if (orderBy === 'startDate' || orderBy === 'endDate') {
          // Convert dates like "28 Jun 2023" to Date objects for comparison
          const dateA = new Date(a[orderBy]);
          const dateB = new Date(b[orderBy]);
          return dateA < dateB ? -1 : 1;
        } else if (orderBy === 'bookingId') {
          // Special handling for DMC format like "PKG-ORD-11"
          return compareDMCFormat(a[orderBy], b[orderBy]);
        } else {
          return a[orderBy].toString().localeCompare(b[orderBy].toString());
        }
      }
    : (a, b) => {
        if (orderBy === 'pax') {
          return a[orderBy] > b[orderBy] ? -1 : 1;
        } else if (orderBy === 'startDate' || orderBy === 'endDate') {
          // Convert dates like "28 Jun 2023" to Date objects for comparison
          const dateA = new Date(a[orderBy]);
          const dateB = new Date(b[orderBy]);
          return dateA > dateB ? -1 : 1;
        } else if (orderBy === 'bookingId') {
          // Special handling for DMC format like "PKG-ORD-11"
          return compareDMCFormat(b[orderBy], a[orderBy]);
        } else {
          return b[orderBy].toString().localeCompare(a[orderBy].toString());
        }
      };
}

// Helper function to compare DMC format strings like "PKG-ORD-11"
function compareDMCFormat(a, b) {
  if (!a || !b) return 0;
  
  const aStr = a.toString();
  const bStr = b.toString();
  
  // Extract numeric part from the end (e.g., "11" from "PKG-ORD-11")
  const aMatch = aStr.match(/(\d+)$/);
  const bMatch = bStr.match(/(\d+)$/);
  
  if (aMatch && bMatch) {
    // Both have numeric parts, compare them
    const aNum = parseInt(aMatch[1], 10);
    const bNum = parseInt(bMatch[1], 10);
    return aNum - bNum;
  } else if (aMatch) {
    // Only a has numeric part, a comes first
    return -1;
  } else if (bMatch) {
    // Only b has numeric part, b comes first
    return 1;
  } else {
    // Neither has numeric part, do string comparison
    return aStr.localeCompare(bStr);
  }
}

// Sample data for demonstration - replace with API data in production
export const sampleData = {
  ongoingData: [
    { bookingId: 'PKG-ORD-11', startDate: '12 Jun 2023', endDate: '19 Jun 2023', pax: 2, destination: 'Bangkok, Thailand', customerName: 'John Smith', status: 'Confirmed', payment: '1,299', paymentStatus: 'Paid' },
    { bookingId: 'PKG-ORD-15', startDate: '15 Jun 2023', endDate: '20 Jun 2023', pax: 4, destination: 'Dubai, UAE', customerName: 'Michael Brown', status: 'Confirmed', payment: '1,899', paymentStatus: 'Paid' },
  ],
  
  upcomingData: [
    { bookingId: 'PKG-ORD-8', startDate: '28 Jun 2023', endDate: '05 Jul 2023', pax: 2, destination: 'Phuket, Thailand', customerName: 'Sarah Johnson', status: 'Confirmed', payment: '1,499', paymentStatus: 'Paid' },
    { bookingId: 'PKG-ORD-22', startDate: '03 Jul 2023', endDate: '09 Jul 2023', pax: 3, destination: 'Singapore', customerName: 'Robert Williams', status: 'Pending', payment: '2,050', paymentStatus: 'Partial' },
    { bookingId: 'PKG-ORD-3', startDate: '10 Jul 2023', endDate: '18 Jul 2023', pax: 2, destination: 'Bali, Indonesia', customerName: 'Emily Davis', status: 'Confirmed', payment: '1,750', paymentStatus: 'Unpaid' },
  ],
  
  pastData: [
    { bookingId: 'PKG-ORD-1', startDate: '02 May 2023', endDate: '09 May 2023', pax: 2, destination: 'London, UK', customerName: 'Jennifer Wilson', status: 'Completed', payment: '2,350', paymentStatus: 'Paid' },
    { bookingId: 'PKG-ORD-5', startDate: '10 May 2023', endDate: '15 May 2023', pax: 1, destination: 'Paris, France', customerName: 'David Thompson', status: 'Completed', payment: '1,450', paymentStatus: 'Paid' },
    { bookingId: 'PKG-ORD-12', startDate: '20 May 2023', endDate: '28 May 2023', pax: 4, destination: 'New York, USA', customerName: 'Jessica Miller', status: 'Cancelled', payment: '3,200', paymentStatus: 'Refunded' },
  ]
}; 