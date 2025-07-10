import { useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { cancelPackageBooking } from "../../../../../slice/tour-packages/prePackagesSlice";
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
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TableSortLabel,
  CircularProgress,
  Alert,
  Snackbar
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
  Close,
  Cancel,
  Settings,
  ConfirmationNumber,
  Event,
  EventAvailable,
  Group,
  Place,
  AccountCircle,
  Badge as BadgeIcon,
  Payment,
  Info
  
} from "@mui/icons-material";
import { getSorting } from "./utils.jsx";
import { StatusChip, PaymentStatusChip } from "./StatusChips";
import BookingViewModal from './BookingViewModal';
// import BookingViewModal from './BookingViewModal';

// Package Table Component
const PackagesTable = ({ data = [], emptyMessage = "No packages available", userRole = null }) => {

  const dispatch = useDispatch();
  const { cancelBookingLoading, cancelBookingSuccess, cancelBookingError } = useSelector((state) => state.prePackages);
  
  const [order, setOrder] = useState('desc');
  const [orderBy, setOrderBy] = useState('startDate');
  const [openViewModal, setOpenViewModal] = useState(false);
  const [selectedBooking, setSelectedBooking] = useState(null);
  
  // New state for cancel confirmation dialog
  const [openCancelDialog, setOpenCancelDialog] = useState(false);
  const [bookingToCancel, setBookingToCancel] = useState(null);
  
  // Snackbar state
  const [snackbar, setSnackbar] = useState({
    open: false,
    message: '',
    severity: 'info'
  });

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
  
  // Handle opening cancel confirmation dialog
  const handleCancelClick = (booking) => {
    // Don't allow cancellation of already cancelled bookings
    if (booking.status && String(booking.status).toLowerCase() === 'cancelled') {
      setSnackbar({
        open: true,
        message: 'This booking is already cancelled',
        severity: 'info'
      });
      return;
    }
    
    setBookingToCancel(booking);
    setOpenCancelDialog(true);
  };
  
  // Handle actual cancellation
  const handleConfirmCancel = () => {
    if (bookingToCancel) {
      // Get the booking_id from either bookingId or booking_id property
      const bookingId = bookingToCancel.booking_id || bookingToCancel.bookingId;
      
      if (bookingId) {
        dispatch(cancelPackageBooking(bookingId))
          .unwrap()
          .then(() => {
            setSnackbar({
              open: true,
              message: 'Booking cancelled successfully',
              severity: 'success'
            });
          })
          .catch((error) => {
            setSnackbar({
              open: true,
              message: error || 'Failed to cancel booking',
              severity: 'error'
            });
          });
      } else {
        setSnackbar({
          open: true,
          message: 'Booking ID not found',
          severity: 'error'
        });
      }
    }
    setOpenCancelDialog(false);
    setBookingToCancel(null);
  };
  
  // Close snackbar
  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
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
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 1 }}>
                  <Settings fontSize="small" />
                  Actions
                </Box>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'bookingId'}
                  direction={orderBy === 'bookingId' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'bookingId')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <ConfirmationNumber fontSize="small" />
                    Booking ID
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'startDate'}
                  direction={orderBy === 'startDate' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'startDate')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Event fontSize="small" />
                    Start Date
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'endDate'}
                  direction={orderBy === 'endDate' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'endDate')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <EventAvailable fontSize="small" />
                    End Date
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'pax'}
                  direction={orderBy === 'pax' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'pax')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Group fontSize="small" />
                    Pax
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'destination'}
                  direction={orderBy === 'destination' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'destination')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Place fontSize="small" />
                    Destination
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <TableSortLabel
                  active={orderBy === 'customerName'}
                  direction={orderBy === 'customerName' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'customerName')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <AccountCircle fontSize="small" />
                    Customer
                  </Box>
                </TableSortLabel>
              </TableCell>
              {userRole !== 'Agent' && (
                <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                  <TableSortLabel
                    active={orderBy === 'agentId'}
                    direction={orderBy === 'agentId' ? order : 'asc'}
                    onClick={(e) => handleRequestSort(e, 'agentId')}
                  >
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <BadgeIcon fontSize="small" />
                      Agent ID
                    </Box>
                  </TableSortLabel>
                </TableCell>
              )}
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Payment fontSize="small" />
                  Payment
                </Box>
              </TableCell>
              <TableCell sx={{ fontWeight: 'bold', color: '#37474f' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Info fontSize="small" />
                  Status
                </Box>
              </TableCell>
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
                    <Tooltip title="Cancel Booking">
                      <IconButton 
                        size="small" 
                        color="error" 
                        sx={{ 
                          bgcolor: row.status && String(row.status).toLowerCase() === 'cancelled' 
                            ? 'rgba(158, 158, 158, 0.1)' 
                            : 'rgba(244, 67, 54, 0.1)'
                        }}
                        onClick={() => handleCancelClick(row)}
                        disabled={row.status && String(row.status).toLowerCase() === 'cancelled'}
                      >
                        <Cancel fontSize="small" />
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
                {userRole !== 'Agent' && (
                  <TableCell>
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Chip
                        label={`ID: ${row.agentId || '0001'}`}
                        size="small"
                        variant="outlined"
                        color="primary"
                        sx={{
                          fontSize: '0.75rem',
                          height: '24px',
                          backgroundColor: '#e3f2fd',
                          borderColor: '#1976d2',
                          color: '#1976d2',
                          '& .MuiChip-label': {
                            px: 1,
                            fontWeight: 500
                          },
                          '&:hover': {
                            backgroundColor: '#bbdefb',
                            borderColor: '#1565c0'
                          }
                        }}
                      />
                    </Box>
                  </TableCell>
                )}
                {userRole !== 'Agent' && (
                  <TableCell>
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Chip
                        label={`ID: ${row.agentId || '0001'}`}
                        size="small"
                        variant="outlined"
                        color="primary"
                        sx={{
                          fontSize: '0.75rem',
                          height: '24px',
                          backgroundColor: '#e3f2fd',
                          borderColor: '#1976d2',
                          color: '#1976d2',
                          '& .MuiChip-label': {
                            px: 1,
                            fontWeight: 500
                          },
                          '&:hover': {
                            backgroundColor: '#bbdefb',
                            borderColor: '#1565c0'
                          }
                        }}
                      />
                    </Box>
                  </TableCell>
                )}
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
                  <StatusChip status={row.status} />
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
      
      {/* Cancel Booking Confirmation Dialog */}
      <Dialog
        open={openCancelDialog}
        onClose={() => setOpenCancelDialog(false)}
        aria-labelledby="cancel-dialog-title"
      >
        <DialogTitle id="cancel-dialog-title" sx={{ bgcolor: '#f44336', color: 'white' }}>
          Confirm Cancellation
        </DialogTitle>
        <DialogContent sx={{ mt: 2, minWidth: '400px' }}>
          <Typography variant="body1" gutterBottom>
            Are you sure you want to cancel this booking?
          </Typography>
          {bookingToCancel && (
            <Box sx={{ mt: 2, p: 2, bgcolor: '#f5f5f5', borderRadius: 1 }}>
              <Typography variant="subtitle2" gutterBottom>
                Booking ID: {bookingToCancel.bookingId || bookingToCancel.booking_id}
              </Typography>
              <Typography variant="subtitle2" gutterBottom>
                Destination: {bookingToCancel.destination}
              </Typography>
              <Typography variant="subtitle2">
                Customer: {bookingToCancel.customerName}
              </Typography>
            </Box>
          )}
          <Typography variant="body2" color="text.secondary" sx={{ mt: 2, fontStyle: 'italic' }}>
            This action cannot be undone. The booking status will be changed to 'Cancelled'.
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpenCancelDialog(false)} color="inherit">
            Back
          </Button>
          <Button 
            onClick={handleConfirmCancel} 
            color="error" 
            variant="contained"
            disabled={cancelBookingLoading}
            startIcon={cancelBookingLoading ? <CircularProgress size={20} color="inherit" /> : null}
          >
            {cancelBookingLoading ? 'Cancelling...' : 'Confirm Cancel'}
          </Button>
        </DialogActions>
      </Dialog>
      
      {/* Success/Error Snackbar */}
      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbar.severity} variant="filled">
          {snackbar.message}
        </Alert>
      </Snackbar>
    </>
  );
};


export default PackagesTable; 