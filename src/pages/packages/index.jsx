import React from 'react';
import { useNavigate } from 'react-router-dom';
import PreDefinePackagesPage from '../pre-define-packages';
import { useSelector, useDispatch } from 'react-redux';
import { Button, Box } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { createTheme, ThemeProvider } from '@mui/material/styles';
import { setPackageData } from '@/slice/tour-packages/tourPackageSlice';

// Create a custom theme for the button
const theme = createTheme({
  palette: {
    primary: {
      main: '#3554D1', // Match the blue-1 class used elsewhere
    },
  },
  typography: {
    button: {
      fontWeight: 500,
      fontSize: '1.25rem', // Matches text-20
      textTransform: 'none',
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          padding: '15px 35px',
          height: '60px',
          borderRadius: '8px',
          boxShadow: '0px 10px 30px rgba(53, 84, 209, 0.25)',
          transition: 'all 0.3s ease',
          '&:hover': {
            transform: 'translateY(-5px)',
            boxShadow: '0px 15px 30px rgba(53, 84, 209, 0.4)',
          },
        },
      },
    },
  },
});

const Packages = () => {
    const navigate = useNavigate();
    const userRole = useSelector((state) => state.auth.userRole);
    const dispatch = useDispatch();
    // console.log("Current userRole:", userRole);
    // console.log("Is userRole exactly 'Agent'?", userRole === "Agent");
    
    const handleTourPackagesClick = () => {
        dispatch(setPackageData(null));
        navigate('/dashboard/tour-packages');
    };
    
    const handlePreDefinePackagesClick = () => {
        navigate('/dashboard/pre-define-packages');
    };
    
    return (
        <div className="packages-container" style={{ padding: '100px 20px', textAlign: 'center' }}>
            {/* <h1 className="text-30 fw-600 mb-20">Select Package Type</h1> */}
            <div className="d-flex flex-column align-items-center y-gap-20">
                <div className="col-md-6">
                    <ThemeProvider theme={theme}>
                        <Button 
                            variant="contained"
                            color="primary"
                            fullWidth
                            onClick={handleTourPackagesClick}
                            startIcon={<AddIcon />}
                            sx={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: '10px',
                            }}
                        >
                            Create your own packages
                        </Button>
                    </ThemeProvider>
                </div>
                {/* <div className="col-md-6"> */}
                    {/* <button 
                        onClick={handlePreDefinePackagesClick}
                        className="button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
                    >
                        <span className="text-20 fw-500">Pre-Define Packages</span>
                    </button> */}
                   
                {/* </div> */}
            </div>
            <PreDefinePackagesPage />
            {/* Remove this line because the footer is already included in PreDefinePackagesPage */}
            {/* {userRole && userRole.trim() === "Agent" && <DefaultFooter />} */}
        </div>
    )
}
export default Packages;