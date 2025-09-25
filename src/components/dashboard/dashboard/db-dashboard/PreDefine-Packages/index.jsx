import { useState, useEffect, useCallback, useRef } from "react";
import { useDispatch, useSelector } from "react-redux";
import { 
    fetchPackageBookingLists, 
    setBookingListsStatus, 
    setBookingListsPagination,
    resetBookingListsPagination 
} from "../../../../../slice/tour-packages/prePackagesSlice";

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
    Badge as BadgeIcon,
    ConfirmationNumber,
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
        bookingListsError,
        bookingListsPagination,
        bookingListsStatus
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
    const [dmcFilterTerm, setDmcFilterTerm] = useState('');
    const [showDmcFilter, setShowDmcFilter] = useState(false);
    const [bookingIdFilterTerm, setBookingIdFilterTerm] = useState('');
    const [showBookingIdFilter, setShowBookingIdFilter] = useState(false);
    const [showDateFilter, setShowDateFilter] = useState(false);
    const [dateRange, setDateRange] = useState(null);
    const [isDateFilterActive, setIsDateFilterActive] = useState(false);
    const [filterKey, setFilterKey] = useState(0); // Key to force DateFilter component to reset
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const loadMoreRef = useRef(null);

    // Map tab values to status
    const getStatusFromTab = (tabValue) => {
        switch (tabValue) {
            case 0: return 'ongoing';
            case 1: return 'upcoming';
            case 2: return 'past';
            default: return 'all';
        }
    };

    // Fetch package booking lists when component mounts or status changes
    useEffect(() => {
        const status = getStatusFromTab(tabValue);
        // console.log("========== FETCHING PACKAGE BOOKING LISTS ==========");
        // console.log("Status:", status);
        
        // Reset pagination and fetch first page
        dispatch(resetBookingListsPagination());
        dispatch(setBookingListsStatus(status));
        dispatch(fetchPackageBookingLists({ 
            start: 0, 
            limit: 10, // Fetch first 10 items
            status: status 
        }));
    }, [dispatch, tabValue]);

    // Process the API data when it's received - now simplified since server handles categorization
    useEffect(() => {
        // console.log("========== API RESPONSE ==========");
        // console.log("Raw bookingLists:", bookingLists);
        // console.log("bookingListsLoading:", bookingListsLoading);
        // console.log("bookingListsError:", bookingListsError);
        // console.log("Current status:", bookingListsStatus);
        // console.log("Pagination:", bookingListsPagination);
        // console.log("hasMore:", bookingListsPagination.hasMore);
        // console.log("Current data length:", bookingLists?.length || 0);

        if (bookingLists && Array.isArray(bookingLists)) {
            // Format the booking data for display
            const formattedBookings = bookingLists.map(booking => {
                try {
                    return formatBookingData(booking);
                } catch (error) {
                    console.error("Error formatting booking:", error);
                    return null;
                }
            }).filter(booking => booking !== null);

            // Set the processed data based on current status
            const status = getStatusFromTab(tabValue);
            setProcessedData({
                ongoing: status === 'ongoing' ? formattedBookings : [],
                upcoming: status === 'upcoming' ? formattedBookings : [],
                past: status === 'past' ? formattedBookings : []
            });
        } else {
            // Reset to empty arrays if no booking lists data
            setProcessedData({
                ongoing: [],
                upcoming: [],
                past: []
            });
        }
    }, [bookingLists, bookingListsStatus, tabValue]);

    // Load more data function
    const loadMoreData = useCallback(() => {
        // console.log("loadMoreData called with:", {
        //     isLoadingMore,
        //     hasMore: bookingListsPagination.hasMore,
        //     bookingListsLoading,
        //     currentStart: bookingListsPagination.start,
        //     currentLimit: bookingListsPagination.limit
        // });
        
        if (isLoadingMore || !bookingListsPagination.hasMore || bookingListsLoading) {
            // console.log("loadMoreData blocked:", { isLoadingMore, hasMore: bookingListsPagination.hasMore, bookingListsLoading });
            return;
        }
        
        setIsLoadingMore(true);
        const status = getStatusFromTab(tabValue);
        const nextStart = bookingListsPagination.start + bookingListsPagination.limit;
        
        // console.log("Making API call for next page:", { nextStart, limit: bookingListsPagination.limit, status });
        
        dispatch(setBookingListsPagination({ start: nextStart }));
        dispatch(fetchPackageBookingLists({ 
            start: nextStart, 
            limit: bookingListsPagination.limit, 
            status: status 
        })).finally(() => {
            setIsLoadingMore(false);
        });
    }, [bookingListsPagination, isLoadingMore, bookingListsLoading, dispatch, tabValue]);

    // Intersection Observer for infinite scroll
    useEffect(() => {
        // console.log("Setting up intersection observer with:", {
        //     hasMore: bookingListsPagination.hasMore,
        //     isLoadingMore,
        //     bookingListsLoading,
        //     loadMoreRef: !!loadMoreRef.current
        // });
        
        const observer = new IntersectionObserver(
            (entries) => {
                const [entry] = entries;
                // console.log("Intersection observer triggered:", {
                //     isIntersecting: entry.isIntersecting,
                //     hasMore: bookingListsPagination.hasMore,
                //     isLoadingMore,
                //     bookingListsLoading
                // });
                
                if (entry.isIntersecting && bookingListsPagination.hasMore && !isLoadingMore && !bookingListsLoading) {
                    // console.log("Calling loadMoreData from intersection observer");
                    loadMoreData();
                }
            },
            { threshold: 0.1, rootMargin: '100px' } // Add rootMargin to trigger earlier
        );

        // Use setTimeout to ensure the ref is available
        const timeoutId = setTimeout(() => {
            if (loadMoreRef.current) {
                observer.observe(loadMoreRef.current);
                // console.log("Observer attached to loadMoreRef");
            } else {
                 console.log("loadMoreRef.current is still null after timeout");
            }
        }, 100);

        return () => {
            clearTimeout(timeoutId);
            if (loadMoreRef.current) {
                observer.unobserve(loadMoreRef.current);
            }
        };
    }, [loadMoreRef, bookingListsPagination.hasMore, isLoadingMore, bookingListsLoading, loadMoreData]);


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

    // Helper function to format date and time strings
    const formatDateTime = (dateString) => {
        if (!dateString) return 'Not specified';
        try {
            // console.log("Formatting date time string:", dateString);
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

            const time = date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            const result = `${formatted} ${time}`;
            // console.log("Formatted date time result:", result);
            return result;
        } catch (error) {
            console.error("Error formatting date time:", error, dateString);
            return 'Not specified';
        }
    };

    // Format booking data to match the expected structure
    const formatBookingData = (booking) => {
        // console.log("========== FORMATTING BOOKING DATA ==========");
        // console.log("Original booking:", booking);
        // console.log("Agent ID from booking:", booking.agent_id);
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
                        // console.log("Parsed booking_details:", bookingDetails);
                        // console.log("Agent ID from booking_details:", bookingDetails.agent_id);
                    } catch (e) {
                        console.error("Failed to parse booking_details:", e);
                    }
                } else if (typeof booking.booking_details === 'object') {
                    bookingDetails = booking.booking_details;
                    // console.log("Using booking_details object directly:", bookingDetails);
                    // console.log("Agent ID from booking_details object:", bookingDetails.agent_id);
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
            status: booking.status || 0, // Pass raw numeric status to StatusChip component
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
            createdAt: (() => {
                // Priority order for created_at extraction
                if (booking.created_at) return formatDateTime(booking.created_at);
                if (booking.createdAt) return formatDateTime(booking.createdAt);
                if (booking.created_date) return formatDateTime(booking.created_date);
                if (booking.createdDate) return formatDateTime(booking.createdDate);
                if (booking.date_created) return formatDateTime(booking.date_created);
                if (booking.dateCreated) return formatDateTime(booking.dateCreated);
                if (booking.booking_date) return formatDateTime(booking.booking_date);
                if (booking.bookingDate) return formatDateTime(booking.bookingDate);
                if (booking.timestamp) return formatDateTime(booking.timestamp);
                if (booking.updated_at) return formatDateTime(booking.updated_at);
                if (booking.updatedAt) return formatDateTime(booking.updatedAt);
                return 'Not specified'; // Default fallback
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

        // Filter by DMC ID if DMC filter term exists
        if (dmcFilterTerm) {
            filteredData = filteredData.filter(item => {
                // Check if item has dmc_data and dmc_company_name
                if (item.dmc_data && item.dmc_data.dmc_company_name) {
                    return item.dmc_data.dmc_company_name.toLowerCase().includes(dmcFilterTerm.toLowerCase());
                }
                // Also check if there's a direct dmc_id field
                if (item.dmc_id) {
                    return item.dmc_id.toString().toLowerCase().includes(dmcFilterTerm.toLowerCase());
                }
                return false;
            });
        }

        // Filter by Booking ID if Booking ID filter term exists
        if (bookingIdFilterTerm) {
            filteredData = filteredData.filter(item => {
                // Check bookingId field
                if (item.bookingId) {
                    return item.bookingId.toString().toLowerCase().includes(bookingIdFilterTerm.toLowerCase());
                }
                // Also check booking_id field
                if (item.booking_id) {
                    return item.booking_id.toString().toLowerCase().includes(bookingIdFilterTerm.toLowerCase());
                }
                // Check id field as fallback
                if (item.id) {
                    return item.id.toString().toLowerCase().includes(bookingIdFilterTerm.toLowerCase());
                }
                return false;
            });
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
        setDmcFilterTerm('');
        setShowDmcFilter(false);
        setBookingIdFilterTerm('');
        setShowBookingIdFilter(false);
        // Clear date filter when changing tabs
        setDateRange(null);
        setIsDateFilterActive(false);
    };

    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value);
    };

    const handleDmcFilterChange = (event) => {
        setDmcFilterTerm(event.target.value);
    };

    const handleBookingIdFilterChange = (event) => {
        setBookingIdFilterTerm(event.target.value);
    };

    const toggleSearch = () => {
        setShowSearchInput(!showSearchInput);
        if (showSearchInput) {
            setSearchTerm('');
        }
    };

    const toggleDmcFilter = () => {
        setShowDmcFilter(!showDmcFilter);
        if (showDmcFilter) {
            setDmcFilterTerm('');
        }
    };

    const toggleBookingIdFilter = () => {
        setShowBookingIdFilter(!showBookingIdFilter);
        if (showBookingIdFilter) {
            setBookingIdFilterTerm('');
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
                    flexDirection: { xs: 'column', sm: 'row' },
                    alignItems: { xs: 'stretch', sm: 'center' },
                    justifyContent: 'space-between',
                    backgroundColor: '#f8f9fa',
                    borderRadius: '8px',
                    padding: { xs: '12px', sm: '10px 15px' },
                    gap: { xs: 2, sm: 0 }
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

        return (
            <Box>
                <PackagesTable data={filteredData} emptyMessage={emptyMessage} userRole={userRole} />
                
                {/* Infinite Scroll Loading Indicator - Always render the element */}
                <Box 
                    ref={loadMoreRef}
                    sx={{ 
                        display: 'flex', 
                        justifyContent: 'center', 
                        alignItems: 'center',
                        mt: { xs: 2, sm: 3 }, 
                        mb: { xs: 1, sm: 2 },
                        py: { xs: 1.5, sm: 2 },
                        px: { xs: 2, sm: 0 },
                        minHeight: { xs: '50px', sm: '60px' } // Ensure it has some height for intersection observer
                    }}
                >
                    {bookingListsPagination.hasMore && filteredData.length > 0 ? (
                        isLoadingMore ? (
                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                                <CircularProgress size={20} />
                                <Typography variant="body2" color="text.secondary">
                                    Loading more packages...
                                </Typography>
                            </Box>
                        ) : (
                            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
                                <Typography variant="body2" color="text.secondary">
                                    Scroll down to load more
                                </Typography>
                                <Button 
                                    variant="outlined" 
                                    size="small"
                                    onClick={loadMoreData}
                                    sx={{ mt: 1 }}
                                >
                                    Test Load More
                                </Button>
                            </Box>
                        )
                    ) : !bookingListsPagination.hasMore && filteredData.length > 0 ? (
                        <Typography variant="body2" sx={{ fontSize: '14px', color: 'text.secondary' }}>
                            ✓ End of results • {filteredData.length} packages loaded
                        </Typography>
                    ) : null}
                </Box>
            </Box>
        );
    };

    return (
        <Box sx={{ 
            width: "100%",
            px: { xs: 1, sm: 2, md: 0 },
            mx: { xs: 0, sm: 0, md: 0 }
        }}>
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
                            py: { xs: 1.5, sm: 2, md: 2.5 },
                            textTransform: 'none',
                            fontSize: { xs: '0.8rem', sm: '0.85rem', md: '0.95rem' },
                            minHeight: { xs: '48px', sm: '56px', md: '64px' },
                            transition: 'all 0.2s',
                            '&:hover': {
                                backgroundColor: 'rgba(67, 97, 238, 0.04)',
                            },
                            '&.Mui-selected': {
                                color: '#4361ee',
                                fontWeight: 700,
                            },
                            // Responsive icon and text layout
                            '& .MuiTab-iconWrapper': {
                                marginBottom: { xs: 0, sm: 0 },
                                marginRight: { xs: 0.5, sm: 1 },
                            },
                            // Stack icon and text vertically on mobile
                            flexDirection: { xs: 'column', sm: 'row' },
                            gap: { xs: 0.5, sm: 0 },
                        }
                    }}
                >
                    <Tab
                        icon={<DonutLarge />}
                        label="Ongoing"
                        iconPosition="start"
                        {...a11yProps(0)}
                    />
                    <Tab
                        icon={<Upcoming />}
                        label="Upcoming"
                        iconPosition="start"
                        {...a11yProps(1)}
                    />
                    <Tab
                        icon={<History />}
                        label="Past"
                        iconPosition="start"
                        {...a11yProps(2)}
                    />
                </Tabs>
            </Card>

            <TabPanel value={tabValue} index={0}>
                <Card sx={{ 
                    mb: 3, 
                    p: { xs: 2, sm: 2.5, md: 3 }, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
                    <Box sx={{ 
                        display: 'flex', 
                        flexDirection: { xs: 'column', sm: 'row' },
                        justifyContent: 'space-between', 
                        alignItems: { xs: 'stretch', sm: 'center' }, 
                        mb: 2,
                        gap: { xs: 2, sm: 0 }
                    }}>
                        <Typography variant="h6" sx={{ 
                            fontWeight: 'bold', 
                            color: '#2c3e50', 
                            display: 'flex', 
                            alignItems: 'center',
                            fontSize: { xs: '1.1rem', sm: '1.25rem' },
                            mb: { xs: 1, sm: 0 }
                        }}>
                            <DonutLarge sx={{ mr: 1, color: '#4361ee', fontSize: { xs: '1.2rem', sm: '1.5rem' } }} /> 
                            <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                Currently Ongoing Packages
                            </Box>
                            <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                Ongoing
                            </Box>
                        </Typography>
                        <Box sx={{ 
                            display: 'flex', 
                            gap: { xs: 0.5, sm: 1 },
                            flexWrap: 'wrap',
                            justifyContent: { xs: 'center', sm: 'flex-end' }
                        }}>
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
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showDmcFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by DMC company name..."
                                    value={dmcFilterTerm}
                                    onChange={handleDmcFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Badge fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showBookingIdFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by Booking ID..."
                                    value={bookingIdFilterTerm}
                                    onChange={handleBookingIdFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <ConfirmationNumber fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            <Button
                                startIcon={<Search />}
                                variant="outlined"
                                size="small"
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showSearchInput ? "Close" : "Search"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showSearchInput ? "✕" : "🔍"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<Badge />}
                                variant="outlined"
                                size="small"
                                onClick={toggleDmcFilter}
                                color={showDmcFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showDmcFilter ? "Close" : "DMC Filter"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showDmcFilter ? "✕" : "🏢"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<ConfirmationNumber />}
                                variant="outlined"
                                size="small"
                                onClick={toggleBookingIdFilter}
                                color={showBookingIdFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showBookingIdFilter ? "Close" : "Booking ID"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showBookingIdFilter ? "✕" : "🎫"}
                                </Box>
                            </Button>
                            {/* <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button> */}
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
                        <Stack 
                            direction="row" 
                            spacing={{ xs: 1, sm: 2 }} 
                            sx={{ 
                                mb: 3,
                                flexWrap: 'wrap',
                                gap: { xs: 1, sm: 2 }
                            }}
                        >
                            <Chip 
                                icon={<Hotel />} 
                                label="Hotels" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<DirectionsCar />} 
                                label="Transport" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<Attractions />} 
                                label="Attractions" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip 
                                icon={<EmojiPeople />} 
                                label="Tour Guide" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No ongoing packages at the moment")}
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={1}>
                <Card sx={{ 
                    mb: 3, 
                    p: { xs: 2, sm: 2.5, md: 3 }, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
                    <Box sx={{ 
                        display: 'flex', 
                        flexDirection: { xs: 'column', sm: 'row' },
                        justifyContent: 'space-between', 
                        alignItems: { xs: 'stretch', sm: 'center' }, 
                        mb: 2,
                        gap: { xs: 2, sm: 0 }
                    }}>
                        <Typography variant="h6" sx={{ 
                            fontWeight: 'bold', 
                            color: '#2c3e50', 
                            display: 'flex', 
                            alignItems: 'center',
                            fontSize: { xs: '1.1rem', sm: '1.25rem' },
                            mb: { xs: 1, sm: 0 }
                        }}>
                            <Upcoming sx={{ mr: 1, color: '#4361ee', fontSize: { xs: '1.2rem', sm: '1.5rem' } }} /> 
                            <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                Upcoming Packages
                            </Box>
                            <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                Upcoming
                            </Box>
                        </Typography>
                        <Box sx={{ 
                            display: 'flex', 
                            gap: { xs: 0.5, sm: 1 },
                            flexWrap: 'wrap',
                            justifyContent: { xs: 'center', sm: 'flex-end' }
                        }}>
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
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showDmcFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by DMC company name..."
                                    value={dmcFilterTerm}
                                    onChange={handleDmcFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Badge fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showBookingIdFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by Booking ID..."
                                    value={bookingIdFilterTerm}
                                    onChange={handleBookingIdFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <ConfirmationNumber fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            <Button
                                startIcon={<Search />}
                                variant="outlined"
                                size="small"
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showSearchInput ? "Close" : "Search"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showSearchInput ? "✕" : "🔍"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<Badge />}
                                variant="outlined"
                                size="small"
                                onClick={toggleDmcFilter}
                                color={showDmcFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showDmcFilter ? "Close" : "DMC Filter"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showDmcFilter ? "✕" : "🏢"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<ConfirmationNumber />}
                                variant="outlined"
                                size="small"
                                onClick={toggleBookingIdFilter}
                                color={showBookingIdFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showBookingIdFilter ? "Close" : "Booking ID"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showBookingIdFilter ? "✕" : "🎫"}
                                </Box>
                            </Button>
                            {/* <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button> */}
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
                        <Stack 
                            direction="row" 
                            spacing={{ xs: 1, sm: 2 }} 
                            sx={{ 
                                mb: 3,
                                flexWrap: 'wrap',
                                gap: { xs: 1, sm: 2 }
                            }}
                        >
                            <Chip 
                                icon={<Hotel />} 
                                label="Hotels" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<DirectionsCar />} 
                                label="Transport" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<Attractions />} 
                                label="Attractions" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip 
                                icon={<EmojiPeople />} 
                                label="Tour Guide" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No upcoming packages scheduled")}
                </Card>
            </TabPanel>

            <TabPanel value={tabValue} index={2}>
                <Card sx={{ 
                    mb: 3, 
                    p: { xs: 2, sm: 2.5, md: 3 }, 
                    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
                    overflow: 'visible',
                    '@media (min-width: 1200px)': {
                        overflow: 'visible'
                    }
                }}>
                    <Box sx={{ 
                        display: 'flex', 
                        flexDirection: { xs: 'column', sm: 'row' },
                        justifyContent: 'space-between', 
                        alignItems: { xs: 'stretch', sm: 'center' }, 
                        mb: 2,
                        gap: { xs: 2, sm: 0 }
                    }}>
                        <Typography variant="h6" sx={{ 
                            fontWeight: 'bold', 
                            color: '#2c3e50', 
                            display: 'flex', 
                            alignItems: 'center',
                            fontSize: { xs: '1.1rem', sm: '1.25rem' },
                            mb: { xs: 1, sm: 0 }
                        }}>
                            <History sx={{ mr: 1, color: '#4361ee', fontSize: { xs: '1.2rem', sm: '1.5rem' } }} /> 
                            <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                Past Packages
                            </Box>
                            <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                Past
                            </Box>
                        </Typography>
                        <Box sx={{ 
                            display: 'flex', 
                            gap: { xs: 0.5, sm: 1 },
                            flexWrap: 'wrap',
                            justifyContent: { xs: 'center', sm: 'flex-end' }
                        }}>
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
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showDmcFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by DMC company name..."
                                    value={dmcFilterTerm}
                                    onChange={handleDmcFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <Badge fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            {showBookingIdFilter ? (
                                <TextField
                                    size="small"
                                    placeholder="Filter by Booking ID..."
                                    value={bookingIdFilterTerm}
                                    onChange={handleBookingIdFilterChange}
                                    InputProps={{
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                <ConfirmationNumber fontSize="small" />
                                            </InputAdornment>
                                        ),
                                    }}
                                    sx={{ 
                                        minWidth: { xs: '100%', sm: '200px', md: '250px' },
                                        maxWidth: { xs: '100%', sm: '300px' }
                                    }}
                                    autoFocus
                                />
                            ) : null}
                            <Button
                                startIcon={<Search />}
                                variant="outlined"
                                size="small"
                                onClick={toggleSearch}
                                color={showSearchInput ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showSearchInput ? "Close" : "Search"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showSearchInput ? "✕" : "🔍"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<Badge />}
                                variant="outlined"
                                size="small"
                                onClick={toggleDmcFilter}
                                color={showDmcFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showDmcFilter ? "Close" : "DMC Filter"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showDmcFilter ? "✕" : "🏢"}
                                </Box>
                            </Button>
                            <Button
                                startIcon={<ConfirmationNumber />}
                                variant="outlined"
                                size="small"
                                onClick={toggleBookingIdFilter}
                                color={showBookingIdFilter ? "primary" : "inherit"}
                                sx={{
                                    fontSize: { xs: '0.75rem', sm: '0.875rem' },
                                    px: { xs: 1, sm: 2 },
                                    minWidth: { xs: 'auto', sm: 'auto' }
                                }}
                            >
                                <Box component="span" sx={{ display: { xs: 'none', sm: 'inline' } }}>
                                    {showBookingIdFilter ? "Close" : "Booking ID"}
                                </Box>
                                <Box component="span" sx={{ display: { xs: 'inline', sm: 'none' } }}>
                                    {showBookingIdFilter ? "✕" : "🎫"}
                                </Box>
                            </Button>
                            {/* <Button startIcon={<FilterList />} variant="outlined" size="small">Filter</Button> */}
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
                        <Stack 
                            direction="row" 
                            spacing={{ xs: 1, sm: 2 }} 
                            sx={{ 
                                mb: 3,
                                flexWrap: 'wrap',
                                gap: { xs: 1, sm: 2 }
                            }}
                        >
                            <Chip 
                                icon={<Hotel />} 
                                label="Hotels" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<DirectionsCar />} 
                                label="Transport" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            <Chip 
                                icon={<Attractions />} 
                                label="Attractions" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                            {/* <Chip icon={<Restaurant />} label="Restaurants" size="small" variant="outlined" /> */}
                            <Chip 
                                icon={<EmojiPeople />} 
                                label="Tour Guide" 
                                size="small" 
                                variant="outlined"
                                sx={{
                                    fontSize: { xs: '0.7rem', sm: '0.75rem' },
                                    height: { xs: '28px', sm: '32px' }
                                }}
                            />
                        </Stack>
                    </Box>
                    {renderContent(getCurrentFilteredData(), "No past package history available")}
                </Card>
            </TabPanel>
        </Box>
    );
};

export default PreDefinePackages;
