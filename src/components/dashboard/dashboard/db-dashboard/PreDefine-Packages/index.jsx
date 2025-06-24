import { useState } from "react";
import {
    Box,
    Tab,
    Tabs,
    Typography,
    Paper,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Button,
    Chip,
    IconButton,
    Tooltip,
    Badge,
    Card,
    Divider,
    Stack,
    TextField,
    InputAdornment
} from "@mui/material";
import {
    DonutLarge,
    Upcoming,
    History,
    Visibility,
    Edit,
    MoreVert,
    CalendarToday,
    EventAvailable,
    PeopleAlt,
    LocationOn,
    Person,
    CheckCircleOutline,
    CreditCard,
    AttachMoney,
    BookmarkBorder,
    Flight,
    DirectionsCar,
    Hotel,
    Search,
    FilterList,
    CloudDownload,
    Sort,
    Close,
    ArrowUpward,
    ArrowDownward,
    Attractions, 
    Restaurant,
    EmojiPeople
} from "@mui/icons-material";
import { TabPanel, a11yProps } from "./TabPanel";
import PackagesTable from "./PackagesTable";
import { sampleData } from "./utils";
import DateFilter from "./DateFilter";

// Get status chip based on status value
const StatusChip = ({ status }) => {
    let color = "default";
    let icon = <CheckCircleOutline fontSize="small" />;

    switch (status.toLowerCase()) {
        case "confirmed":
            color = "success";
            icon = <CheckCircleOutline fontSize="small" />;
            break;
        case "pending":
            color = "warning";
            icon = <DonutLarge fontSize="small" />;
            break;
        case "cancelled":
            color = "error";
            icon = <Close fontSize="small" />;
            break;
        case "completed":
            color = "info";
            icon = <EventAvailable fontSize="small" />;
            break;
        default:
            color = "default";
    }

    return (
        <Chip
            icon={icon}
            label={status}
            color={color}
            size="small"
            sx={{ fontWeight: 500, minWidth: '100px', '& .MuiChip-icon': { fontSize: '16px' } }}
        />
    );
};

// Get payment status chip
const PaymentStatusChip = ({ status }) => {
    let color = "default";
    let icon = <AttachMoney fontSize="small" />;

    switch (status.toLowerCase()) {
        case "paid":
            color = "success";
            icon = <AttachMoney fontSize="small" />;
            break;
        case "partial":
            color = "warning";
            icon = <CreditCard fontSize="small" />;
            break;
        case "unpaid":
            color = "error";
            icon = <CreditCard fontSize="small" />;
            break;
        case "refunded":
            color = "default";
            icon = <AttachMoney fontSize="small" />;
            break;
        default:
            color = "default";
    }

    return (
        <Chip
            icon={icon}
            label={status}
            color={color}
            size="small"
            variant="outlined"
            sx={{ fontWeight: 500, minWidth: '90px', '& .MuiChip-icon': { fontSize: '16px' } }}
        />
    );
};

