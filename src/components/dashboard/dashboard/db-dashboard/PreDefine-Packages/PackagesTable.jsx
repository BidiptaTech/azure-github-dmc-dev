import { useState } from "react";
import {
  Box,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  Paper,
  IconButton,
  Tooltip,
  Badge,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TableSortLabel,
  CircularProgress
} from "@mui/material";
import {
  Visibility,
  Edit,
  MoreVert,
  CalendarToday,
  PeopleAlt,
  LocationOn,
  Person,
  AttachMoney,
  FileDownloadOutlined,
  Close
} from "@mui/icons-material";
import { getSorting } from "./utils";
import { StatusChip, PaymentStatusChip } from "./StatusChips";
import BookingViewModal from './BookingViewModal';

// Package Table Component
const PackagesTable = ({ data = [], emptyMessage = "No packages available" }) => {
  const [order, setOrder] = useState('desc');
  const [orderBy, setOrderBy] = useState('startDate');
  const [openViewModal, setOpenViewModal] = useState(false);
  const [selectedBooking, setSelectedBooking] = useState(null);

  const handleRequestSort = (event, property) => {
    const isAsc = orderBy === property && order === 'asc';
    setOrder(isAsc ? 'desc' : 'asc');
    setOrderBy(property);
  };
  
  const handleViewClick = (booking) => {
    console.log("View button clicked for booking: ", booking);
    
    // Check if this is a processed booking (with bookingId) or a raw API booking (with booking_id)
    // Make sure we pass the data in the expected format for the modal
    const bookingData = booking.booking_id ? {
      // This is API format data, just pass it directly
      ...booking
    } : {
      // This is processed data, use as is
      ...booking
    };
    
    setSelectedBooking(bookingData);
    setOpenViewModal(true);
  };
  
  const handleCloseModal = () => {
    console.log("Closing modal");
    setOpenViewModal(false);
    setSelectedBooking(null);
  };

  if (data.length === 0) {
    return (
      <Box sx={{ textAlign: 'center', py: 4 }}>
        <Typography color="text.secondary">{emptyMessage}</Typography>
      </Box>
    );
  }

  // Sort the data
  const sortedData = [...data].sort(getSorting(order, orderBy));

  return (
    <>
      <TableContainer
        component={Paper}
        elevation={0}
        sx={{
          border: '1px solid #e0e0e0',
          borderRadius: '8px',
          overflow: 'hidden',
          backgroundColor: 'white',
          boxShadow: '0 2px 10px rgba(0,0,0,0.05)'
        }}
      >
        <Table sx={{ minWidth: 1000 }}>
          <TableHead sx={{ backgroundColor: '#f0f4f8' }}>
            <TableRow>
              <TableCell align="center" sx={{ fontWeight: 'bold', color: '#37474f' }}>
                Actions
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'bookingId'}
                  direction={orderBy === 'bookingId' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'bookingId')}
                >
                  Booking ID
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'startDate'}
                  direction={orderBy === 'startDate' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'startDate')}
                >
                  Start Date
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'endDate'}
                  direction={orderBy === 'endDate' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'endDate')}
                >
                  End Date
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'pax'}
                  direction={orderBy === 'pax' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'pax')}
                >
                  Pax
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'destination'}
                  direction={orderBy === 'destination' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'destination')}
                >
                  Destination
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'customerName'}
                  direction={orderBy === 'customerName' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'customerName')}
                >
                  Customer
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>Status</TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>Payment</TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>Payment Status</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {sortedData.map((row) => (
              <TableRow
                key={row.bookingId}
                sx={{
                  '&:last-child td, &:last-child th': { border: 0 },
                  '&:hover': { backgroundColor: '#f5f9ff' },
                  transition: 'background-color 0.2s'
                }}
              >
                <TableCell align="center">
                  <Box sx={{ display: 'flex', gap: 1, justifyContent: 'center' }}>
                    <Tooltip title="View Details">
                      <IconButton 
                        size="small" 
                        color="primary" 
                        sx={{ bgcolor: 'rgba(67, 97, 238, 0.1)' }}
                        onClick={() => handleViewClick(row)}
                      >
                        <Visibility fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Edit Package">
                      <IconButton size="small" color="secondary" sx={{ bgcolor: 'rgba(156, 39, 176, 0.1)' }}>
                        <Edit fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Download">
                      <IconButton
                        size="small"
                        color="success"
                        sx={{ bgcolor: 'rgba(76, 175, 80, 0.1)' }}
                      >
                        <FileDownloadOutlined fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Typography variant="body2" sx={{ fontWeight: 500 }}>
                      {row.bookingId}
                    </Typography>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <CalendarToday fontSize="small" color="action" sx={{ mr: 1, opacity: 0.6 }} />
                    {row.startDate}
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <CalendarToday fontSize="small" color="action" sx={{ mr: 1, opacity: 0.6 }} />
                    {row.endDate}
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Badge badgeContent={row.pax} color="primary" sx={{ '& .MuiBadge-badge': { fontSize: '10px', height: '18px', minWidth: '18px' } }}>
                      <PeopleAlt fontSize="small" color="action" />
                    </Badge>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <LocationOn fontSize="small" color="error" sx={{ mr: 0.5 }} />
                    <Typography variant="body2">{row.destination}</Typography>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Person fontSize="small" color="action" sx={{ mr: 1, opacity: 0.6 }} />
                    <Typography variant="body2">{row.customerName}</Typography>
                  </Box>
                </TableCell>
                <TableCell>
                  <StatusChip status={row.status} />
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Typography variant="body2" sx={{ fontWeight: 500, color: 'success.main', mr: 0.5 }}>
                      S$
                    </Typography>
                    <Typography variant="body2" sx={{ fontWeight: 500 }}>
                      {row.payment}
                    </Typography>
                  </Box>
                </TableCell>

                <TableCell>
                  <PaymentStatusChip status={row.paymentStatus} />
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
      
      {/* View Booking Modal */}
      <BookingViewModal
        open={openViewModal}
        onClose={handleCloseModal}
        bookingData={selectedBooking}
      />
    </>
  );
};

export default PackagesTable; 