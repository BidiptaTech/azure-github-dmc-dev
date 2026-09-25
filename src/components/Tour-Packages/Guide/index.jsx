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
import { useDispatch, useSelector, shallowEqual } from 'react-redux';
import GuideListing from './GuideListing';
import TimeSelection from './TimeSelection';
import PackageSelection from './PackageSelection';
import PassengerSelection from './PassengerSelection';
import GuideBookingSummaryModal from './GuideBookingSummaryModal';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';
import PortCity from './PortCity';
import { fetchGuides, fetchGuideDetails } from '@/slice/tourguide/guideslice';

const parsePickUpTimeToHour = (timeStr) => {
  if (timeStr === null || timeStr === undefined || timeStr === '') return null;
  if (typeof timeStr === 'number' && !Number.isNaN(timeStr)) return timeStr;
  const str = String(timeStr);
  if (str.includes('AM') || str.includes('PM')) {
    const [timePart, period] = str.split(' ');
    let [hours] = timePart.split(':');
    hours = parseInt(hours, 10);
    if (period === 'PM' && hours !== 12) hours += 12;
    else if (period === 'AM' && hours === 12) hours = 0;
    return hours;
  }
  const [hours] = str.split(':');
  const parsed = parseInt(hours, 10);
  return Number.isNaN(parsed) ? null : parsed;
};
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
  const searchParams = useSelector((state) => state.tourguide.searchParams, shallowEqual);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  const globalTourId = useSelector((state) => state.auth?.tourId || state.steps?.id);
  const tourStatus = useSelector((state) => state.tourPackages.tourStatus);
  // Extract numeric part from tour_id
  const numericTourId = React.useMemo(() => {
    const effectiveTourId = globalTourId || tourId;
    if (!effectiveTourId) return null;
    const tourIdStr = String(effectiveTourId);
    const match = tourIdStr.match(/\d+$/); // Extract trailing digits
    return match ? Number(match[0]) : null;
  }, [globalTourId, tourId]);
  
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
    bookingDate: bookingDate,
    localId: `guide-new-${dayIndex}-0`,
    pax: {
      Adults: searchParams?.adults || searchParams?.adult || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams, bookingDate, dayIndex]);

  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  
  // City selection state
  const [selectedCity, setSelectedCity] = useState(null);
  const [cityError, setCityError] = useState(false);
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isGuideListingEnabled, setIsGuideListingEnabled] = useState(false);
  console.log("selectedCity", selectedCity);
  const country = useSelector((state) => state.tourPackages.searchCriteria.country);
  // Refs to prevent infinite loops / stale AllServices merges
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllGuidesRef = useRef(false);
  const currentServicesRef = useRef([]);
  const isInitializingRef = useRef(false);
  const isProcessingRef = useRef(false);
  const lastFormSectionsRef = useRef([]);
  const getBookingSummaryRef = useRef(() => null);

  // Update the current services ref when existingServices changes
  useEffect(() => {
    currentServicesRef.current = existingServices;
  }, [existingServices]);

  // Function to initialize form sections from guidespack data
  const initializeFormSectionsFromGuidePack = useCallback(() => {
    if (!guidespack || !Array.isArray(guidespack) || guidespack.length === 0) {
      isInitializingRef.current = false;
      return;
    }

    isInitializingRef.current = true;

    // Filter guides - show all for first dayIndex, match by bookingDate for other days
    const dayGuides = guidespack.filter(guideService => {
      const guideData = guideService.data?.[0];
      
      if (!guideData) return false;

      // For first dayIndex (dayIndex === 0), show guides that either match current bookingDate OR don't match any tour dates
      if (dayIndex === 0) {
        const matchesCurrentDate = guideData.bookingDate === bookingDate;
        const notInTourDates = !tourDates.includes(guideData.bookingDate);
        return matchesCurrentDate || notInTourDates;
      }
      
      // If we have dayIndex, use it for filtering (backward compatibility)
      if (guideData.dayIndex === dayIndex) {
        return true;
      }
      
      // If dayIndex is missing, filter by bookingDate
      if (guideData.bookingDate) {
        const guideDateFormatted = formatDateToString(guideData.bookingDate);
        const currentDateFormatted = formatDateToString(date);
        return guideDateFormatted === currentDateFormatted;
      }
      
      return false;
    });

    if (dayGuides.length === 0) {
      isInitializingRef.current = false;
      return;
    }

    // Convert guide data to form sections for current day
    const newFormSections = dayGuides.map((guideService, index) => {
      const guideData = guideService.data[0];
      
      if (!guideData?.guide_id) {
        return null;
      }

      const resolvedDayIndex = typeof guideData.dayIndex === 'number'
        ? guideData.dayIndex
        : (Array.isArray(tourDates) && guideData.bookingDate
          ? Math.max(0, tourDates.findIndex((d) => formatDateToString(d) === formatDateToString(guideData.bookingDate)))
          : dayIndex);
      const safeDayIndex = resolvedDayIndex === -1 ? dayIndex : resolvedDayIndex;
      
      return {
        guide: guideData.guide_id,
        guide_name: guideData.guide_name,
        pickUpTime: guideData.entrytime || '',
        pickUpTimeHour: parsePickUpTimeToHour(guideData.entrytime),
        hourlyPackage: guideData.hours !== undefined && guideData.hours !== null && guideData.hours !== ''
          ? Number(guideData.hours)
          : '',
        bookingDate: guideData.bookingDate || bookingDate,
        localId: guideService.booking_id
          ? `bk-${guideService.booking_id}`
          : `guide-${safeDayIndex}-${guideData.guide_id}-${guideData.entrytime || index}`,
        priceBreakdown: {
          basePrice: guideData.basePrice || 0,
          nightSurcharge: guideData.surcharge || 0,
          totalPrice: guideData.totalPrice || 0,
          nightHours: 0,
          dayHours: Number(guideData.hours) || 0
        },
        pax: {
          Adults: Number(guideData.adults) || 1,
          Children: Number(guideData.children) || 0
        },
        originalData: {
          ...guideData,
          booking_id: guideService.booking_id,
          dayIndex: safeDayIndex
        }
      };
    }).filter(section => section !== null);

    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));

    setTimeout(() => {
      isInitializingRef.current = false;
    }, 100);
  }, [dayIndex, bookingDate, date, guidespack, tourDates]);

  // Seed ONLY this day's package guides into AllServices (never wipe other days)
  const seedDayGuidesToRedux = useCallback((daySections) => {
    if (!daySections || daySections.length === 0) return;

    const currentServices = currentServicesRef.current || [];

    const servicesWithoutThisDay = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'guide') return true;
      if (!service.data || !Array.isArray(service.data)) return true;
      return !service.data.some((item) => item.dayIndex === dayIndex);
    });

    const newGuideServices = daySections.map((section) => {
      const guideData = section.originalData || {};
      const resolvedDayIndex = typeof guideData.dayIndex === 'number' ? guideData.dayIndex : dayIndex;

      const processedGuideData = {
        id: section.localId,
        guide_id: guideData.guide_id,
        guide_name: guideData.guide_name,
        image: guideData.image,
        dmc_Id: guideData.dmc_Id,
        Mode: guideData.Mode,
        entrypickup: guideData.entrypickup,
        entrytime: guideData.entrytime || section.pickUpTime || '',
        adults: Number(section.pax?.Adults ?? guideData.adults) || 1,
        children: Number(section.pax?.Children ?? guideData.children) || 0,
        hours: Number(section.hourlyPackage ?? guideData.hours) || 0,
        basePrice: Number(section.priceBreakdown?.basePrice ?? guideData.basePrice) || 0,
        surcharge: Number(section.priceBreakdown?.nightSurcharge ?? guideData.surcharge) || 0,
        totalPrice: Number(section.priceBreakdown?.totalPrice ?? guideData.totalPrice) || 0,
        pickupdate: guideData.pickupdate || section.bookingDate || guideData.bookingDate,
        bookingDate: section.bookingDate || guideData.bookingDate || bookingDate,
        dayIndex: resolvedDayIndex,
        Tax: guideData.Tax,
        Night_Start_Time: guideData.Night_Start_Time,
        Night_End_Time: guideData.Night_End_Time,
        city: guideData.city,
        country: guideData.country,
        languages: guideData.languages || [],
        experience: guideData.experience,
        bookingType: guideData.bookingType || 'enquiry'
      };

      const serviceObject = {
        type: 'guide',
        agent_id: agentId,
        tour_id: tourId,
        data: [processedGuideData],
        bookingType: guideData.bookingType || 'enquiry'
      };

      if (guideData.booking_id) {
        serviceObject.booking_id = guideData.booking_id;
      }

      return serviceObject;
    });

    dispatch(setAllServices([...servicesWithoutThisDay, ...newGuideServices]));
    hasDispatchedAllGuidesRef.current = true;
  }, [agentId, tourId, dispatch, dayIndex, bookingDate]);

  // Reset init flags when dayIndex changes (do NOT clear currentServicesRef)
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllGuidesRef.current = false;
    isInitializingRef.current = false;
  }, [dayIndex]);

  // Reset guide listing state when city changes or component mounts
  useEffect(() => {
    if (!selectedCity) {
      setIsGuideListingEnabled(false);
    }
  }, [selectedCity]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllGuidesRef.current = false;
      isInitializingRef.current = false;
    };
  }, []);

  // Initialize form sections when guidespack arrives
  useEffect(() => {
    if (hasInitializedRef.current) return;
    if (!guidespack || !Array.isArray(guidespack) || guidespack.length === 0) return;

    initializeFormSectionsFromGuidePack();
    hasInitializedRef.current = true;
  }, [guidespack, initializeFormSectionsFromGuidePack]);

  // After form hydrate from pack, seed this day's guides into AllServices once
  useEffect(() => {
    if (!hasInitializedRef.current || hasDispatchedAllGuidesRef.current) return;
    if (!guidespack || guidespack.length === 0) return;
    if (!formSections.some((s) => s.originalData)) return;

    seedDayGuidesToRedux(formSections.filter((s) => s.originalData));
  }, [formSections, guidespack, seedDayGuidesToRedux]);

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
    // Pack / edit hydrate: prefer live form values, fall back to originalData
    if (booking.originalData) {
      return {
        guide: booking.originalData,
        guideName: booking.originalData.guide_name,
        city: booking.originalData.city || searchParams?.location?.city || '',
        country: booking.originalData.country || searchParams?.location?.country || '',
        pickUpTime: booking.pickUpTime || booking.originalData.entrytime,
        pickUpTimeHour: booking.pickUpTimeHour,
        duration: booking.hourlyPackage || booking.originalData.hours,
        pax: booking.pax || {
          Adults: Number(booking.originalData.adults) || 1,
          Children: Number(booking.originalData.children) || 0
        },
        mode: currentMode,
        priceBreakdown: booking.priceBreakdown || {
          basePrice: Number(booking.originalData.basePrice) || 0,
          nightSurcharge: Number(booking.originalData.surcharge) || 0,
          totalPrice: Number(booking.originalData.totalPrice) || 0
        },
        image: booking.originalData.image,
        languages: booking.originalData.languages || [],
        experience: booking.originalData.experience || 'Not specified',
        bookingDate: booking.bookingDate || booking.originalData.bookingDate,
        booking_id: booking.originalData.booking_id
      };
    }

    // Fallback to finding data from Redux state (for new bookings)
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

  getBookingSummaryRef.current = getBookingSummary;

  const validateBookings = useCallback(() => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one guide booking.");
      return false;
    }
    
    const completeSections = formSections.filter(section => 
      section.guide && 
      section.pickUpTime && 
      section.hourlyPackage && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      return false;
    }
    
    setValidationError(null);
    return true;
  }, [formSections, setValidationError]);

  useEffect(() => {
    console.log('Guide Status:', status);
    console.log('Guides Data:', guides);
    console.log('Selected Guide (detailed):', selectedGuide);
    console.log('Search Params:', searchParams);
    console.log('GuideComponent - Received props:', { date, dayIndex, bookingDate });
  }, [status, guides, selectedGuide, searchParams, date, dayIndex, bookingDate]);

  // Upsert one complete section into AllServices (always write live form values)
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.guide || !updatedSection.pickUpTime || !updatedSection.hourlyPackage) {
      return;
    }
    if ((updatedSection.pax?.Adults || 0) + (updatedSection.pax?.Children || 0) <= 0) {
      return;
    }

    const currentServices = [...(currentServicesRef.current || [])];
    const sectionKey = updatedSection.localId
      || (updatedSection.originalData?.booking_id ? `bk-${updatedSection.originalData.booking_id}` : null)
      || `guide-${dayIndex}-${updatedSection.guide}-${updatedSection.pickUpTime}-${updatedSection.hourlyPackage}`;

    const summaryData = getBookingSummaryRef.current(updatedSection) || {};
    const selectedGuideDetails = guides.find((g) => g.id === updatedSection.guide) ||
      guides.find((g) => String(g.id) === String(updatedSection.guide));

    let bookingData;
    if (updatedSection.originalData) {
      bookingData = {
        ...updatedSection.originalData,
        fullName: updatedSection.originalData.fullName || "",
        email: updatedSection.originalData.email || "",
        phone: updatedSection.originalData.phone || "",
        countryCode: updatedSection.originalData.countryCode || "",
        address1: updatedSection.originalData.address1 || "",
        address2: updatedSection.originalData.address2 || "",
        state: updatedSection.originalData.state || "",
        zip: updatedSection.originalData.zip || "",
        specialRequests: updatedSection.originalData.specialRequests || "",
        id: sectionKey,
        guide_id: updatedSection.originalData.guide_id,
        guide_name: updatedSection.originalData.guide_name,
        entrypickup: updatedSection.originalData.entrypickup || updatedSection.originalData.city,
        entrytime: updatedSection.pickUpTime,
        adults: updatedSection.pax.Adults,
        children: updatedSection.pax.Children,
        hours: updatedSection.hourlyPackage,
        basePrice: updatedSection.priceBreakdown?.basePrice ?? updatedSection.originalData.basePrice ?? 0,
        surcharge: updatedSection.priceBreakdown?.nightSurcharge ?? updatedSection.originalData.surcharge ?? 0,
        totalPrice: updatedSection.priceBreakdown?.totalPrice ?? updatedSection.originalData.totalPrice ?? 0,
        bookingDate: updatedSection.bookingDate || bookingDate,
        pickupdate: updatedSection.bookingDate || bookingDate,
        dayIndex: dayIndex,
        Mode: updatedSection.originalData.Mode || currentMode,
        dmc_Id: updatedSection.originalData.dmc_Id || agentId,
        image: updatedSection.originalData.image,
        city: updatedSection.originalData.city,
        country: updatedSection.originalData.country,
        languages: updatedSection.originalData.languages || [],
        experience: updatedSection.originalData.experience,
        booking_id: updatedSection.originalData.booking_id
      };
    } else {
      bookingData = {
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: "",
        id: sectionKey,
        guide_id: updatedSection.guide,
        guide_name: summaryData.guideName,
        image: summaryData.image,
        dmc_Id: agentId,
        Mode: currentMode,
        entrypickup: summaryData.city,
        entrytime: updatedSection.pickUpTime,
        adults: updatedSection.pax.Adults,
        children: updatedSection.pax.Children,
        hours: updatedSection.hourlyPackage,
        basePrice: updatedSection.priceBreakdown?.basePrice || 0,
        surcharge: updatedSection.priceBreakdown?.nightSurcharge || 0,
        totalPrice: updatedSection.priceBreakdown?.totalPrice || 0,
        pickupdate: updatedSection.bookingDate || bookingDate,
        bookingDate: updatedSection.bookingDate || bookingDate,
        dayIndex: dayIndex,
        Tax: selectedGuideDetails?.tax_percentage,
        Night_Start_Time: selectedGuideDetails?.night_start_time,
        Night_End_Time: selectedGuideDetails?.night_end_time,
        city: summaryData.city,
        country: summaryData.country,
        languages: summaryData.languages,
        experience: summaryData.experience
      };
    }

    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'guide') return true;

      if (updatedSection.originalData?.booking_id && service.booking_id === updatedSection.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          item.id === sectionKey ||
          (item.dayIndex === dayIndex &&
            String(item.guide_id) === String(bookingData.guide_id) &&
            String(item.entrytime || '') === String(bookingData.entrytime || '') &&
            String(item.hours) === String(bookingData.hours))
        );
        if (matches) return false;
      }
      return true;
    });

    const newGuideService = {
      type: 'guide',
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData],
      bookingType: 'enquiry'
    };
    if (updatedSection.originalData?.booking_id) {
      newGuideService.booking_id = updatedSection.originalData.booking_id;
    }

    dispatch(setAllServices([...filteredServices, newGuideService]));
  }, [guides, currentMode, agentId, tourId, dayIndex, bookingDate, dispatch]);

  // Sync complete sections to AllServices on form changes (create + edit)
  useEffect(() => {
    if (formSections.length === 0 || isProcessingRef.current || isInitializingRef.current) return;

    const currentFormSectionsString = JSON.stringify(formSections.map(s => ({
      guide: s.guide,
      pickUpTime: s.pickUpTime,
      hourlyPackage: s.hourlyPackage,
      pax: s.pax,
      priceBreakdown: s.priceBreakdown,
      localId: s.localId
    })));
    const lastFormSectionsString = JSON.stringify(lastFormSectionsRef.current);

    if (currentFormSectionsString === lastFormSectionsString) {
      return;
    }

    lastFormSectionsRef.current = formSections.map(s => ({
      guide: s.guide,
      pickUpTime: s.pickUpTime,
      hourlyPackage: s.hourlyPackage,
      pax: s.pax,
      priceBreakdown: s.priceBreakdown,
      localId: s.localId
    }));

    formSections.forEach((section, index) => {
      const isComplete = (
        section.guide &&
        section.pickUpTime &&
        section.hourlyPackage &&
        ((section.pax?.Adults || 0) + (section.pax?.Children || 0) > 0)
      );
      if (!isComplete) return;
      if (!section.originalData && (!guides || guides.length === 0)) return;

      dispatchBookingUpdateToRedux(index, section);
    });
  }, [formSections, guides, dayIndex, dispatchBookingUpdateToRedux]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    const newSection = { 
      ...defaultSection, 
      bookingDate: bookingDate,
      localId: `guide-new-${dayIndex}-${Date.now()}-${newIndex}`,
      originalData: null
    };
    setFormSections([...formSections, newSection]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    const sectionToRemove = formSections[indexToRemove];
    if (!sectionToRemove) return;

    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(
      expandedSections
        .filter((index) => index !== indexToRemove)
        .map((index) => (index > indexToRemove ? index - 1 : index))
    );

    const sectionKey = sectionToRemove.localId
      || (sectionToRemove.originalData?.booking_id ? `bk-${sectionToRemove.originalData.booking_id}` : null);
    const hasIdentity = sectionKey || sectionToRemove.guide || sectionToRemove.originalData?.booking_id;
    if (!hasIdentity) return;

    const currentServices = [...(currentServicesRef.current || [])];
    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'guide') return true;

      if (sectionToRemove.originalData?.booking_id && service.booking_id === sectionToRemove.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          (sectionKey && item.id === sectionKey) ||
          (sectionToRemove.guide &&
            item.dayIndex === dayIndex &&
            String(item.guide_id) === String(sectionToRemove.guide) &&
            String(item.entrytime || '') === String(sectionToRemove.pickUpTime || '') &&
            String(item.hours) === String(sectionToRemove.hourlyPackage))
        );
        if (matches) return false;
      }
      return true;
    });

    if (filteredServices.length !== currentServices.length) {
      dispatch(setAllServices(filteredServices));
    }
  };

  const toggleSectionExpand = (index) => {
    if (expandedSections.includes(index)) {
      setExpandedSections(expandedSections.filter(i => i !== index));
    } else {
      setExpandedSections([...expandedSections, index]);
    }
  };

  // For packageData bookings: load guide details (mode always dmc) before editing time/package
  const ensurePackageGuideDetails = useCallback((section) => {
    if (!section?.originalData) return;

    const od = section.originalData;
    const guideId = od.guide_id || section.guide;
    const city = od.city || od.entrypickup;
    const dateVal = section.bookingDate || od.bookingDate || bookingDate;
    const dmcId = od.dmc_Id || od.dmc_id;

    if (!guideId || !city || !dateVal) return;

    const alreadyLoaded =
      selectedGuide &&
      (String(selectedGuide.id) === String(guideId) || String(selectedGuide.guide_id) === String(guideId)) &&
      selectedGuide.prices;

    if (alreadyLoaded) return;

    dispatch(fetchGuideDetails({
      guide_id: guideId,
      mode: 'dmc',
      dmc_id: dmcId,
      pickup: city,
      date: dateVal
    }));
  }, [dispatch, selectedGuide, bookingDate]);

  const recalculatePriceBreakdown = useCallback((section, pickUpTimeHour, packageHours) => {
    const hours = Number(packageHours);
    const startHour = Number(pickUpTimeHour);
    if (!selectedGuide?.prices || Number.isNaN(hours) || Number.isNaN(startHour)) {
      return section.priceBreakdown || {
        basePrice: Number(section.originalData?.basePrice) || 0,
        nightSurcharge: Number(section.originalData?.surcharge) || 0,
        totalPrice: Number(section.originalData?.totalPrice) || 0,
        nightHours: 0,
        dayHours: hours || 0
      };
    }

    const prices = selectedGuide.prices;
    const basePrice = (() => {
      switch (hours) {
        case 1: return prices.dmc_hourly_price || prices.travclicks_hourly_price || 0;
        case 2: return prices.dmc_two_hour_price || prices.travclicks_two_hour_price || 0;
        case 4: return prices.dmc_four_hour_price || prices.travclicks_four_hour_price || 0;
        case 6: return prices.dmc_six_hour_price || prices.travclicks_six_hour_price || 0;
        case 8: return prices.dmc_eight_hour_price || prices.travclicks_eight_hour_price || 0;
        case 10: return prices.dmc_ten_hour_price || prices.travclicks_ten_hour_price || 0;
        case 12: return prices.dmc_twelve_hour_price || prices.travclicks_twelve_hour_price || 0;
        default: return 0;
      }
    })();

    const nightStart = parsePickUpTimeToHour(
      selectedGuide.night_start_time || section.originalData?.Night_Start_Time || '21:00'
    ) ?? 21;
    const nightEndRaw = selectedGuide.night_end_time || section.originalData?.Night_End_Time || '00:00';
    const nightEnd = parsePickUpTimeToHour(nightEndRaw) ?? 0;
    const nightEndAdj = String(nightEndRaw).includes(':') && parseInt(String(nightEndRaw).split(':')[1], 10) > 0
      ? (nightEnd + 1) % 24
      : nightEnd;

    const isNightHour = (hour) => {
      if (nightStart < nightEndAdj) return hour >= nightStart && hour < nightEndAdj;
      return hour >= nightStart || hour < nightEndAdj;
    };

    let nightHours = 0;
    let dayHours = 0;
    for (let i = 0; i < hours; i++) {
      if (isNightHour((startHour + i) % 24)) nightHours++;
      else dayHours++;
    }

    const nightSurcharge = nightHours > 0 ? (prices.dmc_night_surcharge || 0) * nightHours : 0;
    return {
      basePrice,
      nightSurcharge,
      totalPrice: basePrice + nightSurcharge,
      nightHours,
      dayHours
    };
  }, [selectedGuide]);

  const handleInputChange = (sectionIndex, field, value) => {
    if (isInitializingRef.current) return;

    const currentSection = formSections[sectionIndex];
    // PackageData bookings: guide identity is locked (city search must not replace it)
    if (field === 'guide' && currentSection?.originalData) {
      return;
    }

    const newFormSections = [...formSections];
    
    if (field === 'guide') {
      newFormSections[sectionIndex] = {
        ...defaultSection,
        guide: value,
        bookingDate: newFormSections[sectionIndex].bookingDate,
        localId: newFormSections[sectionIndex].localId || `guide-new-${dayIndex}-${sectionIndex}`,
        pax: newFormSections[sectionIndex].pax,
        originalData: null
      };
    } else if (field === 'pickUpTime') {
      const nextHour = value.hourValue;
      const nextSection = {
        ...newFormSections[sectionIndex],
        pickUpTime: value.value,
        pickUpTimeHour: nextHour,
        bookingDate: newFormSections[sectionIndex].bookingDate || bookingDate
      };
      if (nextSection.hourlyPackage !== '' && nextSection.hourlyPackage != null) {
        nextSection.priceBreakdown = recalculatePriceBreakdown(
          nextSection,
          nextHour,
          nextSection.hourlyPackage
        );
      }
      newFormSections[sectionIndex] = nextSection;
    } else if (field === 'hourlyPackage') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        hourlyPackage: value.value,
        priceBreakdown: value.priceBreakdown || recalculatePriceBreakdown(
          newFormSections[sectionIndex],
          newFormSections[sectionIndex].pickUpTimeHour,
          value.value
        ),
        bookingDate: newFormSections[sectionIndex].bookingDate || bookingDate
      };
    } else if (field === 'pax') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pax: value,
        bookingDate: newFormSections[sectionIndex].bookingDate || bookingDate
      };
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: newFormSections[sectionIndex].bookingDate || bookingDate
      };
    }
    
    setFormSections(newFormSections);
    
    const updatedSection = newFormSections[sectionIndex];
    const isComplete = 
      updatedSection.guide && 
      updatedSection.pickUpTime && 
      updatedSection.hourlyPackage !== '' &&
      updatedSection.hourlyPackage != null &&
      (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
      
    if (isComplete) {
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

  // Handle city selection
  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    setSelectedCity(city);
    
    if (city) {
      setCityError(false);
      // Disable guide listing until API call is successful
      setIsGuideListingEnabled(false);
      
      // Dispatch fetchGuides API call
      const resolvedCountry =
        (typeof country === "string" && country) ||
        country?.name ||
        country?.label ||
        city?.country ||
        "";
      dispatch(
        fetchGuides({
          city: city.address || city.name,
          country: resolvedCountry,
          date: bookingDate,
          tour_id: numericTourId,
        })
      )
        .then((result) => {
          console.log("fetchGuides API result:", result);
          if (result.error) {
            console.error("fetchGuides API Error:", result.error);
            setIsGuideListingEnabled(false);
          } else {
            console.log("fetchGuides API Success - enabling guide listing");
            setIsGuideListingEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchGuides:", error);
          setIsGuideListingEnabled(false);
        });
    } else {
      // If no city selected, disable guide listing
      setIsGuideListingEnabled(false);
    }
  };

  // if (!guides || guides.length === 0) {
  //   return (
  //     <Container maxWidth="xl">
  //       <Card 
  //         elevation={3}
  //         sx={{
  //           borderRadius: 3,
  //           background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
  //           color: 'white',
  //           mb: 2,
  //           mx: 'auto',
  //         }}
  //       >
  //         <CardContent sx={{ py: 2, textAlign: 'center' }}>
  //           <PersonIcon sx={{ fontSize: 64, color: '#FFD700', mb: 2 }} />
  //           <Typography variant="h6" color="white">
  //             Please search for guides first
  //           </Typography>
  //         </CardContent>
  //       </Card>
  //     </Container>
  //   );
  // }

  return (
    <Container maxWidth="xl" sx={{ mt: 3, mb: 4 }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 2,
          background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
          color: 'white',
          mb: 1.5,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ 
          py: { xs: 1, sm: 0.8, md: 0.5 },
          px: { xs: 1.5, sm: 2, md: 2 },
          height: { xs: 'auto', sm: '52px' },
          minHeight: { xs: '60px', sm: '52px' }
        }}>
          <Box 
            display="flex" 
            alignItems="center" 
            justifyContent="space-between"
            flexDirection={{ xs: 'column', sm: 'row' }}
            gap={{ xs: 1, sm: 0 }}
          >
            <Box 
              display="flex" 
              alignItems="center"
              flexDirection={{ xs: 'column', sm: 'row' }}
              textAlign={{ xs: 'center', sm: 'left' }}
              gap={{ xs: 1, sm: 0 }}
            >
              <AssistantIcon sx={{ 
                mr: { xs: 0, sm: 1.5 }, 
                mb: { xs: 0.5, sm: 0 },
                fontSize: { xs: 32, sm: 28 }, 
                color: '#FFD700' 
              }} />
              <Box>
                <Typography 
                  variant="h6" 
                  fontWeight="600" 
                  sx={{ 
                    color: 'white', 
                    fontSize: { xs: '0.85rem', sm: '0.9rem', md: '0.9rem' },
                    lineHeight: 1.2
                  }}
                >
                  Book Tour Guide Services
                </Typography>
                <Typography 
                  variant="body2" 
                  sx={{ 
                    color: 'rgba(255, 255, 255, 0.8)', 
                    fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.7rem' },
                    lineHeight: 1.3,
                    display: { xs: 'none', sm: 'block' }
                  }}
                >
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
                border: '1px solid rgba(255, 255, 255, 0.3)',
                fontSize: { xs: '0.7rem', sm: '0.75rem' },
                height: { xs: '28px', sm: '20px' },
                minWidth: { xs: '80px', sm: 'auto' },
                mt: { xs: 0.5, sm: 0 }
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
              Guide booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={1.5}>
        {formSections.map((section, sectionIndex) => {
          const selectedGuideDetails = getSelectedGuide(section.guide);
          const completionStatus = getCompletionStatus(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          const outOfTourDates = isBookingOutOfTourDates(section);
          console.log("sectionIndexguide1323", section);
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
                  borderRadius: 2,
                  border: outOfTourDates ? '1px solid #e53935' : `1px solid ${alpha('#2196f3', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 4px 12px ${alpha('#e53935', 0.15)}`
                      : `0 4px 12px ${alpha('#2196f3', 0.15)}`,
                    transform: 'translateY(-1px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 1.5,
                    bgcolor: alpha('#2196f3', 0.05),
                    borderBottom: `1px solid ${alpha('#2196f3', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#2196f3',
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
                      {selectedGuideDetails && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 14 }} />}
                          label={selectedGuideDetails.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#2196f3',
                            color: '#2196f3',
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
                          size="medium"
                          onClick={() => handleOpenModal(sectionIndex)}
                          disabled={!section.guide}
                          startIcon={<VisibilityIcon />}
                          sx={{
                            borderRadius: 1.5,
                            px: 3,
                            py: 0.8,
                            fontSize: '0.8rem',
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
                      {(tourStatus !== "Confirmed" && tourStatus !== "Definite" && tourStatus !== "Actual") && (
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
                      )}
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedGuideDetails && (
                    <Box sx={{ p: 1.5 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
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
                            width: 50, 
                            height: 50, 
                            borderRadius: 1.5,
                            objectFit: 'cover',
                            border: `1px solid ${alpha('#2196f3', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 0.5, fontSize: '0.9rem' }}>
                            {section.originalData?.guide_name || 
                             selectedGuide?.guide_name || 
                             selectedGuideDetails.guide_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 1.5, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 14 }} />}
                                label={`${section.pax.Adults + section.pax.Children} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                            {section.pickUpTime && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 14 }} />}
                                label={section.pickUpTime}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                            {section.hourlyPackage && (
                              <Chip 
                                icon={<BusinessCenterIcon sx={{ fontSize: 14 }} />}
                                label={`${section.hourlyPackage}h Package`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3',
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
                        {/* City Selection — only for new bookings (not packageData) */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <LocationOnIcon sx={{ mr: 0.8, color: '#1976d2', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={section.originalData || !isCityEnabled ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                City
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                              {section.originalData ? (
                                <Typography variant="body2" color="text.secondary" sx={{ px: 1 }}>
                                  {section.originalData.city || section.originalData.entrypickup || 'Package guide'}
                                </Typography>
                              ) : (
                                <PortCity
                                  onLocationSelect={handleCitySelect}
                                  hasError={cityError}
                                  setError={setCityError}
                                  disabled={!isCityEnabled}
                                />
                              )}
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guide Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <PersonIcon sx={{ mr: 0.8, color: '#2196f3', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600" 
                                color={section.originalData || !isGuideListingEnabled ? "text.disabled" : "text.primary"} 
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Guide
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <GuideListing 
                                value={section.guide}
                                selectedGuideName={section.originalData?.guide_name || section?.guide_name}
                                onChange={(field, value) => handleInputChange(sectionIndex, field, value)}
                                disabled={!!section.originalData || status === 'loading' || !isGuideListingEnabled}
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
                                color={!section.guide ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
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
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <AccessTimeIcon sx={{ mr: 0.8, color: '#ff9800', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.guide ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Pickup Time
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <TimeSelection
                                value={section.pickUpTime}
                                onChange={(e) => handleInputChange(sectionIndex, 'pickUpTime', e)}
                                disabled={!section.guide || status === 'loading'}
                                formSection={section}
                                onBeforeOpen={() => ensurePackageGuideDetails(section)}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Package Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <BusinessCenterIcon sx={{ mr: 0.8, color: '#9c27b0', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.guide || !section.pickUpTime ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Package
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <PackageSelection
                                value={section.hourlyPackage}
                                onChange={(e) => handleInputChange(sectionIndex, 'hourlyPackage', e)}
                                disabled={!section.guide || !section.pickUpTime || status === 'loading'}
                                pickUpTime={section.pickUpTime ? { value: section.pickUpTime, hourValue: section.pickUpTimeHour } : null}
                                bookingDate={section.bookingDate}
                                formSection={section}
                                onBeforeOpen={() => ensurePackageGuideDetails(section)}
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
              border: `1px dashed ${alpha('#2196f3', 0.4)}`,
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
                gap: 1.5
              }}>
                <AddIcon sx={{ fontSize: 28, color: '#2196f3' }} />
                <Typography variant="subtitle1" color="#2196f3" fontWeight={600} sx={{ fontSize: '0.9rem' }}>
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