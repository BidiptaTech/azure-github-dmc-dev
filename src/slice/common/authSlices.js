// import { createSlice } from "@reduxjs/toolkit";
// import Cookies from "js-cookie";

// const initialState = {
//   isAuthenticated: localStorage.getItem("isAuthenticated") === "true",
//   agentId: Cookies.get("AgentId") || null,
//   tourId: null, // Add tourId to the authentication state
// };

// const authSlice = createSlice({
//   name: "auth",
//   initialState,
//   reducers: {
//     login: (state, action) => {
//       state.isAuthenticated = true;
//       localStorage.setItem("isAuthenticated", "true");

//       if (action.payload?.agentId) {
//         state.agentId = action.payload.agentId;
//         Cookies.set("AgentId", action.payload.agentId);
//       }
//     },
//     logout: (state) => {
//       state.isAuthenticated = false;
//       localStorage.setItem("isAuthenticated", "false");
//       state.agentId = null;
//       state.tourId = null;
//       Cookies.remove("AgentId");
//     },
//     setTourIdd: (state, action) => {
//       state.tourId = action.payload;
//     },
//   },
// });

// export const { login, logout, setTourIdd } = authSlice.actions;

// export default authSlice.reducer;

// authSlice.js

// import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
// import axios from "axios";
// import Cookies from "js-cookie";

// const initialState = {
//   isAuthenticated: localStorage.getItem("isAuthenticated") === "true",
//   agentId: Cookies.get("AgentId") || null,
//   tourId: null,
//   logoutStatus: 'idle',
//   logoutError: null,
// };

// // Async thunk for logout
// export const logoutUser = createAsyncThunk("auth/logoutUser", async (_, { rejectWithValue }) => {
//   const token = Cookies.get("authToken");
//   if (!token) {
//     return rejectWithValue("No authentication token found.");
//   }

//   try {
//     const response = await axios.post(
//       "https://dmcdemo.coactivehub.com/backadm-dmc/api/v1/logout",
//       {},
//       {
//         headers: {
//           Authorization: `Bearer ${token}`,
//         },
//       }
//     );

//     if (response.data.success) {
//       return response.data;
//     } else {
//       return rejectWithValue("Logout failed.");
//     }
//   } catch (error) {
//     return rejectWithValue(error.response?.data?.message || "Logout request failed.");
//   }
// });

// const authSlice = createSlice({
//   name: "auth",
//   initialState,
//   reducers: {
//     login: (state, action) => {
//       state.isAuthenticated = true;
//       localStorage.setItem("isAuthenticated", "true");
//       if (action.payload?.agentId) {
//         state.agentId = action.payload.agentId;
//         Cookies.set("AgentId", action.payload.agentId);
//       }
//     },
//     logout: (state) => {
//       state.isAuthenticated = false;
//       localStorage.setItem("isAuthenticated", "false");
//       state.agentId = null;
//       state.tourId = null;
//       Cookies.remove("AgentId");
//       Cookies.remove("authToken");
//       localStorage.removeItem("authToken");
//     },
//     setTourIdd: (state, action) => {
//       state.tourId = action.payload;
//     },
//   },
//   extraReducers: (builder) => {
//     builder
//       .addCase(logoutUser.pending, (state) => {
//         state.logoutStatus = 'loading';
//         state.logoutError = null;
//       })
//       .addCase(logoutUser.fulfilled, (state) => {
//         state.logoutStatus = 'succeeded';
//       })
//       .addCase(logoutUser.rejected, (state, action) => {
//         state.logoutStatus = 'failed';
//         state.logoutError = action.payload;
//       });
//   },
// });

// export const { login, logout, setTourIdd } = authSlice.actions;
// export default authSlice.reducer;

// authSlice.js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";

import getUserCountry from "./getUserCountry";
import { BASE_URL } from "@/services/api";

