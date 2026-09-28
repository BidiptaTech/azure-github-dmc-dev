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
import { shallowEqual } from 'react-redux';

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
  const searchParams = useSelector((state) => state.attractions.searchParams, shallowEqual);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);
  const tourStatus = useSelector((state) => state.tourPackages.tourStatus);
  
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

  const [formSections, setFormSections] = useState([{
    ...initialFormState,
    bookingDate: bookingDate,
    localId: `attr-new-${dayIndex}-0`
  }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [cityError, setCityError] = useState(false);
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isAttractionListingEnabled, setIsAttractionListingEnabled] = useState(false);
  console.log("selectedCity", selectedCity);
  const country = useSelector((state) => state.tourPackages.searchCriteria.country);
  const tour = useSelector((state) => state.hotels.tourdetails, shallowEqual);
  console.log("tour", tour);
  // Refs to prevent infinite loops / stale AllServices merges
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllAttractionsRef = useRef(false);
  const currentServicesRef = useRef([]);
  const isInitializingRef = useRef(false);
  const hasDataConflictsRef = useRef(false);
  const isProcessingRef = useRef(false);
  const lastFormSectionsRef = useRef([]);
  const getBookingSummaryRef = useRef(() => null);
  console.log("hasDataConflictsRef", hasDataConflictsRef);

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
      isInitializingRef.current = false; // Ensure flag is reset even when no data
      return;
    }

    console.log('Initializing form sections from attractionspack:', attractionspack);
    isInitializingRef.current = true;
    
    // Check for data conflicts that could cause infinite loops (for logging only)
    const hasDataConflicts = attractionspack.some(attractionService => {
      const attractionData = attractionService.data?.[0];
      if (!attractionData) return false;
      
      // Check if adult/child counts from attractionspack don't match search form data
      const searchAdults = searchParams?.adults || 0;
      const searchChildren = searchParams?.children || 0;
      const attractionAdults = Number(attractionData.adultCount) || 0;
      const attractionChildren = Number(attractionData.childCount) || 0;
      
      const hasMismatch = (searchAdults !== attractionAdults) || (searchChildren !== attractionChildren);
      
      if (hasMismatch) {
        console.warn('Data mismatch detected but proceeding with initialization:', {
          searchForm: { adults: searchAdults, children: searchChildren },
          attractionData: { adults: attractionAdults, children: attractionChildren },
          attractionId: attractionData.AttractionId
        });
      }
      
      return hasMismatch;
    });
    
    // Store the conflict status for use in auto-dispatch logic
    hasDataConflictsRef.current = hasDataConflicts;
    if (hasDataConflicts) {
      console.log('Data conflicts detected - will proceed with initialization but skip auto-dispatch');
    }

    // Filter attractions - show all for first dayIndex, match by bookingDate for other days
   
    const dayAttractions = attractionspack.filter((attractionService, index) => {
      const attractionData = attractionService.data?.[0];
      

      if (!attractionData) {
        console.log(`Attraction ${index}: No data, skipping`);
        return false;
      }

      // For first dayIndex (dayIndex === 0), show attractions that either match current bookingDate OR don't match any tour dates
      if (dayIndex === 0) {
        
        
        // Show if it matches current bookingDate OR doesn't match any tour dates
        const matchesCurrentDate = attractionData.bookingDate === bookingDate;
        const notInTourDates = !tourDates.includes(attractionData.bookingDate);
        const shouldShow = matchesCurrentDate || notInTourDates;
        
       
        return shouldShow;
      }

      // For other dayIndexes, match by bookingDate
      const matchesBookingDate = attractionData.bookingDate === bookingDate;
    
      return matchesBookingDate;
    });

    if (dayAttractions.length === 0) {
     
      isInitializingRef.current = false; // Ensure flag is reset when no attractions found
      return;
    }

   

  // Convert attraction data to form sections for current day only
    const newFormSections = dayAttractions.map((attractionService, index) => {
      const attractionData = attractionService.data[0];
      const resolvedDayIndex = Array.isArray(tourDates) && attractionData.bookingDate
        ? Math.max(0, tourDates.indexOf(attractionData.bookingDate))
        : dayIndex;
      
      const formSection = {
        attraction: String(attractionData.AttractionId),
        pax: {
          Adults: Number(attractionData.adultCount) || 0,
          Children: Number(attractionData.childCount) || 0,
          Seniors: Number(attractionData.seniorCount) || 0
        },
        timeSlot: attractionData.visitTime || '',
        ticketType: String(attractionData.ticketId),
        priceType: attractionData.nri || 'residential',
        type: attractionService.type || 'attraction',
        bookingDate: attractionData.bookingDate || bookingDate,
        localId: attractionService.booking_id
          ? `bk-${attractionService.booking_id}`
          : `attr-${resolvedDayIndex}-${attractionData.AttractionId}-${attractionData.ticketId}-${attractionData.visitTime || index}`,
        originalData: {
          ...attractionData,
          booking_id: attractionService.booking_id,
          type: attractionService.type || 'attraction',
          dayIndex: typeof attractionData.dayIndex === 'number' ? attractionData.dayIndex : resolvedDayIndex
        }
      };
      
      return formSection;
    });

    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));
    
    setTimeout(() => {
      isInitializingRef.current = false;
    }, 100);
  }, [dayIndex, bookingDate, searchParams, attractionspack, tourDates]);

  // Seed ONLY this day's package attractions into AllServices (never wipe other days)
  const seedDayAttractionsToRedux = useCallback((daySections) => {
    if (!daySections || daySections.length === 0) return;

    const currentServices = currentServicesRef.current || [];

    // Drop only attraction rows that belong to this dayIndex
    const servicesWithoutThisDay = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'attraction' && type !== 'attraction_package') return true;
      if (!service.data || !Array.isArray(service.data)) return true;
      return !service.data.some((item) => item.dayIndex === dayIndex);
    });

    const newAttractionServices = daySections.map((section) => {
      const attractionData = section.originalData || {};
      const resolvedDayIndex = typeof attractionData.dayIndex === 'number' ? attractionData.dayIndex : dayIndex;

      const processedAttractionData = {
        id: section.localId,
        AttractionId: attractionData.AttractionId,
        AttractionName: attractionData.AttractionName,
        location: attractionData.location || attractionData.city || '',
        city: attractionData.city || '',
        country: attractionData.country || '',
        visitTime: attractionData.visitTime || section.timeSlot || '',
        ticketId: attractionData.ticketId ?? section.ticketType,
        ticketName: attractionData.ticketName,
        adultCount: Number(section.pax?.Adults ?? attractionData.adultCount) || 0,
        childCount: Number(section.pax?.Children ?? attractionData.childCount) || 0,
        seniorCount: Number(section.pax?.Seniors ?? attractionData.seniorCount) || 0,
        ticket_details: attractionData.ticket_details || {
          adult_price: 0,
          child_price: 0,
          senior_price: 0,
          description: ''
        },
        nri: section.priceType || attractionData.nri || 'residential',
        totalPrice: Number(attractionData.totalPrice) || Number(attractionData.price) || 0,
        image: attractionData.image || '',
        mode: attractionData.mode || 'dmc',
        dmc_id: attractionData.dmc_id || '',
        bookingDate: section.bookingDate || attractionData.bookingDate || bookingDate,
        dayIndex: resolvedDayIndex,
        bookingType: attractionData.bookingType || 'enquiry',
        package_type: attractionData.package_type || (section.type === 'attraction_package' ? 1 : 0),
        package_attraction_id: attractionData.package_attraction_id || null,
        ...(attractionData.package_details && { package_details: attractionData.package_details })
      };

      const isPackageBooking = section.type === 'attraction_package' ||
        processedAttractionData.package_type === 1 ||
        (typeof processedAttractionData.ticketId === 'string' && String(processedAttractionData.ticketId).startsWith('pkg_'));

      const serviceObject = {
        type: isPackageBooking ? 'attraction_package' : 'attraction',
        agent_id: agentId,
        tour_id: tourId,
        data: [processedAttractionData],
        bookingType: 'enquiry'
      };

      if (attractionData.booking_id) {
        serviceObject.booking_id = attractionData.booking_id;
      }

      return serviceObject;
    });

    dispatch(setAllServices([...servicesWithoutThisDay, ...newAttractionServices]));
    hasDispatchedAllAttractionsRef.current = true;
  }, [agentId, tourId, dispatch, dayIndex, bookingDate]);

  // Reset init flags when dayIndex changes (each day instance is separate mount usually)
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllAttractionsRef.current = false;
    isInitializingRef.current = false;
    hasDataConflictsRef.current = false;
  }, [dayIndex]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllAttractionsRef.current = false;
      isInitializingRef.current = false;
      hasDataConflictsRef.current = false;
    };
  }, []);

  // Initialize form sections + seed this day once when pack arrives
  useEffect(() => {
    if (hasInitializedRef.current) return;
    if (!attractionspack || !Array.isArray(attractionspack) || attractionspack.length === 0) return;

    initializeFormSectionsFromAttractionPack();
    hasInitializedRef.current = true;
  }, [attractionspack, initializeFormSectionsFromAttractionPack]);

  // After form hydrate from pack, seed this day's attractions into AllServices once
  useEffect(() => {
    if (!hasInitializedRef.current || hasDispatchedAllAttractionsRef.current) return;
    if (!attractionspack || attractionspack.length === 0) return;
    if (!formSections.some((s) => s.originalData)) return;

    seedDayAttractionsToRedux(formSections.filter((s) => s.originalData));
  }, [formSections, attractionspack, seedDayAttractionsToRedux]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    const newSection = { 
      ...initialFormState, 
      bookingDate: bookingDate,
      localId: `attr-new-${dayIndex}-${Date.now()}-${newIndex}`,
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
    const hasIdentity = sectionKey || sectionToRemove.attraction || sectionToRemove.originalData?.booking_id;
    if (!hasIdentity) return;

    const currentServices = [...(currentServicesRef.current || [])];
    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'attraction' && type !== 'attraction_package') return true;

      if (sectionToRemove.originalData?.booking_id && service.booking_id === sectionToRemove.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          (sectionKey && item.id === sectionKey) ||
          (sectionToRemove.attraction &&
            item.dayIndex === dayIndex &&
            String(item.AttractionId) === String(sectionToRemove.attraction) &&
            String(item.visitTime || '') === String(sectionToRemove.timeSlot || '') &&
            String(item.ticketId) === String(sectionToRemove.ticketType))
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

  // Upsert one complete section into AllServices (always write live form values)
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.attraction || !updatedSection.timeSlot || !updatedSection.ticketType) {
      return;
    }
    if ((updatedSection.pax?.Adults || 0) + (updatedSection.pax?.Children || 0) + (updatedSection.pax?.Seniors || 0) <= 0) {
      return;
    }

    const currentServices = [...(currentServicesRef.current || [])];
    const sectionKey = updatedSection.localId
      || (updatedSection.originalData?.booking_id ? `bk-${updatedSection.originalData.booking_id}` : null)
      || `attr-${dayIndex}-${updatedSection.attraction}-${updatedSection.timeSlot}-${updatedSection.ticketType}`;

    let bookingData;
    let serviceType = updatedSection.type === 'attraction_package' ? 'attraction_package' : 'attraction';

    if (updatedSection.originalData) {
      // Preserve identity/pricing from pack, but apply live UI edits
      const summaryData = (() => {
        try { return getBookingSummaryRef.current(updatedSection); } catch { return null; }
      })();

      const adultPrice = summaryData?.adultPrice ?? updatedSection.originalData.ticket_details?.adult_price ?? 0;
      const childPrice = summaryData?.childPrice ?? updatedSection.originalData.ticket_details?.child_price ?? 0;
      const seniorPrice = summaryData?.seniorPrice ?? updatedSection.originalData.ticket_details?.senior_price ?? 0;
      const totalPrice = summaryData
        ? (adultPrice * updatedSection.pax.Adults) + (childPrice * updatedSection.pax.Children) + (seniorPrice * updatedSection.pax.Seniors)
        : Number(updatedSection.originalData.totalPrice) || 0;

      bookingData = {
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
        AttractionId: updatedSection.attraction || updatedSection.originalData.AttractionId,
        AttractionName: summaryData?.attraction || updatedSection.originalData.AttractionName,
        location: summaryData?.location || updatedSection.originalData.location,
        city: summaryData?.city || updatedSection.originalData.city,
        country: summaryData?.country || updatedSection.originalData.country,
        visitTime: updatedSection.timeSlot,
        ticketId: updatedSection.ticketType,
        ticketName: summaryData?.ticketType || updatedSection.originalData.ticketName,
        adultCount: updatedSection.pax.Adults,
        childCount: updatedSection.pax.Children,
        seniorCount: updatedSection.pax.Seniors,
        ticket_details: summaryData ? {
          adult_price: adultPrice,
          child_price: childPrice,
          senior_price: seniorPrice,
          description: summaryData.ticketDescription || ''
        } : (updatedSection.originalData.ticket_details || {}),
        nri: updatedSection.priceType || updatedSection.originalData.nri || 'residential',
        totalPrice,
        image: summaryData?.image || updatedSection.originalData.image || '',
        mode: updatedSection.originalData.mode || currentMode,
        dmc_id: updatedSection.originalData.dmc_id || agentId,
        bookingDate: updatedSection.bookingDate || bookingDate,
        dayIndex: dayIndex,
        bookingType: "enquiry",
        booking_id: updatedSection.originalData.booking_id,
        package_type: updatedSection.originalData.package_type || (summaryData?.type === 'attraction_package' ? 1 : 0),
        package_attraction_id: updatedSection.originalData.package_attraction_id || null,
        ...(updatedSection.originalData.package_details && { package_details: updatedSection.originalData.package_details })
      };
      serviceType = bookingData.package_type === 1 || updatedSection.type === 'attraction_package'
        ? 'attraction_package'
        : 'attraction';
    } else {
      const summaryData = getBookingSummaryRef.current(updatedSection);
      if (!summaryData) return;
      const adultTotal = summaryData.adultPrice * updatedSection.pax.Adults;
      const childTotal = summaryData.childPrice * updatedSection.pax.Children;
      const seniorTotal = summaryData.seniorPrice * updatedSection.pax.Seniors;
      const totalPrice = adultTotal + childTotal + seniorTotal;

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
        totalPrice,
        image: summaryData.image,
        mode: currentMode,
        dmc_id: agentId,
        bookingDate: updatedSection.bookingDate || bookingDate,
        dayIndex: dayIndex,
        bookingType: "enquiry",
        package_type: summaryData.type === 'attraction_package' ? 1 : 0,
        package_attraction_id: summaryData.type === 'attraction_package' ? summaryData.packageDetails?.package_id : null,
        ...(summaryData.type === 'attraction_package' && summaryData.packageDetails && { package_details: summaryData.packageDetails })
      };
      serviceType = bookingData.package_type === 1 ? 'attraction_package' : 'attraction';
    }

    // Remove matching service (by booking_id, local id, or AttractionId+dayIndex+visitTime)
    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'attraction' && type !== 'attraction_package') return true;

      if (updatedSection.originalData?.booking_id && service.booking_id === updatedSection.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          item.id === sectionKey ||
          (item.dayIndex === dayIndex &&
            String(item.AttractionId) === String(bookingData.AttractionId) &&
            String(item.visitTime || '') === String(bookingData.visitTime || '') &&
            String(item.ticketId) === String(bookingData.ticketId))
        );
        if (matches) return false;
      }
      return true;
    });

    const newAttractionService = {
      type: serviceType,
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData],
      bookingType: "enquiry"
    };
    if (updatedSection.originalData?.booking_id) {
      newAttractionService.booking_id = updatedSection.originalData.booking_id;
    }

    dispatch(setAllServices([...filteredServices, newAttractionService]));
  }, [currentMode, agentId, tourId, dayIndex, bookingDate, dispatch]);

  const handleInputChange = (sectionIndex, field, value) => {
    if (isInitializingRef.current) return;

    const newFormSections = [...formSections];

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

        const updatedSection = newFormSections[sectionIndex];
        const isComplete =
          updatedSection.attraction &&
          updatedSection.timeSlot &&
          updatedSection.ticketType &&
          (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);

        if (isComplete) {
          dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
        }
      }
    } else if (field === 'attraction') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        timeSlot: '',
        bookingDate: bookingDate
      };
      setFormSections(newFormSections);
    } else if (field === 'ticketType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        ticketType: value.ticketId,
        priceType: value.priceType,
        type: value.type || 'attraction',
        bookingDate: bookingDate
      };
      setFormSections(newFormSections);

      const updatedSection = newFormSections[sectionIndex];
      const isComplete =
        updatedSection.attraction &&
        updatedSection.timeSlot &&
        updatedSection.ticketType &&
        (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);

      if (isComplete) {
        dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
      }
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: bookingDate
      };
      setFormSections(newFormSections);

      const updatedSection = newFormSections[sectionIndex];
      const isComplete =
        updatedSection.attraction &&
        updatedSection.timeSlot &&
        updatedSection.ticketType &&
        (updatedSection.pax.Adults + updatedSection.pax.Children + updatedSection.pax.Seniors > 0);

      if (isComplete) {
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
    // Pack / edit hydrate: prefer live form values, fall back to originalData
    if (booking.originalData) {
      const isPackageBooking =
        booking.originalData.package_type === 1 ||
        booking.type === 'attraction_package' ||
        (typeof booking.ticketType === 'string' && booking.ticketType?.startsWith('pkg_')) ||
        (typeof booking.originalData.ticketId === 'string' && booking.originalData.ticketId?.startsWith('pkg_'));

      const liveAttraction = booking.attraction
        ? attractions.find((a) => String(a.id) === String(booking.attraction))
        : null;

      return {
        attraction: liveAttraction?.name || booking.originalData.AttractionName || 'Unknown Attraction',
        location: liveAttraction?.location || booking.originalData.location || booking.originalData.city || '',
        country: liveAttraction?.country || booking.originalData.country || '',
        city: liveAttraction?.city || booking.originalData.city || '',
        image: liveAttraction?.image || booking.originalData.image || '',
        description: liveAttraction?.description || booking.originalData.description || '',
        pax: {
          Adults: Number(booking.pax?.Adults ?? booking.originalData.adultCount) || 0,
          Children: Number(booking.pax?.Children ?? booking.originalData.childCount) || 0,
          Seniors: Number(booking.pax?.Seniors ?? booking.originalData.seniorCount) || 0
        },
        timeSlot: booking.timeSlot || booking.originalData.visitTime || '',
        ticketType: booking.originalData.ticketName || '',
        ticketDescription: booking.originalData.ticket_details?.description || 'No description available',
        adultPrice: Number(booking.originalData.ticket_details?.adult_price) || 0,
        childPrice: Number(booking.originalData.ticket_details?.child_price) || 0,
        seniorPrice: Number(booking.originalData.ticket_details?.senior_price) || 0,
        openingHours: booking.originalData.openingHours || '',
        terms: booking.originalData.terms || '',
        remarks: booking.originalData.remarks || '',
        mode: booking.originalData.mode || 'dmc',
        address: booking.originalData.address || '',
        category: booking.originalData.category || '',
        duration: booking.originalData.duration || '',
        cancellation_policy: booking.originalData.cancellation_policy || '',
        inclusions: booking.originalData.inclusions || '',
        exclusions: booking.originalData.exclusions || '',
        tax_percentage: booking.originalData.ticket_details?.tax_percentage || 0,
        tax_amount: booking.originalData.ticket_details?.tax_amount || 0,
        currency: booking.originalData.currency || 'SGD',
        priceType: booking.priceType || booking.originalData.nri || 'residential',
        booking_id: booking.originalData.booking_id,
        type: isPackageBooking ? 'attraction_package' : 'attraction',
        packageAttractions: booking.originalData.package_details?.package_attractions || null,
        packageDescription: booking.originalData.package_details?.package_description || null,
        packageDetails: booking.originalData.package_details || null,
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

  getBookingSummaryRef.current = getBookingSummary;

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

  // Sync complete sections to AllServices on form changes (create + edit)
  useEffect(() => {
    if (formSections.length === 0 || isProcessingRef.current || isInitializingRef.current) return;

    const currentFormSectionsString = JSON.stringify(formSections.map(s => ({
      attraction: s.attraction,
      timeSlot: s.timeSlot,
      ticketType: s.ticketType,
      pax: s.pax,
      localId: s.localId
    })));
    const lastFormSectionsString = JSON.stringify(lastFormSectionsRef.current);

    if (currentFormSectionsString === lastFormSectionsString) {
      return;
    }

    lastFormSectionsRef.current = formSections.map(s => ({
      attraction: s.attraction,
      timeSlot: s.timeSlot,
      ticketType: s.ticketType,
      pax: s.pax,
      localId: s.localId
    }));

    formSections.forEach((section, index) => {
      const isComplete = (
        section.attraction &&
        section.timeSlot &&
        section.ticketType &&
        ((section.pax?.Adults || 0) + (section.pax?.Children || 0) + (section.pax?.Seniors || 0) > 0)
      );
      if (!isComplete) return;
      if (!section.originalData && (!attractions || attractions.length === 0)) return;

      dispatchBookingUpdateToRedux(index, section);
    });
  }, [formSections, attractions, dayIndex, dispatchBookingUpdateToRedux]);

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
      
      const resolvedCountry =
        (typeof country === "string" && country) ||
        country?.name ||
        country?.label ||
        tour?.destination ||
        tour?.country ||
        city?.country ||
        "";

      dispatch(
        fetchAttractions({
          city: city.address || city.name,
          country: resolvedCountry,
          date: bookingDate,
          adults: tour.adult,
          children: tour.child,
          tour_id: tour.tour_id, // Use tour_id from packageData
          selectedDate: bookingDate,
          fromMainSearch: false,
        })
      )
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
              <TourIcon sx={{ 
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
                  Book Attraction Tickets
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
          console.log("sectionIndexatt1476", section);
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
                                selectedAttractionName={section?.originalData?.AttractionName}
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
                                selectedPax={section.pax}
                                initialAdults={section.pax?.Adults || searchParams?.adults || 1}
                                initialChildren={section.pax?.Children || searchParams?.children || 0}
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