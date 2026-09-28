import React, { useState, useEffect, useRef } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Typography,
  Paper,
  TextField,
  Button,
  CircularProgress,
  Alert,
  IconButton,
  InputAdornment,
  Divider,
  Card,
  CardContent,
  Container
} from '@mui/material';
import {
  Email as EmailIcon,
  Phone as PhoneIcon,
  Refresh as RefreshIcon,
  ArrowBack as ArrowBackIcon,
  CheckCircle as CheckCircleIcon,
  Timer as TimerIcon
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { toast } from 'react-toastify';
import { sendOTPSlice, verifyOTPSlice } from '@/slice/common/RegisterSlice';

// Styled components for professional design
const GradientPaper = styled(Paper)(({ theme }) => ({
  background: 'linear-gradient(145deg, #667eea 0%, #764ba2 100%)',
  padding: theme.spacing(0.5),
  borderRadius: theme.spacing(3),
  boxShadow: '0 20px 40px rgba(102, 126, 234, 0.4)',
  minHeight: '100vh',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const InnerPaper = styled(Paper)(({ theme }) => ({
  background: 'linear-gradient(145deg, #ffffff 0%, #f8faff 100%)',
  padding: theme.spacing(4),
  borderRadius: theme.spacing(2.5),
  position: 'relative',
  overflow: 'hidden',
  maxWidth: 500,
  width: '100%',
  '&::before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: '4px',
    background: 'linear-gradient(90deg, #667eea, #764ba2)',
  }
}));

const StyledTextField = styled(TextField)(({ theme }) => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: theme.spacing(1.5),
    background: 'linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%)',
    transition: 'all 0.3s ease',
    '&:hover': {
      transform: 'translateY(-2px)',
      boxShadow: '0 8px 25px rgba(102, 126, 234, 0.15)',
    },
    '&.Mui-focused': {
      transform: 'translateY(-2px)',
      boxShadow: '0 8px 25px rgba(102, 126, 234, 0.25)',
    },
  },
  '& .MuiInputLabel-root': {
    fontWeight: 600,
    color: '#5a67d8',
  },
}));

const GradientButton = styled(Button)(({ theme }) => ({
  background: 'linear-gradient(145deg, #667eea 0%, #764ba2 100%)',
  color: 'white',
  borderRadius: theme.spacing(2),
  padding: theme.spacing(1.5, 4),
  fontWeight: 700,
  textTransform: 'none',
  fontSize: '1.1rem',
  transition: 'all 0.3s ease',
  boxShadow: '0 8px 25px rgba(102, 126, 234, 0.3)',
  '&:hover': {
    transform: 'translateY(-2px)',
    boxShadow: '0 12px 35px rgba(102, 126, 234, 0.4)',
    background: 'linear-gradient(145deg, #5a67d8 0%, #6b46c1 100%)',
  },
  '&:disabled': {
    background: '#e2e8f0',
    color: '#a0aec0',
    transform: 'none',
    boxShadow: 'none',
  }
}));

const OTPInput = styled(TextField)(({ theme }) => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: theme.spacing(1.5),
    background: 'linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%)',
    transition: 'all 0.3s ease',
    fontSize: '1.5rem',
    fontWeight: 700,
    textAlign: 'center',
    '&:hover': {
      transform: 'translateY(-2px)',
      boxShadow: '0 8px 25px rgba(102, 126, 234, 0.15)',
    },
    '&.Mui-focused': {
      transform: 'translateY(-2px)',
      boxShadow: '0 8px 25px rgba(102, 126, 234, 0.25)',
    },
  },
  '& .MuiInputBase-input': {
    textAlign: 'center',
    fontSize: '1.5rem',
    fontWeight: 700,
    letterSpacing: '0.5rem',
  },
}));

const IconContainer = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  gap: theme.spacing(1),
  marginBottom: theme.spacing(2),
  '& .MuiSvgIcon-root': {
    color: '#667eea',
    fontSize: '1.5rem',
  }
}));

