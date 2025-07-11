import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.jsx";
import { Provider } from "react-redux";
import { store } from "./store/store";
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';

// Create a custom theme
// const theme = createTheme({
//   palette: {
//     primary: {
//       main: '#f97316', // Orange color from the design
//     },
//     secondary: {
//       main: '#2e7d32', // Green color for the logo
//     },
//     background: {
//       default: '#ffffff',
//     },
//   },
//   typography: {
//     fontFamily: '"Roboto", "Arial", sans-serif',
//     h1: {
//       fontWeight: 700,
//     },
//     h2: {
//       fontWeight: 600,
//     },
//     h3: {
//       fontWeight: 600,
//     },
//     h4: {
//       fontWeight: 600,
//     },
//     h5: {
//       fontWeight: 500,
//     },
//     h6: {
//       fontWeight: 500,
//     },
//   },
//   components: {
//     MuiButton: {
//       styleOverrides: {
//         root: {
//           borderRadius: 8,
//           textTransform: 'none',
//         },
//       },
//     },
//   },
// });

ReactDOM.createRoot(document.getElementById("root")).render(
  <Provider store={store}>
  
     
      <App />
 
  </Provider>
);
