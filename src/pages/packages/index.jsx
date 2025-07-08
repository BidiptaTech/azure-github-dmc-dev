import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import PreDefinePackagesPage from '../pre-define-packages';
import { useSelector, useDispatch } from 'react-redux';
import { Button, Box } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { createTheme, ThemeProvider, keyframes } from '@mui/material/styles';
import { setPackageData } from '@/slice/tour-packages/tourPackageSlice';

// Dolphin jumping animation keyframes
const dolphinJump = keyframes`
  0% {
    transform: translateY(0) rotate(0deg);
  }
  25% {
    transform: translateY(-15px) rotate(-5deg);
  }
  50% {
    transform: translateY(-30px) rotate(0deg);
  }
  75% {
    transform: translateY(-15px) rotate(5deg);
  }
  100% {
    transform: translateY(0) rotate(0deg);
  }
`;

// Create splash effect
const splashEffect = keyframes`
  0% {
    box-shadow: 0px 10px 30px rgba(0, 255, 255, 0.2);
  }
  50% {
    box-shadow: 0px 20px 40px rgba(0, 255, 255, 0.4), 
                0px 0px 10px 5px rgba(0, 255, 255, 0.3);
  }
  100% {
    box-shadow: 0px 10px 30px rgba(0, 255, 255, 0.2);
  }
`;

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
            transform: 'translateY(-3px)',
            boxShadow: '0px 15px 30px rgba(0, 255, 255, 0.5)',
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
    
    const handleTourPackagesClick = () => {
        dispatch(setPackageData(null));
        navigate('/dashboard/tour-packages');
    };
    
    const handlePreDefinePackagesClick = () => {
        navigate('/dashboard/pre-define-packages');
    };
    
    return (
      <div className="pre-define-packages-background">
        <div className="packages-container" style={{ padding: '100px 20px', textAlign: 'center' }}>
            <div className="d-flex flex-column align-items-center y-gap-20">
                <div className="col-md-6">
                    <ThemeProvider theme={theme}>
                        <Button 
                            variant="contained"
                            fullWidth
                            onClick={handleTourPackagesClick}
                            startIcon={<AddIcon />}
                            sx={{
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              gap: '10px',
                              background: 'linear-gradient(90deg,rgb(56, 221, 56),rgb(17, 209, 0),rgb(17, 223, 69))',
                              color: 'white',
                              ml: 1.5,
                              fontWeight: 1000,
                              fontSize: '1.25rem',
                              textTransform: 'none',
                              borderRadius: '25px 25px 8px 8px', // Dolphin-like rounded top
                              boxShadow: '0px 10px 30px rgba(0, 255, 255, 0.25)',
                              transition: 'all 0.3s ease',
                              position: 'relative',
                              overflow: 'visible',
                              '&:hover': {
                                transform: 'translateY(-3px)',
                                boxShadow: '0px 12px 30px rgba(0, 255, 255, 0.3)',
                              }
                            }}
                        >
                            Create your own packages
                        </Button>
                    </ThemeProvider>
                </div>
            </div>
            <PreDefinePackagesPage />
        </div>
        </div>
    )
}
export default Packages;