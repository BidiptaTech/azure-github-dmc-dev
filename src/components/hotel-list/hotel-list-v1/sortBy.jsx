// import React from "react";
// import { Box, Typography, Grid, Paper } from "@mui/material";

// export default function SortBy() {
//   return (
//     <Box
//       display="flex"
//       alignItems="start"
//       sx={{
//         padding: 2,
//         width: "100%",
//         maxWidth: "100%",
//         margin: "0 auto",
//       }}
//     >
//       {/* Sort By Label with colon */}
//       <Typography
//         variant="h6"
//         fontWeight="bold"
//         sx={{
//           marginRight: 1, // Adjusted margin to the right of the label for spacing
//           color: "text.primary",
//           whiteSpace: "nowrap", // Prevent line break
//           fontSize: "14px", // Decrease font size for the label
//           marginTop:'8px'
//         }}
//       >
//         Sort By:
//       </Typography>

//       {/* Sorting Options */}
//       <Grid container spacing={2} justifyContent="flex-start">
//         <Grid item>
//           <Paper
//             elevation={3}
//             sx={{
//               padding: 1.5,
//               textAlign: "center",
//               cursor: "pointer",
//               borderRadius: "50px",
//               boxShadow: 'none',
//               border: '1px solid blue',
//               "&:hover": {
//                 backgroundColor: "primary.light",
//                 color: "primary.contrastText",
//               },
//               minWidth: 120,
//               fontSize: "12px", // Decrease font size inside the Paper
//             }}
//           >
//             Most Popular
//           </Paper>
//         </Grid>
//         <Grid item>
//           <Paper
//             elevation={3}
//             sx={{
//               padding: 1.5,
//               textAlign: "center",
//               cursor: "pointer",
//               borderRadius: "50px",
//               boxShadow: 'none',
//               border: '1px solid blue',
//               "&:hover": {
//                 backgroundColor: "primary.light",
//                 color: "primary.contrastText",
//               },
//               minWidth: 120,
//               fontSize: "12px", // Decrease font size inside the Paper
//             }}
//           >
//             Price - Low to High
//           </Paper>
//         </Grid>
//         <Grid item>
//           <Paper
//             elevation={3}
//             sx={{
//               padding: 1.5,
//               textAlign: "center",
//               cursor: "pointer",
//               borderRadius: "50px",
//               boxShadow: 'none',
//               border: '1px solid blue',
//               "&:hover": {
//                 backgroundColor: "primary.light",
//                 color: "primary.contrastText",
//               },
//               minWidth: 120,
//               fontSize: "12px", // Decrease font size inside the Paper
//             }}
//           >
//             Price - High to Low
//           </Paper>
//         </Grid>
//         <Grid item>
//           <Paper
//             elevation={3}
//             sx={{
//               padding: 1.5,
//               textAlign: "center",
//               cursor: "pointer",
//               borderRadius: "50px",
//               boxShadow: 'none',
//               border: '1px solid blue',
//               "&:hover": {
//                 backgroundColor: "primary.light",
//                 color: "primary.contrastText",
//               },
//               minWidth: 120,
//               fontSize: "12px", // Decrease font size inside the Paper
//             }}
//           >
//             Highest First
//           </Paper>
//         </Grid>
//         <Grid item>
//           <Paper
//             elevation={3}
//             sx={{
//               padding: 1.5,
//               textAlign: "center",
//               cursor: "pointer",
//               borderRadius: "50px",
//               boxShadow: 'none',
//               border: '1px solid blue',
//               "&:hover": {
//                 backgroundColor: "primary.light",
//                 color: "primary.contrastText",
//               },
//               minWidth: 120,
//               fontSize: "12px", // Decrease font size inside the Paper
//             }}
//           >
//             Newest First
//           </Paper>
//         </Grid>
//       </Grid>
//     </Box>
//   );
// }


import React from "react";
import { Box, Typography, Grid, Paper } from "@mui/material";
import { useDispatch, useSelector } from "react-redux";
import { setSortOption } from "@/slice/hotel/CategorySlice"; // Adjust the path accordingly

export default function SortBy() {
  const dispatch = useDispatch();
  const sortOption = useSelector((state) => state.category.sortOption); // Get current sort option from Redux store

  const handleSortChange = (option) => {
    dispatch(setSortOption(option)); // Dispatch the selected sort option
  };

  const sortingOptions = [
    "Most Popular",
    "Price - Low to High",
    "Price - High to Low",
    "Highest First",
    "Newest First",
  ];

  return (
    <Box
      display="flex"
      alignItems="start"
      sx={{
        padding: 2,
        width: "100%",
        maxWidth: "100%",
        margin: "0 auto",
      }}
    >
      {/* Sort By Label with colon */}
      <Typography
        variant="h6"
        fontWeight="bold"
        sx={{
          marginRight: 1, // Adjusted margin to the right of the label for spacing
          color: "text.primary",
          whiteSpace: "nowrap", // Prevent line break
          fontSize: "14px", // Decrease font size for the label
          marginTop: '8px',
        }}
      >
        Sort By:
      </Typography>
      

      {/* Sorting Options */}
      <Grid container spacing={2} justifyContent="flex-start">
        {sortingOptions.map((option) => (
          <Grid item key={option}>
            <Paper
              elevation={3}
              sx={{
                padding: 1.5,
                textAlign: "center",
                cursor: "pointer",
                borderRadius: "50px",
                boxShadow: "none",
                color:`${sortOption === option ? 'primary.contrastText' : 'black'}`,
                bgcolor:`${sortOption === option ? 'primary.light' : 'white'}`,
                border: `1px solid ${sortOption === option ? 'blue' : 'gray'}`, // Highlight selected option
                "&:hover": {
                  backgroundColor: "primary.light",
                  color: "primary.contrastText",
                },
                minWidth: 120,
                fontSize: "12px", // Decrease font size inside the Paper
              }}
              onClick={() => handleSortChange(option)} // Handle sort change
            >
              {option}
            </Paper>
          </Grid>
        ))}
      </Grid>
    </Box>
  );
}
