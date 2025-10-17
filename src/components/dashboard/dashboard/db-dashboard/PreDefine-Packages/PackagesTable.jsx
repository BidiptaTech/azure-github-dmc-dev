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
    // Only allow cancellation for status 1 (Confirm) and 2 (Definite)
    if (booking.status !== 1 && booking.status !== 2) {
      setSnackbar({
        open: true,
        message: 'This booking cannot be cancelled',
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
          overflow: 'auto', // Enable both horizontal and vertical scrolling
          backgroundColor: 'white',
          boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
          maxWidth: '100%', // Ensure container doesn't exceed viewport
          // Responsive scrolling behavior
          '@media (max-width: 768px)': {
            overflowX: 'auto',
            overflowY: 'visible',
            '&::-webkit-scrollbar': {
              height: '6px',
            },
            '&::-webkit-scrollbar-track': {
              backgroundColor: '#f1f1f1',
              borderRadius: '3px',
            },
            '&::-webkit-scrollbar-thumb': {
              backgroundColor: '#c1c1c1',
              borderRadius: '3px',
              '&:hover': {
                backgroundColor: '#a8a8a8',
              },
            },
          },
          // On large screens, enable horizontal scrolling when content overflows
          '@media (min-width: 1200px)': {
            overflowX: 'auto',
            overflowY: 'visible',
            // Add subtle visual hint for horizontal scrollability
            '&::-webkit-scrollbar': {
              height: '8px',
            },
            '&::-webkit-scrollbar-track': {
              backgroundColor: '#f1f1f1',
              borderRadius: '4px',
            },
            '&::-webkit-scrollbar-thumb': {
              backgroundColor: '#c1c1c1',
              borderRadius: '4px',
              '&:hover': {
                backgroundColor: '#a8a8a8',
              },
            },
          }
        }}
      >
        <Table sx={{ 
          minWidth: { xs: 1000, sm: 1200, md: 1200 }, // Responsive minimum width
          // Ensure table takes full width but allows horizontal scroll when needed
          width: '100%',
          tableLayout: 'auto'
        }}>
          <TableHead sx={{ backgroundColor: '#f0f4f8' }}>
            <TableRow>
              <TableCell align="center" sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 1 }}>
                  <Settings fontSize="small" />
                  <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                    Actions
                  </Box>
                </Box>
              </TableCell>
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 120, sm: 150 }, 
                width: { xs: 120, sm: 150 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                <TableSortLabel
                  active={orderBy === 'bookingId'}
                  direction={orderBy === 'bookingId' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'bookingId')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <ConfirmationNumber fontSize="small" />
                    <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                      Booking ID / DMC
                    </Box>
                    <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                      ID
                    </Box>
                  </Box>
                </TableSortLabel>
              </TableCell>
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 60, sm: 80 }, 
                width: { xs: 60, sm: 80 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 120, sm: 150 }, 
                width: { xs: 120, sm: 150 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 120, sm: 150 }, 
                width: { xs: 120, sm: 150 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
                <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
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
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Payment fontSize="small" />
                  Payment
                </Box>
              </TableCell>
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 80, sm: 100 }, 
                width: { xs: 80, sm: 100 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Info fontSize="small" />
                  Status
                </Box>
              </TableCell>
              <TableCell sx={{ 
                fontWeight: 'bold', 
                color: '#37474f', 
                minWidth: { xs: 100, sm: 120 }, 
                width: { xs: 100, sm: 120 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                <TableSortLabel
                  active={orderBy === 'createdAt'}
                  direction={orderBy === 'createdAt' ? order : 'asc'}
                  onClick={(e) => handleRequestSort(e, 'createdAt')}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <CalendarToday fontSize="small" />
                    Created At
                  </Box>
                </TableSortLabel>
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
                  <Box sx={{ 
                    display: 'flex', 
                    gap: { xs: 0.5, sm: 1 }, 
                    justifyContent: 'center',
                    flexDirection: { xs: 'column', sm: 'row' }
                  }}>
                    <Tooltip title="View Details">
                      <IconButton
                        size="small"
                        color="primary"
                        sx={{ 
                          bgcolor: 'rgba(67, 97, 238, 0.1)',
                          minWidth: { xs: '32px', sm: '40px' },
                          minHeight: { xs: '32px', sm: '40px' }
                        }}
                        onClick={() => handleViewClick(row)}
                      >
                        <Visibility fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    {/* Only show cancel button for status 1 (Confirm) and 2 (Definite) */}
                    {(row.status === 1 || row.status === 2) && (
                      <Tooltip title="Cancel Booking">
                        <IconButton 
                          size="small" 
                          color="error" 
                          sx={{ 
                            bgcolor: 'rgba(244, 67, 54, 0.1)',
                            minWidth: { xs: '32px', sm: '40px' },
                            minHeight: { xs: '32px', sm: '40px' }
                          }}
                          onClick={() => handleCancelClick(row)}
                        >
                          <Cancel fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    )}
                   
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 0.5 }}>
                    <Typography variant="body2" sx={{ 
                      fontWeight: 500, 
                      color: '#2c3e50',
                      fontSize: { xs: '0.75rem', sm: '0.875rem' }
                    }}>
                      {row.bookingId}
                    </Typography>
                    {row.dmc_data && row.dmc_data.dmc_company_name && (
                      <Tooltip 
                        title={row.dmc_data.dmc_company_name}
                        placement="top"
                        arrow
                      >
                        <Chip
                          label={row.dmc_data.dmc_company_name}
                          size="small"
                          variant="outlined"
                          sx={{
                            height: { xs: '18px', sm: '20px' },
                            fontSize: { xs: '0.65rem', sm: '0.7rem' },
                            fontStyle: 'italic',
                            color: '#7f8c8d',
                            borderColor: '#bdc3c7',
                            backgroundColor: 'rgba(189, 195, 199, 0.1)',
                            '& .MuiChip-label': {
                              px: { xs: 0.5, sm: 1 },
                              maxWidth: { xs: '120px', sm: '180px' },
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              whiteSpace: 'nowrap'
                            }
                          }}
                        />
                      </Tooltip>
                    )}
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <CalendarToday fontSize="small" color="action" sx={{ mr: { xs: 0.5, sm: 1 }, opacity: 0.6 }} />
                    <Typography variant="body2" sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}>
                      {row.startDate}
                    </Typography>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <CalendarToday fontSize="small" color="action" sx={{ mr: { xs: 0.5, sm: 1 }, opacity: 0.6 }} />
                    <Typography variant="body2" sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}>
                      {row.endDate}
                    </Typography>
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
                    <LocationOn fontSize="small" color="error" sx={{ mr: { xs: 0.25, sm: 0.5 } }} />
                    <Typography variant="body2" sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}>
                      {row.destination}
                    </Typography>
                  </Box>
                </TableCell>
                <TableCell>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Person fontSize="small" color="action" sx={{ mr: { xs: 0.5, sm: 1 }, opacity: 0.6 }} />
                    <Typography variant="body2" sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}>
                      {row.customerName}
                    </Typography>
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
                          fontSize: { xs: '0.65rem', sm: '0.75rem' },
                          height: { xs: '20px', sm: '24px' },
                          backgroundColor: '#e3f2fd',
                          borderColor: '#1976d2',
                          color: '#1976d2',
                          '& .MuiChip-label': {
                            px: { xs: 0.5, sm: 1 },
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
                <TableCell sx={{ minWidth: { xs: 100, sm: 120 }, width: { xs: 100, sm: 120 } }}>
                  <Box sx={{ display: 'flex', alignItems: 'baseline' }}>
                    <Typography variant="body2" sx={{ 
                      fontWeight: 500, 
                      color: 'success.main', 
                      mr: 0.5, 
                      fontSize: { xs: '8px', sm: '10px' }
                    }}>
                      SGD
                    </Typography>
                    <Typography variant="body2" sx={{ 
                      fontWeight: 500,
                      fontSize: { xs: '0.75rem', sm: '0.875rem' }
                    }}>
                      {row.payment}
                    </Typography>
                  </Box>
                </TableCell>
                <TableCell sx={{ minWidth: { xs: 80, sm: 100 }, width: { xs: 80, sm: 100 } }}>
                  <StatusChip status={row.status} />
                </TableCell>
                <TableCell sx={{ minWidth: { xs: 100, sm: 120 }, width: { xs: 100, sm: 120 } }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: { xs: 0.5, sm: 1 } }}>
                    <CalendarToday fontSize="small" color="action" sx={{ opacity: 0.6 }} />
                    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 0.5 }}>
                      <Typography variant="body2" sx={{ 
                        fontWeight: 500, 
                        color: '#2c3e50',
                        fontSize: { xs: '0.75rem', sm: '0.875rem' }
                      }}>
                        {row.createdAt ? row.createdAt.split(' ').slice(0, 3).join(' ') : 'Not specified'}
                      </Typography>
                      {row.createdAt && row.createdAt.includes(' ') && (
                        <Chip
                          label={row.createdAt.split(' ').slice(3).join(' ')}
                          size="small"
                          variant="outlined"
                          sx={{
                            height: { xs: '16px', sm: '18px' },
                            fontSize: { xs: '0.6rem', sm: '0.65rem' },
                            color: '#7f8c8d',
                            borderColor: '#bdc3c7',
                            backgroundColor: 'rgba(189, 195, 199, 0.1)',
                            '& .MuiChip-label': {
                              px: { xs: 0.25, sm: 0.5 },
                              fontWeight: 500
                            }
                          }}
                        />
                      )}
                    </Box>
                  </Box>
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