// Function to get initial state from cookies
const getInitialState = () => {
  const isAuthenticated = localStorage.getItem("isAuthenticated") === "true";
  const agentId = Cookies.get("AgentId") || null;
  const Username = Cookies.get("Username") || null;
  const Email = Cookies.get("Email") || null;
  const exchangeRate = Cookies.get("exchangeRate") || null;
  const currencyCode = Cookies.get("currencyCode") || null;
  const currencySymbol = Cookies.get("currencySymbol") || null;
  const usdExchangeRate = Cookies.get("usdExchangeRate") || null;
  const usdCurrencyCode = Cookies.get("usdCurrencyCode") || null;
  const usdCurrencySymbol = Cookies.get("usdCurrencySymbol") || null;
  const countryCode = Cookies.get("countryCode") || null;
  const currentTax = Cookies.get("currentTax") || null;
  const sgdTax = Cookies.get("sgdTax") || null;
  const usdTax = Cookies.get("usdTax") || null;
  const DmcLogo = Cookies.get("DmcLogo") || null;
  const DmcName = Cookies.get("DmcName") || null;
  const dialMaxLength = Cookies.get("dialMaxLength") || null;
  const dialMinLength = Cookies.get("dialMinLength") || null;
  const PriceHide = Cookies.get("PriceHide") ? String(Cookies.get("PriceHide")) : "0";
  const userRole = Cookies.get("userRole") || "Agent"; // Default to Agent role
  
  // Parse user_country from cookies or localStorage
  let user_country = null;
  try {
    const storedCountry = Cookies.get("user_country") || localStorage.getItem("user_country");
    if (storedCountry) {
      user_country = JSON.parse(storedCountry);
    }
  } catch (error) {
    console.error("Error parsing user_country:", error);
  }
  
  const zone_on = Cookies.get("zone_on") || null;

  return {
    isAuthenticated,
    agentId,
    tourId: null,
    loginStatus: "idle",
    loginError: null,
    logoutStatus: "idle",
    logoutError: null,
    Username,
    Email,
    exchangeRate,
    currencyCode,
    currencySymbol,
    DmcLogo,
    DmcName,
    usdExchangeRate,
    usdCurrencyCode,
    usdCurrencySymbol,
    currentTax,
    sgdTax,
    usdTax,
    countryCode,
    dialMaxLength,
    dialMinLength,
    PriceHide,
    userRole,
    user_country,
    zone_on,
  };
};

// Initial state
const initialState = getInitialState();

