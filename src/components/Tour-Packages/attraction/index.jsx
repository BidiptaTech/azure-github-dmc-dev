import React, { useState, useEffect, useCallback, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import {
  Typography,
  Container,
  Button,
  Box,
  Grid,
  Card,
  CardContent,
  Stack,
  IconButton,
  Tooltip,
  Alert,
  Chip,
  Fade,
  Collapse,
  useTheme,
  alpha,
  Paper,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PeopleIcon from '@mui/icons-material/People';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import AttractionsIcon from '@mui/icons-material/Attractions';
import TourIcon from '@mui/icons-material/Tour';
import { fetchAttractions, selectAttractions } from '../../../slice/attractions/attractionSlice';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';
import AttractionListing from './AttractionListing';
import PaxSelector from './PaxSelector';
import TimeSlotSelector from './TimeSlotSelector';
import TicketTypeSelector from './TicketTypeSelector';
import BookingSummaryModal from './BookingSummaryModal';
import PortCity from './PortCity';

const initialFormState = {
  attraction: '',
  pax: {
    Adults: 0,
    Children: 0,
    Seniors: 0
  },
  timeSlot: '',
  ticketType: '',
  bookingDate: new Date().toISOString().split('T')[0]
};

export default function AttractionComponent({ date, dayIndex, attractionspack, tourDates = [] }) {
  const theme = useTheme();
  const dispatch = useDispatch();
  const attractions = useSelector(selectAttractions);
  const searchParams = useSelector((state) => state.attractions.searchParams);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);
  
  
  console.log('Attraction update', attractionspack);
  console.log('AttractionDetails:', attractionDetails);
  console.log('Packages from attractionDetails:', attractionDetails?.packages);

  // Helper function to convert any date format to YYYY-MM-DD string
  const formatDateToString = (dateInput) => {
    if (!dateInput) {
      return new Date().toISOString().split('T')[0];
    }
    
    // If it's a Moment object
    if (dateInput._isAMomentObject) {
      return dateInput.format('YYYY-MM-DD');
    }
    
    // If it's already a string in YYYY-MM-DD format
    if (typeof dateInput === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
      return dateInput;
    }
    
    // If it's a Date object or other format
    try {
      return new Date(dateInput).toISOString().split('T')[0];
    } catch (error) {
      console.error('Error formatting date:', error);
      return new Date().toISOString().split('T')[0];
    }
  };

  // Use the passed date as the booking date for this specific day
  const bookingDate = formatDateToString(date);

  const [formSections, setFormSections] = useState([{ ...initialFormState, bookingDate: bookingDate }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  // Track which sections have already been saved to Redux
  const [savedSectionIds, setSavedSectionIds] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [cityError, setCityError] = useState(false);
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isAttractionListingEnabled, setIsAttractionListingEnabled] = useState(false);
  console.log("selectedCity", selectedCity);
  const country = useSelector((state) => state.tourPackages.searchCriteria.country);
  const tour = useSelector((state) => state.hotels.tourdetails);
  console.log("tour", tour);
  // Refs to prevent infinite loops
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllAttractionsRef = useRef(false);
  const currentServicesRef = useRef([]);
  const isInitializingRef = useRef(false);

  console.log("formsection", formSections);
  
  // Debug effect to track formSections changes
  useEffect(() => {
    console.log('formSections state changed:', formSections);
  }, [formSections]);
  
  // Update the current services ref when existingServices changes
  useEffect(() => {
    currentServicesRef.current = existingServices;
  }, [existingServices]);

  // Reset attraction listing state when city changes or component mounts
  useEffect(() => {
    if (!selectedCity) {
      setIsAttractionListingEnabled(false);
    }
  }, [selectedCity]);

  // Debug effect to track isAttractionListingEnabled changes
  useEffect(() => {
    console.log("isAttractionListingEnabled changed to:", isAttractionListingEnabled);
  }, [isAttractionListingEnabled]);

  // Function to initialize form sections from attractionspack data
  const initializeFormSectionsFromAttractionPack = useCallback(() => {
    if (!attractionspack || !Array.isArray(attractionspack) || attractionspack.length === 0) {
      console.log('No attractionspack data to initialize from');
      return;
    }

    console.log('Initializing form sections from attractionspack:', attractionspack);
    isInitializingRef.current = true;

    // Filter attractions that match the current bookingDate (following restaurant pattern)
    const dayAttractions = attractionspack.filter(attractionService => {
      const attractionData = attractionService.data?.[0];
      console.log('Attraction data:', attractionData);

      // Match by bookingDate since attractionspack uses bookingDate
      return attractionData && attractionData.bookingDate === bookingDate;
    });

    if (dayAttractions.length === 0) {
      console.log(`No attractions found for bookingDate ${bookingDate}`);
      return;
    }

    // Convert attraction data to form sections for current day
    const newFormSections = dayAttractions.map((attractionService, index) => {
      const attractionData = attractionService.data[0];
      
      console.log('Processing attraction data for form section:', {
        AttractionId: attractionData.AttractionId,
        ticketId: attractionData.ticketId,
        visitTime: attractionData.visitTime,
        adultCount: attractionData.adultCount,
        childCount: attractionData.childCount,
        seniorCount: attractionData.seniorCount
      });
      
      const formSection = {
        attraction: String(attractionData.AttractionId), // Convert to string
        pax: {
          Adults: Number(attractionData.adultCount) || 0,
          Children: Number(attractionData.childCount) || 0,
          Seniors: Number(attractionData.seniorCount) || 0
        },
        timeSlot: attractionData.visitTime || '',
        ticketType: String(attractionData.ticketId), // Convert to string
        priceType: attractionData.nri || 'residential',
        type: attractionService.type || 'attraction', // Include service type
        bookingDate: attractionData.bookingDate || bookingDate,
        // Store the original data for reference, including booking_id and service type
        originalData: {
          ...attractionData,
          booking_id: attractionService.booking_id, // Preserve booking_id from service level
          type: attractionService.type || 'attraction', // Preserve service type
          dayIndex: dayIndex // Set dayIndex in the copy, not the original
        }
      };
      
      console.log('Created form section:', formSection);
      return formSection;
    });

    console.log('Initialized form sections for current day:', newFormSections);
    console.log('Form sections by type:', {
      attractions: newFormSections.filter(s => s.type === 'attraction').length,
      packages: newFormSections.filter(s => s.type === 'attraction_package').length
    });
    console.log('Setting form sections with data:', newFormSections);
    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));
    
    // Clear initialization flag after a short delay to allow components to render
    setTimeout(() => {
      isInitializingRef.current = false;
      console.log('Initialization complete, allowing component updates');
    }, 100);
  }, [attractionspack, dayIndex, bookingDate]);

  // Function to dispatch ALL attractions from attractionspack to Redux state
  const dispatchAllAttractionsToRedux = useCallback(() => {
    if (!attractionspack || !Array.isArray(attractionspack) || attractionspack.length === 0) {
      console.log('No attractionspack data to dispatch to Redux');
      return;
    }

    // Create a unique key for this dispatch to prevent duplicates
    const dispatchKey = JSON.stringify(attractionspack.map(service => service.data?.[0]?.AttractionId));
    
    if (lastDispatchRef.current === dispatchKey) {
      console.log('Skipping duplicate dispatch for all attractions');
      return;
    }

    // Check if there are already attraction services for the current day in Redux
    const existingAttractionServices = currentServicesRef.current.filter(service => 
      (service.type === "attraction" || service.type === "attraction_package") && 
      service.data && 
      Array.isArray(service.data) &&
      service.data.some(booking => booking.dayIndex === dayIndex)
    );
    console.log('Existing attraction services:', existingAttractionServices);
    if (existingAttractionServices.length > 0) {
      console.log('Attraction services already exist for this day, skipping dispatch to prevent duplicates');
      return;
    }

    console.log('Dispatching ALL attractions from attractionspack to Redux:', attractionspack);

    // Remove any existing attraction services using the ref (both attraction and attraction_package types)
    const filteredServices = currentServicesRef.current.filter(service => 
      service.type !== "attraction" && service.type !== "attraction_package"
    );

    // Create new attraction service entries for ALL attractions, preserving booking_id
    const newAttractionServices = attractionspack.map(attractionService => {
      const originalAttractionData = attractionService.data[0];
      console.log('Original attraction data:', originalAttractionData);
      if (!originalAttractionData) {
        console.log('No attraction data found in service:', attractionService);
        return null;
      }

      console.log('Processing attraction for Redux:', originalAttractionData);
      console.log('Service type:', attractionService.type, 'Package type:', originalAttractionData.package_type);
      
      // Create a copy of the data instead of modifying the original
      const processedAttractionData = {
        id: originalAttractionData.id || `attraction-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
        AttractionId: originalAttractionData.AttractionId,
        AttractionName: originalAttractionData.AttractionName,
        location: originalAttractionData.location || originalAttractionData.city || '',
        city: originalAttractionData.city || '',
        country: originalAttractionData.country || '',
        visitTime: originalAttractionData.visitTime || '',
        ticketId: originalAttractionData.ticketId,
        ticketName: originalAttractionData.ticketName,
        adultCount: Number(originalAttractionData.adultCount) || 0,
        childCount: Number(originalAttractionData.childCount) || 0,
        seniorCount: Number(originalAttractionData.seniorCount) || 0,
        ticket_details: originalAttractionData.ticket_details || {
          adult_price: 0,
          child_price: 0,
          senior_price: 0,
          description: ''
        },
        nri: originalAttractionData.nri || 'residential',
        totalPrice: Number(originalAttractionData.totalPrice) || Number(originalAttractionData.price) || 0,
        image: originalAttractionData.image || '',
        mode: originalAttractionData.mode || 'dmc',
        dmc_id: originalAttractionData.dmc_id || '',
        bookingDate: originalAttractionData.bookingDate || bookingDate,
        dayIndex: dayIndex, // Always use current dayIndex
        bookingType: originalAttractionData.bookingType || "enquiry",
        // Include package-related fields if they exist
        package_type: originalAttractionData.package_type || (attractionService.type === "attraction_package" ? 1 : 0),
        package_attraction_id: originalAttractionData.package_attraction_id || null,
        ...(originalAttractionData.package_details && { package_details: originalAttractionData.package_details })
      };

      // Determine if this is a package booking (check service type first, then data properties)
      const isPackageBooking = attractionService.type === "attraction_package" || 
                               processedAttractionData.package_type === 1 || 
                               (typeof processedAttractionData.ticketId === 'string' && processedAttractionData.ticketId.startsWith('pkg_'));
      
      // Create service object with booking_id preserved
      const serviceObject = {
        type: isPackageBooking ? "attraction_package" : "attraction",
        agent_id: agentId,
        tour_id: tourId,
        data: [processedAttractionData],
        bookingType: "enquiry"
      };

      // Add booking_id if it exists in the original service
      if (attractionService.booking_id) {
        serviceObject.booking_id = attractionService.booking_id;
      }

      return serviceObject;
    }).filter(Boolean); // Remove null entries

    console.log('New attraction services:', newAttractionServices);
    if (newAttractionServices.length === 0) {
      console.log('No valid attractions to dispatch to Redux');
      return;
    }

    // Add new services to filtered services
    const finalServices = [...filteredServices, ...newAttractionServices];

    console.log('Dispatching ALL attraction services to Redux:', finalServices);
    console.log('Services by type:', {
      attractions: newAttractionServices.filter(s => s.type === 'attraction').length,
      packages: newAttractionServices.filter(s => s.type === 'attraction_package').length
    });
    dispatch(setAllServices(finalServices));
    
    // Update the last dispatch ref
    lastDispatchRef.current = dispatchKey;
    hasDispatchedAllAttractionsRef.current = true;
  }, [attractionspack, agentId, tourId, dispatch, dayIndex, bookingDate]);

  // Reset refs when dayIndex changes
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllAttractionsRef.current = false;
    currentServicesRef.current = [];
    isInitializingRef.current = false;
  }, [dayIndex]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllAttractionsRef.current = false;
      currentServicesRef.current = [];
      isInitializingRef.current = false;
    };
  }, []);

  // Initialize form sections when attractionspack changes
  useEffect(() => {
    if (!hasInitializedRef.current && attractionspack && Array.isArray(attractionspack) && attractionspack.length > 0) {
      console.log('Initializing form sections from attractionspack');
      initializeFormSectionsFromAttractionPack();
      hasInitializedRef.current = true;
    }
  }, [attractionspack, initializeFormSectionsFromAttractionPack]);

  // Dispatch ALL attractions to Redux when attractionspack is available (only once)
  useEffect(() => {
    if (!hasDispatchedAllAttractionsRef.current && attractionspack && Array.isArray(attractionspack) && attractionspack.length > 0) {
      console.log('Dispatching ALL attractions from attractionspack to Redux on mount');
      dispatchAllAttractionsToRedux();
      hasDispatchedAllAttractionsRef.current = true;
    }
  }, [attractionspack, dispatchAllAttractionsToRedux]);

  // Log attractionspack data when it changes
  useEffect(() => {
    if (attractionspack && Array.isArray(attractionspack) && attractionspack.length > 0) {
      console.log('Received attractionspack data:', attractionspack);
      console.log('Attractions by type:', {
        attractions: attractionspack.filter(s => s.type === 'attraction').length,
        packages: attractionspack.filter(s => s.type === 'attraction_package').length
      });
      
      // Reset the dispatch flag when attractionspack changes
      hasDispatchedAllAttractionsRef.current = false;
    }
  }, [attractionspack]);

  useEffect(() => {
    console.log('AttractionComponent - Received props:', { date, dayIndex, bookingDate });
    console.log('AttractionComponent - Form sections count:', formSections.length);
    console.log('AttractionComponent - Has initialized:', hasInitializedRef.current);
  }, [date, dayIndex, bookingDate, formSections.length]);



  const handleAddMore = () => {
    const newIndex = formSections.length;
    const newSection = { 
      ...initialFormState, 
      bookingDate: bookingDate,
      // Ensure new sections don't have originalData to avoid conflicts
      originalData: null
    };
    setFormSections([...formSections, newSection]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    const sectionToRemove = formSections[indexToRemove];
    
    if (!sectionToRemove) {
      console.log("Attraction - No section found at index:", indexToRemove);
      return;
    }

    console.log("Attraction - Removing section:", sectionToRemove);
    
    // Remove from local state
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
    
    // Remove section signature from saved IDs
    if (sectionToRemove) {
      const sectionSignature = `${sectionToRemove.attraction}-${sectionToRemove.timeSlot}-${sectionToRemove.ticketType}-${dayIndex}`;
      setSavedSectionIds(prev => prev.filter(signature => signature !== sectionSignature));
    }
    
    // Remove from Redux state if the section has attraction data (either has an original ID or attraction selection)
    const hasOriginalId = sectionToRemove?.originalData?.id;
    const hasAttractionId = sectionToRemove?.attraction;
    
    if (hasOriginalId || hasAttractionId) {
      // Clone the existing services array
      const currentServices = [...existingServices];
      
      // Filter out attraction services that contain this booking
      const filteredServices = currentServices.map(service => {
        // Check if this is an attraction service (including attraction packages)
        if (service.type === "attraction" || service.type === "attraction_package") {
          // Check if this service contains data that matches our booking
          if (service.data && Array.isArray(service.data)) {
            // Remove the specific booking with matching ID and booking_id (if available)
            const filteredData = service.data.filter(dataItem => {
              // Match by booking_id first (most reliable)
              if (sectionToRemove.originalData?.booking_id && dataItem.booking_id) {
                return !(dataItem.id === sectionToRemove.originalData.id && 
                        dataItem.booking_id === sectionToRemove.originalData.booking_id);
              }
              
              // Match by ID as fallback
              if (sectionToRemove.originalData?.id && dataItem.id === sectionToRemove.originalData.id) {
                return false;
              }
              
              // Match by attraction ID and dayIndex as final fallback for new bookings
              if (sectionToRemove.attraction && 
                  dataItem.AttractionId === sectionToRemove.attraction &&
                  dataItem.dayIndex === dayIndex) {
                return false;
              }
              
              return true;
            });
            
            if (filteredData.length === 0) {
              // If no data left, mark for removal
              return null;
            } else {
              // Create a new service with filtered data (immutable update)
              return {
                ...service,
                data: filteredData
              };
            }
          }
        }
        
        // Keep all other services as-is
        return service;
      }).filter(service => service !== null); // Remove services marked as null
      
      // Only dispatch if there's an actual change
      if (filteredServices.length !== currentServices.length || 
          JSON.stringify(filteredServices) !== JSON.stringify(currentServices)) {
        console.log("Attraction - Removing booking from Redux:", sectionToRemove);
        console.log("Attraction - Updated services:", filteredServices);
        dispatch(setAllServices(filteredServices));
      }
    }
  };

  const toggleSectionExpand = (index) => {
    if (expandedSections.includes(index)) {
      setExpandedSections(expandedSections.filter(i => i !== index));
    } else {
      setExpandedSections([...expandedSections, index]);
    }
  };

  // Function to dispatch individual booking updates to Redux
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.attraction || !updatedSection.timeSlot || !updatedSection.ticketType) {
      console.log('Incomplete section, skipping Redux dispatch');
      return;
    }

    // If we have original data, use it directly
    if (updatedSection.originalData) {
      console.log('Using original data for individual booking update:', updatedSection.originalData);
      
      // Get customer details from original data if available
      const customerDetails = {
        fullName: updatedSection.originalData.fullName || "",
        email: updatedSection.originalData.email || "",
        phone: updatedSection.originalData.phone || "",
        countryCode: updatedSection.originalData.countryCode || "",
        address1: updatedSection.originalData.address1 || "",
        address2: updatedSection.originalData.address2 || "",
        state: updatedSection.originalData.state || "",
        zip: updatedSection.originalData.zip || "",
        specialRequests: updatedSection.originalData.specialRequests || "",
      };
      
      const bookingData = {
        // Include customer details
        ...customerDetails,
        
        // Core booking details
        id: updatedSection.originalData.id,
        AttractionId: updatedSection.originalData.AttractionId,
        AttractionName: updatedSection.originalData.AttractionName,
        location: updatedSection.originalData.location,
        city: updatedSection.originalData.city,
        country: updatedSection.originalData.country,
        visitTime: updatedSection.originalData.visitTime,
        ticketId: updatedSection.originalData.ticketId,
        ticketName: updatedSection.originalData.ticketName,
        adultCount: updatedSection.originalData.adultCount,
        childCount: updatedSection.originalData.childCount,
        seniorCount: updatedSection.originalData.seniorCount,
        ticket_details: updatedSection.originalData.ticket_details,
        nri: updatedSection.originalData.nri,
        totalPrice: updatedSection.originalData.totalPrice,
        image: updatedSection.originalData.image,
        mode: updatedSection.originalData.mode,
        dmc_id: updatedSection.originalData.dmc_id,
        bookingDate: updatedSection.originalData.bookingDate,
        dayIndex: updatedSection.originalData.dayIndex,
        bookingType: updatedSection.originalData.bookingType || "enquiry",
        booking_id: updatedSection.originalData.booking_id // Preserve booking_id
      };

      // Clone existing services
      const currentServices = [...existingServices];
      
      // Find and update existing attraction service for this dayIndex
      let found = false;
      const updatedServices = currentServices.map(service => {
        if ((service.type === "attraction" || service.type === "attraction_package") && service.data && Array.isArray(service.data)) {
          const updatedData = service.data.map(item => {
            if (item.dayIndex === dayIndex && item.id === bookingData.id) {
              found = true;
              return bookingData;
            }
            return item;
          });
          
          if (found) {
            return { ...service, data: updatedData };
          }
        }
        return service;
      });

      // If not found, add new service entry
      if (!found) {
        // Determine if this is a package booking
        const isPackageBooking = bookingData.package_type === 1 || (typeof bookingData.ticketId === 'string' && bookingData.ticketId.startsWith('pkg_'));
        
        const newAttractionService = {
          type: isPackageBooking ? "attraction_package" : "attraction",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData],
          bookingType: "enquiry"
        };
        
        // Add booking_id if available from original data
        if (updatedSection.originalData?.booking_id) {
          newAttractionService.booking_id = updatedSection.originalData.booking_id;
        }
        
        updatedServices.push(newAttractionService);
      }

      console.log("Attraction - Dispatching individual booking update to Redux (original data):", bookingData);
      dispatch(setAllServices(updatedServices));
      return;
    }

    // For new bookings, calculate everything
    const summaryData = getBookingSummary(updatedSection);
    
    const adultTotal = summaryData.adultPrice * updatedSection.pax.Adults;
    const childTotal = summaryData.childPrice * updatedSection.pax.Children;
    const seniorTotal = summaryData.seniorPrice * updatedSection.pax.Seniors;
    const totalPrice = adultTotal + childTotal + seniorTotal;

    const bookingData = {
      // Customer information fields (will be populated when available)
      fullName: "",
      email: "",
      phone: "",
      countryCode: "",
      address1: "",
      address2: "",
      state: "",
      zip: "",
      specialRequests: "",
      
      // Core booking details
      id: updatedSection.originalData?.id || `attraction-${Date.now()}-${sectionIndex}`,
      AttractionId: updatedSection.attraction,
      AttractionName: summaryData.attraction,
      location: summaryData.location,
      city: summaryData.city,
      country: summaryData.country,
      visitTime: updatedSection.timeSlot,
      ticketId: updatedSection.ticketType,
      ticketName: summaryData.ticketType,
      adultCount: updatedSection.pax.Adults,
      childCount: updatedSection.pax.Children,
      seniorCount: updatedSection.pax.Seniors,
      ticket_details: {
        adult_price: summaryData.adultPrice,
        child_price: summaryData.childPrice,
        senior_price: summaryData.seniorPrice,
        description: summaryData.ticketDescription || ''
      },
      nri: updatedSection.priceType || 'residential',
      totalPrice: totalPrice,
      image: summaryData.image,
      mode: currentMode,
      dmc_id: agentId,
      bookingDate: updatedSection.bookingDate,
      dayIndex: dayIndex,
      bookingType: "enquiry",
      package_type: summaryData.type === 'attraction_package' ? 1 : 0,
      package_attraction_id: summaryData.type === 'attraction_package' ? summaryData.packageDetails?.package_id : null,
      ...(summaryData.type === 'attraction_package' && summaryData.packageDetails && { package_details: summaryData.packageDetails })
    };

    // Clone existing services
    const currentServices = [...existingServices];
    
    // Find and update existing attraction service for this dayIndex
    let found = false;
    const updatedServices = currentServices.map(service => {
      if ((service.type === "attraction" || service.type === "attraction_package") && service.data && Array.isArray(service.data)) {
        const updatedData = service.data.map(item => {
          if (item.dayIndex === dayIndex && item.id === bookingData.id) {
            found = true;
            return bookingData;
          }
          return item;
        });
        
        if (found) {
          return { ...service, data: updatedData };
        }
      }
      return service;
    });

    // If not found, add new service entry
    if (!found) {
      const newAttractionService = {
        type: "attraction",
        agent_id: agentId,
        tour_id: tourId,
        data: [bookingData],
        bookingType: "enquiry"
      };
      
      // Add booking_id if available from original data
      if (updatedSection.originalData?.booking_id) {
        newAttractionService.booking_id = updatedSection.originalData.booking_id;
      }
      
      updatedServices.push(newAttractionService);
    }

    console.log("Attraction - Dispatching individual booking update to Redux:", bookingData);
    dispatch(setAllServices(updatedServices));
  }, [attractions, attractionDetails, currentMode, agentId, tourId, dayIndex, existingServices, dispatch]);

  const handleInputChange = (sectionIndex, field, value) => {
    console.log('handleInputChange called:', { sectionIndex, field, value, currentFormSections: formSections, isInitializing: isInitializingRef.current });
    
    // Skip updates during initialization to prevent overwriting initialized data
    if (isInitializingRef.current) {
      console.log('Skipping handleInputChange during initialization');
      return;
    }
    
    const newFormSections = [...formSections];
    
    // Generate old signature before changes
    const oldSectionSignature = formSections[sectionIndex] ? 
      `${formSections[sectionIndex].attraction}-${formSections[sectionIndex].timeSlot}-${formSections[sectionIndex].ticketType}-${dayIndex}` : '';
    
    if (field === 'pax') {
      const currentPax = newFormSections[sectionIndex].pax;
      if (
        currentPax.Adults !== value.Adults ||
        currentPax.Children !== value.Children ||
        currentPax.Seniors !== value.Seniors
      ) {
        newFormSections[sectionIndex] = {
          ...newFormSections[sectionIndex],
          pax: {
            Adults: value.Adults || 0,
            Children: value.Children || 0,
            Seniors: value.Seniors || 0
          }
        };
        setFormSections(newFormSections);
        
        // Check completion and manage saved signatures
        const updatedSection = newFormSections[sectionIndex];
        const isComplete = 
          updatedSection.attraction && 
          updatedSection.timeSlot && 
          updatedSection.ticketType && 
          (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);
        
        const newSectionSignature = 
          `${updatedSection.attraction}-${updatedSection.timeSlot}-${updatedSection.ticketType}-${dayIndex}`;
        
        // If the data changed, remove the old signature from saved list
        if (oldSectionSignature !== newSectionSignature) {
          setSavedSectionIds(prev => 
            prev.filter(signature => signature !== oldSectionSignature)
          );
          console.log(`Attraction booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
        }
        
        // Dispatch update to Redux if section is complete
        if (isComplete) {
          console.log(`Attraction booking section ${sectionIndex + 1} is now complete`);
          dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
        }
      }
    } else if (field === 'attraction') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        timeSlot: '',
        bookingDate: bookingDate // Preserve booking date
      };
      setFormSections(newFormSections);
      
      // Remove old signature since attraction changed
      if (oldSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Attraction booking section ${sectionIndex + 1} attraction changed, will be re-evaluated for saving`);
      }
    } else if (field === 'ticketType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        ticketType: value.ticketId,
        priceType: value.priceType,
        type: value.type || 'attraction', // Add the type field
        bookingDate: bookingDate // Preserve booking date
      };
      setFormSections(newFormSections);
      
      // Check completion and manage saved signatures
      const updatedSection = newFormSections[sectionIndex];
      const isComplete = 
        updatedSection.attraction && 
        updatedSection.timeSlot && 
        updatedSection.ticketType && 
        (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);
      
      const newSectionSignature = 
        `${updatedSection.attraction}-${updatedSection.timeSlot}-${updatedSection.ticketType}-${dayIndex}`;
      
      // If the data changed, remove the old signature from saved list
      if (oldSectionSignature !== newSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Attraction booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
      }
      
      // Dispatch update to Redux if section is complete
      if (isComplete) {
        console.log(`Attraction booking section ${sectionIndex + 1} is now complete`);
        dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
      }
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: bookingDate // Preserve booking date
      };
      setFormSections(newFormSections);
      
      // Check completion and manage saved signatures
      const updatedSection = newFormSections[sectionIndex];
      const isComplete = 
        updatedSection.attraction && 
        updatedSection.timeSlot && 
        updatedSection.ticketType && 
        (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);
      
      const newSectionSignature = 
        `${updatedSection.attraction}-${updatedSection.timeSlot}-${updatedSection.ticketType}-${dayIndex}`;
      
      // If the data changed, remove the old signature from saved list
      if (oldSectionSignature !== newSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Attraction booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
      }
      
      // Dispatch update to Redux if section is complete
      if (isComplete) {
        console.log(`Attraction booking section ${sectionIndex + 1} is now complete`);
        dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
      }
    }
  };

  const handleOpenModal = (index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  };

  const handleCloseModal = () => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  };

  const getBookingSummary = useCallback((booking) => {
    // If we have original data, use it directly
    if (booking.originalData) {
      console.log('Using original data for booking summary:', booking.originalData);
      
      // Determine if this is a package booking
      const isPackageBooking = 
        booking.originalData.package_type === 1 || 
        booking.type === 'attraction_package' ||
        (typeof booking.originalData.ticketId === 'string' && booking.originalData.ticketId?.startsWith('pkg_'));
      
      return {
        attraction: booking.originalData.AttractionName || 'Unknown Attraction',
        location: booking.originalData.location || booking.originalData.city || '',
        country: booking.originalData.country || '',
        city: booking.originalData.city || '',
        image: booking.originalData.image || '',
        description: booking.originalData.description || 'Description from original booking',
        pax: {
          Adults: Number(booking.originalData.adultCount) || 0,
          Children: Number(booking.originalData.childCount) || 0,
          Seniors: Number(booking.originalData.seniorCount) || 0
        },
        timeSlot: booking.originalData.visitTime || '',
        ticketType: booking.originalData.ticketName || '',
        ticketDescription: booking.originalData.ticket_details?.description || 'No description available',
        adultPrice: Number(booking.originalData.ticket_details?.adult_price) || 0,
        childPrice: Number(booking.originalData.ticket_details?.child_price) || 0,
        seniorPrice: Number(booking.originalData.ticket_details?.senior_price) || 0,
        openingHours: booking.originalData.openingHours || 'Opening hours from original booking',
        terms: booking.originalData.terms || 'Terms from original booking',
        remarks: booking.originalData.remarks || 'Remarks from original booking',
        mode: booking.originalData.mode || 'dmc',
        address: booking.originalData.address || 'Address from original booking',
        category: booking.originalData.category || 'Category from original booking',
        duration: booking.originalData.duration || 'Duration from original booking',
        cancellation_policy: booking.originalData.cancellation_policy || 'Cancellation policy from original booking',
        inclusions: booking.originalData.inclusions || 'Inclusions from original booking',
        exclusions: booking.originalData.exclusions || 'Exclusions from original booking',
        tax_percentage: booking.originalData.ticket_details?.tax_percentage || 0,
        tax_amount: booking.originalData.ticket_details?.tax_amount || 0,
        currency: booking.originalData.currency || 'SGD',
        priceType: booking.originalData.nri || 'residential',
        booking_id: booking.originalData.booking_id, // Preserve booking_id
        type: isPackageBooking ? 'attraction_package' : 'attraction',
        packageAttractions: booking.originalData.package_details?.package_attractions || null,
        packageDescription: booking.originalData.package_details?.package_description || null,
        packageDetails: booking.originalData.package_details || null, // Add full package details
      };
    }

    // Fallback to finding data from Redux state (for new bookings)
    const selectedAttraction = attractions.find(a => a.id === booking.attraction);
    const ticketDetails = attractionDetails?.ticket_prices?.find(
      ticket => ticket.ticket_id === booking.ticketType
    );
    
    // Check if this is a package booking
    console.log('getBookingSummary - booking.ticketType:', booking.ticketType, 'type:', typeof booking.ticketType);
    console.log('getBookingSummary - booking.type:', booking.type);
    
    const isPackage = (typeof booking.ticketType === 'string' && booking.ticketType.startsWith('pkg_')) || booking.type === 'attraction_package';
    const packageDetails = isPackage ? attractionDetails?.packages?.find(
      pkg => `pkg_${pkg.id}` === booking.ticketType
    ) : null;

    // Get prices based on mode and price type (residential/nri)
    let adultPrice, childPrice, seniorPrice;
    const isNRI = booking.priceType === 'nri';
    
    if (isPackage && packageDetails) {
      // Use package prices
      adultPrice = parseFloat(packageDetails.adult_price) || 0;
      childPrice = parseFloat(packageDetails.child_price) || 0;
      seniorPrice = parseFloat(packageDetails.senior_citizen_price) || 0;
    } else if (currentMode === 'dmc') {
      if (isNRI) {
        adultPrice = parseFloat(ticketDetails?.dmc_adult_price_nri) || 0;
        childPrice = parseFloat(ticketDetails?.dmc_child_price_nri) || 0;
        seniorPrice = parseFloat(ticketDetails?.dmc_senior_price_nri) || 0;
      } else {
        adultPrice = parseFloat(ticketDetails?.dmc_adult_price) || 0;
        childPrice = parseFloat(ticketDetails?.dmc_child_price) || 0;
        seniorPrice = parseFloat(ticketDetails?.dmc_senior_price) || 0;
      }
    } else {
      adultPrice = parseFloat(selectedAttraction?.travClicks_adult_price) || 0;
      childPrice = parseFloat(selectedAttraction?.travClicks_child_price) || 0;
      seniorPrice = parseFloat(selectedAttraction?.travClicks_senior_price) || 0;
    }
    
    const formatOpeningHours = () => {
      const times = [];
      if (selectedAttraction?.morning_opening === 1) times.push("Morning");
      if (selectedAttraction?.afternoon_opening === 1) times.push("Afternoon");
      if (selectedAttraction?.evening_opening === 1) times.push("Evening");
      if (selectedAttraction?.night_opening === 1) times.push("Night");
      return times.join(", ") || "Not specified";
    };

    // Get package details if it's a package booking
    const packageDetailsForBooking = isPackage ? {
      package_id: packageDetails?.id || null,
      package_name: packageDetails?.name || 'Package',
      package_attractions: packageDetails?.attractions || [],
      package_description: packageDetails?.description || "",
      package_adult_price: adultPrice || 0,
      package_child_price: childPrice || 0,
      package_senior_price: seniorPrice || 0,
      package_total_attractions: packageDetails?.attractions?.length || 0
    } : null;

    const summaryData = {
      attraction: selectedAttraction?.attraction_name || 'Not selected',
      location: selectedAttraction?.city || 'Not specified',
      country: selectedAttraction?.country || 'Not specified',
      city: selectedAttraction?.city || 'Not specified',
      image: selectedAttraction?.image || '/placeholder-image.jpg',
      description: selectedAttraction?.description || 'No description available',
      pax: booking.pax || { Adults: 0, Children: 0, Seniors: 0 },
      timeSlot: booking.timeSlot || 'Not selected',
      ticketType: isPackage ? packageDetails?.name : (ticketDetails?.ticket_name || 'Not selected'),
      ticketDescription: isPackage ? packageDetails?.description : (ticketDetails?.description || 'No description available'),
      adultPrice,
      childPrice,
      seniorPrice,
      openingHours: formatOpeningHours(),
      terms: selectedAttraction?.terms || ticketDetails?.terms || 'No terms and conditions specified',
      remarks: selectedAttraction?.remarks || ticketDetails?.remarks || 'No additional remarks',
      mode: currentMode,
      address: selectedAttraction?.address || 'Address not specified',
      category: selectedAttraction?.category || 'Category not specified',
      duration: selectedAttraction?.duration || 'Duration not specified',
      cancellation_policy: selectedAttraction?.cancellation_policy || ticketDetails?.cancellation_policy || 'Cancellation policy not specified',
      inclusions: selectedAttraction?.inclusions || ticketDetails?.inclusions || 'Inclusions not specified',
      exclusions: selectedAttraction?.exclusions || ticketDetails?.exclusions || 'Exclusions not specified',
      tax_percentage: selectedAttraction?.tax_percentage || ticketDetails?.tax_percentage,
      tax_amount: selectedAttraction?.tax_amount || ticketDetails?.tax_amount,
      currency: selectedAttraction?.currency || 'SGD',
      priceType: booking.priceType || 'residential',
      type: isPackage ? 'attraction_package' : 'attraction',
      packageAttractions: isPackage ? packageDetails?.attractions : null,
      packageDescription: isPackage ? packageDetails?.description : null,
      packageDetails: packageDetailsForBooking, // Add the full package details for booking
    };

    console.log('Summary data:', summaryData);
    return summaryData;
  }, [attractions, attractionDetails, currentMode]);

  const validateBookings = () => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one attraction.");
      return false;
    }
    
    for (let i = 0; i < formSections.length; i++) {
      const section = formSections[i];
      
      if (!section.attraction) {
        setValidationError(`Booking #${i + 1}: Please select an attraction.`);
        return false;
      }
      
      if (!section.timeSlot) {
        setValidationError(`Booking #${i + 1}: Please select a time slot.`);
        return false;
      }
      
      if (!section.ticketType) {
        setValidationError(`Booking #${i + 1}: Please select a ticket type.`);
        return false;
      }
      
      const totalPax = section.pax.Adults + section.pax.Children + section.pax.Seniors;
      if (totalPax <= 0) {
        setValidationError(`Booking #${i + 1}: Please select at least one person.`);
        return false;
      }
    }
    
    setValidationError(null);
    return true;
  };

  const handleBookNow = useCallback(() => {
    if (!validateBookings()) {
      return;
    }
    
    console.log("Processing attraction bookings for Redux dispatch");
    
    // Format sections for Redux state
    const attractionsForRedux = formSections.map((section, index) => {
      // If we have original data, use it directly
      if (section.originalData) {
        console.log('Using original data for booking:', section.originalData);
        
        // Get customer details from original data if available
        const customerDetails = {
          fullName: section.originalData.fullName || "",
          email: section.originalData.email || "",
          phone: section.originalData.phone || "",
          countryCode: section.originalData.countryCode || "",
          address1: section.originalData.address1 || "",
          address2: section.originalData.address2 || "",
          state: section.originalData.state || "",
          zip: section.originalData.zip || "",
          specialRequests: section.originalData.specialRequests || "",
        };
        
        return {
          // Include customer details
          ...customerDetails,
          
          // Core booking details
          id: section.originalData.id,
          AttractionId: section.originalData.AttractionId,
          AttractionName: section.originalData.AttractionName,
          location: section.originalData.location,
          city: section.originalData.city,
          country: section.originalData.country,
          visitTime: section.originalData.visitTime,
          ticketId: section.originalData.ticketId,
          ticketName: section.originalData.ticketName,
          adultCount: section.originalData.adultCount,
          childCount: section.originalData.childCount,
          seniorCount: section.originalData.seniorCount,
          ticket_details: section.originalData.ticket_details,
          nri: section.originalData.nri,
          totalPrice: section.originalData.totalPrice,
          image: section.originalData.image,
          mode: section.originalData.mode,
          dmc_id: section.originalData.dmc_id,
          bookingDate: section.originalData.bookingDate,
          dayIndex: section.originalData.dayIndex,
          bookingType: section.originalData.bookingType || "enquiry",
          booking_id: section.originalData.booking_id, // Preserve booking_id
          package_type: section.originalData.package_type || 0,
          package_attraction_id: section.originalData.package_attraction_id || null,
          ...(section.originalData.package_details && { package_details: section.originalData.package_details })
        };
      }

      // For new bookings, calculate everything
      const summaryData = getBookingSummary(section);
      
      const adultTotal = summaryData.adultPrice * section.pax.Adults;
      const childTotal = summaryData.childPrice * section.pax.Children;
      const seniorTotal = summaryData.seniorPrice * section.pax.Seniors;
      const totalPrice = adultTotal + childTotal + seniorTotal;
      
      return {
        // Customer information fields (will be populated when available)
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: "",
        
        // Core booking details
        id: section.originalData?.id || `attraction-${Date.now()}-${index}`,
        AttractionId: section.attraction,
        AttractionName: summaryData.attraction,
        location: summaryData.location,
        city: summaryData.city,
        country: summaryData.country,
        visitTime: section.timeSlot,
        ticketId: section.ticketType,
        ticketName: summaryData.ticketType,
        adultCount: section.pax.Adults,
        childCount: section.pax.Children,
        seniorCount: section.pax.Seniors,
        ticket_details: {
          adult_price: summaryData.adultPrice,
          child_price: summaryData.childPrice,
          senior_price: summaryData.seniorPrice,
          description: summaryData.ticketDescription || ''
        },
        nri: section.priceType || 'residential',
        totalPrice: totalPrice,
        image: summaryData.image,
        mode: currentMode,
        dmc_id: agentId,
        bookingDate: section.bookingDate,
        dayIndex: dayIndex,
        bookingType: "enquiry",
        package_type: summaryData.type === 'attraction_package' ? 1 : 0,
        package_attraction_id: summaryData.type === 'attraction_package' ? summaryData.packageDetails?.package_id : null,
        ...(summaryData.type === 'attraction_package' && summaryData.packageDetails && { package_details: summaryData.packageDetails })
      };
    });

    // Remove any existing attraction services for this dayIndex
    const filteredServices = existingServices.filter(service => {
      if ((service.type === "attraction" || service.type === "attraction_package") && service.data && Array.isArray(service.data)) {
        // Keep services that don't match this dayIndex
        return !service.data.some(item => item.dayIndex === dayIndex);
      }
      return true; // Keep all other services
    });

    // Create new attraction service entries
    const newAttractionServices = attractionsForRedux.map((attractionData, index) => {
      // Determine if this is a package booking
      const isPackageBooking = attractionData.package_type === 1 || (typeof attractionData.ticketId === 'string' && attractionData.ticketId.startsWith('pkg_'));
      
      const serviceObject = {
        type: isPackageBooking ? "attraction_package" : "attraction",
        agent_id: agentId,
        tour_id: tourId,
        data: [attractionData],
        bookingType: "enquiry"
      };
      
      // Add booking_id if available from original data
      const originalSection = formSections[index];
      if (originalSection?.originalData?.booking_id) {
        serviceObject.booking_id = originalSection.originalData.booking_id;
      }
      
      return serviceObject;
    });

    // Add new services to filtered services
    const finalServices = [...filteredServices, ...newAttractionServices];

    console.log("Attraction - Dispatching updated services to Redux:", finalServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(finalServices));
    
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, existingServices, validateBookings, dispatch, currentMode, getBookingSummary, agentId, tourId, dayIndex]);

  // Effect to automatically dispatch completed attraction bookings to Redux
  useEffect(() => {
    // Skip if no form sections or during loading
    if (formSections.length === 0 || !attractions || attractions.length === 0) return;
    
    // Skip auto-dispatch if we have attractionspack data (to prevent duplicates)
    // Only auto-dispatch when there's no attractionspack data (new bookings)
    if (attractionspack && Array.isArray(attractionspack) && attractionspack.length > 0) {
      console.log('Attraction - Skipping auto-dispatch because attractionspack data exists');
      return;
    }
    
    // Find sections that are complete but not yet saved
    const newCompleteSections = formSections.filter((section, index) => {
      // Check if all required fields are filled
      const isComplete = (
        section.attraction && 
        section.timeSlot && 
        section.ticketType && 
        (section.pax.Adults + section.pax.Children + section.pax.Seniors > 0)
      );
      
      // Generate a unique ID for this section based on its contents and dayIndex
      const sectionSignature = `${section.attraction}-${section.timeSlot}-${section.ticketType}-${dayIndex}`;
      
      // Check if this section has already been saved
      const isSaved = savedSectionIds.includes(sectionSignature);
      
      // Return true if this section is complete and not yet saved
      return isComplete && !isSaved;
    });
    
    // If we found new complete sections, update Redux
    if (newCompleteSections.length > 0) {
      // Get signatures for the new sections
      const newSectionSignatures = newCompleteSections.map(section => 
        `${section.attraction}-${section.timeSlot}-${section.ticketType}-${dayIndex}`
      );
      
      console.log('Attraction - Auto dispatch triggered:', {
        newCompleteSections: newCompleteSections.length,
        dayIndex: dayIndex,
        newSectionSignatures: newSectionSignatures,
        currentSavedIds: savedSectionIds
      });
      
      // Wait a bit to avoid too many Redux updates
      const timeoutId = setTimeout(() => {
        // Call handleBookNow
        handleBookNow();
        
        // Mark these sections as saved
        setSavedSectionIds(prev => [...prev, ...newSectionSignatures]);
      }, 500);
      
      return () => clearTimeout(timeoutId);
    }
  }, [formSections, handleBookNow, attractions, savedSectionIds, dayIndex, attractionspack]);

  const getCompletionStatus = (section) => {
    const steps = [
      section.attraction,
      section.pax.Adults + section.pax.Children + section.pax.Seniors > 0,
      section.timeSlot,
      section.ticketType
    ];
    return steps.filter(Boolean).length;
  };

  const getSelectedAttraction = (attractionId) => {
    return attractions.find(a => a.id === attractionId);
  };

  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    // Only validate if this booking belongs to the current dayIndex
    const bookingDayIndex = booking.originalData?.dayIndex || dayIndex;
    
    // If the booking doesn't belong to this dayIndex, don't validate
    if (bookingDayIndex !== dayIndex) {
      return false;
    }
    
    const bookingDate = booking.originalData?.bookingDate || booking.bookingDate;
    
    // Debug logging to check date formats
    console.log('Date validation debug:', {
      bookingId: booking.originalData?.id || 'new-booking',
      bookingDate: bookingDate,
      tourDates: tourDates,
      dayIndex: dayIndex,
      bookingDayIndex: bookingDayIndex
    });
    
    // Handle edge cases
    if (!bookingDate || !tourDates || tourDates.length === 0) {
      console.log('Missing bookingDate or tourDates, skipping validation');
      return false;
    }
    
    // Normalize booking date to YYYY-MM-DD format
    let normalizedBookingDate;
    try {
      if (typeof bookingDate === 'string') {
        // If it's already in YYYY-MM-DD format
        if (/^\d{4}-\d{2}-\d{2}$/.test(bookingDate)) {
          normalizedBookingDate = bookingDate;
        } else {
          // Convert from other formats to YYYY-MM-DD
          normalizedBookingDate = new Date(bookingDate).toISOString().split('T')[0];
        }
      } else {
        // If it's a Date object
        normalizedBookingDate = new Date(bookingDate).toISOString().split('T')[0];
      }
    } catch (error) {
      console.error('Error normalizing booking date:', error);
      return false;
    }
    
    // Check if the normalized booking date exists in tourDates
    const isDateValid = tourDates.includes(normalizedBookingDate);
    
    console.log('Date validation result:', {
      normalizedBookingDate: normalizedBookingDate,
      isDateValid: isDateValid,
      willShowError: !isDateValid
    });
    
    return !isDateValid;
  };

  // if (!attractions || attractions.length === 0) {
  //   return (
  //     <Container maxWidth="xl">
  //       <Card 
  //         elevation={3}
  //         sx={{
  //           borderRadius: 3,
  //           background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
  //           color: 'white',
  //           mb: 2,
  //           mx: 'auto',
  //         }}
  //       >
  //         <CardContent sx={{ py: 2, textAlign: 'center' }}>
  //           <AttractionsIcon sx={{ fontSize: 64, color: '#FFD700', mb: 2 }} />
  //           <Typography variant="h6" color="white">
  //             Please search for attractions first
  //           </Typography>
  //         </CardContent>
  //       </Card>
  //     </Container>
  //   );
  // }

  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    console.log("Current isAttractionListingEnabled:", isAttractionListingEnabled);
    setSelectedCity(city);
    
    if (city) {
      setCityError(false);
      // Disable attraction listing until API call is successful
      console.log("Disabling attraction listing - waiting for API response");
      setIsAttractionListingEnabled(false);
      
      // Dispatch fetchAttractions API call
      console.log("Dispatching fetchAttractions with params:", {
        city: `${city.name}, (${country})`,
        date: bookingDate,
        adults: tour.adult,
        children: tour.child,
        tour_id: tour.tour_id,
        selectedDate: bookingDate,
        fromMainSearch: false
      });
      
      dispatch(fetchAttractions({ city: `${city.name}, (${country})`, date: bookingDate, adults: tour.adult,
        children: tour.child,
        tour_id: tour.tour_id, // Use tour_id from packageData
        selectedDate: bookingDate,
        fromMainSearch: false, }))
        .then((result) => {
          console.log("fetchAttractions API result:", result);
          if (result.error) {
            console.error("fetchAttractions API Error:", result.error);
            console.log("API failed - keeping attraction listing disabled");
            setIsAttractionListingEnabled(false);
          } else {
            console.log("fetchAttractions API Success - enabling attraction listing");
            console.log("API succeeded - enabling attraction listing");
            setIsAttractionListingEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchAttractions:", error);
          console.log("API dispatch failed - keeping attraction listing disabled");
          setIsAttractionListingEnabled(false);
        });
    } else {
      // If no city selected, disable attraction listing
      console.log("No city selected - disabling attraction listing");
      setIsAttractionListingEnabled(false);
    }
  };

  return (
    <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 2,
          background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
          color: 'white',
          mb: 1.5,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 0.5, height: '52px' }}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <TourIcon sx={{ mr: 1.5, fontSize: 28, color: '#FFD700' }} />
              <Box>
                <Typography variant="h6" fontWeight="600" sx={{ color: 'white', fontSize: '0.9rem' }}>
                  Book Attraction Tickets
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)', fontSize: '0.7rem' }}>
                  Select attractions and configure your perfect tour package
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${formSections.length} Booking${formSections.length > 1 ? 's' : ''}`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)',
                fontSize: '0.75rem',
                height: '20px'
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
      <Fade in={validationError} timeout={300}>
        <Box>
          {validationError && (
            <Alert severity="error" sx={{ mb: 1.5, borderRadius: 1.5 }}>
              {validationError}
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Fade in={bookingSuccess} timeout={300}>
        <Box>
          {bookingSuccess && (
            <Alert severity="success" sx={{ mb: 1.5, borderRadius: 1.5 }}>
              Booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={1.5}>
        {formSections.map((section, sectionIndex) => {
          const selectedAttraction = getSelectedAttraction(section.attraction);
          const completionStatus = getCompletionStatus(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          const outOfTourDates = isBookingOutOfTourDates(section);
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 2,
                  border: outOfTourDates ? '1px solid #e53935' : `1px solid ${alpha('#ff6b6b', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 4px 12px ${alpha('#e53935', 0.15)}`
                      : `0 4px 12px ${alpha('#ff6b6b', 0.15)}`,
                    transform: 'translateY(-1px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 1.5,
                    bgcolor: alpha('#ff6b6b', 0.05),
                    borderBottom: `1px solid ${alpha('#ff6b6b', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#ff6b6b',
                          color: 'white',
                          fontWeight: 600,
                          fontSize: '0.7rem',
                          height: '20px'
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                        sx={{ fontSize: '0.7rem', height: '20px' }}
                      />
                      {selectedAttraction && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 14 }} />}
                          label={selectedAttraction.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#ff6b6b',
                            color: '#ff6b6b',
                            fontSize: '0.7rem',
                            height: '20px'
                          }}
                        />
                      )}
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Tooltip title={isExpanded ? "Collapse" : "Expand"}>
                        <IconButton 
                          size="small" 
                          onClick={() => toggleSectionExpand(sectionIndex)}
                          sx={{ 
                            bgcolor: alpha('#ff6b6b', 0.1),
                            '&:hover': { bgcolor: alpha('#ff6b6b', 0.2) }
                          }}
                        >
                          <i className={`icon-chevron-${isExpanded ? 'up' : 'down'}`} />
                        </IconButton>
                      </Tooltip>
                      
                      {section.attraction && (
                        <Button
                              variant="outlined"
                              size="medium"
                              onClick={() => handleOpenModal(sectionIndex)}
                              disabled={!section.attraction}
                              startIcon={<VisibilityIcon />}
                              sx={{
                                borderRadius: 1.5,
                                px: 3,
                                py: 0.8,
                                fontSize: '0.8rem',
                                fontWeight: 600,
                                textTransform: 'none',
                                borderColor: '#ff6b6b',
                                color: '#ff6b6b',
                                '&:hover': {
                                  borderColor: '#ee5a24',
                                  bgcolor: alpha('#ff6b6b', 0.05),
                                  transform: 'translateY(-1px)',
                                },
                                transition: 'all 0.3s ease',
                              }}
                            >
                              View Summary
                            </Button>
                      )}
                                  
                      <Tooltip title="Remove Booking">
                        <IconButton 
                          size="small"
                          color="error" 
                          onClick={() => handleRemoveSection(sectionIndex)}
                          sx={{ 
                            bgcolor: alpha(theme.palette.error.main, 0.1),
                            '&:hover': { bgcolor: alpha(theme.palette.error.main, 0.2) }
                          }}
                        >
                          <DeleteIcon sx={{ fontSize: 16 }} />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedAttraction && (
                    <Box sx={{ p: 1.5 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                        <Box 
                          component="img"
                          src={selectedAttraction.image}
                          alt={selectedAttraction.attraction_name}
                          sx={{ 
                            width: 50, 
                            height: 50, 
                            borderRadius: 1.5,
                            objectFit: 'cover',
                            border: `1px solid ${alpha('#ff6b6b', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 0.5, fontSize: '0.9rem' }}>
                            {selectedAttraction.attraction_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 1.5, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children + section.pax.Seniors > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 14 }} />}
                                label={`${section.pax.Adults + section.pax.Children + section.pax.Seniors} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#ff6b6b',
                                  color: '#ff6b6b',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                            {section.timeSlot && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 14 }} />}
                                label={section.timeSlot}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#ff6b6b',
                                  color: '#ff6b6b',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                          </Box>
                        </Box>
                      </Box>
                    </Box>
                  )}

                  {/* Expanded Content */}
                  <Collapse in={isExpanded} timeout={300}>
                    <Paper 
                      elevation={0} 
                      sx={{ 
                        m: 1.5,
                        p: 0, 
                        borderRadius: 1.5,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      <Grid container spacing={1.5} alignItems="flex-end">
                        {/* Attraction Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <LocationOnIcon sx={{ mr: 0.8, color: '#1976d2', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!isCityEnabled ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                City
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                              <PortCity
                                onLocationSelect={handleCitySelect}
                                hasError={cityError}
                                setError={setCityError}
                                disabled={!isCityEnabled}
                              />
                            </Box>
                          </Box>
                        </Grid>
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <AttractionsIcon sx={{ mr: 0.8, color: '#ff6b6b', fontSize: 18 }} />
                              <Typography variant="body2" fontWeight="600" color={!isAttractionListingEnabled ? "text.disabled" : "text.primary"}  sx={{ fontSize: '0.8rem' }}>
                                Select Attraction
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <AttractionListing
                                attractions={attractions}
                                selectedAttraction={section.attraction}
                                disabled={!isAttractionListingEnabled}
                                onAttractionChange={(value) => handleInputChange(sectionIndex, 'attraction', value)}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guests Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <PeopleIcon sx={{ mr: 0.8, color: '#2e7d32', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <PaxSelector
                                initialAdults={searchParams?.adults || 1}
                                initialChildren={searchParams?.children || 0}
                                onPaxChange={(value) => handleInputChange(sectionIndex, 'pax', value)}
                                disabled={!section.attraction}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Time Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <AccessTimeIcon sx={{ mr: 0.8, color: '#ff9800', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Time
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <TimeSlotSelector
                                selectedTimeSlot={section.timeSlot}
                                onTimeSlotChange={(value) => handleInputChange(sectionIndex, 'timeSlot', value)}
                                attraction={section.attraction}
                                disabled={!section.attraction}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Ticket Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <ConfirmationNumberIcon sx={{ mr: 0.8, color: '#9c27b0', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Ticket
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                                                          <TicketTypeSelector
                              selectedTicketType={section.ticketType}
                              onTicketTypeChange={(value) => handleInputChange(sectionIndex, 'ticketType', value)}
                              disabled={!section.attraction}
                              sectionIndex={sectionIndex}
                              formSections={formSections}
                              bookingDate={date}
                              dayIndex={dayIndex}
                              packages={attractionDetails?.packages || []}
                            />
                            </Box>
                          </Box>
                        </Grid>
                      </Grid>
                    </Paper>
                  </Collapse>

                  {/* Red alert if out of tour dates */}
                  {outOfTourDates && (
                    <Box sx={{ px: 1.5, pt: 0.5 }}>
                      <Alert severity="error" sx={{ borderRadius: 1.5, mb: 0.5 }}>
                        The booking is out of currently updated tour dates
                      </Alert>
                    </Box>
                  )}
                </CardContent>
              </Card>
            </Grid>
          );
        })}

        {/* Add More Card */}
        <Grid item xs={12}>
          <Card 
            sx={{ 
              borderRadius: 2,
              border: `1px dashed ${alpha('#ff6b6b', 0.4)}`,
              bgcolor: alpha('#ff6b6b', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#ff6b6b', 0.05),
                borderColor: '#ff6b6b',
                transform: 'translateY(-1px)',
              }
            }}
            onClick={handleAddMore}
          >
            <CardContent sx={{ py: 2 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 1.5
              }}>
                <AddIcon sx={{ fontSize: 28, color: '#ff6b6b' }} />
                <Typography variant="subtitle1" color="#ff6b6b" fontWeight={600} sx={{ fontSize: '0.9rem' }}>
                  Add More
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <BookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? getBookingSummary(formSections[selectedSectionIndex]) : null}
        bookingIndex={selectedSectionIndex}
      />
    </Container>
  );
}