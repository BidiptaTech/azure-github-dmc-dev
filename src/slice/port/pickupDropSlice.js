//import { Details } from "@mui/icons-material";
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { logoutUser } from "../common/authSlices";
import { BASE_URL } from "@/services/api";

//import state from "sweetalert/typings/modules/state";
//import { settourId } from "../../HotelSlices/stepsSlice";
//import { format } from "date-fns";

export const fetchVehicles = createAsyncThunk(
  "pickupDrop/fetchVehicles",
  async (_, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const { PickupPlaceid, DropoffPlaceid, pickupdate, entrytime, PickupPlaceid1, DropoffPlaceid1, selectionType } =
        state.pickupDrop;
        console.log("PickupPlaceid1exit", PickupPlaceid1);
        console.log("DropoffPlaceid1exit", DropoffPlaceid1);
        console.log("entrytimeexit", entrytime);
        console.log("pickupdateexit", pickupdate);
        console.log("selectionTypeexit", selectionType);

      

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // ✅ Determine which date to use
      const travelDate = JSON.stringify({ 0: pickupdate });

      // if (pickupdate) {
      //   travelDate = pickupdate;
      // } else if (exitpickupdate) {
      //   travelDate = exitpickupdate;
      // }

      // ✅ Build query parameters dynamically
      let params;
      if (selectionType === "Entry Port") {
        if (!PickupPlaceid ) {
          throw new Error("Pickup location is required");
        }
       params = {
        pickup: JSON.stringify(PickupPlaceid),
        date: travelDate, // ✅ Include date
        time: JSON.stringify(entrytime),
      };
    }
    else if (selectionType === "Exit Port") {
       // No need to parse JSON string as it's already an object
       const pickupCoords = PickupPlaceid1;
       
       if (!pickupCoords) {
         throw new Error("Pickup location is required");
       }
       
       params = {
        pickup: JSON.stringify(pickupCoords),
        date: travelDate, // ✅ Include date
        time: JSON.stringify(entrytime),
      };
    }

      if (selectionType === "Entry Port") {
        if (DropoffPlaceid) {
          params.dropoff = JSON.stringify(DropoffPlaceid); // ✅ Add dropoff only if available
        }
      }
      else if (selectionType === "Exit Port") {
        if (DropoffPlaceid1) {
          // No need to parse JSON string as it's already an object
          const dropoffCoords = DropoffPlaceid1;
          params.dropoff = JSON.stringify(dropoffCoords); // ✅ Add dropoff only if available
        }
      }
      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/vehicles-list`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

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

export const fetchVehicleDetails = createAsyncThunk(
  "pickupDrop/fetchVehiclesDetails",
  async ({ city, country, type }, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state

      const {
        PickupPlaceid,
        DropoffPlaceid,
        selectedVehicle,
        selectedVehicle1,
        pickupdate,
        entrytime,
        picktype,
        droptype,
        selectionType,
        PickupPlaceid1,
        DropoffPlaceid1,
      } = state.pickupDrop;

      
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

      // ✅ Determine which date to use
      const travelDate = JSON.stringify({ 0: pickupdate });

      // if (pickupdate) {
      //   travelDate = pickupdate;
      // } else if (exitpickupdate) {
      //   travelDate = exitpickupdate;
      // }

      // ✅ Build query parameters dynamically
      const params =
        type === "zone"
          ? {
            ...(selectionType === "Entry Port" && {
              from_zone_id: PickupPlaceid,
              to_zone_id: DropoffPlaceid,
              vehicle_id: selectedVehicle?.id,
              date: travelDate,
              time: JSON.stringify(entrytime),
              mode: selectedVehicle?.mode,
              dmc_id: selectedVehicle?.dmcId,
              pickup_type: picktype,
              drop_type: droptype,
              city: city,
              country: country,
              type,
            }),
            ...(selectionType === "Exit Port" && {
              pickup: JSON.stringify(PickupPlaceid1),
              ...(DropoffPlaceid1 && {
                dropoff: JSON.stringify(DropoffPlaceid1),
              }),
              vehicle_id: selectedVehicle1?.id, // ✅ Correct way to access id
              mode: selectedVehicle1?.mode, // ✅ Correct way to access mode
              dmc_id: selectedVehicle1?.dmcId, // ✅ Correct way to access dmcId
              pickup_type: picktype,
              drop_type: droptype,
              date: travelDate,
              time: JSON.stringify(entrytime),
              city: city,
              country: country,
              type,
            }),
          } : {
              ...(selectionType === "Entry Port" && {
              pickup: JSON.stringify(PickupPlaceid),
              ...(DropoffPlaceid && {
                dropoff: JSON.stringify(DropoffPlaceid),
              }), // ✅ Add dropoff if available
              date: travelDate, // ✅ Include date
              time: JSON.stringify(entrytime),
              vehicle_id: selectedVehicle?.id, // ✅ Correct way to access id
              mode: selectedVehicle?.mode, // ✅ Correct way to access mode
              dmc_id: selectedVehicle?.dmcId, // ✅ Correct way to access dmcId
              city: city,
              country: country,
            }),
            ...(selectionType === "Exit Port" && {
              pickup: JSON.stringify(PickupPlaceid1),
              ...(DropoffPlaceid1 && {
                dropoff: JSON.stringify(DropoffPlaceid1),
              }),
              vehicle_id: selectedVehicle1?.id, // ✅ Correct way to access id
              mode: selectedVehicle1?.mode, // ✅ Correct way to access mode
              dmc_id: selectedVehicle1?.dmcId, // ✅ Correct way to access dmcId
              date: travelDate,
              time: JSON.stringify(entrytime),
              city: city,
              country: country,
            }),
            };

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/vehicle-details`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

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

export const fetchPortCity = createAsyncThunk(
  "pickupDrop/fetchPortCity",
  async ({ city, tourId, type }, { rejectWithValue, dispatch }) => {
    try {
      console.log("Fetching port city data with params:", { city, tourId });

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        console.error("Missing auth token or agent ID");
        throw new Error("Authorization required");
      }

      // Ensure tourId is properly formatted
      if (tourId === undefined || tourId === null || tourId === "") {
        console.error("Tour ID is missing or invalid:", tourId);
        throw new Error("Tour ID is required");
      }

      // Ensure tourId is a number for the API
      const numericTourId =
        typeof tourId === "string" ? parseInt(tourId, 10) : tourId;

      if (isNaN(numericTourId)) {
        console.error("Tour ID is not a valid number:", tourId);
        throw new Error("Tour ID must be a valid number");
      }

      // ✅ Build query parameters dynamically
      const params = {
        city: city,
        tour_id: numericTourId, // Ensure it's a number
        type: type,
      };

      console.log("API Request params:", params);

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/get-ports`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
          "Content-Type": "application/json",
        },
      });

      console.log("Port city API response:", response.data);
      return response.data;
    } catch (error) {
      console.error("Error in fetchPortCity:", error);

      if (error.response) {
        console.error("API response error:", error.response.data);
        console.error("API response status:", error.response.status);
        console.error("API response headers:", error.response.headers);
      }

      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }

      return rejectWithValue(error.response?.data || error.message);
    }
  }
);
export const fetchLocalZone = createAsyncThunk(
  "pickupDrop/fetchLocalZone",
  async ({ id, type }, { rejectWithValue, dispatch }) => {
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(`${BASE_URL}/zone-lists`, {
        params: {
          id,
          type,
        },
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      console.log("Local Zone Response:", response.data);

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        dispatch(logoutUser());
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchZoneVehicles = createAsyncThunk(
  "pickupDrop/fetchZoneVehicles",
  async (_, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const {
        PickupPlaceid,
        DropoffPlaceid,
        entrypickup,
        exitpickup,
        entrydropoff,
        exitdropoff,
        pickupdate,
        entrytime,
        exittime,
        picktype,
        droptype,
        selectedType,
      } = state.pickupDrop;

      if (!PickupPlaceid) {
        throw new Error("Pickup location is required");
      }

      if (!droptype) {
        console.error("Drop-off type is missing");
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // ✅ Format pickdate as JSON { "0": "YYYY-MM-DD" }
      const travelDate = JSON.stringify({ 0: pickupdate });

      // ✅ Build query parameters dynamically
      const params =
        selectedType === "entry"
          ? {
              pickupid: PickupPlaceid,
              dropoffid: DropoffPlaceid,
              PickUpLocation: entrypickup,
              DropOffLocation: entrydropoff,
              time: entrytime,
              date: travelDate, // ✅ Include formatted date
              pickup_type: picktype,
              drop_type: droptype, // Set default to 'hotel' if droptype is missing
            }
          : {
              pickupid: PickupPlaceid,
              dropoffid: DropoffPlaceid,
              PickUpLocation: exitpickup,
              DropOffLocation: exitdropoff,
              time: exittime,
              date: travelDate, // ✅ Include formatted date
              pickup_type: picktype,
              drop_type: droptype, // Set default to 'hotel' if droptype is missing
            };

      console.log("Zone API parameters:", params);

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/zone-vehicles`, {
        params,
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

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

// Async thunk for fetching hotels
export const submitPickupDrop = createAsyncThunk(
  "pickupDrop/submit",
  async ({ selectedType }, { getState, rejectWithValue, dispatch }) => {
    try {
      const state = getState();
      const {
        entrypickup,
        entrydropoff,
        exitpickup,
        exitdropoff,
        pickupdate,
        //exitpickupdate,
        entrytime,
        exittime,
        adult,
        bookingtype,
        children,

        type,
        type1,
        tourid,
        details,
        details1,
      } = state.pickupDrop;

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

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Create FormData instances
      // const formData = new FormData();
      let formData = {};
      let formData1 = {};
      console.log("abc", typeof details);
      console.log(typeof details);

      // Create JSON objects based on the provided conditions
      //let json0 = {};
      //let json1 = {};

      if (
        entrypickup &&
        entrydropoff &&
        pickupdate &&
        entrytime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
        // const json0 = {
        //   entrypickup,
        //   entrydropoff,
        //   pickupdate,
        //   vehicletype,
        //   traveller0,
        // };

        // const formattedData = [
        //   {
        //     entrypickup: json0.entrypickup,
        //     entrydropoff: json0.entrydropoff,
        //     pickupdate: json0.pickupdate,
        //     vehicletype: json0.vehicletype,
        //     traveller0: json0.traveller0,
        //   },
        // ];

        // let dataString = JSON.stringify(formattedData);
        // dataString = `\\${dataString.slice(0, 1)}${dataString.slice(
        //   1,
        //   -1
        // )}\\${dataString.slice(-1)}`;
        // // Append the data to formData
        // formData.append("data", details);
        // formData.append("type", type);
        // formData.append("agent_id", AgentId);
        // formData.append("tour_id", tourid);
        formData = {
          data: details,
          type: type,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }

      if (
        exitpickup &&
        exitdropoff &&
        pickupdate &&
        exittime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
        //   json1 = {
        //     exitpickup,
        //     exitdropoff,
        //     exitpickupdate,
        //     exittime,
        //     vehicletype1,
        //     traveller1,
        //   };

        //   // Append json1 to formData1
        //   formData1.append("data", JSON.stringify(json1));
        //   formData1.append("type", type1);
        //   formData1.append("agent_id", AgentId);
        //   formData1.append("tour_id", tourid);

        formData1 = {
          data: details1,
          type: type1,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }

      // console.log("formData:", formData);
      // console.log("formData1:", formData1);
      // console.log("entrypickup:", entrypickup);
      // console.log("entrydropoff:", entrydropoff);
      // console.log("pickupdate:", pickupdate);
      // console.log("entrytime:", entrytime);
      // console.log("adult:", adult);
      // console.log("children:", children);

      // console.log("exitpickup:", exitpickup);
      // console.log("exitdropoff:", exitdropoff);
      // console.log("exittime:", exittime);

      // Determine which FormData to send
      let selectedFormData = null;

      // Check if both formData and formData1 are available, prioritize formData1
      if (selectedType === "entry" && "data" in formData) {
        selectedFormData = formData;
      } else if (selectedType === "exit" && "data" in formData1) {
        selectedFormData = formData1;
      } else {
        throw new Error("No valid data to submit.");
      }
      console.log("formselect", selectedFormData);

      // Debugging: Log selected FormData
      // console.log("Selected FormData content:");
      // for (let [key, value] of selectedFormData.entries()) {
      //   console.log(`${key}: ${value}`);
      // }

      // Actual API call
      const response = await axios.post(
        `${BASE_URL}/create-booking`,
        selectedFormData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "multipart/form-data",
            "agent-id": AgentId,
          },
        }
      );

      console.log("API Response:", response.data);

      // Check if response has the expected structure
      if (!response.data || !response.data.service) {
        console.error("Invalid response structure:", response.data);
        return response.data;
      }

      // Extracting response data - CORRECTED to use service.data
      const responseData = response.data.service.data || [];
      console.log("Response data array:", responseData);

      if (!Array.isArray(responseData)) {
        console.error(
          "Expected responseData to be an array but got:",
          responseData
        );

        return response.data;
      }

      // Look for specific identifiers in the data to determine port type
      const EntryPortData = responseData.filter((item) => {
        // Check if this is an entry port item by checking the response type or other properties
        return (
          // The type in the main response object
          response.data.service.type === "entry_port" ||
          // Or check if there's any explicit type in the item itself
          (item.type &&
            typeof item.type === "string" &&
            (item.type.includes("entry") || item.type === "entry_port"))
        );
      });

      const ExitPortData = responseData.filter((item) => {
        // Check if this is an exit port item
        return (
          // The type in the main response object
          response.data.service.type === "exit_port" ||
          // Or check if there's any explicit type in the item itself
          (item.type &&
            typeof item.type === "string" &&
            (item.type.includes("exit") || item.type === "exit_port"))
        );
      });

      console.log("Filtered Entry Port data:", EntryPortData);
      console.log("Filtered Exit Port data:", ExitPortData);

      // Since the API might return only one result based on the request type,
      // let's also consider the service type to determine what to dispatch
      if (
        response.data.service.type === "entry_port" &&
        responseData.length > 0
      ) {
        dispatch(setEntryport(responseData));
        console.log("Dispatched to Entry Port:", responseData);
      } else if (
        response.data.service.type === "exit_port" &&
        responseData.length > 0
      ) {
        dispatch(setExitport(responseData));
        console.log("Dispatched to Exit Port:", responseData);
      } else {
        // Apply the original filter logic as fallback
        if (EntryPortData.length > 0) {
          dispatch(setEntryport(EntryPortData));
        }

        if (ExitPortData.length > 0) {
          dispatch(setExitport(ExitPortData));
        }
      }

      // Check the type in the response and assign to formData or formData1
      const data = response.data.order.data;
      // const cleanedData = data.slice(1, -1); // Remove the first and last character (quotes)
      // const decodedData = cleanedData
      //   .replace(/\\"/g, '"')
      //   .replace(/\\\//g, "/");
      const responseType = response.data.order.type;

      // Parse the 'data' field from the response
      // const parsedData = JSON.parse(decodedData);
      // console.log(parsedData);

      if (responseType === "entry_port") {
        // Create an array to hold EntryPort data
        const formData = [];
        Object.entries(data).forEach(([key, value]) => {
          switch (key) {
            case "entrypickup":
              formData.push({ "Pick Up Location": value });
              break;
            case "entrydropoff":
              formData.push({ "Drop Off Location": value });
              break;
            case "pickupdate":
              formData.push({ Date: value });
              break;
            case "entrytime":
              formData.push({ Time: value });
              break;
            case "vehicles_name":
              formData.push({ Vehicle: value });
              break;
            case "adults":
              formData.push({ Adult: value });
              break;
            case "children":
              formData.push({ Children: value });
              break;
            default:
              break;
          }
        });
        console.log("form", formData);

        // Update the state with formData
        dispatch(updateFormData(formData));
      } else if (responseType === "exit_port") {
        // Create an array to hold ExitPort data
        const formData1 = [];
        Object.entries(data).forEach(([key, value]) => {
          switch (key) {
            case "exitpickup":
              formData1.push({ "Pick Up Location": value });
              break;
            case "exitdropoff":
              formData1.push({ "Drop Off Location": value });
              break;
            case "exitpickupdate":
              formData1.push({ Date: value });
              break;
            case "exittime":
              formData1.push({ Time: value });
              break;
            case "vehicles_name":
              formData1.push({ Vehicle: value });
              break;
            case "adults":
              formData1.push({ Adult: value });
              break;
            case "children":
              formData1.push({ Children: value });
              break;
            default:
              break;
          }
        });
        console.log("form1", formData1);

        // Update the state with formData1
        dispatch(updateFormData1(formData1));
      }

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

const pickupDropSlice = createSlice({
  name: "pickupDrop",
  initialState: {
    status: "idle", // Tracks the status: 'idle', 'loading', 'succeeded', 'failed'
    error: null, // Stores any errors from API requests
    entrypickup: "",
    entrydropoff: "",
    exitpickup: "",
    exitdropoff: "",
    pickupdate: "",
    exitpickupdate: "",
    entrytime: "",
    exittime: "",
    adult: "",
    vehicletype1: "",
    children: 0,
    traveller1: 0,
    formData: [], // Array to hold initial state variables for entry-related data
    formData1: [], // Array to hold initial state variables for exit-related data
    details: [],
    details1: [],
    type: "entry_port",
    type1: "exit_port",
    tourid: 0,
    entryport: [],
    exitport: [],
    vehicles: [],
    vehicles1: [],
    selectedVehicle: null,
    selectedVehicle1: null, // For Exit Port
    selectionType: "",
    PickupPlaceid: "",
    DropoffPlaceid: "",
    mode: {}, // Key-value pair: { vehicleId: mode }
    pricemode: "",
    bookingtype: "",
    response: [],
    selectedPort: "",
    // Add port city state
    portCityStatus: "idle",
    portCityError: null,
    portCityData: null,
    zone: [],
    picktype: "",
    droptype: "",
    portZoneType: "",
    PickupPlaceid1: "",
    DropoffPlaceid1: "",
    selectedVehicleId: null,
    adultCount: 1,
    childCount: 0,
  },
  reducers: {
    setentrypickup: (state, action) => {
      state.entrypickup = action.payload;
      console.log("entrypickup", state.entrypickup);
    },
    setentrydropoff: (state, action) => {
      state.entrydropoff = action.payload;
      console.log("entrydropoff", state.entrydropoff);
    },
    setexitpickup: (state, action) => {
      state.exitpickup = action.payload;
      console.log("exitpickup", state.exitpickup);
    },
    setexitdropoff: (state, action) => {
      state.exitdropoff = action.payload;
      console.log("exitdropoff", state.exitdropoff);
    },
    setPickupPlaceid: (state, action) => {
      state.PickupPlaceid = action.payload;
      console.log("pickplaceid", state.PickupPlaceid);
    },
    setDropoffPlaceid: (state, action) => {
      state.DropoffPlaceid = action.payload;
      console.log("dropplaceid", state.DropoffPlaceid);
    },
    setPickupPlaceid1: (state, action) => {
      state.PickupPlaceid1 = action.payload;
      console.log("pickplaceid1", state.PickupPlaceid1);
    },
    setDropoffPlaceid1: (state, action) => {
      state.DropoffPlaceid1 = action.payload;
      console.log("dropplaceid1", state.DropoffPlaceid1);
    },
    setpickupdate: (state, action) => {
      state.pickupdate = action.payload;
    },
    setexitpickupdate: (state, action) => {
      state.exitpickupdate = action.payload;
    },
    setentrytime: (state, action) => {
      state.entrytime = action.payload;
      console.log("tt", state.entrytime);
    },
    setexittime: (state, action) => {
      state.exittime = action.payload;
    },
    setadult: (state, action) => {
      state.adult = action.payload;
      console.log("adult", state.adult);
    },
    setchildren: (state, action) => {
      state.children = action.payload;
      console.log("chil", state.children);
    },
    setvehicletype1: (state, action) => {
      state.vehicletype1 = action.payload;
    },
    settravellers1: (state, action) => {
      state.traveller1 = action.payload;
    },
    settourId: (state, action) => {
      state.tourid = action.payload;
    },
    // setbookingType: (state, action) => {
    //   state.type = action.payload;
    // },
    setbookingtype1: (state, action) => {
      state.bookingtype = action.payload;
      console.log("bookingtype", state.bookingtype);
    },
    setentrydata: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details = []; // Ensure details is an array
      }

      // If index 0 exists, merge the new data
      if (state.details[0]) {
        state.details[0] = { ...state.details[0], ...action.payload }; // ✅ Merge with existing data
      } else {
        state.details[0] = action.payload; // ✅ Insert if empty
      }

      console.log("Updated form data at index 0:", state.details);
    },

    setexitdata: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details1 = []; // Ensure details is an array
      }
      if (state.details1[0]) {
        state.details1[0] = { ...state.details1[0], ...action.payload }; // Append new data instead of overriding
      } else {
        state.details1[0] = action.payload; // ✅ Insert if empty
      }
      console.log("form2", state.details1[0]);
    },
    setResponse: (state, action) => {
      state.response = action.payload;
      console.log("response125", state.response);
    },
    setPriceMode: (state, action) => {
      state.pricemode = action.payload;
      console.log("m", state.pricemode);
    },
    setEntryport: (state, action) => {
      state.entryport = action.payload;
    },
    setExitport: (state, action) => {
      state.exitport = action.payload;
    },
    updateFormData: (state, action) => {
      // Update the formData array with entry-related variables
      // state.formData = [
      //   { "Pick Up Location": state.entrypickup },
      //   { "Drop Off Location": state.entrydropoff },
      //   { Date: state.pickupdate },
      //   { Time: state.entrytime },
      //   { Vehicle: state.vehicletype },
      //   { Traveller: state.traveller0 },
      // ];
      state.formData = action.payload;
      console.log("entryport updated", state.formData);
    },

    updateFormData1: (state, action) => {
      // Update the formData1 array with exit-related variables
      // state.formData1 = [
      //   { "Pick Up Location": state.exitpickup },
      //   { "Drop Off Location": state.exitdropoff },
      //   { Date: state.exitpickupdate },
      //   { Time: state.exittime },
      //   { Vehicle: state.vehicletype1 },
      //   { Traveller: state.traveller1 },
      // ];
      state.formData1 = action.payload;
      console.log("exitport updated", state.formData1);
    },
    setSelectedVehicle: (state, action) => {
      state.selectedVehicle = action.payload;
      console.log("mode array", state.selectedVehicle);
    },
    setSelectedVehicle1: (state, action) => {
      state.selectedVehicle1 = action.payload;
    },
    setSelectionType: (state, action) => {
      state.selectionType = action.payload;
      console.log("porttype", state.selectionType);
    },
    setMode: (state, action) => {
      state.mode = { ...state.mode, ...action.payload }; // ✅ Merge new mode
      console.log("Updated Mode:", state.mode);
    },
    setSelectedPort: (state, action) => {
      state.selectedPort = action.payload;
      console.log("selectedPort", state.selectedPort);
    },
    resetVehicles: (state, action) => {
      state.vehicles = [];
      state.DropoffPlaceid = "";
      state.PickupPlaceid = "";
      state.DropoffPlaceid1 = "";
      state.PickupPlaceid1 = "";
      //state.selectedVehicleId = null;
      state.status = "idle";
    },
    setPicktype: (state, action) => {
      state.picktype = action.payload;
      console.log("picktype", state.picktype);
    },
    setDroptype: (state, action) => {
      state.droptype = action.payload;
      console.log("droptype", state.droptype);
    },
    setPortZoneType: (state, action) => {
      state.portZoneType = action.payload;
      console.log("portZoneType", state.portZoneType);
    },
    setSelectedVehicleId: (state, action) => {
      state.selectedVehicleId = action.payload;
    },
    setAdultCount: (state, action) => {
      state.adultCount = action.payload;
    },
    setChildCount: (state, action) => {
      state.childCount = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchVehicles.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchVehicles.fulfilled, (state, action) => {
        state.status = "succeeded";
        if(state.selectionType === "Entry Port"){
          state.vehicles = action.payload;
          console.log("vehicle", state.vehicles);
        }
        if(state.selectionType === "Exit Port"){
          state.vehicles1 = action.payload;
          console.log("vehicle1", state.vehicles1);
        }

        //console.log("Fetched Attractions Data:", action.payload); // Log the fetched data
      })
      .addCase(fetchVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload; // Save the error in state
        console.error("Error fetching attractions:", action.payload); // Log error details
      })
      // Handle fetchPortCity actions
      .addCase(fetchPortCity.pending, (state) => {
        state.portCityStatus = "loading";
        state.portCityError = null;
      })
      .addCase(fetchPortCity.fulfilled, (state, action) => {
        state.portCityStatus = "succeeded";
        state.portCityData = action.payload;
        console.log("Port City Data:", action.payload); // Log the fetched data
      })
      .addCase(fetchPortCity.rejected, (state, action) => {
        state.portCityStatus = "failed";
        state.portCityError = action.payload;
        console.error("Error fetching port city data:", action.payload);
      });

    builder
      .addCase(fetchZoneVehicles.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchZoneVehicles.fulfilled, (state, action) => {
        state.status = "succeeded";
        if(state.selectionType === "Entry Port"){
          state.vehicles = action.payload;
          console.log("vehicle", state.vehicles);
        }
        if(state.selectionType === "Exit Port"){
          state.vehicles1 = action.payload;
          console.log("vehicle1", state.vehicles1);
        }
        console.log("vehicles", state.vehicles);

        // ✅ Reset PickupPlaceid & DropoffPlaceid after API success
        //state.PickupPlaceid = null;
        //state.DropoffPlaceid = null;

        //console.log("Fetched Attractions Data:", action.payload); // Log the fetched data
      })
      .addCase(fetchZoneVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload; // Save the error in state
        console.error("Error fetching attractions:", action.payload); // Log error details
      });

    builder
      .addCase(fetchLocalZone.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchLocalZone.fulfilled, (state, action) => {
        state.loading = false;
        state.zone = action.payload;
      })
      .addCase(fetchLocalZone.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  },
});

export const {
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setexitpickupdate,
  setentrytime,
  setexittime,
  setadult,
  setvehicletype1,
  setchildren,
  settravellers1,
  updateFormData,
  updateFormData1,
  settourId,
  setentrydata,
  setexitdata,
  setEntryport,
  setExitport,
  setSelectedVehicle,
  setSelectedVehicle1,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setPickupPlaceid1,
  setDropoffPlaceid1,
  setMode,
  setPriceMode,
  setResponse,
  resetVehicles,
  setbookingtype1,
  setSelectedPort,
  //setbookingType,
  setPicktype,
  setDroptype,
  setPortZoneType,
  setSelectedVehicleId,
  setAdultCount,
  setChildCount,
} = pickupDropSlice.actions;

export default pickupDropSlice.reducer;
