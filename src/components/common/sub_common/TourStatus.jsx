import React, { useState, useEffect, useRef } from "react";
import { Tooltip } from "react-tooltip";
import "../../../styles/TourStatus.css";
import {
  setCheckIn,
  setCheckOut,
  setSearchLocation,
  setGuest,
} from "../../../slice/common/BookingSlice";
import { useDispatch, useSelector } from "react-redux";
import { Box } from "@mui/material";
import hotelIcon from "../../../../public/icons/resort.png";
import airplane from "../../../../public/icons/airplane.png";
import airplane2 from "../../../../public/icons/airplane2.png";
import car1 from "../../../../public/icons/car1.png";
import attractionIcon from "../../../../public/icons/attraction.png";
// import travellersIcon from "../../../../public/icons/travelling.png";
import restaurant from "../../../../public/icons/restaurants.png";
import guideIcon from "../../../../public/icons/tour-guides.png";
import AttractionModal from "../../tour-list/common/AttractionModal";
import RestaurantModal from "../../restaurants/common/RestaurantModal";
import PortModal from "@/components/activity-list/activity-list-v2/PickupDropModal";
import LocalTourModal from "@/components/activity-list/activity-list-v3/LocaltourModal";
import TourguideModal from "@/components/activity-list/activity-list-v1/TourguideModal";
import HotelModal from "@/components/hotel-list/common/HotelModal";
import { setHaveBooking, setSelectedCity, setBookingType, setBookingMode, setGuestCounts } from "@/slice/common/commonSlice";
import { setTourIdd } from "@/slice/common/authSlices";
import { setTourId1, fetchEditid } from "@/slice/common/EditSlice";
import { setTourId, hydrateStepsFromSession } from "@/slice/common/stepsSlice";
import {
  setId,
  setHotelService,
  settourdetails,
  updateSearchState,
} from "@/slice/hotel/hotelSlice";
import { setAttractionService } from "@/slice/attractions/attractionSlice";
import { setRestaurantsService } from "@/slice/restaurant/RestaurantsSlice";
import { setEntryport, setExitport } from "@/slice/port/pickupDropSlice";
import {
  setHourly,
  setPointToPoint,
  setZone,
} from "@/slice/localtour/Localslice";
import { setbookedGuide } from "@/slice/tourguide/guideslice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { setCity } from "@/slice/common/citySlice";
import {
  saveTourSession,
  loadTourSession,
} from "@/utils/tourSession";
import {
  resolveCountryName,
} from "@/utils/locationFormat";

