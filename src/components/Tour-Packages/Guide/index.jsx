import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
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
import BusinessCenterIcon from '@mui/icons-material/BusinessCenter';
import PersonIcon from '@mui/icons-material/Person';
import AssistantIcon from '@mui/icons-material/Assistant';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { useDispatch, useSelector } from 'react-redux';
import GuideListing from './GuideListing';
import TimeSelection from './TimeSelection';
import PackageSelection from './PackageSelection';
import PassengerSelection from './PassengerSelection';
import GuideBookingSummaryModal from './GuideBookingSummaryModal';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';

const initialFormState = {
  guide: '',
  pickUpTime: '',
  pickUpTimeHour: null,
  hourlyPackage: '',
  bookingDate: new Date().toISOString().split('T')[0],
  priceBreakdown: {
    basePrice: 0,
    nightSurcharge: 0,
    totalPrice: 0,
    nightHours: 0,
    dayHours: 0
  },
  pax: {
    Adults: 1,
    Children: 0
  }
};

export default function GuideComponent({ date, dayIndex, guidespack, tourDates = [] }) {
  const theme = useTheme();
  const dispatch = useDispatch();
  const selectedGuide = useSelector((state) => state.tourguide.selectedGuide);
  const guides = useSelector((state) => state.tourguide.Guides);
  const status = useSelector((state) => state.tourguide.status);
  const searchParams = useSelector((state) => state.tourguide.searchParams);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  console.log('Guide update', guidespack);
  
  // Get existing services from Redux state
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);

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

  // Initialize form state with search params
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: bookingDate, // Use the date from the specific itinerary day
    pax: {
      Adults: searchParams?.adults || searchParams?.adult || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams, bookingDate]);

  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  // Track which sections have already been saved to Redux
  const [savedSectionIds, setSavedSectionIds] = useState([]);

  // Refs to prevent infinite loops (following attraction component pattern)
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllGuidesRef = useRef(false);
  const currentServicesRef = useRef([]);

  // Update the current services ref when existingServices changes
  useEffect(() => {
    currentServicesRef.current = existingServices;
  }, [existingServices]);

  // Function to initialize form sections from guidespack data
  const initializeFormSectionsFromGuidePack = useCallback(() => {
    if (!guidespack || !Array.isArray(guidespack) || guidespack.length === 0) {
      console.log('No guidespack data to initialize from');
      return;
    }

    console.log('Initializing form sections from guidespack:', guidespack);

    // Filter guides that match the current bookingDate for form sections
    // If dayIndex is missing, use bookingDate for filtering
    const dayGuides = guidespack.filter(guideService => {
      const guideData = guideService.data?.[0];
      
      // If we have dayIndex, use it for filtering (backward compatibility)
      if (guideData && guideData.dayIndex === dayIndex) {
        return true;
      }
      
      // If dayIndex is missing, filter by bookingDate
      if (guideData && guideData.bookingDate) {
        // Format dates for comparison
        const guideDateFormatted = formatDateToString(guideData.bookingDate);
        const currentDateFormatted = formatDateToString(date);
        
        console.log('Guide date comparison:', {
          guideDate: guideDateFormatted,
          currentDate: currentDateFormatted,
          matches: guideDateFormatted === currentDateFormatted
        });
        
        return guideDateFormatted === currentDateFormatted;
      }
      
      return false;
    });

    if (dayGuides.length === 0) {
      console.log(`No guides found for date ${bookingDate} or dayIndex ${dayIndex}`);
      return;
    }

    // Convert guide data to form sections for current day
    const newFormSections = dayGuides.map((guideService, index) => {
      const guideData = guideService.data[0];
      
      // Debug guide data
      console.log(`Processing guide ${index + 1}:`, {
        guideId: guideData?.guide_id,
        guideName: guideData?.guide_name,
        hasGuideId: !!guideData?.guide_id,
        guideIdType: typeof guideData?.guide_id,
        fullGuideData: guideData
      });
      
      // Check if guide_id is missing or invalid
      if (!guideData?.guide_id) {
        console.warn(`Guide ${index + 1} has missing or invalid guide_id:`, guideData);
        // Skip this guide or provide a fallback
        return null;
      }
      
      // Check if the guide exists in available guides
      const availableGuide = guides.find(g => 
        g.id === guideData.guide_id || 
        String(g.id) === String(guideData.guide_id)
      );
      
      if (!availableGuide) {
        console.warn(`Guide with ID ${guideData.guide_id} not found in available guides list. Available guide IDs:`, guides.map(g => g.id));
      }
      
      console.log('Guide originalData image check:', {
        guideId: guideData.guide_id,
        guideName: guideData.guide_name,
        image: guideData.image,
        hasImage: !!guideData.image
      });
      
      return {
        guide: guideData.guide_id,
        guide_name: guideData.guide_name,
        pickUpTime: guideData.entrytime || '',
        pickUpTimeHour: guideData.hours|| null,
        hourlyPackage: guideData.hours || '',
        bookingDate: guideData.bookingDate || bookingDate,
        priceBreakdown: {
          basePrice: guideData.basePrice || 0,
          nightSurcharge: guideData.surcharge || 0,
          totalPrice: guideData.totalPrice || 0,
          nightHours: 0, // Calculate if needed
          dayHours: guideData.hours || 0
        },
        pax: {
          Adults: Number(guideData.adults) || 1,
          Children: Number(guideData.children) || 0
        },
        // Store the original data for reference, including booking_id
        originalData: {
          ...guideData,
          booking_id: guideService.booking_id // Preserve booking_id from service level
        }
      };
    }).filter(section => section !== null); // Remove null entries

    console.log('Initialized guide form sections for current day:', newFormSections);
    console.log('Form sections guide IDs:', newFormSections.map(section => ({
      guide: section.guide,
      guide_name: section.guide_name
    })));
    
    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));
  }, [guidespack, dayIndex, bookingDate, date, formatDateToString, guides]);

  // Function to dispatch ALL guides from guidespack to Redux state
  const dispatchAllGuidesToRedux = useCallback(() => {
    if (!guidespack || !Array.isArray(guidespack) || guidespack.length === 0) {
      console.log('No guidespack data to dispatch to Redux');
      return;
    }

    // Create a unique key for this dispatch to prevent duplicates
    const dispatchKey = JSON.stringify(guidespack.map(service => service.data?.[0]?.id));
    
    if (lastDispatchRef.current === dispatchKey) {
      console.log('Skipping duplicate dispatch for all guides');
      return;
    }

    console.log('Dispatching ALL guides from guidespack to Redux:', guidespack);

    // Remove any existing guide services using the ref
    const filteredServices = currentServicesRef.current.filter(service => service.type !== "guide");

    // Create new guide service entries for ALL guides, preserving booking_id
    const newGuideServices = guidespack.map(guideService => {
      const guideData = guideService.data[0];
      
      if (!guideData) {
        console.log('No guide data found in service:', guideService);
        return null;
      }

      console.log('Processing guide for Redux:', guideData);
      
      // Determine dayIndex based on bookingDate if not provided
      let guideDayIndex = guideData.dayIndex;
      
      // If dayIndex is missing but bookingDate is present, find the matching day
      if (guideDayIndex === undefined && guideData.bookingDate) {
        const guideBookingDate = formatDateToString(guideData.bookingDate);
        // Find index of this date in tourDates
        const dateIndex = tourDates.findIndex(date => formatDateToString(date) === guideBookingDate);
        if (dateIndex !== -1) {
          guideDayIndex = dateIndex;
          console.log(`Mapped guide booking date ${guideBookingDate} to dayIndex ${guideDayIndex}`);
        } else {
          // Default to current dayIndex if date not found
          guideDayIndex = dayIndex;
          console.log(`Could not map guide booking date ${guideBookingDate} to any tour date, using current dayIndex ${dayIndex}`);
        }
      }
      
      const processedGuideData = {
        id: guideData.id,
        guide_id: guideData.guide_id,
        guide_name: guideData.guide_name,
        image: guideData.image,
        dmc_Id: guideData.dmc_Id,
        Mode: guideData.Mode,
        entrypickup: guideData.entrypickup,
        entrytime: guideData.entrytime,
        adults: Number(guideData.adults) || 1,
        children: Number(guideData.children) || 0,
        hours: Number(guideData.hours) || 0,
        basePrice: Number(guideData.basePrice) || 0,
        surcharge: Number(guideData.surcharge) || 0,
        totalPrice: Number(guideData.totalPrice) || 0,
        pickupdate: guideData.pickupdate || guideData.bookingDate,
        bookingDate: guideData.bookingDate,
        dayIndex: guideDayIndex,
        Tax: guideData.Tax,
        Night_Start_Time: guideData.Night_Start_Time,
        Night_End_Time: guideData.Night_End_Time,
        city: guideData.city,
        country: guideData.country,
        languages: guideData.languages || [],
        experience: guideData.experience,
        bookingType: guideData.bookingType || "enquiry" // Use original bookingType if available
      };

      // Create service object with booking_id preserved
      const serviceObject = {
        type: "guide",
        agent_id: agentId,
        tour_id: tourId,
        data: [processedGuideData],
        bookingType: guideData.bookingType || "enquiry" // Use original bookingType if available
      };

      // Add booking_id if it exists in the original service
      if (guideService.booking_id) {
        serviceObject.booking_id = guideService.booking_id;
      }

      return serviceObject;
    }).filter(Boolean); // Remove null entries

    if (newGuideServices.length === 0) {
      console.log('No valid guides to dispatch to Redux');
      return;
    }

    // Add new services to filtered services
    const finalServices = [...filteredServices, ...newGuideServices];

    console.log('Dispatching ALL guide services to Redux:', finalServices);
    dispatch(setAllServices(finalServices));
    
    // Update the last dispatch ref
    lastDispatchRef.current = dispatchKey;
    
    // Set flag to indicate we've dispatched all guides
    hasDispatchedAllGuidesRef.current = true;
  }, [guidespack, agentId, tourId, dispatch, dayIndex, tourDates, formatDateToString]);

  // Reset refs when dayIndex changes
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllGuidesRef.current = false;
    currentServicesRef.current = [];
  }, [dayIndex]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllGuidesRef.current = false;
      currentServicesRef.current = [];
    };
  }, []);

  // Initialize form sections when guidespack changes
  useEffect(() => {
    if (!hasInitializedRef.current && guidespack && Array.isArray(guidespack) && guidespack.length > 0) {
      console.log('Initializing form sections from guidespack');
      initializeFormSectionsFromGuidePack();
      hasInitializedRef.current = true;
    }
  }, [guidespack, initializeFormSectionsFromGuidePack]);

  // Dispatch ALL guides to Redux when guidespack is available (only once)
  useEffect(() => {
    if (!hasDispatchedAllGuidesRef.current && guidespack && Array.isArray(guidespack) && guidespack.length > 0) {
      console.log('Dispatching ALL guides from guidespack to Redux on mount');
      dispatchAllGuidesToRedux();
      hasDispatchedAllGuidesRef.current = true;
    }
  }, [guidespack, dispatchAllGuidesToRedux]);

  // Log guidespack data when it changes
  useEffect(() => {
    if (guidespack && Array.isArray(guidespack) && guidespack.length > 0) {
      console.log('Received guidespack data:', guidespack);
      console.log('Guides by type:', {
        guideCount: guidespack.length,
        bookingTypes: guidespack.map(g => g.bookingType).filter((v, i, a) => a.indexOf(v) === i),
        hasBookingIds: guidespack.filter(g => g.booking_id).length
      });
      
      // Reset the dispatch flag when guidespack changes
      hasDispatchedAllGuidesRef.current = false;
      hasInitializedRef.current = false;
    }
  }, [guidespack]);

  // Define helper functions at the beginning
  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    // Get booking date from the booking
    const bookingDate = booking.originalData?.bookingDate || booking.bookingDate;
    
    // Debug logging to check date formats
    console.log('Guide date validation debug:', {
      bookingId: booking.originalData?.id || 'new-booking',
      bookingDate: bookingDate,
      tourDates: tourDates,
      dayIndex: dayIndex
    });
    
    // Handle edge cases
    if (!bookingDate || !tourDates || tourDates.length === 0) {
      console.log('Missing bookingDate or tourDates, skipping validation');
      return false;
    }
    
    // Normalize booking date to YYYY-MM-DD format
    let normalizedBookingDate;
    try {
      normalizedBookingDate = formatDateToString(bookingDate);
    } catch (error) {
      console.error('Error normalizing booking date:', error);
      return false;
    }
    
    // Check if the normalized booking date exists in tourDates
    const formattedTourDates = tourDates.map(date => formatDateToString(date));
    const isDateValid = formattedTourDates.includes(normalizedBookingDate);
    
    console.log('Guide date validation result:', {
      normalizedBookingDate: normalizedBookingDate,
      formattedTourDates: formattedTourDates,
      isDateValid: isDateValid,
      willShowError: !isDateValid
    });
    
    return !isDateValid;
  };

  const getSelectedGuide = (guideId) => {
    return guides.find(g => g.id === guideId);
  };

  const getCompletionStatus = (section) => {
    const steps = [
      section.guide,
      section.pax.Adults + section.pax.Children > 0,
      section.pickUpTime,
      section.hourlyPackage
    ];
    return steps.filter(Boolean).length;
  };

  // Define memoized functions early
  const getBookingSummary = useCallback((booking) => {
    // If we have original data, use it directly
    if (booking.originalData) {
      console.log('Using original data for guide booking summary:', booking.originalData);
      return {
        guide: booking.originalData,
        guideName: booking.originalData.guide_name,
        city: booking.originalData.city || searchParams?.location?.city || '',
        country: booking.originalData.country || searchParams?.location?.country || '',
        pickUpTime: booking.pickUpTime,
        pickUpTimeHour: booking.pickUpTimeHour,
        duration: booking.hourlyPackage,
        pax: booking.pax,
        mode: currentMode,
        priceBreakdown: booking.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0 },
        image: booking.originalData.image,
        languages: booking.originalData.languages || [],
        experience: booking.originalData.experience || 'Not specified',
        bookingDate: booking.bookingDate,
        booking_id: booking.originalData.booking_id // Preserve booking_id
      };
    }

    // Fallback to finding data from Redux state (for new bookings)
    // Use selectedGuide for detailed information (like attractionDetails in attraction component)
    const selectedGuideDetails = guides.find(g => g.id === booking.guide) || {};
    
    return {
      guide: selectedGuideDetails,
      guideName: selectedGuide?.guide_name || selectedGuideDetails.guide_name || 'Guide',
      city: selectedGuide?.city || selectedGuideDetails.city || searchParams?.location?.city || '',
      country: selectedGuide?.country || selectedGuideDetails.country || searchParams?.location?.country || '',
      pickUpTime: booking.pickUpTime,
      pickUpTimeHour: booking.pickUpTimeHour,
      duration: booking.hourlyPackage,
      pax: booking.pax,
      mode: currentMode,
      priceBreakdown: booking.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0 },
      image: selectedGuide?.guide_image || selectedGuide?.image || selectedGuideDetails.image || '/placeholder-guide.jpg',
      languages: selectedGuide?.languages || selectedGuideDetails.languages || [],
      experience: selectedGuide?.experience_years || selectedGuideDetails.experience_years || 'Not specified',
      bookingDate: booking.bookingDate
    };
  }, [guides, selectedGuide, searchParams, currentMode]);

  const validateBookings = useCallback(() => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one guide booking.");
      return false;
    }
    
    // Only validate complete sections
    const completeSections = formSections.filter(section => 
      section.guide && 
      section.pickUpTime && 
      section.hourlyPackage && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      // Don't show error when auto-validating
      return false;
    }
    
    setValidationError(null);
    return true;
  }, [formSections, setValidationError]);

  const handleBookNow = useCallback(() => {
    if (!validateBookings()) {
      return;
    }
    
    // Filter out incomplete sections
    const completeSections = formSections.filter(section => 
      section.guide && 
      section.pickUpTime && 
      section.hourlyPackage && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      return; // No complete sections to save
    }
    
    // Clone the existing services array, but only remove guide services for the current dayIndex
    // Preserve guide services for other dates
    const servicesWithoutGuides = existingServices.filter(service => {
      if (service.type !== "guide") {
        return true; // Keep non-guide services
      }
      
      // For guide services, check if they belong to the current dayIndex
      // If the service has data and any booking has the same dayIndex, remove it
      if (service.data && Array.isArray(service.data)) {
        const hasCurrentDayBooking = service.data.some(booking => 
          booking.dayIndex === dayIndex
        );
        return !hasCurrentDayBooking; // Keep if it doesn't have current day booking
      }
      
      return true; // Keep if no data or invalid structure
    });
    
    // Create new guide services for the current complete sections
    const guideServices = completeSections.map((section, index) => {
      // If we have original data, use it as base and update with current form values
      if (section.originalData) {
        console.log('Using original data for guide booking:', section.originalData);
        
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
        
        const bookingData = {
          ...section.originalData,
          // Include customer details
          ...customerDetails,
          // Update with current form values
          guide_id: section.guide,
          entrypickup: section.pickUpTime,
          entrytime: section.pickUpTimeHour,
          adults: section.pax.Adults,
          children: section.pax.Children,
          hours: section.hourlyPackage,
          basePrice: section.priceBreakdown.basePrice,
          surcharge: section.priceBreakdown.nightSurcharge,
          totalPrice: section.priceBreakdown.totalPrice,
          bookingDate: section.bookingDate,
          pickupdate: section.bookingDate,
          dayIndex: dayIndex,
          Mode: currentMode,
          dmc_Id: agentId,
          booking_id: section.originalData.booking_id // Preserve booking_id
        };
        
        const serviceObject = {
          type: "guide",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData],
          bookingType: "enquiry"
        };
        
        // Add booking_id if available from original data
        if (section.originalData?.booking_id) {
          serviceObject.booking_id = section.originalData.booking_id;
        }
        
        return serviceObject;
      }
      
      // For new bookings, create from scratch
      const summaryData = getBookingSummary(section);
      
      // Create unique booking ID - use formSections index to maintain identity
      const sectionIndex = formSections.indexOf(section);
      const bookingId = section.originalData?.id || `guide-${Date.now()}-${sectionIndex}`;
      
      // Create the guide booking data
      // Get the guide details for this specific section
      const selectedGuideDetails = getSelectedGuide(section.guide);
      
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
        id: bookingId,
        guide_id: section.guide,
        guide_name: summaryData.guideName,
        image: summaryData.image,
        dmc_Id: agentId,
        Mode: currentMode,
        entrypickup: section.pickUpTime,
        entrytime: section.pickUpTimeHour,
        adults: section.pax.Adults,
        children: section.pax.Children,
        hours: section.hourlyPackage,
        basePrice: section.priceBreakdown.basePrice,
        surcharge: section.priceBreakdown.nightSurcharge,
        totalPrice: section.priceBreakdown.totalPrice,
        pickupdate: section.bookingDate,
        bookingDate: section.bookingDate, // Ensure consistency with other booking types
        dayIndex: dayIndex, // Track which day this booking belongs to
        Tax: selectedGuideDetails?.tax_percentage,
        Night_Start_Time: selectedGuideDetails?.night_start_time,
        Night_End_Time: selectedGuideDetails?.night_end_time,
        // Keep some fields that might still be needed
        city: summaryData.city,
        country: summaryData.country,
        languages: summaryData.languages,
        experience: summaryData.experience,
      };
      
      // Add booking_id if available from original data
      if (section.originalData?.booking_id) {
        bookingData.booking_id = section.originalData.booking_id;
      }
      
      console.log(`Guide booking data for section ${index}:`, bookingData);
      console.log(`Guide booking date check for section ${index}:`, {
        sectionBookingDate: section.bookingDate,
        formattedBookingDate: bookingDate,
        dayIndex: dayIndex,
        finalBookingDate: bookingData.bookingDate
      });
      
      // Create a new guide service entry for this booking
      const serviceObject = {
        type: "guide",
        agent_id: agentId,
        tour_id: tourId,
        data: [bookingData],
        bookingType: "enquiry"
      };
      
      // Add booking_id if available from original data
      if (section.originalData?.booking_id) {
        serviceObject.booking_id = section.originalData.booking_id;
      }
      
      return serviceObject;
    });
    
    // Combine non-guide services with new guide services
    const updatedServices = [...servicesWithoutGuides, ...guideServices];
    
    console.log("Guide - Dispatching updated services to Redux:", updatedServices);
    console.log("Guide - Services filtering check:", {
      totalExistingServices: existingServices.length,
      guideServicesRemoved: existingServices.filter(s => s.type === "guide").length - servicesWithoutGuides.filter(s => s.type === "guide").length,
      currentDayIndex: dayIndex,
      newGuideServices: guideServices.length,
      finalTotalServices: updatedServices.length
    });
    
    // Dispatch the updated services
    dispatch(setAllServices(updatedServices));
    
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, existingServices, validateBookings, dispatch, currentMode, getBookingSummary]);

  useEffect(() => {
    console.log('Guide Status:', status);
    console.log('Guides Data:', guides);
    console.log('Selected Guide (detailed):', selectedGuide);
    console.log('Search Params:', searchParams);
    console.log('GuideComponent - Received props:', { date, dayIndex, bookingDate });
    console.log('GuideComponent - Date type check:', { 
      dateType: typeof date, 
      isMoment: date?._isAMomentObject,
      formattedBookingDate: bookingDate,
      bookingDateType: typeof bookingDate
    });
    console.log('GuideComponent - Form sections with booking dates:', formSections.map(section => ({
      bookingDate: section.bookingDate,
      dayIndex: dayIndex
    })));
  }, [status, guides, selectedGuide, searchParams, date, dayIndex, bookingDate, formSections]);

  // Function to dispatch individual booking updates to Redux
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.guide || !updatedSection.pickUpTime || !updatedSection.hourlyPackage) {
      console.log('Incomplete guide section, skipping Redux dispatch');
      return;
    }

    // If we have original data, use it directly
    if (updatedSection.originalData) {
      console.log('Using original data for individual guide booking update:', updatedSection.originalData);
      
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
        ...updatedSection.originalData,
        // Include customer details
        ...customerDetails,
        // Update with current form values
        guide_id: updatedSection.guide,
        entrypickup: updatedSection.pickUpTime,
        entrytime: updatedSection.pickUpTimeHour,
        adults: updatedSection.pax.Adults,
        children: updatedSection.pax.Children,
        hours: updatedSection.hourlyPackage,
        basePrice: updatedSection.priceBreakdown.basePrice,
        surcharge: updatedSection.priceBreakdown.nightSurcharge,
        totalPrice: updatedSection.priceBreakdown.totalPrice,
        bookingDate: updatedSection.bookingDate,
        pickupdate: updatedSection.bookingDate,
        dayIndex: dayIndex,
        Mode: currentMode,
        dmc_Id: agentId,
        booking_id: updatedSection.originalData.booking_id // Preserve booking_id
      };

      // Clone existing services
      const currentServices = [...existingServices];
      
      // Find and update existing guide service for this dayIndex
      let found = false;
      const updatedServices = currentServices.map(service => {
        if (service.type === "guide" && service.data && Array.isArray(service.data)) {
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
        const newGuideService = {
          type: "guide",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData],
          bookingType: "enquiry"
        };
        
        // Add booking_id if available from original data
        if (updatedSection.originalData?.booking_id) {
          newGuideService.booking_id = updatedSection.originalData.booking_id;
        }
        
        updatedServices.push(newGuideService);
      }

      console.log("Guide - Dispatching individual booking update to Redux (original data):", bookingData);
      dispatch(setAllServices(updatedServices));
      return;
    }

    // For new bookings, calculate everything
    const summaryData = getBookingSummary(updatedSection);
    const selectedGuideDetails = getSelectedGuide(updatedSection.guide);

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
      id: updatedSection.originalData?.id || `guide-${Date.now()}-${sectionIndex}`,
      guide_id: updatedSection.guide,
      guide_name: summaryData.guideName,
      image: summaryData.image,
      dmc_Id: agentId,
      Mode: currentMode,
      entrypickup: updatedSection.pickUpTime,
      entrytime: updatedSection.pickUpTimeHour,
      adults: updatedSection.pax.Adults,
      children: updatedSection.pax.Children,
      hours: updatedSection.hourlyPackage,
      basePrice: updatedSection.priceBreakdown.basePrice,
      surcharge: updatedSection.priceBreakdown.nightSurcharge,
      totalPrice: updatedSection.priceBreakdown.totalPrice,
      pickupdate: updatedSection.bookingDate,
      bookingDate: updatedSection.bookingDate,
      dayIndex: dayIndex,
      Tax: selectedGuideDetails?.tax_percentage,
      Night_Start_Time: selectedGuideDetails?.night_start_time,
      Night_End_Time: selectedGuideDetails?.night_end_time,
      city: summaryData.city,
      country: summaryData.country,
      languages: summaryData.languages,
      experience: summaryData.experience
    };
    
    // Add booking_id if available from original data
    if (updatedSection.originalData?.booking_id) {
      bookingData.booking_id = updatedSection.originalData.booking_id;
    }

    // Clone existing services
    const currentServices = [...existingServices];
    
    // Find and update existing guide service for this dayIndex
    let found = false;
    const updatedServices = currentServices.map(service => {
      if (service.type === "guide" && service.data && Array.isArray(service.data)) {
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
      const newGuideService = {
        type: "guide",
        agent_id: agentId,
        tour_id: tourId,
        data: [bookingData],
        bookingType: "enquiry"  
      };
      
      // Add booking_id if available from original data
      if (updatedSection.originalData?.booking_id) {
        newGuideService.booking_id = updatedSection.originalData.booking_id;
      }
      
      updatedServices.push(newGuideService);
    }

    console.log("Guide - Dispatching individual booking update to Redux:", bookingData);
    dispatch(setAllServices(updatedServices));
  }, [guides, currentMode, agentId, tourId, dayIndex, existingServices, dispatch, getBookingSummary, getSelectedGuide]);
  
  // Effect to automatically dispatch completed guide bookings to Redux
  useEffect(() => {
    // Skip if no form sections or during loading
    if (formSections.length === 0 || status === 'loading') return;
    
    // Find sections that are complete but not yet saved
    const newCompleteSections = formSections.filter((section, index) => {
      // Check if all required fields are filled
      const isComplete = (
        section.guide && 
        section.pickUpTime && 
        section.hourlyPackage && 
        (section.pax.Adults + section.pax.Children > 0)
      );
      
      // Generate a unique ID for this section based on its contents and dayIndex
      const sectionSignature = `${section.guide}-${section.pickUpTime}-${section.hourlyPackage}-${dayIndex}`;
      
      // Check if this section has already been saved
      const isSaved = savedSectionIds.includes(sectionSignature);
      
      // Return true if this section is complete and not yet saved
      return isComplete && !isSaved;
    });
    
    // If we found new complete sections, update Redux
    if (newCompleteSections.length > 0) {
      // Get signatures for the new sections
      const newSectionSignatures = newCompleteSections.map(section => 
        `${section.guide}-${section.pickUpTime}-${section.hourlyPackage}-${dayIndex}`
      );
      
      console.log('Guide - Auto dispatch triggered:', {
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
  }, [formSections, handleBookNow, status, savedSectionIds, dispatchBookingUpdateToRedux]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    const newSection = { 
      ...defaultSection, 
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
      console.log("Guide - No section found at index:", indexToRemove);
      return;
    }

    console.log("Guide - Removing section:", sectionToRemove);
    
    // Remove from local state
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
    
    // Remove section signature from saved IDs
    if (sectionToRemove) {
      const sectionSignature = `${sectionToRemove.guide}-${sectionToRemove.pickUpTime}-${sectionToRemove.hourlyPackage}-${dayIndex}`;
      setSavedSectionIds(prev => prev.filter(signature => signature !== sectionSignature));
    }
    
    // Remove from Redux state if the section has guide data (either has an original ID or guide selection)
    const hasOriginalId = sectionToRemove?.originalData?.id;
    const hasGuideId = sectionToRemove?.guide;
    
    if (hasOriginalId || hasGuideId) {
      // Clone the existing services array
      const currentServices = [...existingServices];
      
      // Filter out guide services that contain this booking
      const filteredServices = currentServices.map(service => {
        // Check if this is a guide service
        if (service.type === "guide") {
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
              
              // Match by guide ID and dayIndex as final fallback for new bookings
              if (sectionToRemove.guide && 
                  dataItem.guide_id === sectionToRemove.guide &&
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
        console.log("Guide - Removing booking from Redux:", sectionToRemove);
        console.log("Guide - Updated services:", filteredServices);
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

  const handleInputChange = (sectionIndex, field, value) => {
    console.log('GuideComponent - handleInputChange called:', {
      sectionIndex: sectionIndex,
      field: field,
      value: value,
      currentSectionGuide: formSections[sectionIndex]?.guide
    });
    
    const newFormSections = [...formSections];
    
    if (field === 'guide') {
      newFormSections[sectionIndex] = {
        ...defaultSection,
        guide: value,
        bookingDate: newFormSections[sectionIndex].bookingDate,
        pax: newFormSections[sectionIndex].pax,
        // Preserve original data when changing guide to maintain connection
        originalData: newFormSections[sectionIndex].originalData
      };
    } else if (field === 'pickUpTime') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pickUpTime: value.value,
        pickUpTimeHour: value.hourValue,
        bookingDate: bookingDate // Preserve booking date
      };
    } else if (field === 'hourlyPackage') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        hourlyPackage: value.value,
        priceBreakdown: value.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0, nightHours: 0, dayHours: 0 },
        bookingDate: bookingDate // Preserve booking date
      };
    } else if (field === 'pax') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pax: value,
        bookingDate: bookingDate // Preserve booking date
      };
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: bookingDate // Preserve booking date
      };
    }
    
    console.log('GuideComponent - Updated section:', {
      sectionIndex: sectionIndex,
      field: field,
      newGuideValue: newFormSections[sectionIndex].guide,
      fullSection: newFormSections[sectionIndex]
    });
    
    setFormSections(newFormSections);
    
    // Check if the current section is now complete
    const updatedSection = newFormSections[sectionIndex];
    const isComplete = 
      updatedSection.guide && 
      updatedSection.pickUpTime && 
      updatedSection.hourlyPackage && 
      (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
    
    // Generate signature for this section
    const oldSectionSignature = formSections[sectionIndex] ? 
      `${formSections[sectionIndex].guide}-${formSections[sectionIndex].pickUpTime}-${formSections[sectionIndex].hourlyPackage}-${dayIndex}` : '';
    
    const newSectionSignature = 
      `${updatedSection.guide}-${updatedSection.pickUpTime}-${updatedSection.hourlyPackage}-${dayIndex}`;
    
    // If the data changed, remove the old signature from saved list
    if (oldSectionSignature !== newSectionSignature) {
      setSavedSectionIds(prev => 
        prev.filter(signature => signature !== oldSectionSignature)
      );
      
      console.log(`Guide booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
    }
      
    // If the section just became complete, show success message
    if (isComplete) {
      // The useEffect will handle dispatching to Redux
      console.log(`Guide booking section ${sectionIndex + 1} is now complete`);
      
      // Also dispatch individual update to Redux if section is complete
      dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
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

  if (!guides || guides.length === 0) {
    return (
      <Container maxWidth="xl">
        <Card 
          elevation={3}
          sx={{
            borderRadius: 3,
            background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
            color: 'white',
            mb: 2,
            mx: 'auto',
          }}
        >
          <CardContent sx={{ py: 2, textAlign: 'center' }}>
            <PersonIcon sx={{ fontSize: 64, color: '#FFD700', mb: 2 }} />
            <Typography variant="h6" color="white">
              Please search for guides first
            </Typography>
          </CardContent>
        </Card>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 3,
          background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
          color: 'white',
          mb: 3,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 1}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <AssistantIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Book Tour Guide Services
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                  Select professional guides and configure your tour package
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${formSections.length} Booking${formSections.length > 1 ? 's' : ''}`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)'
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
      <Fade in={validationError} timeout={300}>
        <Box>
          {validationError && (
            <Alert severity="error" sx={{ mb: 2, borderRadius: 2 }}>
              {validationError}
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Fade in={bookingSuccess} timeout={300}>
        <Box>
          {bookingSuccess && (
            <Alert severity="success" sx={{ mb: 2, borderRadius: 2 }}>
              Guide booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={2}>
        {formSections.map((section, sectionIndex) => {
          const selectedGuideDetails = getSelectedGuide(section.guide);
          const completionStatus = getCompletionStatus(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          const outOfTourDates = isBookingOutOfTourDates(section);
          
          console.log(`Rendering section ${sectionIndex}:`, {
            guideId: section.guide,
            guideName: section.guide_name,
            selectedGuideDetails: selectedGuideDetails ? {
              id: selectedGuideDetails.id,
              name: selectedGuideDetails.guide_name
            } : 'Not found',
            hasOriginalData: !!section.originalData
          });
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid ${alpha('#2196f3', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 8px 24px ${alpha('#e53935', 0.15)}`
                      : `0 8px 24px ${alpha('#2196f3', 0.15)}`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: alpha('#2196f3', 0.05),
                    borderBottom: `1px solid ${alpha('#2196f3', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#2196f3',
                          color: 'white',
                          fontWeight: 600
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                      />
                      {selectedGuideDetails && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 16 }} />}
                          label={selectedGuideDetails.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#2196f3',
                            color: '#2196f3'
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
                            bgcolor: alpha('#2196f3', 0.1),
                            '&:hover': { bgcolor: alpha('#2196f3', 0.2) }
                          }}
                        >
                          <i className={`icon-chevron-${isExpanded ? 'up' : 'down'}`} />
                        </IconButton>
                      </Tooltip>
                      
                      {section.guide && (
                        <Button
                          variant="outlined"
                          size="large"
                          onClick={() => handleOpenModal(sectionIndex)}
                          disabled={!section.guide}
                          startIcon={<VisibilityIcon />}
                          sx={{
                            borderRadius: 2,
                            px: 4,
                            py: 1,
                            fontSize: '0.875rem',
                            fontWeight: 600,
                            textTransform: 'none',
                            borderColor: '#2196f3',
                            color: '#2196f3',
                            '&:hover': {
                              borderColor: '#1976d2',
                              bgcolor: alpha('#2196f3', 0.05),
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
                          <DeleteIcon sx={{ fontSize: 18 }} />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedGuideDetails && (
                    <Box sx={{ p: 2 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Box 
                          component="img"
                          src={(() => {
                            const imageUrl = section.originalData?.image || 
                                           selectedGuide?.guide_image || 
                                           selectedGuide?.image || 
                                           selectedGuideDetails.image || 
                                           '/placeholder-guide.jpg';
                            console.log('Guide collapsed view image debug:', {
                              sectionIndex,
                              originalDataImage: section.originalData?.image,
                              selectedGuideImage: selectedGuide?.guide_image || selectedGuide?.image,
                              selectedGuideDetailsImage: selectedGuideDetails.image,
                              finalImageUrl: imageUrl,
                              hasOriginalData: !!section.originalData,
                              hasSelectedGuide: !!selectedGuide
                            });
                            return imageUrl;
                          })()}
                          alt={section.originalData?.guide_name || 
                               selectedGuide?.guide_name || 
                               selectedGuideDetails.guide_name}
                          sx={{ 
                            width: 60, 
                            height: 60, 
                            borderRadius: 2,
                            objectFit: 'cover',
                            border: `2px solid ${alpha('#2196f3', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="h6" fontWeight={600} sx={{ mb: 0.5 }}>
                            {section.originalData?.guide_name || 
                             selectedGuide?.guide_name || 
                             selectedGuideDetails.guide_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 16 }} />}
                                label={`${section.pax.Adults + section.pax.Children} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
                                }}
                              />
                            )}
                            {section.pickUpTime && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 16 }} />}
                                label={section.pickUpTime}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
                                }}
                              />
                            )}
                            {section.hourlyPackage && (
                              <Chip 
                                icon={<BusinessCenterIcon sx={{ fontSize: 16 }} />}
                                label={`${section.hourlyPackage}h Package`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
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
                        m: 2,
                        p: 0, 
                        borderRadius: 2,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      <Grid container spacing={2} alignItems="flex-end">
                        {/* Guide Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <PersonIcon sx={{ mr: 1, color: '#2196f3', fontSize: 20 }} />
                              <Typography variant="subtitle2" fontWeight="600" color="text.primary">
                                Select Guide
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <GuideListing 
                                
                                value={section.guide}
                                onChange={(field, value) => handleInputChange(sectionIndex, field, value)}
                                disabled={status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guests Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <PeopleIcon sx={{ mr: 1, color: '#2e7d32', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide ? "text.disabled" : "text.primary"}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PassengerSelection
                                value={section.pax}
                                onChange={(value) => handleInputChange(sectionIndex, 'pax', value)}
                                disabled={!section.guide || status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Time Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <AccessTimeIcon sx={{ mr: 1, color: '#ff9800', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide ? "text.disabled" : "text.primary"}
                              >
                                Pickup Time
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <TimeSelection
                                value={section.pickUpTime}
                                onChange={(e) => handleInputChange(sectionIndex, 'pickUpTime', e)}
                                disabled={!section.guide || status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Package Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <BusinessCenterIcon sx={{ mr: 1, color: '#9c27b0', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide || !section.pickUpTime ? "text.disabled" : "text.primary"}
                              >
                                Select Package
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PackageSelection
                                value={section.hourlyPackage}
                                onChange={(e) => handleInputChange(sectionIndex, 'hourlyPackage', e)}
                                disabled={!section.guide || !section.pickUpTime || status === 'loading'}
                                pickUpTime={section.pickUpTime ? { value: section.pickUpTime, hourValue: section.pickUpTimeHour } : null}
                                bookingDate={section.bookingDate}
                                formSection={section}
                              />
                            </Box>
                          </Box>
                        </Grid>
                      </Grid>
                    </Paper>
                  </Collapse>

                  {/* Red alert if out of tour dates */}
                  {outOfTourDates && (
                    <Box sx={{ px: 2, pt: 1 }}>
                      <Alert severity="error" sx={{ borderRadius: 2, mb: 1 }}>
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
              borderRadius: 3,
              border: `2px dashed ${alpha('#2196f3', 0.4)}`,
              bgcolor: alpha('#2196f3', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#2196f3', 0.05),
                borderColor: '#2196f3',
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
                gap: 2
              }}>
                <AddIcon sx={{ fontSize: 32, color: '#2196f3' }} />
                <Typography variant="h6" color="#2196f3" fontWeight={600}>
                  Add More
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        
      </Grid>

      <GuideBookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? formSections[selectedSectionIndex] : null}
        bookingIndex={selectedSectionIndex}
        guideDetails={selectedSectionIndex !== null ? (() => {
          const section = formSections[selectedSectionIndex];
          const baseGuideDetails = getSelectedGuide(section?.guide);
          
          // If we have originalData, merge it with base guide details
          if (section?.originalData) {
            return {
              ...baseGuideDetails,
              image: section.originalData.image,
              guide_name: section.originalData.guide_name,
              city: section.originalData.city || baseGuideDetails?.city,
              country: section.originalData.country || baseGuideDetails?.country,
              experience_years: section.originalData.experience || baseGuideDetails?.experience_years,
              languages: section.originalData.languages || baseGuideDetails?.languages
            };
          }
          
          // For new bookings, use selectedGuide for detailed information
          return {
            ...baseGuideDetails,
            image: selectedGuide?.guide_image || selectedGuide?.image || baseGuideDetails?.image,
            guide_name: selectedGuide?.guide_name || baseGuideDetails?.guide_name,
            city: selectedGuide?.city || baseGuideDetails?.city,
            country: selectedGuide?.country || baseGuideDetails?.country,
            experience_years: selectedGuide?.experience_years || baseGuideDetails?.experience_years,
            languages: selectedGuide?.languages || baseGuideDetails?.languages
          };
        })() : null}
      />
    </Container>
  );
} 