// import { createSlice } from "@reduxjs/toolkit";

// const initialState = {
//   agent_id: "",
//   name: "",
//   email: "",
//   isAuthenticated: false,
// };

// const loginSlice = createSlice({
//   name: "login",
//   initialState,
//   reducers: {
//     setAgentId: (state, action) => {
//       state.agent_id = action.payload;
//     },
//     setName: (state, action) => {
//       state.name = action.payload;
//     },
//     setEmail1: (state, action) => {
//       state.email = action.payload;
//     },
//     setAuthenticated: (state, action) => {
//       state.isAuthenticated = action.payload;
//     },
//     resetUser: (state) => {
//       state.name = "";
//       state.email = "";
//       state.isAuthenticated = false;
//     },
//   },
// });

// export const { setAgentId, setName, setEmail1, setAuthenticated, resetUser } =
//   loginSlice.actions;

// export default loginSlice.reducer;


import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  agent_id: "",
  name: "",
  email: "",
  isAuthenticated: false,
};

const loginSlice = createSlice({
  name: "login",
  initialState,
  reducers: {
    setAgentId: (state, action) => {
      state.agent_id = action.payload;
    },
    setName: (state, action) => {
      state.name = action.payload;
    },
    setEmail1: (state, action) => {
      state.email = action.payload;
    },
    setAuthenticated: (state, action) => {
      state.isAuthenticated = action.payload;
    },
    resetUser: (state) => {
      state.agent_id = "";
      state.name = "";
      state.email = "";
      state.isAuthenticated = false;
    },
  },
});

export const { setAgentId, setName, setEmail1, setAuthenticated, resetUser } = loginSlice.actions;
export default loginSlice.reducer;
