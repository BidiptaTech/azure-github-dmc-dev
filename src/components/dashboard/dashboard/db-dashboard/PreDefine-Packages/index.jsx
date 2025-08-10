import { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchPackageBookingLists } from "../../../../../slice/tour-packages/prePackagesSlice";

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
    InputAdornment,
    CircularProgress
    
    
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
    EmojiPeople,
    
    DateRange,
    
    
    FilterAltOff,
    
    ClearAll
} from "@mui/icons-material";
import { TabPanel, a11yProps } from "./TabPanel";
import PackagesTable from "./PackagesTable";
import DateFilter from "./DateFilter";







const PreDefinePackages = () => {
    const dispatch = useDispatch();
    const {
        bookingLists,
        bookingListsLoading,
        bookingListsError
    } = useSelector((state) => state.prePackages);
    const { userRole } = useSelector((state) => state.auth);

    const [tabValue, setTabValue] = useState(0);

    // Organize booking list data by status
    const [processedData, setProcessedData] = useState({
        ongoing: [],
        upcoming: [],
        past: []
    });

    const [searchTerm, setSearchTerm] = useState('');
    const [showSearchInput, setShowSearchInput] = useState(false);
    const [showDateFilter, setShowDateFilter] = useState(false);
    const [dateRange, setDateRange] = useState(null);
    const [isDateFilterActive, setIsDateFilterActive] = useState(false);
    const [filterKey, setFilterKey] = useState(0); // Key to force DateFilter component to reset

    // Fetch package booking lists when component mounts
    useEffect(() => {
        // console.log("========== FETCHING PACKAGE BOOKING LISTS ==========");
        dispatch(fetchPackageBookingLists());
    }, [dispatch]);

    // Process the API data when it's received
    useEffect(() => {
        // console.log("========== API RESPONSE ==========");
        // console.log("Raw bookingLists:", bookingLists);
        // console.log("bookingListsLoading:", bookingListsLoading);
        // console.log("bookingListsError:", bookingListsError);

        if (bookingLists) {
            // console.log("bookingLists is an array:", Array.isArray(bookingLists));
            // console.log("bookingLists length:", Array.isArray(bookingLists) ? bookingLists.length : 'Not an array');

            // It's possible the data is nested in a property of the response
            if (!Array.isArray(bookingLists) && typeof bookingLists === 'object') {
                // console.log("bookingLists keys:", Object.keys(bookingLists));

                // Common response patterns to check
                const possibleArrayProps = ['data', 'results', 'items', 'booking_lists', 'bookings', 'packages'];
                for (const prop of possibleArrayProps) {
                    if (bookingLists[prop] && Array.isArray(bookingLists[prop])) {
                        // console.log(`Found array in bookingLists.${prop}:`, bookingLists[prop]);
                    }
                }
            }
        }

        // Only process if we have an array of bookings
        let bookingsArray = [];
        if (Array.isArray(bookingLists) && bookingLists.length > 0) {
            bookingsArray = bookingLists;
        } else if (bookingLists && typeof bookingLists === 'object') {
            // Try to find the array in the response object
            if (bookingLists.booking_lists && Array.isArray(bookingLists.booking_lists)) {
                bookingsArray = bookingLists.booking_lists;
                // console.log("Using booking_lists array from response:", bookingsArray);
            } else if (bookingLists.data && Array.isArray(bookingLists.data)) {
                bookingsArray = bookingLists.data;
                // console.log("Using data array from response:", bookingsArray);
            }
            // Add more potential paths if needed
        }

        // console.log("Final bookings array to process:", bookingsArray);

        if (bookingsArray && bookingsArray.length > 0) {
            try {
                // Process the booking lists into three categories
                const now = new Date();
                // console.log("Current date for comparison:", now);
                const ongoing = [];
                const upcoming = [];
                const past = [];

                // Format all bookings first, then categorize them
                bookingsArray.forEach((booking, index) => {
                    if (!booking) {
                        // console.log(`Booking at index ${index} is null or undefined, skipping`);
                        return; // Skip this iteration
                    }

                    try {
                        // console.log(`\n========== PROCESSING BOOKING ${index + 1} ==========`);

                        // Format the booking data
                        const formattedBooking = formatBookingData(booking);
                        // console.log("Formatted booking data:", formattedBooking);

                        // Parse dates for categorization
                        let startDate, endDate;

                        // Parse the start date from the formatted string (e.g., "Jun 12, 2023")
                        if (formattedBooking.startDate && formattedBooking.startDate !== 'Not specified') {
                            startDate = new Date(formattedBooking.startDate);
                            // console.log("Parsed start date from formatted string:", startDate);
                        }

                        // Parse the end date from the formatted string
                        if (formattedBooking.endDate && formattedBooking.endDate !== 'Not specified') {
                            endDate = new Date(formattedBooking.endDate);
                            // console.log("Parsed end date from formatted string:", endDate);
                        }

                        // Final fallback - use current date if dates are invalid
                        if (!startDate || isNaN(startDate.getTime())) {
                            // console.log("Start date invalid, using current date");
                            startDate = new Date(now);
                        }

                        if (!endDate || isNaN(endDate.getTime())) {
                            // console.log("End date invalid, using tomorrow");
                            endDate = new Date(now);
                            endDate.setDate(endDate.getDate() + 1); // Add one day
                        }

                        // console.log("Final dates for categorization:");
                        // console.log("- startDate:", startDate);
                        // console.log("- endDate:", endDate);

                        // Set time to beginning/end of day for accurate comparison
                        const today = new Date(now);
                        today.setHours(0, 0, 0, 0);

                        // Clone dates and set to beginning/end of day for accurate comparison
                        const startOfStartDate = new Date(startDate);
                        startOfStartDate.setHours(0, 0, 0, 0);

                        const endOfEndDate = new Date(endDate);
                        endOfEndDate.setHours(23, 59, 59, 999);

                        // console.log("Comparison dates:");
                        // console.log("- today:", today);
                        // console.log("- startOfStartDate:", startOfStartDate);
                        // console.log("- endOfEndDate:", endOfEndDate);

                        // Determine booking category based on dates
                        // Ongoing: today is between start_date and end_date (inclusive)
                        // Upcoming: start_date is after today
                        // Past: end_date is before today
                        if (today >= startOfStartDate && today <= endOfEndDate) {
                            // console.log("Category: ONGOING (today is between start and end dates)");
                            ongoing.push(formattedBooking);
                        } else if (startOfStartDate > today) {
                            // console.log("Category: UPCOMING (start date is after today)");
                            upcoming.push(formattedBooking);
                        } else {
                            // console.log("Category: PAST (end date is before today)");
                            past.push(formattedBooking);
                        }
                    } catch (error) {
                        console.error(`Error processing booking at index ${index}:`, error);
                        console.error("Problematic booking:", booking);
                        // We won't add the problematic booking to any category
                    }
                });

                // console.log("========== FINAL CATEGORIZED DATA ==========");
                // console.log("Ongoing bookings:", ongoing);
                // console.log("Upcoming bookings:", upcoming);
                // console.log("Past bookings:", past);
                // console.log("Category counts:", {
                // ongoing: ongoing.length,
                // upcoming: upcoming.length,
                // past: past.length
                // });

                setProcessedData({
                    ongoing,
                    upcoming,
                    past
                });
            } catch (error) {
                console.error("Fatal error processing booking data:", error);
                // Reset to empty arrays if processing fails
                setProcessedData({
                    ongoing: [],
                    upcoming: [],
                    past: []
                });
            }
        } else {
            console.log("No booking data available to process");
            // Reset to empty arrays if no booking lists data
            setProcessedData({
                ongoing: [],
                upcoming: [],
                past: []
            });
        }
    }, [bookingLists]);

    // Helper function to format date strings
    const formatDate = (dateString) => {
        if (!dateString) return 'Not specified';
        try {
            // console.log("Formatting date string:", dateString);
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                // console.log("Invalid date from string:", dateString);
                return 'Not specified';
            }

            const formatted = date.toLocaleDateString('en-US', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });

            // console.log("Formatted date result:", formatted);
            return formatted;
        } catch (error) {
            console.error("Error formatting date:", error, dateString);
            return 'Not specified';
        }
    };

    // Format booking data to match the expected structure
    const formatBookingData = (booking) => {
        // console.log("========== FORMATTING BOOKING DATA ==========");
        console.log("Original booking:", booking);
        console.log("Agent ID from booking:", booking.agent_id);
        // Parse the JSON strings in the API response
        let bookingDetails = {};
        let travelDates = {};
        let userInfo = {};
        let packageInfo = {};

        try {
            // Parse booking_details if it exists and is a string
            if (booking.booking_details) {
                // console.log("Processing booking_details:", booking.booking_details);
                if (typeof booking.booking_details === 'string') {
                    try {
                        bookingDetails = JSON.parse(booking.booking_details);
                        console.log("Parsed booking_details:", bookingDetails);
                        console.log("Agent ID from booking_details:", bookingDetails.agent_id);
                    } catch (e) {
                        console.error("Failed to parse booking_details:", e);
                    }
                } else if (typeof booking.booking_details === 'object') {
                    bookingDetails = booking.booking_details;
                    console.log("Using booking_details object directly:", bookingDetails);
                    console.log("Agent ID from booking_details object:", bookingDetails.agent_id);
                }
            }

            // Parse travel_dates if it exists and is a string
            if (booking.travel_dates) {
                // console.log("Processing travel_dates:", booking.travel_dates);
                if (typeof booking.travel_dates === 'string') {
                    try {
                        travelDates = JSON.parse(booking.travel_dates);
                        // console.log("Parsed travel_dates:", travelDates);
                    } catch (e) {
                        console.error("Failed to parse travel_dates:", e);
                    }
                } else if (typeof booking.travel_dates === 'object') {
                    travelDates = booking.travel_dates;
                    // console.log("Using travel_dates object directly:", travelDates);
                }
            }

            // Parse user_info if it exists and is a string
            if (booking.user_info) {
                // console.log("Processing user_info:", booking.user_info);
                if (typeof booking.user_info === 'string') {
                    try {
                        userInfo = JSON.parse(booking.user_info);
                        // console.log("Parsed user_info:", userInfo);
                    } catch (e) {
                        console.error("Failed to parse user_info:", e);
                    }
                } else if (typeof booking.user_info === 'object') {
                    userInfo = booking.user_info;
                    // console.log("Using user_info object directly:", userInfo);
                }
            }

            // Parse package info if it exists and is a string
            if (booking.package) {
                // console.log("Processing package:", booking.package);
                if (typeof booking.package === 'string') {
                    try {
                        packageInfo = JSON.parse(booking.package);
                        // console.log("Parsed package:", packageInfo);
                    } catch (e) {
                        console.error("Failed to parse package:", e);
                    }
                } else if (typeof booking.package === 'object') {
                    packageInfo = booking.package;
                    // console.log("Using package object directly:", packageInfo);
                }
            }
        } catch (error) {
            console.error("Error parsing JSON data:", error);
        }

        // Calculate pax from male_count + female_count as specified
        const maleFemaleCount =
            ((bookingDetails && bookingDetails.male_count) || 0) +
            ((bookingDetails && bookingDetails.female_count) || 0);

        // console.log("male_count:", bookingDetails && bookingDetails.male_count);
        // console.log("female_count:", bookingDetails && bookingDetails.female_count);
        // console.log("Calculated maleFemaleCount:", maleFemaleCount);

        // Use total pax from API or calculated value
        const totalPax = booking.total_pax || maleFemaleCount ||
            (bookingDetails && bookingDetails.adult_count && bookingDetails.child_count
                ? bookingDetails.adult_count + bookingDetails.child_count
                : 0);
        // console.log("Final totalPax:", totalPax);

        // Get check-in and check-out dates from travel_dates with safety checks
        let rawStartDate = null;
        let rawEndDate = null;

        // console.log("Starting date extraction priority search...");

        // PRIORITY 1: Direct travel_dates object on booking
        // console.log("Priority 1: Checking travel_dates object");
        if (travelDates && travelDates.check_in) {
            rawStartDate = travelDates.check_in;
            // console.log("Found start date in travelDates:", rawStartDate);
        }

        if (travelDates && travelDates.check_out) {
            rawEndDate = travelDates.check_out;
            // console.log("Found end date in travelDates:", rawEndDate);
        }

        // PRIORITY 2: Nested travel_dates in booking_details
        // console.log("Priority 2: Checking booking_details.travel_dates");
        if (!rawStartDate && bookingDetails && bookingDetails.travel_dates && bookingDetails.travel_dates.check_in) {
            rawStartDate = bookingDetails.travel_dates.check_in;
            // console.log("Found start date in bookingDetails.travel_dates:", rawStartDate);
        }

        if (!rawEndDate && bookingDetails && bookingDetails.travel_dates && bookingDetails.travel_dates.check_out) {
            rawEndDate = bookingDetails.travel_dates.check_out;
            // console.log("Found end date in bookingDetails.travel_dates:", rawEndDate);
        }

        // PRIORITY 3: Direct start_date/end_date fields on booking
        // console.log("Priority 3: Checking direct fields on booking");
        if (!rawStartDate) {
            if (booking.start_date) {
                rawStartDate = booking.start_date;
                // console.log("Found start_date on booking:", rawStartDate);
            } else if (booking.check_in) {
                rawStartDate = booking.check_in;
                // console.log("Found check_in on booking:", rawStartDate);
            }
        }

        if (!rawEndDate) {
            if (booking.end_date) {
                rawEndDate = booking.end_date;
                // console.log("Found end_date on booking:", rawEndDate);
            }
        }

        // PRIORITY 4: Package info dates
        // console.log("Priority 4: Checking package info");
        if (!rawStartDate && packageInfo) {
            if (packageInfo.start_date) {
                rawStartDate = packageInfo.start_date;
                // console.log("Found start_date in packageInfo:", rawStartDate);
            } else if (packageInfo.check_in) {
                rawStartDate = packageInfo.check_in;
                // console.log("Found check_in in packageInfo:", rawStartDate);
            } else if (packageInfo.date) {
                rawStartDate = packageInfo.date;
                // console.log("Found date in packageInfo:", rawStartDate);
            }
        }

        // PRIORITY 5: Check for date field with different names
        // console.log("Priority 5: Checking for alternate date field names");
        if (!rawStartDate) {
            const possibleDateFields = ['date', 'booking_date', 'created_at', 'updated_at'];
            for (const field of possibleDateFields) {
                if (booking[field]) {
                    rawStartDate = booking[field];
                    // console.log(`Found date in booking.${field}:`, rawStartDate);

                    if (!rawEndDate) {
                        // If we found a start date but no end date, set end date to start date + 1 day
                        const endDate = new Date(rawStartDate);
                        if (!isNaN(endDate.getTime())) {
                            endDate.setDate(endDate.getDate() + 1);
                            rawEndDate = endDate.toISOString();
                            // console.log("Auto-generated end date:", rawEndDate);
                        }
                    }
                    break;
                }
            }
        }

        // console.log("Final raw dates for formatting:", {
        //     startDate: rawStartDate,
        //     endDate: rawEndDate
        // });

        const formattedStartDate = formatDate(rawStartDate);
        const formattedEndDate = formatDate(rawEndDate);

        // console.log("Formatted dates:", {
        //     startDate: formattedStartDate,
        //     endDate: formattedEndDate
        // });

        // Get customer name from user_info if available
        const customerName =
            (userInfo && userInfo.fullName) ||
            booking.customer_name ||
            booking.name ||
            'Unknown customer';

        // Get destination from package if available
        const destination =
            (packageInfo && packageInfo.destination) ||
            booking.destination ||
            booking.location ||
            'Unknown destination';



        // Use default values for missing properties
        const result = {
            // Keep essential formatted display properties
            bookingId: booking.booking_id || booking.id || 'Unknown ID',
            startDate: formattedStartDate || 'Not specified',
            endDate: formattedEndDate || 'Not specified',
            pax: totalPax || 0,
            destination: destination,
            customerName: customerName,
            dmc_data: booking.dmc_data || null,
            agentId: (() => {
                // Priority order for agent ID extraction
                // 1. Direct booking fields
                if (booking.agent_id) return booking.agent_id;
                if (booking.agentId) return booking.agentId;
                if (booking.agent) return booking.agent;
                if (booking.assigned_agent_id) return booking.assigned_agent_id;
                if (booking.assignedAgentId) return booking.assignedAgentId;
                if (booking.created_by) return booking.created_by;
                if (booking.createdBy) return booking.createdBy;
                if (booking.user_id) return booking.user_id;
                if (booking.userId) return booking.userId;
                
                // 2. From parsed booking_details
                if (bookingDetails && bookingDetails.agent_id) return bookingDetails.agent_id;
                if (bookingDetails && bookingDetails.agentId) return bookingDetails.agentId;
                if (bookingDetails && bookingDetails.agent) return bookingDetails.agent;
                if (bookingDetails && bookingDetails.assigned_agent_id) return bookingDetails.assigned_agent_id;
                if (bookingDetails && bookingDetails.created_by) return bookingDetails.created_by;
                if (bookingDetails && bookingDetails.createdBy) return bookingDetails.createdBy;
                if (bookingDetails && bookingDetails.user_id) return bookingDetails.user_id;
                if (bookingDetails && bookingDetails.userId) return bookingDetails.userId;
                
                // 3. From parsed user_info
                if (userInfo && userInfo.agent_id) return userInfo.agent_id;
                if (userInfo && userInfo.user_id) return userInfo.user_id;
                
                // 4. From parsed package info
                if (packageInfo && packageInfo.agent_id) return packageInfo.agent_id;
                
                // 5. Default fallback
                return '0001';
            })(),
            status: (() => {
                // Handle numeric status codes
                if (booking.status) {
                    switch (Number(booking.status)) {
                        case 1: return 'Pending';
                        case 2: return 'Confirmed';
                        case 3: return 'On Hold';
                        case 4: return 'Cancelled';
                        default: return 'Unknown'; // Return Unknown for any other status code
                    }
                }
                return 'Pending'; // Default status
            })(),
            payment: (() => {
                // Priority order for payment extraction
                // 1. From parsed booking_details
                if (bookingDetails && bookingDetails.total_price) return bookingDetails.total_price;
                if (bookingDetails && bookingDetails.price) return bookingDetails.price;
                if (bookingDetails && bookingDetails.amount) return bookingDetails.amount;
                
                // 2. Direct booking fields
                if (booking.payment_amount) return booking.payment_amount;
                if (booking.total_payment) return booking.total_payment;
                if (booking.price) return booking.price;
                if (booking.amount) return booking.amount;
                
                // 3. From parsed package info
                if (packageInfo && packageInfo.price) return packageInfo.price;
                if (packageInfo && packageInfo.amount) return packageInfo.amount;
                
                // 4. Default fallback
                return '0';
            })(),
            paymentStatus: (() => {
                // Priority order for payment status extraction
                if (booking.payment_status) return booking.payment_status;
                if (bookingDetails && bookingDetails.payment_status) return bookingDetails.payment_status;
                if (bookingDetails && bookingDetails.status) return bookingDetails.status;
                return 'Unpaid'; // Default payment status
            })(),

            // Preserve original data for modal
            booking_id: booking.booking_id || booking.id,
            booking_details: booking.booking_details,
            travel_dates: booking.travel_dates,
            package: booking.package,
            user_info: booking.user_info,

            // Include other important fields from API
            hotels: booking.hotels || [],
            attractions: booking.attractions || [],
            guides: booking.guides || [],
            restaurants: booking.restaurants || []
        };

        console.log("Final formatted booking:", result);
        
        // Log all possible agent ID fields for debugging
        console.log("Agent ID fields found:", {
            "booking.agent_id": booking.agent_id,
            "booking.agentId": booking.agentId,
            "booking.agent": booking.agent,
            "booking.assigned_agent_id": booking.assigned_agent_id,
            "booking.assignedAgentId": booking.assignedAgentId,
            "booking.created_by": booking.created_by,
            "booking.createdBy": booking.createdBy,
            "booking.user_id": booking.user_id,
            "booking.userId": booking.userId,
            "bookingDetails.agent_id": bookingDetails && bookingDetails.agent_id,
            "bookingDetails.agentId": bookingDetails && bookingDetails.agentId,
            "bookingDetails.agent": bookingDetails && bookingDetails.agent,
            "bookingDetails.assigned_agent_id": bookingDetails && bookingDetails.assigned_agent_id,
            "bookingDetails.created_by": bookingDetails && bookingDetails.created_by,
            "bookingDetails.createdBy": bookingDetails && bookingDetails.createdBy,
            "bookingDetails.user_id": bookingDetails && bookingDetails.user_id,
            "bookingDetails.userId": bookingDetails && bookingDetails.userId,
            "userInfo.agent_id": userInfo && userInfo.agent_id,
            "userInfo.user_id": userInfo && userInfo.user_id,
            "packageInfo.agent_id": packageInfo && packageInfo.agent_id,
            "Final agentId used": result.agentId
        });
        
        // Log payment and status fields for debugging
        console.log("Payment and Status fields found:", {
            "booking.payment_amount": booking.payment_amount,
            "booking.total_payment": booking.total_payment,
            "booking.price": booking.price,
            "booking.amount": booking.amount,
            "booking.status": booking.status,
            "booking.payment_status": booking.payment_status,
            "bookingDetails.total_price": bookingDetails && bookingDetails.total_price,
            "bookingDetails.price": bookingDetails && bookingDetails.price,
            "bookingDetails.amount": bookingDetails && bookingDetails.amount,
            "bookingDetails.payment_status": bookingDetails && bookingDetails.payment_status,
            "bookingDetails.status": bookingDetails && bookingDetails.status,
            "packageInfo.price": packageInfo && packageInfo.price,
            "packageInfo.amount": packageInfo && packageInfo.amount,
            "Final payment used": result.payment,
            "Final status used": result.status,
            "Final paymentStatus used": result.paymentStatus
        });
        
        return result;
    };

    // Check if date range is valid and has both start and end dates
    const hasValidDateRange = () => {
        return dateRange && Array.isArray(dateRange) && dateRange.length === 2 &&
            dateRange[0] && dateRange[1];
    };

    // Update the date filter active state whenever dateRange changes
    useEffect(() => {
        setIsDateFilterActive(hasValidDateRange());
    }, [dateRange]);

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
        if (hasValidDateRange()) {
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

    // Get filtered data for current tab, using only API data
    const getCurrentFilteredData = () => {
        switch (tabValue) {
            case 0:
                return filterData(processedData.ongoing);
            case 1:
                return filterData(processedData.upcoming);
            case 2:
                return filterData(processedData.past);
            default:
                return [];
        }
    };

    const handleTabChange = (event, newValue) => {
        setTabValue(newValue);
        setSearchTerm('');
        setShowSearchInput(false);
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
    };

    const handleDateChange = (newDates) => {
        setDateRange(newDates);
    };

    // Memoized clear function to avoid recreation on every render
    const clearDateFilter = useCallback(() => {
        // Reset all date filter related state
        setDateRange(null);
        setShowDateFilter(false);
        setIsDateFilterActive(false);

        // Force DateFilter component to reset by changing its key
        setFilterKey(prevKey => prevKey + 1);
    }, []);

    // Render the active filters section with clear button
    const renderActiveFilters = () => {
        if (isDateFilterActive && hasValidDateRange()) {
            return (
                <Box sx={{
                    mb: 2,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    backgroundColor: '#f8f9fa',
                    borderRadius: '8px',
                    padding: '10px 15px'
                }}>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <DateRange sx={{ color: '#4361ee', mr: 1 }} />
                        <Typography variant="body2" sx={{ fontWeight: 500, color: '#2c3e50' }}>
                            Active Date Filter:
                        </Typography>
                        <Chip
                            label={`${dateRange[0].format("MMM DD, YYYY")} - ${dateRange[1].format("MMM DD, YYYY")}`}
                            onDelete={clearDateFilter}
                            color="primary"
                            variant="outlined"
                            size="small"
                            sx={{ ml: 1 }}
                        />
                    </Box>
                    <Button
                        variant="contained"
                        color="error"
                        size="small"
                        startIcon={<FilterAltOff />}
                        onClick={clearDateFilter}
                        sx={{
                            textTransform: 'none',
                            boxShadow: '0 2px 8px rgba(239, 68, 68, 0.2)',
                            '&:hover': {
                                boxShadow: '0 4px 12px rgba(239, 68, 68, 0.3)',
                                backgroundColor: '#e53e3e'
                            }
                        }}
                    >
                        Clear Date Filter
                    </Button>
                </Box>
            );
        }
        return null;
    };

    // Render loading state or error message
    const renderContent = (filteredData, emptyMessage) => {
        if (bookingListsLoading) {
            return (
                <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 6 }}>
                    <CircularProgress size={40} thickness={4} />
                </Box>
            );
        }

        if (bookingListsError) {
            return (
                <Box sx={{
                    textAlign: 'center',
                    py: 6,
                    px: 2,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    backgroundColor: '#fff5f5',
                    borderRadius: 2
                }}>
                    <Box sx={{ color: 'error.main', mb: 2 }}>
                        <Close fontSize="large" sx={{ fontSize: 60 }} />
                    </Box>
                    <Typography variant="h6" color="error.main" gutterBottom>
                        Error Loading Data
                    </Typography>
                    <Typography color="text.secondary">
                        {bookingListsError}
                    </Typography>
                </Box>
            );
        }

        if (filteredData.length === 0) {
            // Determine which icon to show based on the empty message
            let EmptyIcon = DonutLarge; // Default icon
            let backgroundColor = '#f0f7ff'; // Default background color
            let borderColor = '#cce3ff'; // Default border color
            let iconColor = '#4361ee'; // Default icon color

            if (emptyMessage.includes("ongoing")) {
                EmptyIcon = DonutLarge;
                backgroundColor = '#f0f7ff';
                borderColor = '#cce3ff';
                iconColor = '#4361ee';
            } else if (emptyMessage.includes("upcoming")) {
                EmptyIcon = Upcoming;
                backgroundColor = '#f0f9f0';
                borderColor = '#c6e7c6';
                iconColor = '#2e7d32';
            } else if (emptyMessage.includes("past")) {
                EmptyIcon = History;
                backgroundColor = '#f5f5f5';
                borderColor = '#e0e0e0';
                iconColor = '#757575';
            }

            return (
                <Paper
                    elevation={0}
                    sx={{
                        p: 6,
                        textAlign: 'center',
                        borderRadius: 2,
                        backgroundColor: backgroundColor,
                        border: `1px dashed ${borderColor}`,
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        minHeight: 250
                    }}
                >
                    <Box
                        sx={{
                            mb: 3,
                            p: 2,
                            borderRadius: '50%',
                            backgroundColor: `${iconColor}20`, // 20% opacity
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        }}
                    >
                        <EmptyIcon sx={{ fontSize: 60, color: iconColor }} />
                    </Box>
                    <Typography variant="h6" gutterBottom sx={{ fontWeight: 600, color: '#424242' }}>
                        {emptyMessage}
                    </Typography>
                    <Typography variant="body2" color="text.secondary" sx={{ maxWidth: 400, mx: 'auto', mt: 1 }}>
                        {emptyMessage.includes("ongoing") ? (
                            "No packages are currently active. Check back later or view upcoming packages."
                        ) : emptyMessage.includes("upcoming") ? (
                            "No future packages are scheduled at this time. Check the ongoing tab for current packages."
                        ) : (
                            "No historical package data is available. Past completed packages will appear here."
                        )}
                    </Typography>
                    <Button
                        variant="outlined"
                        startIcon={<FilterAltOff />}
                        size="small"
                        sx={{ mt: 3 }}
                        onClick={clearDateFilter}
                        disabled={!isDateFilterActive}
                    >
                        {isDateFilterActive ? "Clear Filter" : "No Active Filters"}
                    </Button>
                </Paper>
            );
        }

        return <PackagesTable data={filteredData} emptyMessage={emptyMessage} userRole={userRole} />;
    };

    return (
        <Box sx={{ width: "100%" }}>
            <Card sx={{ 
                mb: 3, 
                overflow: 'visible',  // Allow content to overflow for horizontal scrolling
                '@media (min-width: 1200px)': {
                    overflow: 'visible'  // Ensure large screens allow overflow
                }
            }}>
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
                                    badgeContent={processedData.ongoing.length}
                                    
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
                                    badgeContent={processedData.upcoming.length}
                                
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
                                    badgeContent={processedData.past.length}
                                    
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
                <Card sx={{ 
                    mb: 3, 
                    p: 3, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
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
                                key={`date-filter-${filterKey}`}
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                            {isDateFilterActive && (
                                <Tooltip title="Clear date filter">
                                    <Button
                                        startIcon={<ClearAll />}
                                        variant="outlined"
                                        size="small"
                                        color="error"
                                        onClick={clearDateFilter}
                                    >
                                        Clear
                                    </Button>
                                </Tooltip>
                            )}
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {renderActiveFilters()}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transport" size="small" variant="outlined" />
                            <Chip icon={<Attractions />} label="Attractions" size="small" variant="outlined" />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip icon={<EmojiPeople />} label="Tour Guide" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No ongoing packages at the moment")}
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={1}>
                <Card sx={{ 
                    mb: 3, 
                    p: 3, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
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
                                key={`date-filter-${filterKey}`}
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                            {isDateFilterActive && (
                                <Tooltip title="Clear date filter">
                                    <Button
                                        startIcon={<ClearAll />}
                                        variant="outlined"
                                        size="small"
                                        color="error"
                                        onClick={clearDateFilter}
                                    >
                                        Clear
                                    </Button>
                                </Tooltip>
                            )}
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {renderActiveFilters()}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transport" size="small" variant="outlined" />
                            <Chip icon={<Attractions />} label="Attractions" size="small" variant="outlined" />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip icon={<EmojiPeople />} label="Tour Guide" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No upcoming packages scheduled")}
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={2}>
                <Card sx={{ 
                    mb: 3, 
                    p: 3, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
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
                                key={`date-filter-${filterKey}`}
                                onDateChange={handleDateChange}
                                isOpen={showDateFilter}
                                onToggle={toggleDateFilter}
                            />
                            {isDateFilterActive && (
                                <Tooltip title="Clear date filter">
                                    <Button
                                        startIcon={<ClearAll />}
                                        variant="outlined"
                                        size="small"
                                        color="error"
                                        onClick={clearDateFilter}
                                    >
                                        Clear
                                    </Button>
                                </Tooltip>
                            )}
                        </Box>
                    </Box>

                    <Divider sx={{ mb: 2 }} />

                    {renderActiveFilters()}

                    <Box sx={{ mb: 2 }}>
                        <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                            <Chip icon={<Hotel />} label="Hotels" size="small" variant="outlined" />
                            <Chip icon={<DirectionsCar />} label="Transport" size="small" variant="outlined" />
                            <Chip icon={<Attractions />} label="Attractions" size="small" variant="outlined" />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip icon={<EmojiPeople />} label="Tour Guide" size="small" variant="outlined" />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No past package history available")}
                </Card>
            </TabPanel>
        </Box>
    );
};

export default PreDefinePackages;
