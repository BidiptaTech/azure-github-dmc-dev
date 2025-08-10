import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { logoutUser } from "../common/authSlices";
import { setPriceMode1 } from "../localtour/Localslice";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";
import { updateServiceResponse } from "../common/stepperButtonSlice";

export const fetchGuides = createAsyncThunk(
  "tourguide/fetchGuides",
  async (params, { getState, rejectWithValue }) => {
    try {
      const state = getState();
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      const dmcId = selectDmcId(state);
      // Use the passed parameters or fallback to state values
      const city = params?.city || state.tourguide.entrypickup;
      const date = params?.date || state.tourguide.pickupdate;

      if (!city) {
        return rejectWithValue({ message: "City is required" });
      }

      const response = await axios.get(`${BASE_URL}/guide`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
        params: {
          city: city,
          date: JSON.stringify({ 0: date }),
          dmc_id: dmcId,
        },
      });

      // Validate the response data
      if (!response.data || !Array.isArray(response.data) || response.data.length === 0) {
        return rejectWithValue({ message: "No guides found for the selected city" });
      }

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser());
      }
      return rejectWithValue(error.response?.data || { message: error.message });
    }
  }
);

export const fetchGuideDetails = createAsyncThunk(
  "tourguide/fetchGuideDetails",
  async (params, { rejectWithValue, getState }) => {
    try {
      const state = getState();
      const dmcId = selectDmcId(state);
      // Validate required parameters
      if (!params) {
        throw new Error("Parameters are required for fetching guide details");
      }
      
      const { pickup, date, guide_id, mode } = params;
      
      if (!pickup) {
        throw new Error("Pickup location is required");
      }
      
      if (!guide_id) {
        throw new Error("Guide ID is required");
      }
      
      if (!mode) {
        throw new Error("Mode is required");
      }
      
      if (!dmcId) {
        throw new Error("DMC ID is required");
      }
      
      if (!date) {
        throw new Error("Date is required");
      }

      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

      // Corrected conditional statement
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }

      // Build query parameters from passed params
      const apiParams = {
        pickup: JSON.stringify(pickup),
        date: JSON.stringify({ 0: date }), 
        guide_id,
        mode,
        dmc_id: dmcId,
      };

      // Make API request with params
      const response = await axios.get(`${BASE_URL}/guide-details`, {
        params: apiParams,
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser());
      }
      return rejectWithValue(error.message || "Failed to fetch guide details");
    }
  }
);

