import { Box, TableCell, TableHead, TableRow, Tooltip } from "@mui/material";
import {
  ArrowUpward,
  ArrowDownward,
  Sort,
  BookmarkBorder,
  CalendarToday,
  PeopleAlt,
  LocationOn,
  Person,
  CheckCircleOutline,
  AttachMoney,
  CreditCard,
  AccountCircle
} from "@mui/icons-material";

// Sortable Column Header
export const SortableColumnHeader = ({ label, icon, field, orderBy, order, onRequestSort }) => {
  const createSortHandler = (property) => (event) => {
    onRequestSort(event, property);
  };
  
  return (
    <TableCell
      sortDirection={orderBy === field ? order : false}
      sx={{ 
        fontWeight: 'bold',
        cursor: 'pointer',
        '&:hover': { backgroundColor: '#e3f2fd' },
        transition: 'background-color 0.2s'
      }}
      onClick={createSortHandler(field)}
    >
      <Tooltip title={`Sort by ${label}`}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          {icon}
          <Box sx={{ ml: 0.5 }}>{label}</Box>
          {orderBy === field ? (
            <Box sx={{ ml: 0.5 }}>
              {order === 'asc' ? <ArrowUpward fontSize="small" /> : <ArrowDownward fontSize="small" />}
            </Box>
          ) : null}
        </Box>
      </Tooltip>
    </TableCell>
  );
};

// Table Header component for reusability
export const TableHeader = ({ order, orderBy, onRequestSort, userRole = null }) => {
  return (
    <TableHead sx={{ backgroundColor: '#f0f4f8' }}>
      <TableRow>
        <TableCell align="center" sx={{ fontWeight: 'bold' }}>
          <Tooltip title="Actions">
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Sort fontSize="small" sx={{ mr: 0.5 }} /> Actions
            </Box>
          </Tooltip>
        </TableCell>
        <SortableColumnHeader 
          label="Booking ID" 
          icon={<BookmarkBorder fontSize="small" sx={{ mr: 0.5 }} />} 
          field="bookingId" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        <SortableColumnHeader 
          label="Start Date" 
          icon={<CalendarToday fontSize="small" sx={{ mr: 0.5 }} />} 
          field="startDate" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        <SortableColumnHeader 
          label="End Date" 
          icon={<CalendarToday fontSize="small" sx={{ mr: 0.5 }} />} 
          field="endDate" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        <SortableColumnHeader 
          label="Pax" 
          icon={<PeopleAlt fontSize="small" sx={{ mr: 0.5 }} />} 
          field="pax" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        <SortableColumnHeader 
          label="Destination" 
          icon={<LocationOn fontSize="small" sx={{ mr: 0.5 }} />} 
          field="destination" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        <SortableColumnHeader 
          label="Customer Name" 
          icon={<Person fontSize="small" sx={{ mr: 0.5 }} />} 
          field="customerName" 
          orderBy={orderBy} 
          order={order} 
          onRequestSort={onRequestSort} 
        />
        {userRole !== 'Agent' && (
          <SortableColumnHeader 
            label="Agent ID" 
            icon={<AccountCircle fontSize="small" sx={{ mr: 0.5 }} />} 
            field="agentId" 
            orderBy={orderBy} 
            order={order} 
            onRequestSort={onRequestSort} 
          />
        )}
        <TableCell sx={{ fontWeight: 'bold' }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <CheckCircleOutline fontSize="small" sx={{ mr: 0.5 }} /> Status
          </Box>
        </TableCell>
        <TableCell sx={{ fontWeight: 'bold' }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <AttachMoney fontSize="small" sx={{ mr: 0.5 }} /> Payment
          </Box>
        </TableCell>
        <TableCell sx={{ fontWeight: 'bold' }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <CreditCard fontSize="small" sx={{ mr: 0.5 }} /> Payment Status
          </Box>
        </TableCell>
      </TableRow>
    </TableHead>
  );
}; 