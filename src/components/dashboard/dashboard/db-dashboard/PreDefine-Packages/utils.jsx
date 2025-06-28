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
        } else {
          return b[orderBy].toString().localeCompare(a[orderBy].toString());
        }
      };
}

// Sample data for demonstration - replace with API data in production
export const sampleData = {
  ongoingData: [
    { bookingId: 'BK-23789', startDate: '12 Jun 2023', endDate: '19 Jun 2023', pax: 2, destination: 'Bangkok, Thailand', customerName: 'John Smith', status: 'Confirmed', payment: '1,299', paymentStatus: 'Paid' },
    { bookingId: 'BK-23790', startDate: '15 Jun 2023', endDate: '20 Jun 2023', pax: 4, destination: 'Dubai, UAE', customerName: 'Michael Brown', status: 'Confirmed', payment: '1,899', paymentStatus: 'Paid' },
  ],
  
  upcomingData: [
    { bookingId: 'BK-23795', startDate: '28 Jun 2023', endDate: '05 Jul 2023', pax: 2, destination: 'Phuket, Thailand', customerName: 'Sarah Johnson', status: 'Confirmed', payment: '1,499', paymentStatus: 'Paid' },
    { bookingId: 'BK-23799', startDate: '03 Jul 2023', endDate: '09 Jul 2023', pax: 3, destination: 'Singapore', customerName: 'Robert Williams', status: 'Pending', payment: '2,050', paymentStatus: 'Partial' },
    { bookingId: 'BK-23803', startDate: '10 Jul 2023', endDate: '18 Jul 2023', pax: 2, destination: 'Bali, Indonesia', customerName: 'Emily Davis', status: 'Confirmed', payment: '1,750', paymentStatus: 'Unpaid' },
  ],
  
  pastData: [
    { bookingId: 'BK-23770', startDate: '02 May 2023', endDate: '09 May 2023', pax: 2, destination: 'London, UK', customerName: 'Jennifer Wilson', status: 'Completed', payment: '2,350', paymentStatus: 'Paid' },
    { bookingId: 'BK-23775', startDate: '10 May 2023', endDate: '15 May 2023', pax: 1, destination: 'Paris, France', customerName: 'David Thompson', status: 'Completed', payment: '1,450', paymentStatus: 'Paid' },
    { bookingId: 'BK-23780', startDate: '20 May 2023', endDate: '28 May 2023', pax: 4, destination: 'New York, USA', customerName: 'Jessica Miller', status: 'Cancelled', payment: '3,200', paymentStatus: 'Refunded' },
  ]
}; 