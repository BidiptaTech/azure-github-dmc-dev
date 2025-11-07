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