// Async thunk for login
export const loginUser = createAsyncThunk(
  "auth/loginUser",
  async ({ email, password }, { rejectWithValue }) => {
    try {
      const { country, country_code } = await getUserCountry();
      console.log("Country from getUserCountry:", country);
      const response = await axios.post(
        `${BASE_URL}/login`,
        { email, password },
        { headers: { "user-country": country } },
        { withCredentials: true }
      );

      if (response.data.success) {
        const {
          token,
          agent_id: agentId,
          name: Username,
          email: Email,
          dmc_name: DmcName,
          current_exchange_rate: exchangeRate,
          current_currency_code: currencyCode,
          current_currency_symbol: currencySymbol,
          inr_exchange_rate: inrExchangeRate,
          inr_currency_code: inrCurrencyCode,
          inr_currency_symbol: inrCurrencySymbol,
          usd_exchange_rate: usdExchangeRate,
          usd_currency_code: usdCurrencyCode,
          usd_currency_symbol: usdCurrencySymbol,
          usd_tax: usdTax,
          sgd_tax: sgdTax,
          agent_country_tax: currentTax,
          logo: DmcLogo,
          agent_country_max_length: dialMaxLength,
          agent_country_min_length: dialMinLength,
          price_hide: PriceHide,
          user_role: userRole, // Add user_role from API response
          user_country: user_country,
          zone_on: zone_on,
        } = response.data.user;

        console.log("DMC Logo from response:", zone_on); // Log the logo URL
        const countryCode = country_code;
        // Convert currency symbol from Unicode to string without the semicolon
        const convertedCurrencySymbol = currencySymbol
          .replace(/\\u/g, "&#x")
          .replace(/"/g, "");

        const expiryDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 days

        Cookies.set("authToken", token, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("AgentId", agentId, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("Username", Username, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("Email", Email, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("exchangeRate", exchangeRate, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("currencyCode", currencyCode, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("currencySymbol", convertedCurrencySymbol, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });

        // Store INR and USD details as well
        Cookies.set("inrExchangeRate", inrExchangeRate, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("inrCurrencyCode", inrCurrencyCode, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("inrCurrencySymbol", inrCurrencySymbol, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("usdExchangeRate", usdExchangeRate, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("usdCurrencyCode", usdCurrencyCode, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("usdCurrencySymbol", usdCurrencySymbol, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("countryCode", country_code, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("currentTax", currentTax, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("sgdTax", sgdTax, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("usdTax", usdTax, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("dialMaxLength", dialMaxLength, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("dialMinLength", dialMinLength, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });

        // Ensure PriceHide is stored as a string both in cookie and for Redux state
        const priceHideString = String(PriceHide);
        Cookies.set("PriceHide", priceHideString, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });

        // Store user role in cookies
        Cookies.set("userRole", userRole || "Agent", {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("user_country", JSON.stringify(user_country), {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });
        Cookies.set("zone_on", String(zone_on), {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });

        // Also store in localStorage as a fallback
        if (user_country) {
          localStorage.setItem("user_country", JSON.stringify(user_country));
        }

        // Store DMC Logo in cookies
        if (DmcLogo) {
          Cookies.set("DmcLogo", DmcLogo, {
            expires: expiryDate,
            secure: true,
            sameSite: "Strict",
          });
        }

        // Add DmcName to cookies
        Cookies.set("DmcName", DmcName, {
          expires: expiryDate,
          secure: true,
          sameSite: "Strict",
        });

        localStorage.setItem("isAuthenticated", "true");

        return {
          agentId,
          Username,
          Email,
          DmcName,
          exchangeRate,
          currencyCode,
          currencySymbol,
          usdExchangeRate,
          usdCurrencyCode,
          usdCurrencySymbol,
          currentTax,
          sgdTax,
          usdTax,
          countryCode,
          dialMaxLength,
          dialMinLength,
          PriceHide: priceHideString,
          DmcLogo,
          userRole: userRole || "Agent",
          user_country,
          zone_on,
        };
      } else {
        return rejectWithValue(response.data.message || "Login failed");
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || "Login request failed."
      );
    }
  }
);

// Async thunk for logout
export const logoutUser = createAsyncThunk(
  "auth/logoutUser",
  async (_, { dispatch, rejectWithValue }) => {
    const token = Cookies.get("authToken");

    if (!token) {
      dispatch(logout()); // Reset state if token not found
      return rejectWithValue("No authentication token found.");
    }

    try {
      const response = await axios.post(
        `${BASE_URL}/logout`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (response.data.success) {
        dispatch(logout()); // Clear state after successful logout
        return response.data;
      }

      dispatch(logout()); // Clear state even if logout fails
      return rejectWithValue("Logout failed.");
    } catch (error) {
      dispatch(logout()); // Ensure logout state reset on 401 errors
      return rejectWithValue(
        error.response?.data?.message || "Logout request failed."
      );
    }
  }
);

// Slice
const authSlice = createSlice({
  name: "auth",
  initialState,
  reducers: {
    logout: (state) => {
      state.isAuthenticated = false;
      state.agentId = null;
      state.tourId = null;
      state.Username = null; // Reset Username
      state.Email = null; // Reset Email
      state.exchangeRate = null; // Reset exchangeRate
      state.currencyCode = null; // Reset currencyCode
      state.currencySymbol = null; // Reset currencySymbol
      state.DmcName = null;
      state.userRole = null; // Reset user role
      Cookies.remove("authToken");
      Cookies.remove("AgentId");
      Cookies.remove("Username");
      Cookies.remove("Email");
      Cookies.remove("exchangeRate");
      Cookies.remove("currencyCode");
      Cookies.remove("currencySymbol");
      Cookies.remove("countryCode");
      Cookies.remove("dialMaxLength");
      Cookies.remove("dialMinLength");
      Cookies.remove("DmcName");
      Cookies.remove("PriceHide");
      Cookies.remove("userRole"); // Remove user role cookie
      Cookies.remove("user_country");
      Cookies.remove("zone_on");
      localStorage.removeItem("isAuthenticated");
      localStorage.removeItem("user_country");
    },
    setTourIdd: (state, action) => {
      state.tourId = action.payload;
    },
    // New reducers to update the new state variables
    setUsername: (state, action) => {
      state.Username = action.payload;
    },
    setEmail: (state, action) => {
      state.Email = action.payload;
    },
    setExchangeRate: (state, action) => {
      state.exchangeRate = action.payload;
    },
    setCurrencyCode: (state, action) => {
      state.currencyCode = action.payload;
    },
    setCurrencySymbol: (state, action) => {
      state.currencySymbol = action.payload;
    },
    setDmcLogo: (state, action) => {
      console.log("setDmcLogo", action.payload);
      state.DmcLogo = action.payload;
    },
    setDmcName: (state, action) => {
      state.DmcName = action.payload;
    },
    setDialMaxLength: (state, action) => {
      state.dialMaxLength = action.payload;
    },
    setDialMinLength: (state, action) => {
      state.dialMinLength = action.payload;
    },
    setPriceHide: (state, action) => {
      state.PriceHide = String(action.payload);
    },
    setZone_on: (state, action) => {
      state.zone_on = action.payload;
    },
    setUserRole: (state, action) => {
      state.userRole = action.payload;
      Cookies.set("userRole", action.payload, {
        expires: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
        secure: true,
        sameSite: "Strict",
      });
    },
    setUser_country: (state, action) => {
      state.user_country = action.payload;
      // Store as JSON string
      if (action.payload) {
        const userCountryStr = JSON.stringify(action.payload);
        localStorage.setItem("user_country", userCountryStr);
        Cookies.set("user_country", userCountryStr, {
          expires: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
          secure: true,
          sameSite: "Strict",
        });
      } else {
        localStorage.removeItem("user_country");
        Cookies.remove("user_country");
      }
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(loginUser.pending, (state) => {
        state.loginStatus = "loading";
        state.loginError = null;
      })
      .addCase(loginUser.fulfilled, (state, action) => {
        state.isAuthenticated = true;
        state.agentId = action.payload.agentId;
        state.Username = action.payload.Username;
        state.Email = action.payload.Email;
        state.DmcName = action.payload.DmcName;
        state.exchangeRate = action.payload.exchangeRate;
        state.currencyCode = action.payload.currencyCode;
        state.currencySymbol = action.payload.currencySymbol;
        state.usdExchangeRate = action.payload.usdExchangeRate;
        state.usdCurrencyCode = action.payload.usdCurrencyCode;
        state.usdCurrencySymbol = action.payload.usdCurrencySymbol;
        state.currentTax = action.payload.currentTax;
        state.sgdTax = action.payload.sgdTax;
        state.usdTax = action.payload.usdTax;
        state.countryCode = action.payload.countryCode;
        state.dialMaxLength = action.payload.dialMaxLength;
        state.dialMinLength = action.payload.dialMinLength;
        state.PriceHide = String(action.payload.PriceHide);
        state.userRole = action.payload.userRole;
        state.user_country = action.payload.user_country;
        state.zone_on = action.payload.zone_on;
        state.DmcLogo = action.payload.DmcLogo;
        
        // Store user_country as JSON string
        if (action.payload.user_country) {
          const userCountryStr = JSON.stringify(action.payload.user_country);
          Cookies.set("user_country", userCountryStr, {
            expires: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
            secure: true,
            sameSite: "Strict",
          });
          localStorage.setItem("user_country", userCountryStr);
        }
        
        state.loginStatus = "succeeded";
      })
      .addCase(loginUser.rejected, (state, action) => {
        state.loginStatus = "failed";
        state.loginError = action.payload;
      })

      // Logout cases
      .addCase(logoutUser.pending, (state) => {
        state.logoutStatus = "loading";
        state.logoutError = null;
      })
      .addCase(logoutUser.fulfilled, (state) => {
        state.logoutStatus = "succeeded";
        state.isAuthenticated = false;
        state.agentId = null;
      })
      .addCase(logoutUser.rejected, (state, action) => {
        state.logoutStatus = "failed";
        state.logoutError = action.payload;
      });
  },
});

export const {
  logout,
  setTourIdd,
  setUsername,
  setEmail,
  setExchangeRate,
  setCurrencyCode,
  setCurrencySymbol,
  setDmcLogo,
  setDmcName,
  setDialMaxLength,
  setDialMinLength,
  setPriceHide,
  setUserRole,
  setUser_country,
  setZone_on,
} = authSlice.actions;
export default authSlice.reducer;
