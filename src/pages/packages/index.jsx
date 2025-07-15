import React, { useEffect, useState } from 'react';
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
      main: '#3554D1',
    },
  },
  typography: {
    button: {
      fontWeight: 500,
      fontSize: '1.25rem',
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
    const [animate, setAnimate] = useState(false);
    
    useEffect(() => {
        // Trigger animation after component mounts
        setTimeout(() => {
            setAnimate(true);
        }, 300);
    }, []);
    
    const handleTourPackagesClick = () => {
        dispatch(setPackageData(null));
        navigate('/dashboard/tour-packages');
    };
    
    const handlePreDefinePackagesClick = () => {
        navigate('/dashboard/pre-define-packages');
    };
    
    return (
      <div className="pre-define-packages-background">
      <div className="pre-define-packages-background">
      <div className="pre-define-packages-background">
        <div className="packages-container" style={{ padding: '100px 20px', textAlign: 'center' }}>
            <div className="d-flex flex-column align-items-center y-gap-20">
                <div className="col-md-6" style={{
                    opacity: animate ? 1 : 0,
                    transform: animate ? 'translateY(0)' : 'translateY(-50px)',
                    transition: 'opacity 0.8s ease-out, transform 0.8s ease-out'
                }}>
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
                              background: 'linear-gradient(90deg, #4776E6, #8E54E9, #4776E6)',
                              backgroundSize: '200% auto',
                              animation: 'gradientShift 3s ease infinite',
                              color: 'white',
                              ml: 1.5,
                              fontWeight: 600,
                              fontSize: '1.5rem',
                              textTransform: 'none',
                              borderRadius: '30px',
                              boxShadow: '0 10px 20px rgba(78, 89, 222, 0.4)',
                              transition: 'all 0.4s ease',
                              position: 'relative',
                              overflow: 'hidden',
                              '&::before': {
                                content: '""',
                                position: 'absolute',
                                top: 0,
                                left: '-100%',
                                width: '100%',
                                height: '100%',
                                background: 'linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent)',
                                transition: 'all 0.5s ease',
                              },
                              '&:hover': {
                                transform: 'translateY(-5px)',
                                boxShadow: '0 15px 25px rgba(78, 89, 222, 0.6)',
                                '&::before': {
                                  left: '100%',
                                  transition: 'all 0.8s ease',
                                },
                              },
                              '@keyframes gradientShift': {
                                '0%': { backgroundPosition: '0% 50%' },
                                '50%': { backgroundPosition: '100% 50%' },
                                '100%': { backgroundPosition: '0% 50%' },
                              }
                            }}
                        >
                            Create Your Own Packages
                        </Button>
                    </ThemeProvider>
                </div>
            </div>
            <PreDefinePackagesPage />
        </div>
        </div>
        </div>
        </div>
    )
}
export default Packages;