// Async thunk for fetching hotels
export const guideslice = createAsyncThunk(
  "tourguide",
  async (_, { getState, dispatch, rejectWithValue }) => {
    // 'dispatch' is available here
    try {
      const state = getState();
      let {
        entrypickup,
        // entrydropoff,
        pickupdate,
        entrytime,
        hours,
        adult,
        children,
        type1,
        tourid,
        details,
        bookingtype,
      } = state.tourguide;
      console.log("State values before FormData population:");
      console.log({
        entrypickup,
        // entrydropoff,
        pickupdate,
        entrytime,
        adult,
        hours,
        children,
        details,
      });
      console.log("Entry Pickup:", entrypickup, "Pickupdate:", pickupdate);
      console.log("pqr", typeof details);

      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;
      const dmcId = selectDmcId(state);
      // Corrected conditional statement
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }
      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Create FormData instances
      let formData = {};

      // Create JSON objects based on the provided conditions
      // let json0 = {};
      // let json1 = {};

      // Populate FormData if entrypickup and related fields exist
      if (
        entrypickup &&
        // entrydropoff &&
        pickupdate &&
        entrytime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
        formData = {
          data: details.map(item => ({ ...item, dmc_id: dmcId })),
          type: type1,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }
      console.log("form", formData);

      // Populate FormData1 if exitpickup and related fields exist
      // if (
      //   exitpickup &&
      //   exitpickupdate &&
      //   entrytime1 &&
      //   hours &&
      //   languagetype1 &&
      //   traveller1
      // ) {
      //   json1 = {
      //     exitpickup,
      //     exitpickupdate,
      //     entrytime1,
      //     hours,
      //     languagetype1,
      //     traveller1,
      //   };
      //   formData1.append("data", JSON.stringify(json1));
      //   formData1.append("type", type1);
      //   formData1.append("agent_id", AgentId);
      //   formData1.append("tour_id", tourid);
      // }

      //console.log("formData has 'data' key:", formData.has("data"));
      // console.log("formData1 has 'data1' key:", formData1.has("data"));

      // // Determine which FormData to send
      // let selectedFormData = null;
      // if (formData.has("data") && !formData1.has("data")) {
      //   selectedFormData = formData;
      //   dispatch(setentrypickup(null)); // Reset state after form selection
      //   dispatch(setentrydropoff([]));
      //   dispatch(setpickupdate(null));
      //   dispatch(setentrytime(null));
      //   dispatch(setlanguagetype(null));
      //   dispatch(settravellers0(0));
      // } else if (formData1.has("data") && !formData.has("data")) {
      //   selectedFormData = formData1;
      //   dispatch(setexitpickup(null)); // Reset state after form selection
      //   dispatch(setexitpickupdate(null));
      //   dispatch(setentrytime1(null));
      //   dispatch(sethour(null));
      //   dispatch(setlanguagetype1(null));
      //   dispatch(settravellers1(0));
      // } else {
      //   throw new Error("No valid data to submit.");
      // }

      // Debugging: Log selected FormData
      // console.log("Selected FormData content:");
      // for (let [key, value] of formData.entries()) {
      //   console.log(`${key}: ${value}`);
      // }

      // Skip API call for debugging
      //return { message: "Debugging FormData complete, no API call made." };

      // Uncomment the code below for the actual API call

      const response = await axios.post(
        `${BASE_URL}/create-booking`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "multipart/form-data",
            "agent-id": AgentId,
          },
        }
      );

      console.log("API Response:", response.data);

      let guide = response.data?.service?.data || [];
      dispatch(setbookedGuide(guide));
      
      // Update stepper button visibility based on booking response
      dispatch(updateServiceResponse({ 
        service: 'guide', 
        response: response.data 
      }));
      
      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const Localguideslice = createSlice({
  name: "tourguide",
  initialState: {
    status: "idle", // Tracks the status: 'idle', 'loading', 'succeeded', 'failed'
    error: null, // Stores any errors from API requests
    filters: {
      category: "All",
      priceRange: { min: 0, max: 1000 },
      rating: 0,
      dateRange: [0, 365],
    },
    searchText: "",
    dateRange: [null, null],
    selectedGuide: null,
    guideDetails: null, // Add this to store guide details
    sortBy: "",
    entrypickup: "",
    entrydropoff: "",
    //exitpickup: "",
    //exitdropoff: "",
    pickupdate: "",
    exitpickupdate: "",
    entrytime: "",
    //exittime: "",
    //entrytime1: "",
    hours: "",
    mode: {},
    // selectedGuide: {},
    // languagetype1: "",
    adult: 0,
    children: 0,
    //traveller1:0,
    type1: "guide",
    type: "",
    tourid: 0,
    totalprice: 0,
    details: [],
    Guides: [],
    bookedguide: [],
    PickupPlaceid: "",
    DropoffPlaceid: "",
    pricemode: "",
    image: "",
    bookingtype: "",
    searchParams: {
      location: null,
      date: null,
      adults: 1,
      children: 0,
      tour_id: null
    },
    guideBookings: [],
  },
  reducers: {
    setFilters(state, action) {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters(state) {
      state.filters = { category: "All", priceRange: { min: "", max: "" } };
    },
    setSearchText(state, action) {
      state.searchText = action.payload;
    },
    setDateRange(state, action) {
      state.dateRange = action.payload;
    },
    setSelectedGuide(state, action) {
      state.selectedGuide = action.payload;
      state.status = "succeeded";
      console.log("selectedGuideMode", state.selectedGuide?.mode);
    },
    setSortBy(state, action) {
      state.sortBy = action.payload;
    },
    setentrypickup: (state, action) => {
      state.entrypickup = action.payload;
      console.log("entrypick", state.entrypickup);
    },
    setentrydropoff: (state, action) => {
      state.entrydropoff = action.payload;
      console.log("entrydrop", state.entrydropoff);
    },
    setPickupPlaceid: (state, action) => {
      state.PickupPlaceid = action.payload;
      console.log("pickplaceid", state.PickupPlaceid);
    },
    setDropoffPlaceid: (state, action) => {
      state.DropoffPlaceid = action.payload;
      console.log("dropplaceid", state.DropoffPlaceid);
    },
    setbookingType: (state, action) => {
      state.type = action.payload;
    },
    setbookingtype: (state, action) => {
      state.bookingtype = action.payload;
      console.log("bookingtype", state.bookingtype);
    },
    // setexitpickup: (state, action) => {
    //   state.exitpickup = action.payload;
    //   console.log("exitpick", state.exitpickup);
    // },
    //   setexitdropoff: (state, action) => {
    //     state.exitdropoff = action.payload;
    //   },
    setpickupdate: (state, action) => {
      state.pickupdate = action.payload;
      console.log("epickdate", state.pickupdate);
    },
    // setexitpickupdate: (state, action) => {
    //   state.exitpickupdate = action.payload;
    //   console.log("expickdate", state.exitpickupdate);
    // },
    setentrytime: (state, action) => {
      state.entrytime = action.payload;
      console.log("entime", state.entrytime);
    },
    setPriceMode2: (state, action) => {
      state.pricemode = action.payload;
    },

    setbookingImage: (state, action) => {
      state.image = action.payload;
    },
    // setexittime: (state, action) => {
    //   state.exittime = action.payload;
    // },
    // setentrytime1: (state, action) => {
    //   state.entrytime1 = action.payload;
    //   console.log("en1time", state.entrytime1);
    // },
    sethour: (state, action) => {
      state.hours = action.payload;
      console.log("htime", state.hours);
    },
    setadult: (state, action) => {
      state.adult = action.payload;
      console.log("trav", state.traveller0);
    },
    setchildren: (state, action) => {
      state.children = action.payload;
      console.log("language", state.languagetype);
    },
    // setlanguagetype1: (state, action) => {
    //   state.languagetype1 = action.payload;
    //   console.log("language1", state.languagetype1);
    // },
    // settravellers1: (state, action) => {
    //   state.traveller1 = action.payload;
    //   console.log("trav1", state.traveller1);
    // },
    settourId: (state, action) => {
      state.tourid = action.payload;
    },
    setTotalPrice: (state, action) => {
      state.totalprice = action.payload;
    },
    setData: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details = []; // Ensure details is an array
      }
      if (state.details[0]) {
        state.details[0] = { ...state.details[0], ...action.payload }; // Append new data instead of overriding
      } else {
        state.details[0] = action.payload; // ✅ Insert if empty
      }
    },
    setbookedGuide: (state, action) => {
      state.bookedguide = action.payload;
      console.log("updated guide", state.bookedguide);
    },
    setMode: (state, action) => {
      state.mode = { ...state.mode, ...action.payload }; // ✅ Merge new mode
      console.log("Updated Mode:", state.mode);
    },
    resetguide: (state) => {
      state.Guides = [];
      state.status = "idle";
      state.error = null;
      state.selectedGuide = null;
      state.guideDetails = null;
    },
    setSearchParams: (state, action) => {
      // Create a serialized version of the payload
      const serializedPayload = { ...action.payload };
      
      // Check if date exists and is a Moment object
      if (serializedPayload.date && serializedPayload.date._isAMomentObject) {
        // Convert Moment object to string in YYYY-MM-DD format
        serializedPayload.date = serializedPayload.date.format('YYYY-MM-DD');
      }
      
      state.searchParams = {
        ...state.searchParams,
        ...serializedPayload
      };
      console.log("Updated search params:", state.searchParams);
    },
    setGuideDetails: (state, action) => {
      state.guideDetails = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchGuides.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchGuides.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.Guides = Array.isArray(action.payload) ? action.payload : [];
        if (state.Guides.length === 0) {
          state.status = "idle";
          state.error = "No guides found for the selected location";
        }
        console.log("guide", state.Guides);
      })
      .addCase(fetchGuides.rejected, (state, action) => {
        state.status = "failed";
        if (
          action.payload.message === "No guides found for the selected city"
        ) {
          state.error = "No Guides found for the Selected City";
        } else {
          state.error = "Failed to fetch guides";
        }
        console.error("Error fetching guides:", state.error); // Log error details
      })
      .addCase(fetchGuideDetails.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchGuideDetails.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.guideDetails = action.payload.guide;
        // Update selectedGuide with the fetched guide details
        state.selectedGuide = {
          ...state.selectedGuide,
          ...action.payload.guide,
          prices: action.payload.guide.prices || {}
        };
        console.log("Guide Details:", action.payload);
      })
      .addCase(fetchGuideDetails.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
        console.error("Error fetching guide details:", action.payload);
      });
    // .addCase(guideslice.pending, (state) => {
    //   state.status = "loading";
    //   state.error = null;
    // })
    // .addCase(guideslice.fulfilled, (state, action) => {
    //   state.status = "succeeded";
    //   state.bookedguide = action.payload;
    //   console.log("booked guides", state.bookedguide);

    //   //console.log("Fetched Attractions Data:", action.payload); // Log the fetched data
    // })
    // .addCase(guideslice.rejected, (state, action) => {
    //   state.status = "failed";
    //   state.error = action.payload; // Save the error in state
    //   console.error("Error fetching attractions:", action.payload); // Log error details
    // });
  },
});