export default function TourStatus() {
  const [selectedDate, setSelectedDate] = useState(null); // Track selected date
  const [modalOpen, setModalOpen] = useState(false); // Modal open state
  const [restaurantModalOpen, setRestaurantModalOpen] = useState(false); // Restaurant modal state
  const [localtourModalOpen, setlocaltourModalOpen] = useState(false); // Restaurant modal state
  const [guideModalOpen, setguideModalOpen] = useState(false);
  const [entryexitModalOpen, setentryexitModalOpen] = useState(false); // Restaurant modal state
  const [range, setRange] = useState([]);
  const [hotelModalOpen, setHotelModalOpen] = useState(false);
  const [portType, setPortType] = useState(""); // "entry" or "exit"
  const hasRestoredTourRef = useRef(false);

  const { checkIn, checkOut, searchLocation, guests } = useSelector(
    (state) => state.bookings
  );
  console.log("TourStatus - checkIn:", checkIn, "checkOut:", checkOut);
  const attractionServices = useSelector((state) => state.attractions.services || []);
  // console.log("Attraction bookings from Redux:", attractionServices);

  const restaurantServices = useSelector(
    (state) => state.restaurants.services || []
  );
  // console.log('Restaurant bookings from Redux:', restaurantServices);

  const travelPointRaw = useSelector(
    (state) => state.localtour.pointtopoint || []
  );
  // console.log("point 2 point", travelPointRaw);
  const travelHourlyRaw = useSelector((state) => state.localtour.hourly || []);
  // console.log("hourlyveh", travelHourlyRaw);

  const travelZoneRaw = useSelector((state) => state.localtour.Zonebook || []);
  // console.log("travelzzzzzz", travelZoneRaw);
  const entryPortRaw = useSelector((state) => state.pickupDrop.entryport || []);
  const exitPortRaw = useSelector((state) => state.pickupDrop.exitport || []);
  const portCheckoutVehicle = useSelector(
    (state) => state.pickupDrop.checkoutVehicle
  );
  const portSelectionType = useSelector(
    (state) => state.pickupDrop.selectionType
  );
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  const portEntryPickup = useSelector(
    (state) => state.pickupDrop.entrypickup
  );
  const portEntryDropoff = useSelector(
    (state) => state.pickupDrop.entrydropoff
  );
  const portEntryDate = useSelector((state) => state.pickupDrop.pickupdate);
  const portEntryTime = useSelector((state) => state.pickupDrop.entrytime);
  const portExitPickup = useSelector((state) => state.pickupDrop.exitpickup);
  const portExitDropoff = useSelector((state) => state.pickupDrop.exitdropoff);
  const portExitDate = useSelector((state) => state.pickupDrop.pickupdate);
  const portExitTime = useSelector((state) => state.pickupDrop.exittime);
  const portPickupPlaceid = useSelector(
    (state) => state.pickupDrop.PickupPlaceid
  );
  const portDropoffPlaceid = useSelector(
    (state) => state.pickupDrop.DropoffPlaceid
  );
  const portPickupPlaceid1 = useSelector(
    (state) => state.pickupDrop.PickupPlaceid1
  );
  const portDropoffPlaceid1 = useSelector(
    (state) => state.pickupDrop.DropoffPlaceid1
  );
  const portAdult = useSelector((state) => state.pickupDrop.adult);
  const portChildren = useSelector((state) => state.pickupDrop.children);
  const portMode = useSelector((state) => state.pickupDrop.mode);
  const portPriceMode = useSelector((state) => state.pickupDrop.pricemode);
  const portBookingType = useSelector(
    (state) => state.pickupDrop.bookingtype
  );
  const bookedGuideRaw = useSelector((state) => state.tourguide.bookedguide || []);
  const guideEntrypickup = useSelector((state) => state.tourguide.entrypickup);
  const guidePickupdate = useSelector((state) => state.tourguide.pickupdate);
  const guideEntrytime = useSelector((state) => state.tourguide.entrytime);
  const guideHours = useSelector((state) => state.tourguide.hours);
  const guideHourlyPrice = useSelector((state) => state.tourguide.hourlyPrice);
  const guideAdult = useSelector((state) => state.tourguide.adult);
  const guideChildren = useSelector((state) => state.tourguide.children);
  const guidePickupPlaceid = useSelector((state) => state.tourguide.PickupPlaceid);
  const guideDropoffPlaceid = useSelector((state) => state.tourguide.DropoffPlaceid);
  const guideMode = useSelector((state) => state.tourguide.mode);
  const guideCheckoutGuide = useSelector((state) => state.tourguide.checkoutGuide);
  const attractionDetailsSession = useSelector(
    (state) => state.attractions.attractionDetails
  );
  const selectedAttractionSession = useSelector(
    (state) => state.attractions.selectedAttraction
  );
  const attractionSearchParams = useSelector(
    (state) => state.attractions.searchParams
  );
  const attractionCheckoutDraft = useSelector(
    (state) => state.attractions.checkoutDraft
  );
  const selectedRestaurantSession = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );
  const listRestaurantSession = useSelector(
    (state) => state.restaurants.listRestaurant
  );
  const restaurantSearchParams = useSelector(
    (state) => state.restaurants.searchParams
  );
  const restaurantCheckoutDraft = useSelector(
    (state) => state.restaurants.checkoutDraft
  );
  const localSelectionType = useSelector((state) => state.localtour.selectionType);
  const localCheckoutVehicle = useSelector((state) => state.localtour.checkoutVehicle);
  const localExitpickup = useSelector((state) => state.localtour.exitpickup);
  const localPickdate = useSelector((state) => state.localtour.pickdate);
  const localEntrytime = useSelector((state) => state.localtour.entrytime);
  const localEntrytime1 = useSelector((state) => state.localtour.entrytime1);
  const localEntrypickup = useSelector((state) => state.localtour.entrypickup);
  const localEntrydropoff = useSelector((state) => state.localtour.entrydropoff);
  const localPickupPlaceid = useSelector((state) => state.localtour.PickupPlaceid);
  const localDropoffPlaceid = useSelector((state) => state.localtour.DropoffPlaceid);
  const localPickupZoneid = useSelector((state) => state.localtour.PickupZoneid);
  const localDropoffZoneid = useSelector((state) => state.localtour.DropoffZoneid);
  const localMode = useSelector((state) => state.localtour.mode);
  const localAdult = useSelector((state) => state.localtour.adult);
  const localChildren = useSelector((state) => state.localtour.children);
  const localHours = useSelector((state) => state.localtour.hours);
  const localPricemode = useSelector((state) => state.localtour.pricemode);

  const dateServiceRaw = useSelector((state) => state.dateService.services || []);
  // console.log("dateService123", dateService);
  // const dateKey = Object.keys(dateService)[0];
  // console.log("dateKey", dateKey);

  const dispatch = useDispatch();
  const { status } = useSelector((state) => state.bookings || []);
  // console.log(status,"status");

  // Get hotel bookings from redux store
  //here i am gett all the data from response and store in a redux and send data to the hotel modal
  const hotelBookingsRaw = useSelector((state) => state.hotels.hotelService || []);
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const hotelSearchState = useSelector((state) => state.hotels.searchState);
  const hotelCheckoutHotel = useSelector((state) => state.hoteldetails.checkoutHotel);
  const hotelBookingDetails = useSelector((state) => state.hoteldetails.bookingDetails);
  const hotelPoliciesSession = useSelector((state) => state.hoteldetails.hotelPolicies);
  const hotelImagesSession = useSelector((state) => state.hoteldetails.images);
  const hotelRoomDatas = useSelector((state) => state.rooms.roomDatas);
  const hotelRoomsId = useSelector((state) => state.rooms.id);
  const hotelRoomsTourId = useSelector((state) => state.rooms.tour_id);
  const hotelCategoryPriceMode = useSelector((state) => state.category.priceMode);
  const hotelCategoryPriceModeId = useSelector((state) => state.category.priceModeId);
  const selectedCity = useSelector((state) => state.common.selectedCity);
  const cityList = useSelector((state) => state.city?.city || []);
  const userCountry = useSelector((state) => state.auth?.user_country);
  //  console.log(hotelBookings,"hotelBookings");

  const haveBooking = useSelector((state) => state.common.haveBooking);
  const bookingType = useSelector((state) => state.common.bookingType);
  const bookingMode = useSelector((state) => state.common.bookingMode);
  const guestCounts = useSelector((state) => state.common.guestCounts);
  const {
    stepStatus1,
    localStepStatus,
    currentStep,
    localCurrentStep,
    active_status: stepsActiveStatus,
    type: stepType,
  } = useSelector((state) => state.steps || {});
  const globalTourId = useSelector(
    (state) => state.auth?.tourId || state.steps?.id
  );

  const hasActiveTour = Boolean(haveBooking && globalTourId);

  const bookings = hasActiveTour ? attractionServices : [];
  const restaurantBooking = hasActiveTour ? restaurantServices : [];
  const travelPoint = hasActiveTour ? travelPointRaw : [];
  const travelHourly = hasActiveTour ? travelHourlyRaw : [];
  const travelZone = hasActiveTour ? travelZoneRaw : [];
  const travel = hasActiveTour
    ? [...travelHourly, ...travelPoint, ...travelZone]
    : [];
  const formData = hasActiveTour ? entryPortRaw : [];
  const formData1 = hasActiveTour ? exitPortRaw : [];
  const bookedguide = hasActiveTour ? bookedGuideRaw : [];
  const dateService = hasActiveTour ? dateServiceRaw : {};
  const hotelBookings = hasActiveTour ? hotelBookingsRaw : [];

  const hydrateDestination = (destination, checkInValue, checkOutValue) => {
    if (!destination) return;
    // Destination must be country name/code — never a city object
    if (typeof destination === "object" && !Array.isArray(destination)) {
      destination =
        destination.country ||
        destination.name ||
        destination.label ||
        "";
      if (!destination) return;
    }

    const nameToCode = {};
    const codeToName = {};
    if (Array.isArray(userCountry)) {
      userCountry.forEach((country) => {
        if (country?.name && country?.code) {
          nameToCode[country.name] = country.code;
          nameToCode[country.name.toLowerCase()] = country.code;
          codeToName[country.code] = country.name;
          codeToName[country.code.toLowerCase()] = country.name;
        }
      });
    }

    const destinationArray = Array.isArray(destination)
      ? destination
      : String(destination)
          .split(",")
          .map((name) => name.trim())
          .filter(Boolean);

    const countryCodeArray = destinationArray
      .map((item) => {
        let code = nameToCode[item] || nameToCode[String(item).toLowerCase()];
        if (!code && codeToName[item]) {
          // Already a code
          code = item;
        }
        return code || item;
      })
      .filter(Boolean);

    if (countryCodeArray.length) {
      dispatch(setSearchLocation(countryCodeArray));
    }

    // Destination from API/tour is country — do NOT put it into selectedCity
    // or hotel search location (those are city fields). Only sync dates here.
    dispatch(
      updateSearchState({
        ...(checkInValue
          ? {
              ucheckIn: checkInValue.includes("/")
                ? checkInValue.split("/").reverse().join("-")
                : checkInValue,
            }
          : {}),
        ...(checkOutValue
          ? {
              ucheckOut: checkOutValue.includes("/")
                ? checkOutValue.split("/").reverse().join("-")
                : checkOutValue,
            }
          : {}),
      })
    );

    dispatch(
      settourdetails({
        destination,
        country: destination,
        CheckInTime: checkInValue || "",
        CheckOutTime: checkOutValue || "",
      })
    );
  };

  // Restore active tour after refresh (Redux is in-memory only).
  // Prefer sessionStorage; skip edit-tour API when cached services exist.
  useEffect(() => {
    if (hasRestoredTourRef.current) return;
    if (globalTourId) return;

    const saved = loadTourSession();
    if (!saved?.tourId) return;

    hasRestoredTourRef.current = true;

    dispatch(setTourId1(saved.tourId));
    dispatch(setTourIdd(saved.tourId));
    dispatch(setTourId(saved.tourId));
    dispatch(setId(saved.tourId));
    if (saved.checkIn) dispatch(setCheckIn(saved.checkIn));
    if (saved.checkOut) dispatch(setCheckOut(saved.checkOut));
    if (saved.haveBooking) dispatch(setHaveBooking(true));
    if (saved.bookingType) dispatch(setBookingType(saved.bookingType));
    if (saved.bookingMode) dispatch(setBookingMode(saved.bookingMode));
    if (saved.guestCounts) dispatch(setGuestCounts(saved.guestCounts));

    // Restore steps slice (stepper colors / current step)
    dispatch(
      hydrateStepsFromSession({
        id: saved.tourId,
        stepStatus1: saved.stepStatus1,
        localStepStatus: saved.localStepStatus,
        currentStep: saved.currentStep,
        localCurrentStep: saved.localCurrentStep,
        active_status: saved.active_status,
        type: saved.stepType,
      })
    );

    if (Array.isArray(saved.searchLocation) && saved.searchLocation.length) {
      dispatch(setSearchLocation(saved.searchLocation));
    }
    if (saved.selectedCity != null) {
      dispatch(setSelectedCity(saved.selectedCity));
    }

    const countryDestination = resolveCountryName(
      (typeof saved.tourdetails?.destination === "string" &&
        saved.tourdetails.destination) ||
        (typeof saved.tourdetails?.country === "string" &&
          saved.tourdetails.country) ||
        saved.searchLocation ||
        "",
      userCountry
    );

    if (Array.isArray(saved.cityList) && saved.cityList.length) {
      dispatch(
        setCity({
          cities: saved.cityList,
          country: countryDestination,
        })
      );
    }
    if (saved.guests) {
      dispatch(setGuest(saved.guests));
    }
    if (saved.tourdetails || saved.checkIn || saved.checkOut) {
      dispatch(
        settourdetails({
          ...(saved.tourdetails || {}),
          CheckInTime:
            saved.tourdetails?.CheckInTime ||
            saved.tourdetails?.check_in_time ||
            saved.checkIn ||
            "",
          CheckOutTime:
            saved.tourdetails?.CheckOutTime ||
            saved.tourdetails?.check_out_time ||
            saved.checkOut ||
            "",
          destination:
            countryDestination || saved.tourdetails?.destination || "",
          country: countryDestination || saved.tourdetails?.country || "",
        })
      );
    }
    if (saved.searchState) {
      dispatch(updateSearchState(saved.searchState));
    } else if (saved.checkIn || saved.checkOut) {
      const cityLocation =
        typeof saved.selectedCity === "string"
          ? saved.selectedCity
          : saved.selectedCity?.address ||
            saved.selectedCity?.name ||
            (Array.isArray(saved.searchState?.location)
              ? saved.searchState.location
              : saved.searchState?.location) ||
            [];
      dispatch(
        updateSearchState({
          location: cityLocation,
          ucheckIn: saved.checkIn
            ? saved.checkIn.includes("/")
              ? saved.checkIn.split("/").reverse().join("-")
              : saved.checkIn
            : null,
          ucheckOut: saved.checkOut
            ? saved.checkOut.includes("/")
              ? saved.checkOut.split("/").reverse().join("-")
              : saved.checkOut
            : null,
        })
      );
    }

    const svc = saved.services || {};
    if (Array.isArray(svc.hotels) && svc.hotels.length) {
      dispatch(setHotelService(svc.hotels));
    }
    if (Array.isArray(svc.attractions) && svc.attractions.length) {
      dispatch(setAttractionService(svc.attractions));
    }
    if (Array.isArray(svc.restaurants) && svc.restaurants.length) {
      dispatch(setRestaurantsService(svc.restaurants));
    }
    if (Array.isArray(svc.entryPorts) && svc.entryPorts.length) {
      dispatch(setEntryport(svc.entryPorts));
    }
    if (Array.isArray(svc.exitPorts) && svc.exitPorts.length) {
      dispatch(setExitport(svc.exitPorts));
    }
    if (Array.isArray(svc.travelPoint) && svc.travelPoint.length) {
      dispatch(setPointToPoint(svc.travelPoint));
    }
    if (Array.isArray(svc.travelHourly) && svc.travelHourly.length) {
      dispatch(setHourly(svc.travelHourly));
    }
    if (Array.isArray(svc.travelZone) && svc.travelZone.length) {
      dispatch(setZone(svc.travelZone));
    }
    if (Array.isArray(svc.guides) && svc.guides.length) {
      dispatch(setbookedGuide(svc.guides));
    }
    if (svc.dateService) {
      dispatch(setDateService(svc.dateService));
    }

    dispatch(setHaveBooking(true));

    const hasCachedServices =
      (Array.isArray(svc.hotels) && svc.hotels.length > 0) ||
      (Array.isArray(svc.attractions) && svc.attractions.length > 0) ||
      (Array.isArray(svc.restaurants) && svc.restaurants.length > 0) ||
      (Array.isArray(svc.guides) && svc.guides.length > 0) ||
      (Array.isArray(svc.entryPorts) && svc.entryPorts.length > 0) ||
      (Array.isArray(svc.exitPorts) && svc.exitPorts.length > 0) ||
      (Array.isArray(svc.travelPoint) && svc.travelPoint.length > 0) ||
      (Array.isArray(svc.travelHourly) && svc.travelHourly.length > 0) ||
      (Array.isArray(svc.travelZone) && svc.travelZone.length > 0);

    // Avoid unnecessary API call when session already has booking data
    if (hasCachedServices) return;

    dispatch(fetchEditid(saved.tourId))
      .unwrap()
      .then((response) => {
        dispatch(setHaveBooking(true));
        const data = response?.data || response;
        if (data?.destination) {
          hydrateDestination(
            data.destination,
            saved.checkIn,
            saved.checkOut
          );
        }
      })
      .catch(() => {});
  }, [dispatch, globalTourId]);

  // Persist tour context so refresh can restore it (overwrites previous tour)
  useEffect(() => {
    // Allow persisting drafts even if check-in/out aren't set yet.
    // We still require `tourId` so hydration can restore state after refresh.
    if (!globalTourId) return;
    saveTourSession({
      tourId: globalTourId,
      // bookings
      checkIn,
      checkOut,
      searchLocation: searchLocation || [],
      guests: guests || null,
      // common
      haveBooking: !!haveBooking,
      selectedCity: selectedCity ?? null,
      bookingType: bookingType ?? null,
      bookingMode: bookingMode ?? null,
      guestCounts: guestCounts || null,
      // steps
      stepStatus1: stepStatus1 || null,
      localStepStatus: localStepStatus || null,
      currentStep: currentStep ?? null,
      localCurrentStep: localCurrentStep ?? null,
      active_status: stepsActiveStatus ?? null,
      stepType: stepType ?? null,
      // hotel / city
      cityList: cityList || [],
      tourdetails: tourdetails || null,
      searchState: hotelSearchState || null,
      hotelCheckout: {
        checkoutHotel: hotelCheckoutHotel || null,
        bookingDetails: hotelBookingDetails || null,
        hotelPolicies: hotelPoliciesSession || [],
        images: hotelImagesSession || [],
        roomDatas: hotelRoomDatas || null,
        roomsId: hotelRoomsId || null,
        roomsTourId: hotelRoomsTourId || 0,
        priceMode: hotelCategoryPriceMode || null,
        priceModeId: hotelCategoryPriceModeId || "",
      },
      guideCheckout: {
        entrypickup: guideEntrypickup || "",
        pickupdate: guidePickupdate || "",
        entrytime: guideEntrytime || "",
        hours: guideHours || "",
        hourlyPrice: guideHourlyPrice || 0,
        adults: guideAdult,
        children: guideChildren,
        PickupPlaceid: guidePickupPlaceid || "",
        DropoffPlaceid: guideDropoffPlaceid || "",
        mode: guideMode || {},
        checkoutGuide: guideCheckoutGuide || null,
      },
      attractionCheckout: {
        attractionDetails: attractionDetailsSession || null,
        selectedAttraction: selectedAttractionSession || null,
        searchParams: attractionSearchParams || {},
        checkoutDraft: attractionCheckoutDraft || null,
      },
      restaurantCheckout: {
        selectedRestaurant: selectedRestaurantSession || null,
        listRestaurant: listRestaurantSession || null,
        searchParams: restaurantSearchParams || {},
        checkoutDraft: restaurantCheckoutDraft || null,
      },
      portCheckout: {
        selectionType: portSelectionType || "",
        portZoneType: portZoneType || "",
        checkoutVehicle: portCheckoutVehicle || null,
        // entry port fields
        entrypickup: portEntryPickup || "",
        entrydropoff: portEntryDropoff || "",
        pickupdate: portEntryDate || "",
        entrytime: portEntryTime || "",
        PickupPlaceid: portPickupPlaceid || "",
        DropoffPlaceid: portDropoffPlaceid || "",
        // exit port fields
        exitpickup: portExitPickup || "",
        exitdropoff: portExitDropoff || "",
        exittime: portExitTime || "",
        PickupPlaceid1: portPickupPlaceid1 || "",
        DropoffPlaceid1: portDropoffPlaceid1 || "",
        // guests/pricing
        adult: portAdult,
        children: portChildren,
        mode: portMode || {},
        pricemode: portPriceMode || "",
        bookingtype: portBookingType || "",
      },
      localTransferCheckout: {
        selectionType: localSelectionType || "",
        checkoutVehicle: localCheckoutVehicle || null,
        exitpickup: localExitpickup || "",
        pickdate: localPickdate || "",
        entrytime: localEntrytime || "",
        entrytime1: localEntrytime1 || "",
        entrypickup: localEntrypickup || "",
        entrydropoff: localEntrydropoff || "",
        PickupPlaceid: localPickupPlaceid || "",
        DropoffPlaceid: localDropoffPlaceid || "",
        PickupZoneid: localPickupZoneid || "",
        DropoffZoneid: localDropoffZoneid || "",
        mode: localMode || {},
        adult: localAdult,
        children: localChildren,
        hours: localHours || "",
        pricemode: localPricemode || "",
      },
      services: {
        hotels: hotelBookingsRaw || [],
        attractions: attractionServices || [],
        restaurants: restaurantServices || [],
        entryPorts: entryPortRaw || [],
        exitPorts: exitPortRaw || [],
        travelPoint: travelPointRaw || [],
        travelHourly: travelHourlyRaw || [],
        travelZone: travelZoneRaw || [],
        guides: bookedGuideRaw || [],
        dateService: dateServiceRaw || [],
      },
    });
  }, [
    globalTourId,
    checkIn,
    checkOut,
    haveBooking,
    searchLocation,
    selectedCity,
    cityList,
    guests,
    tourdetails,
    hotelSearchState,
    hotelCheckoutHotel,
    hotelBookingDetails,
    hotelPoliciesSession,
    hotelImagesSession,
    hotelRoomDatas,
    hotelRoomsId,
    hotelRoomsTourId,
    hotelCategoryPriceMode,
    hotelCategoryPriceModeId,
    bookingType,
    bookingMode,
    guestCounts,
    stepStatus1,
    localStepStatus,
    currentStep,
    localCurrentStep,
    stepsActiveStatus,
    stepType,
    hotelBookingsRaw,
    attractionServices,
    restaurantServices,
    guideEntrypickup,
    guidePickupdate,
    guideEntrytime,
    guideHours,
    guideHourlyPrice,
    guideAdult,
    guideChildren,
    guidePickupPlaceid,
    guideDropoffPlaceid,
    guideMode,
    guideCheckoutGuide,
    attractionDetailsSession,
    selectedAttractionSession,
    attractionSearchParams,
    attractionCheckoutDraft,
    selectedRestaurantSession,
    listRestaurantSession,
    restaurantSearchParams,
    restaurantCheckoutDraft,
    portCheckoutVehicle,
    portSelectionType,
    portZoneType,
    portEntryPickup,
    portEntryDropoff,
    portEntryDate,
    portEntryTime,
    portExitPickup,
    portExitDropoff,
    portExitDate,
    portExitTime,
    portPickupPlaceid,
    portDropoffPlaceid,
    portPickupPlaceid1,
    portDropoffPlaceid1,
    portAdult,
    portChildren,
    portMode,
    portPriceMode,
    portBookingType,
    localSelectionType,
    localCheckoutVehicle,
    localExitpickup,
    localPickdate,
    localEntrytime,
    localEntrytime1,
    localEntrypickup,
    localEntrydropoff,
    localPickupPlaceid,
    localDropoffPlaceid,
    localPickupZoneid,
    localDropoffZoneid,
    localMode,
    localAdult,
    localChildren,
    localHours,
    localPricemode,
    entryPortRaw,
    exitPortRaw,
    travelPointRaw,
    travelHourlyRaw,
    travelZoneRaw,
    bookedGuideRaw,
    dateServiceRaw,
  ]);

  useEffect(() => {
    let newDateServiceDates = new Set();

    const formatDate = (date) => {
      if (!date) return "";
      const [year, month, day] = date.split("-").map(Number); // Expects "YYYY-MM-DD"
      return `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(
        2,
        "0"
      )}`;
    };

    if (typeof dateService === "string") {
      const formatted = formatDate(dateService);
      if (formatted) newDateServiceDates.add(formatted);
    } else if (Array.isArray(dateService)) {
      dateService.forEach((service) => {
        const formatted = formatDate(service.date);
        if (formatted) newDateServiceDates.add(formatted);
      });
    } else if (dateService && typeof dateService === "object") {
      (dateService.data || []).forEach((service) => {
        const formatted = formatDate(service.date);
        if (formatted) newDateServiceDates.add(formatted);
      });
    }

    // setDateServiceDates(newDateServiceDates); // Uncomment to update state
  }, [dateService]);

  const parseDate = (dateStr) => {
    const [day, month, year] = dateStr.split("/").map(Number); // Expects "DD/MM/YYYY"
    return new Date(Date.UTC(year, month - 1, day)); // Creates UTC date to prevent offset issues
  };

  useEffect(() => {
    if (checkIn && checkOut) {
      const startDate = parseDate(checkIn);
      const endDate = parseDate(checkOut);
      const parseOk = !isNaN(startDate) && !isNaN(endDate) && startDate <= endDate;

      if (parseOk) {
        const updatedRange = [];
        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
          updatedRange.push(new Date(currentDate));
          currentDate.setUTCDate(currentDate.getUTCDate() + 1); // Use UTC date addition
        }

        setRange(updatedRange);
      } else {
        setRange([]);
      }
    } else {
      setRange([]);
    }
  }, [checkIn, checkOut]);

  // Removed auto-fetch of booking ID on mount since tour is now created during booking/enquiry
  // useEffect(() => {
  //   if (status === "idle") {
  //     dispatch(fetchBookingid());
  //   }
  // }, [dispatch, status]);

  const formatDate = (date) => date.toISOString().split("T")[0];

  const handleHotelClick = (date) => {
    setSelectedDate(formatDate(date));
    setHotelModalOpen(true);
  };

  const handleAttractionClick = (date) => {
    setSelectedDate(formatDate(date));
    setModalOpen(true);
  };

  const handleRestaurantClick = (date) => {
    setSelectedDate(formatDate(date));
    setRestaurantModalOpen(true);
  };

  const handleLocaltourClick = (date) => {
    setSelectedDate(formatDate(date));
    setlocaltourModalOpen(true);
  };

  const handleEntryPortClick = (date) => {
    setSelectedDate(formatDate(date));
    setPortType("entry");
    setentryexitModalOpen(true);
  };

  const handleExitPortClick = (date) => {
    setSelectedDate(formatDate(date));
    setPortType("exit");
    setentryexitModalOpen(true);
  };

  const handleGuideClick = (date) => {
    setSelectedDate(formatDate(date));
    setguideModalOpen(true);
  };

  const subBoxIcons = [
    { icon: hotelIcon, name: "Hotel", color: "#4caf50" },
    { icon: airplane, name: "Pickup/Drop(Entry Port)", color: "#ff9800" },
    { icon: airplane2, name: "Pickup/Drop(Exit Port)", color: "#2196f3" },
    {
      icon: attractionIcon,
      name: "Attraction & Experiences",
      color: "#9c27b0",
    },
    { icon: guideIcon, name: "Tour Guide", color: "#795548" },
    { icon: restaurant, name: "Restaurant", color: "#607d8b" },
    { icon: car1, name: "Travellers", color: "#f44336" },
  ];

  const formatDateForDisplay = (date) => {
    const d = new Date(date);

    // Get day name (Mon, Tue, etc.)
    const dayName = new Intl.DateTimeFormat("en-US", {
      weekday: "short",
    }).format(d);

    // Get day of month (1-31)
    const day = d.getDate();

    // Get month name (Jan, Feb, etc.)
    const monthName = new Intl.DateTimeFormat("en-US", {
      month: "short",
    }).format(d);

    // Get year and take last 2 digits
    const year = d.getFullYear().toString().substr(-2);

    // Format as "Mon, 24 Jan'25"
    return `${dayName}, ${day} ${monthName}'${year}`;
  };

  return (
    <div className="tour-status-container">
      <div className="range-box">
        {range.length > 0 ? (
          <div className="boxes-container">
            {range.map((date, index) => {
              const formattedDate = formatDate(date);
              const services = dateService[formattedDate]?.services || {};
              // console.log("services", services);

              const attractionCount = (services.attraction?.count || 0) + (services.attraction_package?.count || 0);
              // console.log("attractionCount", attractionCount);
              const restaurantCount = services.restaurant?.count || 0;
              const localtravelPointCount = services.travel_point?.count || 0;
              const localtravelHourCount = services.travel_hourly?.count || 0;
              const localtravelZoneCount = services.local_transport?.count || 0;
              const localtourCount =
                localtravelPointCount +
                localtravelHourCount +
                localtravelZoneCount;
              const entrycount = services.entry_port?.count || 0;
              const exitcount = services.exit_port?.count || 0;
              const portcount = entrycount + exitcount;
              const guidecount = services.guide?.count || 0;

              // Add hotel count
              const hotelCount = services.hotel?.count || 0;

              // Filter icons based on the current day
              const visibleIcons = subBoxIcons.filter((icon) => {
                if (icon.name === "Pickup/Drop(Entry Port)") {
                  return index === 0;
                }
                if (icon.name === "Pickup/Drop(Exit Port)") {
                  return index === range.length - 1;
                }
                return true;
              });

              return (
                <div
                  key={index}
                  className="number-box"
                  style={{
                    width: "180px",
                    height: "80px",
                    // backgroundColor: "#f0e70d1a",
                    backgroundColor: "#0dcaf033",
                    border: "1.5px solid rgba(5, 4, 4, 0.98)",
                    borderRadius: "8px",
                    padding: "10px 8px",
                    boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                    transition: "all 0.2s ease",
                    position: "relative",
                    overflow: "hidden",
                  }}
                >
                  <div
                    style={{
                      fontWeight: "600",
                      fontSize: "0.85rem",
                      color: "#333",
                      textAlign: "center",
                      marginBottom: "10px",
                      padding: "2px 0",
                      borderBottom: "1px dashed rgba(0,0,0,0.1)",
                      position: "relative",
                    }}
                  >
                    {formatDateForDisplay(date)}
                  </div>
                  <div
                    className="sub-box-container"
                    style={{
                      display: "grid",
                      gridTemplateColumns: `repeat(${visibleIcons.length}, 1fr)`,
                      gap: "4px",
                      width: "100%",
                      marginTop: "5px",
                    }}
                  >
                    {visibleIcons.map((icon, subIndex) => {
                      // Determine if the icon should be active (has a count)
                      let count = 0;
                      if (icon.name === "Hotel") count = hotelCount;
                      else if (icon.name === "Pickup/Drop(Entry Port)")
                        count = entrycount;
                      else if (icon.name === "Pickup/Drop(Exit Port)")
                        count = exitcount;
                      else if (icon.name === "Attraction & Experiences")
                        count = attractionCount;
                      else if (icon.name === "Tour Guide") count = guidecount;
                      else if (icon.name === "Restaurant")
                        count = restaurantCount;
                      else if (icon.name === "Travellers")
                        count = localtourCount;

                      const isActive = count > 0;

                      return (
                        <div
                          key={subIndex}
                          className="sub-box"
                          style={{
                            width: "20px",
                            height: "20px",
                            textAlign: "center",
                            lineHeight: "50px",
                            cursor: isActive ? "pointer" : "default",
                            marginLeft:
                              visibleIcons.length === 5
                                ? "6px"
                                : visibleIcons.length === 6
                                ? "3px"
                                : "4px",
                            position: "relative",
                            opacity: isActive ? 1 : 0.5,
                            filter: isActive ? "none" : "grayscale(60%)",
                            transition: "all 0.2s ease",
                          }}
                          onClick={() => {
                            if (!isActive) return;

                            const iconName = icon.name;
                            if (iconName === "Hotel" && hotelCount > 0)
                              handleHotelClick(date);
                            else if (
                              iconName === "Attraction & Experiences" &&
                              attractionCount > 0
                            )
                              handleAttractionClick(date);
                            else if (
                              iconName === "Pickup/Drop(Entry Port)" &&
                              entrycount > 0
                            )
                              handleEntryPortClick(date);
                            else if (
                              iconName === "Pickup/Drop(Exit Port)" &&
                              exitcount > 0
                            )
                              handleExitPortClick(date);
                            else if (
                              iconName === "Travellers" &&
                              localtourCount > 0
                            )
                              handleLocaltourClick(date);
                            else if (
                              iconName === "Tour Guide" &&
                              guidecount > 0
                            )
                              handleGuideClick(date);
                            else if (
                              iconName === "Restaurant" &&
                              restaurantCount > 0
                            )
                              handleRestaurantClick(date);
                          }}
                        >
                          <div style={{ position: "relative" }}>
                            <img
                              src={icon.icon}
                              alt={icon.name}
                              data-tooltip-content={icon.name}
                              data-tooltip-id={`tooltip-${subIndex}-${index}`}
                              style={{
                                width: "15px",
                                height: "15px",
                                transition: "transform 0.2s ease",
                              }}
                            />
                            {count > 0 && (
                              <div
                                style={{
                                  position: "absolute",
                                  top: 3,
                                  right: -10,
                                  backgroundColor: icon.color || "blue",
                                  color: "white",
                                  borderRadius: "50%",
                                  width: "14px",
                                  height: "15px",
                                  display: "flex",
                                  justifyContent: "center",
                                  alignItems: "center",
                                  fontSize: "10px",
                                  boxShadow: "0 1px 3px rgba(0,0,0,0.2)",
                                }}
                              >
                                {count}
                              </div>
                            )}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <Box
            style={{
              textAlign: "center",
              padding: "20px",
              color: "#666",
              fontStyle: "italic",
              backgroundColor: "rgba(0,0,0,0.02)",
              borderRadius: "8px",
            }}
          >
            No date range selected
          </Box>
        )}
      </div>

      {/* Tooltips */}
      {subBoxIcons.map((icon, iconIndex) =>
        range.map((_, dateIndex) => (
          <Tooltip
            key={`tooltip-${iconIndex}-${dateIndex}`}
            id={`tooltip-${iconIndex}-${dateIndex}`}
            effect="solid"
            place="top"
            style={{
              borderRadius: "4px",
              fontSize: "12px",
              padding: "4px 8px",
              boxShadow: "0 2px 10px rgba(0,0,0,0.1)",
            }}
          />
        ))
      )}

      {/* Modals */}
      <PortModal
        open={entryexitModalOpen}
        onClose={() => setentryexitModalOpen(false)}
        bookings={portType === "entry" ? formData : formData1}
        date={selectedDate}
        portType={portType}
      />
      <AttractionModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        bookings={bookings}
        date={selectedDate}
      />
      <LocalTourModal
        open={localtourModalOpen}
        onClose={() => setlocaltourModalOpen(false)}
        bookings={travel}
        date={selectedDate}
      />
      <TourguideModal
        open={guideModalOpen}
        onClose={() => setguideModalOpen(false)}
        bookings={bookedguide}
        date={selectedDate}
      />
      <RestaurantModal
        open={restaurantModalOpen}
        onClose={() => setRestaurantModalOpen(false)}
        bookings={restaurantBooking}
        date={selectedDate}
      />
      <HotelModal
        open={hotelModalOpen}
        onClose={() => setHotelModalOpen(false)}
        bookings={hotelBookings}
        date={selectedDate}
      />

      <style jsx>{`
        .tour-status-container {
          padding: 15px;
          background: linear-gradient(145deg, #ffffff, #f9f9f9);
          border-radius: 12px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .boxes-container {
          display: flex;
          overflow-x: auto;
          gap: 12px;
          padding: 8px 4px;
          -webkit-overflow-scrolling: touch;
          scrollbar-width: thin;
          scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        }

        .boxes-container::-webkit-scrollbar {
          height: 6px;
        }

        .boxes-container::-webkit-scrollbar-track {
          background: rgba(0, 0, 0, 0.05);
          border-radius: 10px;
        }

        .boxes-container::-webkit-scrollbar-thumb {
          background: rgba(0, 0, 0, 0.15);
          border-radius: 10px;
        }

        .boxes-container::-webkit-scrollbar-thumb:hover {
          background: rgba(0, 0, 0, 0.25);
        }

        .number-box {
          position: relative;
          transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .number-box:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }

        .sub-box:hover img {
          transform: scale(1.1);
        }
      `}</style>
    </div>
  );
}