// Sortable Column Header
const SortableColumnHeader = ({ label, icon, field, orderBy, order, onRequestSort }) => {
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
const TableHeader = ({ order, orderBy, onRequestSort }) => {
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

// Function to sort data
function getSorting(order, orderBy) {
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

const PreDefinePackages = () => {
    const [tabValue, setTabValue] = useState(0);
    const { ongoingData, upcomingData, pastData } = sampleData;
    const [searchTerm, setSearchTerm] = useState('');
    const [showSearchInput, setShowSearchInput] = useState(false);
    const [showDateFilter, setShowDateFilter] = useState(false);
    const [dateRange, setDateRange] = useState(null);

    // Filter data based on search term (destination)
    const filterData = (data) => {
        let filteredData = data;
        
        // Filter by destination name if search term exists
        if (searchTerm) {
            filteredData = filteredData.filter(item => 
                item.destination.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        
        // Filter by date range if date range exists
        if (dateRange && dateRange.length === 2) {
            filteredData = filteredData.filter(item => {
                // Parse dates from strings like "12 Jun 2023"
                const startDate = new Date(item.startDate);
                const endDate = new Date(item.endDate);
                
                // Convert DateObject to JavaScript Date for comparison
                const filterStartDate = dateRange[0].toDate();
                const filterEndDate = dateRange[1].toDate();
                
                // Check if package dates overlap with filter date range
                // A package is included if:
                // 1. Its start date falls within the filter range, or
                // 2. Its end date falls within the filter range, or
                // 3. It completely encompasses the filter range
                return (
                    (startDate >= filterStartDate && startDate <= filterEndDate) ||
                    (endDate >= filterStartDate && endDate <= filterEndDate) ||
                    (startDate <= filterStartDate && endDate >= filterEndDate)
                );
            });
        }
        
        return filteredData;
    };

    // Get filtered data for current tab
    const getCurrentFilteredData = () => {
        switch(tabValue) {
            case 0:
                return filterData(ongoingData);
            case 1:
                return filterData(upcomingData);
            case 2:
                return filterData(pastData);
            default:
                return [];
        }
    };

    const handleTabChange = (event, newValue) => {
        setTabValue(newValue);
        setSearchTerm('');
        setShowSearchInput(false);
        setShowDateFilter(false);
    };

    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value);
    };

    const toggleSearch = () => {
        setShowSearchInput(!showSearchInput);
        if (showSearchInput) {
            setSearchTerm('');
        }
    };
    
    const toggleDateFilter = () => {
        setShowDateFilter(!showDateFilter);
        if (showDateFilter) {
            setDateRange(null); // Reset date filter when closing
        }
    };
    
    const handleDateChange = (newDates) => {
        setDateRange(newDates);
    };

    return (
        <Box sx={{ width: "100%" }}>
            <Card sx={{ mb: 3, overflow: 'hidden' }}>
                <Tabs
                    value={tabValue}
                    onChange={handleTabChange}
                    aria-label="package tabs"
                    indicatorColor="primary"
                    textColor="primary"
                    variant="fullWidth"
                    sx={{
                        background: 'linear-gradient(to right, #f5f7fa, #f9fcff)',
                        '& .MuiTab-root': {
                            fontWeight: 600,
                            py: 2.5,
                            textTransform: 'none',
                            fontSize: '0.95rem',
                            minHeight: '64px',
                            transition: 'all 0.2s',
                            '&:hover': {
                                backgroundColor: 'rgba(67, 97, 238, 0.04)',
                            },
                            '&.Mui-selected': {
                                color: '#4361ee',
                                fontWeight: 700,
                            }
                        }
                    }}
                >
                    <Tab
                        icon={<DonutLarge />}
                        label={
                            <Box sx={{ position: 'relative', pr: 3 }}>
                                Ongoing
                                <Badge
                                    badgeContent={ongoingData.length}
                                    color="primary"
                                    sx={{
                                        position: 'absolute',
                                        top: 0,
                                        right: 14,
                                        '& .MuiBadge-badge': {
                                            fontSize: '10px',
                                            height: '20px',
                                            minWidth: '20px'
                                        }
                                    }}
                                />
                            </Box>
                        }
                        iconPosition="start"
                        {...a11yProps(0)}
                    />
                    <Tab
                        icon={<Upcoming />}
                        label={
                            <Box sx={{ position: 'relative', pr: 3 }}>
                                Upcoming
                                <Badge
                                    badgeContent={upcomingData.length}
                                    color="primary"
                                    sx={{
                                        position: 'absolute',
                                        top: 0,
                                        right: 14,
                                        '& .MuiBadge-badge': {
                                            fontSize: '10px',
                                            height: '20px',
                                            minWidth: '20px'
                                        }
                                    }}
                                />
                            </Box>
                        }
                        iconPosition="start"
                        {...a11yProps(1)}
                    />
                    <Tab
                        icon={<History />}
                        label={
                            <Box sx={{ position: 'relative', pr: 3 }}>
                                Past
                                <Badge
                                    badgeContent={pastData.length}
                                    color="primary"
                                    sx={{
                                        position: 'absolute',
                                        top: 0,
                                        right: 14,
                                        '& .MuiBadge-badge': {
                                            fontSize: '10px',
                                            height: '20px',
                                            minWidth: '20px'
                                        }
                                    }}
                                />
                            </Box>
                        }
                        iconPosition="start"
                        {...a11yProps(2)}
                    />
                </Tabs>
            </Card>

            <TabPanel value={tabValue} index={0}>
                <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                        <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#2c3e50', display: 'flex', alignItems: 'center' }}>
                            <DonutLarge sx={{ mr: 1, color: '#4361ee' }} /> Currently Ongoing Packages
                            <Chip label={`${getCurrentFilteredData().length} packages`} size="small" sx={{ ml: 2, backgroundColor: '#e3f2fd', color: '#1976d2' }} />
                        </Typography>
                        <Box sx={{ display: 'flex', gap: 1 }}>
                            {showSearchInput ? (
                                <TextField
                                    size="small"
                                    placeholder="Search by destination..."
                                    value={searchTerm}
                                    onChange={handleSearchChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Search fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ minWidth: '250px' }}
                                    autoFocus
                                />
                            ) : null}
                            <Button 
                                startIcon={<Search />} 
                                variant="outlined" 
                                size="small" 
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                            >
                                {showSearchInput ? "Close" : "Search"}
                            </Button>
                            <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button>
                            <DateFilter 
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {dateRange && dateRange.length === 2 && (
                        <Box sx={{ mb: 2 }}>
                            <Chip 
                                label={`Date Filter: ${dateRange[0].format("MMM DD, YYYY")} - ${dateRange[1].format("MMM DD, YYYY")}`}
                                onDelete={() => setDateRange(null)}
                                color="primary"
                                variant="outlined"
                                size="small"
                            />
                        </Box>
                    )}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transport" size="small" variant="outlined" />
                            <Chip icon={<Attractions />} label="Attractions" size="small" variant="outlined" />
                            <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" />
                            <Chip icon={<EmojiPeople />} label="Tour Guide" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    <PackagesTable data={getCurrentFilteredData()} emptyMessage="No ongoing packages at the moment" />
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={1}>
                <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                        <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#2c3e50', display: 'flex', alignItems: 'center' }}>
                            <Upcoming sx={{ mr: 1, color: '#4361ee' }} /> Upcoming Packages
                            <Chip label={`${getCurrentFilteredData().length} packages`} size="small" sx={{ ml: 2, backgroundColor: '#e8f5e9', color: '#2e7d32' }} />
                        </Typography>
                        <Box sx={{ display: 'flex', gap: 1 }}>
                            {showSearchInput ? (
                                <TextField
                                    size="small"
                                    placeholder="Search by destination..."
                                    value={searchTerm}
                                    onChange={handleSearchChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Search fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ minWidth: '250px' }}
                                    autoFocus
                                />
                            ) : null}
                            <Button 
                                startIcon={<Search />} 
                                variant="outlined" 
                                size="small" 
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                            >
                                {showSearchInput ? "Close" : "Search"}
                            </Button>
                            <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button>
                            <DateFilter 
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {dateRange && dateRange.length === 2 && (
                        <Box sx={{ mb: 2 }}>
                            <Chip 
                                label={`Date Filter: ${dateRange[0].format("MMM DD, YYYY")} - ${dateRange[1].format("MMM DD, YYYY")}`}
                                onDelete={() => setDateRange(null)}
                                color="primary"
                                variant="outlined"
                                size="small"
                            />
                        </Box>
                    )}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<Flight />} label="Flights" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transfers" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    <PackagesTable data={getCurrentFilteredData()} emptyMessage="No upcoming packages scheduled" />
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={2}>
                <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                        <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#2c3e50', display: 'flex', alignItems: 'center' }}>
                            <History sx={{ mr: 1, color: '#4361ee' }} /> Past Packages
                            <Chip label={`${getCurrentFilteredData().length} packages`} size="small" sx={{ ml: 2, backgroundColor: '#f5f5f5', color: '#616161' }} />
                        </Typography>
                        <Box sx={{ display: 'flex', gap: 1 }}>
                            {showSearchInput ? (
                                <TextField
                                    size="small"
                                    placeholder="Search by destination..."
                                    value={searchTerm}
                                    onChange={handleSearchChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Search fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ minWidth: '250px' }}
                                    autoFocus
                                />
                            ) : null}
                            <Button 
                                startIcon={<Search />} 
                                variant="outlined" 
                                size="small" 
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                            >
                                {showSearchInput ? "Close" : "Search"}
                            </Button>
                            <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button>
                            <DateFilter 
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {dateRange && dateRange.length === 2 && (
                        <Box sx={{ mb: 2 }}>
                            <Chip 
                                label={`Date Filter: ${dateRange[0].format("MMM DD, YYYY")} - ${dateRange[1].format("MMM DD, YYYY")}`}
                                onDelete={() => setDateRange(null)}
                                color="primary"
                                variant="outlined"
                                size="small"
                            />
                        </Box>
                    )}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<Flight />} label="Flights" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transfers" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    <PackagesTable data={getCurrentFilteredData()} emptyMessage="No past package history available" />
                </Card>
            </TabPanel>
        </Box>
    );
};

export default PreDefinePackages;
