import { useState } from "react";
import { 
  Box, 
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableRow,
  Typography, 
  Paper,
  IconButton,
  Tooltip,
  Badge
} from "@mui/material";
import {
  Visibility,
  Edit,
  MoreVert,
  CalendarToday,
  PeopleAlt,
  LocationOn,
  Person,
  AttachMoney
} from "@mui/icons-material";
import { getSorting } from "./utils";
import { StatusChip, PaymentStatusChip } from "./StatusChips";
import { TableHeader } from "./TableComponents";

// Package Table Component
const PackagesTable = ({ data = [], emptyMessage = "No packages available" }) => {
  const [order, setOrder] = useState('asc');
  const [orderBy, setOrderBy] = useState('bookingId');

  const handleRequestSort = (event, property) => {
    const isAsc = orderBy === property && order === 'asc';
    setOrder(isAsc ? 'desc' : 'asc');
    setOrderBy(property);
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
        <TableHeader 
          order={order}
          orderBy={orderBy}
          onRequestSort={handleRequestSort}
        />
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
                    <IconButton size="small" color="primary" sx={{ bgcolor: 'rgba(67, 97, 238, 0.1)' }}>
                      <Visibility fontSize="small" />
                    </IconButton>
                  </Tooltip>
                  <Tooltip title="Edit Package">
                    <IconButton size="small" color="secondary" sx={{ bgcolor: 'rgba(156, 39, 176, 0.1)' }}>
                      <Edit fontSize="small" />
                    </IconButton>
                  </Tooltip>
                  <Tooltip title="More Options">
                    <IconButton size="small" sx={{ bgcolor: 'rgba(0, 0, 0, 0.04)' }}>
                      <MoreVert fontSize="small" />
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
                  <AttachMoney fontSize="small" color="success" />
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>{row.payment}</Typography>
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
  );
};

export default PackagesTable; 