export const {
  setFilters,
  clearFilters,
  setSearchText,
  setDateRange,
  setSelectedGuide,
  setSortBy,
  setentrypickup,
  setentrydropoff,
  setPickupPlaceid,
  setDropoffPlaceid,
  //setexitpickup,
  //setexitdropoff,
  setpickupdate,
  //setexitpickupdate,
  setentrytime,
  //setexittime,
  //setentrytime1,
  sethour,
  setadult,
  //setlanguagetype1,
  setchildren,
  //settravellers1,
  settourId,
  setTotalPrice,
  setData,
  setbookedGuide,
  setMode,
  setPriceMode2,
  setbookingImage,
  setbookingtype,
  setbookingType,
  resetguide,
  setSearchParams,
  setGuideDetails,
} = Localguideslice.actions;

export const selectFilteredGuides = (state) => {
  const { Guides, filters, sortBy, searchText } = state.tourguide;
  const { category, priceRange } = filters;

  // Filter the attractions first based on search text and category/price range
  const filteredGuides = Guides.filter((Guides) => {
    const matchesSearchText = Guides.guide_name
      ?.toLowerCase()
      .includes(searchText?.toLowerCase());
    const matchesCategory =
      category && category !== "All" ? Guides.category === category : true;
    const matchesPriceRange =
      (priceRange.min === "" || Guides.price >= parseFloat(priceRange.min)) &&
      (priceRange.max === "" || Guides.price <= parseFloat(priceRange.max));

    return matchesSearchText && matchesCategory && matchesPriceRange;
  });

  // Sort the filtered attractions
  const sortedGuides = [...filteredGuides].sort((a, b) => {
    switch (sortBy) {
      case "ratingHighToLow":
        return b.rating - a.rating;
      case "ratingLowToHigh":
        return a.rating - b.rating;
      case "priceLowToHigh":
        return a.price - b.price;
      case "priceHighToLow":
        return b.price - a.price;
      // case "adultPriceLowToHigh":
      //   return a.adult_price - b.adult_price;
      // case "adultPriceHighToLow":
      //   return b.adult_price - a.adult_price;
      default:
        // Fallback for undefined names
        return (a.guide_name || "").localeCompare(b.guide_name || "");
    }
  });

  return sortedGuides;
};

export const SelectedGuide = (state) => state.tourguide.Guides;
export const selectFilters = (state) => state.tourguide.filters;
export const selectSortBy = (state) => state.tourguide.sortBy;
export const selectGuidesStatus = (state) => state.tourguide.status;
export const selectGuidesError = (state) => state.tourguide.error;
export const selectSearchText = (state) => state.tourguide.searchText;
export const selectGuideDetails = (state) => state.tourguide.guideDetails;

export default Localguideslice.reducer;