const OTPVerification = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [otp, setOtp] = useState(['', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [resendTimer, setResendTimer] = useState(0);
  const [canResend, setCanResend] = useState(false);
  const dispatch = useDispatch();
  
  const inputRefs = useRef([]);

  // Get user data from location state
  const userData = location.state?.userData;
  
  useEffect(() => {
    console.log("userData from location state:", userData);
    
    // If no user data, redirect back to registration
    if (!userData) {
      console.warn('No user data found, redirecting to registration');
      navigate('/register');
    }
  }, [userData, navigate]);

  useEffect(() => {
    // Start resend timer
    if (resendTimer > 0) {
      const timer = setTimeout(() => setResendTimer(resendTimer - 1), 1000);
      return () => clearTimeout(timer);
    } else {
      setCanResend(true);
    }
  }, [resendTimer]);

  useEffect(() => {
    // Start initial timer
    setResendTimer(30);
  }, []);

  const handleOtpChange = (index, value) => {
    if (value.length <= 1 && /^\d*$/.test(value)) {
      const newOtp = [...otp];
      newOtp[index] = value;
      setOtp(newOtp);

      // Auto-focus next input
      if (value && index < 3) {
        inputRefs.current[index + 1]?.focus();
      }

      // Auto-focus previous input on backspace
      if (!value && index > 0) {
        inputRefs.current[index - 1]?.focus();
      }
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !otp[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handlePaste = (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text').slice(0, 4);
    if (/^\d{4}$/.test(pastedData)) {
      const newOtp = pastedData.split('');
      setOtp([...newOtp, '', '', '', ''].slice(0, 4));
      inputRefs.current[3]?.focus();
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const otpString = otp.join('');
    const emailOtp ={
      email: userData.email,
      otp: otpString
    }
    if (otpString.length !== 4) {
      setError('Please enter the complete 4-digit OTP');
      return;
    }

    if (!/^\d{4}$/.test(otpString)) {
      setError('Please enter a valid 4-digit OTP');
      return;
    }

    setLoading(true);
    setError('');

    try {
      // Simulate API call - replace with your actual OTP verification API
      const response = await dispatch(verifyOTPSlice(emailOtp)).unwrap();
      
      // Mock verification - replace with actual API call
      if (response.success) { // Replace with actual verification logic
        setSuccess(true);
        toast.success('🎉 OTP verified successfully!');
        navigate('/login');
      } else {
        setError('Invalid OTP. Please try again.');
        setOtp(['', '', '', '']);
        inputRefs.current[0]?.focus();
      }
    } catch (error) {
      setError('Verification failed. Please try again.');
      console.error('OTP verification error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleResendOTP = async () => {
    if (!canResend) return;
    
    if (!userData) {
      toast.error('User data not available. Please try registering again.');
      navigate('/register');
      return;
    }

    setLoading(true);
    setError('');

    try {
      // Simulate resend API call - replace with your actual resend API
      const response = await dispatch(sendOTPSlice(userData)).unwrap();
      if (response.success) {
        toast.success('📧 New OTP sent to your email/phone');
      } else {
        toast.error('Failed to resend OTP. Please try again.');
      }
      setResendTimer(30);
      setCanResend(false);
      setOtp(['', '', '', '']);
      inputRefs.current[0]?.focus();
    } catch (error) {
      setError('Failed to resend OTP. Please try again.');
      console.error('Resend OTP error:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  // Show loading if userData is not yet loaded
  if (!userData) {
    return (
      <GradientPaper>
        <Container maxWidth="sm">
          <InnerPaper>
            <Box sx={{ textAlign: 'center', py: 8 }}>
              <CircularProgress size={60} sx={{ color: '#667eea', mb: 3 }} />
              <Typography variant="h6" color="primary" gutterBottom>
                Loading verification page...
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Please wait while we prepare your verification
              </Typography>
            </Box>
          </InnerPaper>
        </Container>
      </GradientPaper>
    );
  }

  return (
    <GradientPaper>
      <Container maxWidth="sm">
        <InnerPaper>
          {/* Header */}
          <Box sx={{ textAlign: 'center', mb: 4 }}>
            <IconButton
              onClick={() => navigate('/register')}
              sx={{ position: 'absolute', left: 16, top: 16, color: '#667eea' }}
            >
              <ArrowBackIcon />
            </IconButton>
            
            <Typography variant="h4" fontWeight={700} color="primary" gutterBottom>
              Verify OTP
            </Typography>
            <Typography variant="body1" color="text.secondary">
              We've sent a 4-digit code to your email/phone
            </Typography>
          </Box>

          {/* User Info */}
          <Card sx={{ mb: 3, background: 'linear-gradient(145deg, #f8faff 0%, #ffffff 100%)' }}>
            <CardContent>
              <IconContainer>
                <EmailIcon />
                <Typography variant="body2" color="text.secondary">
                  {userData?.email || 'user@example.com'}
                </Typography>
              </IconContainer>
            </CardContent>
          </Card>

          {/* OTP Input Form */}
          <form onSubmit={handleSubmit}>
            <Box sx={{ mb: 3 }}>
              <Typography variant="h6" fontWeight={600} color="primary" gutterBottom>
                Enter 4-digit OTP
              </Typography>
              
              <Box sx={{ display: 'flex', gap: 2, justifyContent: 'center', mb: 3 }}>
                {otp.map((digit, index) => (
                  <OTPInput
                    key={index}
                    inputRef={(el) => (inputRefs.current[index] = el)}
                    value={digit}
                    onChange={(e) => handleOtpChange(index, e.target.value)}
                    onKeyDown={(e) => handleKeyDown(index, e)}
                    onPaste={handlePaste}
                    inputProps={{
                      maxLength: 1,
                      style: { textAlign: 'center' }
                    }}
                    sx={{ width: 80 }}
                    variant="outlined"
                    size="large"
                  />
                ))}
              </Box>

              {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                  {error}
                </Alert>
              )}

              {success && (
                <Alert severity="success" sx={{ mb: 2 }}>
                  <CheckCircleIcon sx={{ mr: 1 }} />
                  OTP verified successfully! Redirecting to login...
                </Alert>
              )}
            </Box>

            {/* Submit Button */}
            <GradientButton
              type="submit"
              fullWidth
              disabled={loading || success || otp.join('').length !== 4}
              sx={{ mb: 3 }}
            >
              {loading ? (
                <>
                  <CircularProgress size={24} sx={{ mr: 2, color: 'white' }} />
                  Verifying...
                </>
              ) : success ? (
                <>
                  <CheckCircleIcon sx={{ mr: 1 }} />
                  Verified Successfully!
                </>
              ) : (
                'Verify OTP'
              )}
            </GradientButton>
          </form>

          <Divider sx={{ my: 3 }} />

          {/* Resend Section */}
          <Box sx={{ textAlign: 'center' }}>
            <Typography variant="body2" color="text.secondary" gutterBottom>
              Didn't receive the code?
            </Typography>
            
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 1 }}>
              {resendTimer > 0 ? (
                <>
                  <TimerIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    Resend in {formatTime(resendTimer)}
                  </Typography>
                </>
              ) : (
                <Button
                  onClick={handleResendOTP}
                  disabled={loading}
                  startIcon={<RefreshIcon />}
                  sx={{
                    color: '#667eea',
                    textTransform: 'none',
                    fontWeight: 600,
                    '&:hover': {
                      background: 'rgba(102, 126, 234, 0.1)',
                    }
                  }}
                >
                  Resend OTP
                </Button>
              )}
            </Box>
          </Box>

          {/* Help Text */}
          <Box sx={{ mt: 3, textAlign: 'center' }}>
            <Typography variant="caption" color="text.secondary">
              Check your email inbox and spam folder for the verification code
            </Typography>
          </Box>
        </InnerPaper>
      </Container>
    </GradientPaper>
  );
};

export default OTPVerification;
