import React, { useState, useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  TextField,
  Button,
  Typography,
  Grid,
  Paper,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  FormHelperText,
  Autocomplete,
  Chip,
  IconButton,
  InputAdornment,
  Alert,
  CircularProgress,
  LinearProgress,
  Card,
  CardContent,
} from '@mui/material';
import {
  CloudUpload as CloudUploadIcon,
  Visibility,
  VisibilityOff,
  CheckCircle,
  Business as BusinessIcon,
  LocationOn as LocationIcon,
  Phone as PhoneIcon,
  Upload as UploadIcon,
  Lock as LockIcon,
  Close as CloseIcon,
  ArrowBack
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { toast } from 'react-toastify';
import { registerSlice, sendOTPSlice } from '@/slice/common/RegisterSlice';

const NAVY = '#13357b';

const FormShell = styled(Paper)(({ theme }) => ({
  background: '#ffffff',
  padding: theme.spacing(1.5, 2),
  borderRadius: 8,
  border: '1px solid #dce4f0',
  boxShadow: '0 4px 20px rgba(19, 53, 123, 0.06)',
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(1.25),
    borderRadius: 8,
  },
}));

const StyledTextField = styled(TextField)(() => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: 6,
    background: '#fff',
    fontSize: '0.8125rem',
    minHeight: 36,
    '& fieldset': { borderColor: '#dce4f0' },
    '&:hover fieldset': { borderColor: '#a8bcd6' },
    '&.Mui-focused fieldset': { borderColor: NAVY },
    '& input': { paddingTop: 8, paddingBottom: 8 },
  },
  '& .MuiInputLabel-root': {
    fontWeight: 500,
    fontSize: '0.8125rem',
    color: '#64748b',
    '&.Mui-focused': { color: NAVY },
  },
  '& .MuiFormHelperText-root': {
    fontSize: '0.7rem',
    marginLeft: 2,
    marginTop: 2,
    lineHeight: 1.2,
  },
}));

const ColorfulAutocomplete = styled(Autocomplete)(() => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: 6,
    background: '#fff',
    fontSize: '0.8125rem',
    minHeight: 36,
    paddingTop: '2px !important',
    paddingBottom: '2px !important',
    '& fieldset': { borderColor: '#dce4f0' },
    '&:hover fieldset': { borderColor: '#a8bcd6' },
    '&.Mui-focused fieldset': { borderColor: NAVY },
  },
  '& .MuiInputLabel-root': {
    fontWeight: 500,
    fontSize: '0.8125rem',
    color: '#64748b',
  },
}));

const GradientButton = styled(Button)(() => ({
  background: NAVY,
  borderRadius: 6,
  padding: '6px 18px',
  fontSize: '0.8125rem',
  fontWeight: 600,
  textTransform: 'none',
  color: '#fff',
  boxShadow: 'none',
  minHeight: 34,
  '&:hover': {
    background: '#0f2d6b',
    boxShadow: 'none',
  },
  '&:disabled': {
    background: '#94a3b8',
    color: '#fff',
  },
}));

const VisuallyHiddenInput = styled('input')({
  clip: 'rect(0 0 0 0)',
  clipPath: 'inset(50%)',
  height: 1,
  overflow: 'hidden',
  position: 'absolute',
  bottom: 0,
  left: 0,
  whiteSpace: 'nowrap',
  width: 1,
});

const ColorfulUploadBox = styled(Box)(({ theme, error }) => ({
  border: `1.5px dashed ${error ? '#ef4444' : '#c5d0e0'}`,
  borderRadius: 6,
  padding: theme.spacing(1, 1.25),
  textAlign: 'center',
  background: error ? '#fef2f2' : '#f8fafc',
  cursor: 'pointer',
  transition: 'border-color 0.2s ease',
  '&:hover': {
    borderColor: error ? '#ef4444' : NAVY,
    background: error ? '#fef2f2' : '#f1f5f9',
  },
}));

const SectionCard = styled(Card)(({ theme }) => ({
  background: '#fff',
  borderRadius: 6,
  boxShadow: 'none',
  marginBottom: theme.spacing(1),
  border: '1px solid #e2e8f0',
  [theme.breakpoints.down('sm')]: {
    marginBottom: theme.spacing(0.875),
  },
  '& .MuiCardContent-root': {
    padding: theme.spacing(1, 1.5),
    '&:last-child': { paddingBottom: theme.spacing(1) },
  },
}));

const ColorfulChip = styled(Chip)(() => ({
  background: NAVY,
  color: 'white',
  fontWeight: 500,
  height: 22,
  fontSize: '0.7rem',
  '& .MuiChip-deleteIcon': {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 14,
  },
}));

const IconContainer = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(0.75),
  paddingBottom: theme.spacing(0.5),
  borderBottom: '1px solid #eef2f7',
  '& .MuiSvgIcon-root': {
    marginRight: theme.spacing(0.5),
    color: NAVY,
    fontSize: 16,
  },
}));

// City data for major countries (fallback when API doesn't provide cities)
const CITY_DATA = {
  'US': ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'],
  'GB': ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Leeds', 'Sheffield', 'Edinburgh', 'Bristol', 'Cardiff'],
  'IN': ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Kolkata', 'Hyderabad', 'Pune', 'Ahmedabad', 'Jaipur', 'Lucknow'],
  'AU': ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Newcastle', 'Canberra', 'Wollongong', 'Hobart'],
  'CA': ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener'],
  'SG': ['Singapore'],
  'MY': ['Kuala Lumpur', 'George Town', 'Ipoh', 'Shah Alam', 'Petaling Jaya', 'Johor Bahru', 'Malacca', 'Alor Setar', 'Kuching', 'Kota Kinabalu'],
  'TH': ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Krabi', 'Hua Hin', 'Koh Samui', 'Chiang Rai', 'Ayutthaya', 'Sukhothai'],
  'ID': ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi'],
  'FR': ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille'],
  'DE': ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Dortmund', 'Essen', 'Leipzig'],
  'IT': ['Rome', 'Milan', 'Naples', 'Turin', 'Palermo', 'Genoa', 'Bologna', 'Florence', 'Bari', 'Catania'],
  'ES': ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza', 'Málaga', 'Murcia', 'Palma', 'Las Palmas', 'Bilbao'],
  'JP': ['Tokyo', 'Osaka', 'Kyoto', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kobe', 'Sendai', 'Hiroshima', 'Kitakyushu'],
  'CN': ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Hangzhou', 'Wuhan', 'Xi\'an', 'Nanjing', 'Tianjin'],
  'BR': ['São Paulo', 'Rio de Janeiro', 'Brasília', 'Salvador', 'Fortaleza', 'Belo Horizonte', 'Manaus', 'Curitiba', 'Recife', 'Goiânia'],
  'MX': ['Mexico City', 'Guadalajara', 'Puebla', 'Tijuana', 'León', 'Juárez', 'Zapopan', 'Monterrey', 'Nezahualcóyotl', 'Chihuahua'],
  'RU': ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Nizhny Novgorod', 'Kazan', 'Chelyabinsk', 'Omsk', 'Samara', 'Rostov-on-Don'],
  'ZA': ['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Pietermaritzburg', 'Benoni', 'Tembisa', 'East London', 'Vereeniging'],
  'AE': ['Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain', 'Khor Fakkan', 'Dibba Al-Fujairah'],
  'SA': ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Tabuk', 'Buraidah', 'Khamis Mushait', 'Hail'],
  'EG': ['Cairo', 'Alexandria', 'Giza', 'Shubra El Kheima', 'Port Said', 'Suez', 'Luxor', 'Aswan', 'Mansoura', 'Tanta'],
  'TR': ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya', 'Adana', 'Konya', 'Gaziantep', 'Mersin', 'Diyarbakır'],
  'KR': ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon', 'Gwangju', 'Suwon', 'Ulsan', 'Changwon', 'Goyang'],
  'PH': ['Manila', 'Quezon City', 'Davao', 'Cebu', 'Zamboanga', 'Antipolo', 'Pasig', 'Taguig', 'Valenzuela', 'Dasmariñas'],
  'VN': ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Hai Phong', 'Can Tho', 'Bien Hoa', 'Hue', 'Nha Trang', 'Buon Ma Thuot', 'Nam Dinh'],
  'BD': ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Comilla', 'Narayanganj', 'Gazipur', 'Rangpur'],
  'PK': ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala'],
  'LK': ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Negombo', 'Batticaloa', 'Trincomalee', 'Kurunegala', 'Anuradhapura', 'Ratnapura'],
  'NP': ['Kathmandu', 'Pokhara', 'Lalitpur', 'Bharatpur', 'Biratnagar', 'Birgunj', 'Dharan', 'Butwal', 'Hetauda', 'Janakpur'],
};

const RegistrationForm = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    agencyCompany: '',
    salutation: '',
    name: '',
    email: '',
    agentCountry: null,
    agentCity: null,
    agentAddress: '',
    countryCode: null,
    phoneNumber: '',
    serviceCountries: [],
    idCard: '',
    cardNumber: '',
    agencyLogo: null,
    idProof: null,
    password: ''
  });
 // console.log("formDataregister", formData);

  // Add preview URLs for images
  const [imagePreviews, setImagePreviews] = useState({ agencyLogo: null, idProof: null });

  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [uploadingFiles, setUploadingFiles] = useState({});
  const [availableCities, setAvailableCities] = useState([]);
  const [countries, setCountries] = useState([]);
  const [loadingCountries, setLoadingCountries] = useState(true);
  const [currentStep, setCurrentStep] = useState(1);

  // Cleanup function for preview URLs
  useEffect(() => {
    return () => {
      // Clean up preview URLs when component unmounts
      Object.values(imagePreviews).forEach(url => {
        if (url) URL.revokeObjectURL(url);
      });
    };
  }, [imagePreviews]);

  // Fetch countries from REST Countries API
  useEffect(() => {
    const fetchCountries = async () => {
      try {
        setLoadingCountries(true);
        const response = await fetch('https://restcountries.com/v3.1/all?fields=name,cca2,idd,flag');
        const data = await response.json();
        
        const processedCountries = data
          .map(country => ({
            code: country.cca2,
            name: country.name.common,
            dialCode: country.idd?.root ? 
              `${country.idd.root}${country.idd.suffixes?.[0] || ''}` : 
              '+1', // fallback
            flag: country.flag,
            cities: CITY_DATA[country.cca2] || [`${country.name.common} City`], // fallback city
            minLength: getPhoneMinLength(country.cca2),
            maxLength: getPhoneMaxLength(country.cca2)
          }))
          .sort((a, b) => a.name.localeCompare(b.name));
        
        setCountries(processedCountries);
        setLoadingCountries(false);
      } catch (error) {
       // console.error('Error fetching countries:', error);
        // Fallback to some basic countries if API fails
        setCountries([
          { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸', cities: CITY_DATA.US, minLength: 10, maxLength: 10 },
          { code: 'GB', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', cities: CITY_DATA.GB, minLength: 10, maxLength: 11 },
          { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳', cities: CITY_DATA.IN, minLength: 10, maxLength: 10 },
          { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺', cities: CITY_DATA.AU, minLength: 9, maxLength: 9 },
          { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦', cities: CITY_DATA.CA, minLength: 10, maxLength: 10 },
        ]);
        setLoadingCountries(false);
        toast.error('Failed to load countries. Using fallback data.');
      }
    };

    fetchCountries();
  }, []);

  // Helper functions for phone validation
  const getPhoneMinLength = (countryCode) => {
    const phoneLengths = {
      'US': 10, 'CA': 10, 'GB': 10, 'IN': 10, 'AU': 9, 'SG': 8, 'MY': 9, 'TH': 9,
      'ID': 10, 'FR': 10, 'DE': 11, 'IT': 10, 'ES': 9, 'JP': 10, 'CN': 11,
      'BR': 10, 'MX': 10, 'RU': 10, 'ZA': 9, 'AE': 9, 'SA': 9, 'EG': 10,
      'TR': 10, 'KR': 10, 'PH': 10, 'VN': 9, 'BD': 10, 'PK': 10, 'LK': 9, 'NP': 10
    };
    return phoneLengths[countryCode] || 8;
  };

  const getPhoneMaxLength = (countryCode) => {
    const phoneLengths = {
      'US': 10, 'CA': 10, 'GB': 11, 'IN': 10, 'AU': 9, 'SG': 8, 'MY': 10, 'TH': 9,
      'ID': 12, 'FR': 10, 'DE': 12, 'IT': 11, 'ES': 9, 'JP': 11, 'CN': 11,
      'BR': 11, 'MX': 10, 'RU': 10, 'ZA': 9, 'AE': 9, 'SA': 9, 'EG': 11,
      'TR': 10, 'KR': 11, 'PH': 10, 'VN': 10, 'BD': 10, 'PK': 10, 'LK': 9, 'NP': 10
    };
    return phoneLengths[countryCode] || 15;
  };

  // Update available cities when agent country changes
  useEffect(() => {
    if (formData.agentCountry) {
      const country = countries.find(c => c.code === formData.agentCountry.code);
      setAvailableCities(country ? country.cities : []);
      setFormData(prev => ({ ...prev, agentCity: null }));
    } else {
      setAvailableCities([]);
    }
  }, [formData.agentCountry, countries]);

  // Validation functions
  const validateField = (name, value) => {
    switch (name) {
      case 'agencyCompany':
        if (!value?.trim()) return 'Agency Company is required';
        if (!/^[a-zA-Z\s]+$/.test(value)) return 'Only plain text allowed';
        return '';

      case 'salutation':
        if (!value) return 'Salutation is required';
        return '';

      case 'name':
        if (!value?.trim()) return 'Name is required';
        return '';

      case 'email':
        if (!value?.trim()) return 'Email is required';
        if (!/\S+@\S+\.com$/.test(value)) return 'Email must include @ and .com';
        if (/\s/.test(value)) return 'No spaces allowed in email';
        return '';

      case 'agentCountry':
        if (!value) return 'Agent Country is required';
        return '';

      case 'agentCity':
        if (!value) return 'Agent City is required';
        return '';

      case 'agentAddress':
        if (!value?.trim()) return 'Agent Address is required';
        return '';

      case 'countryCode':
        if (!value) return 'Country Code is required';
        return '';

      case 'phoneNumber':
        if (!value?.trim()) return 'Phone Number is required';
        if (!/^\d+$/.test(value)) return 'No special characters allowed';
        if (formData.countryCode) {
          const country = countries.find(c => c.dialCode === formData.countryCode.dialCode);
          if (country && (value.length < country.minLength || value.length > country.maxLength)) {
            return `Phone number must be ${country.minLength}-${country.maxLength} digits for ${country.name}`;
          }
        }
        return '';

      case 'serviceCountries':
        if (!value || value.length === 0) return 'At least one Service Country is required';
        return '';

      case 'idCard':
        if (!value?.trim()) return 'ID Card is required';
        return '';

      case 'cardNumber':
        if (!value?.trim()) return 'Card Number is required';
        return '';

      case 'idProof':
        if (!value) return 'ID Proof is required';
        return '';

      case 'password':
        if (!value) return 'Password is required';
        if (value.length < 6 || value.length > 8) return 'Password must be 6-8 characters';
        return '';

      default:
        return '';
    }
  };

  const handleInputChange = (name, value) => {
    setFormData(prev => ({ ...prev, [name]: value }));
    
    if (touched[name]) {
      const error = validateField(name, value);
      setErrors(prev => ({ ...prev, [name]: error }));
    }
  };

  const handleBlur = (name) => {
    setTouched(prev => ({ ...prev, [name]: true }));
    const error = validateField(name, formData[name]);
    setErrors(prev => ({ ...prev, [name]: error }));
  };

  const handleFileUpload = (name, file) => {
    if (file) {
      // Show loading state for file upload
      setUploadingFiles(prev => ({ ...prev, [name]: true }));
      
      // Validate file size (2MB limit)
      if (file.size > 2 * 1024 * 1024) {
        setErrors(prev => ({ ...prev, [name]: 'File size must be less than 2MB' }));
        setUploadingFiles(prev => ({ ...prev, [name]: false }));
        return;
      }
      
      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        setErrors(prev => ({ ...prev, [name]: 'Only JPG, PNG, and WebP files are allowed' }));
        setUploadingFiles(prev => ({ ...prev, [name]: false }));
        return;
      }
      
      // Validate file name length
      if (file.name.length > 100) {
        setErrors(prev => ({ ...prev, [name]: 'File name is too long. Please use a shorter name.' }));
        setUploadingFiles(prev => ({ ...prev, [name]: false }));
        return;
      }
      
      // Clear any previous errors
      setErrors(prev => ({ ...prev, [name]: '' }));
      
      // Update form data with the file
      setFormData(prev => ({ ...prev, [name]: file }));
      
      // Create preview URL
      const url = URL.createObjectURL(file);
      setImagePreviews(prev => ({ ...prev, [name]: url }));
      
      // Hide loading state
      setUploadingFiles(prev => ({ ...prev, [name]: false }));
      
      // console.log(`File uploaded for ${name}:`, {
      //   name: file.name,
      //   size: file.size,
      //   type: file.type,
      //   lastModified: file.lastModified,
      //   isFile: file instanceof File
      // });
      
      // Verify file is properly stored in form data
      setTimeout(() => {
        const currentFile = formData[name];
        // console.log(`Verification - ${name} in formData:`, {
        //   exists: !!currentFile,
        //   isFile: currentFile instanceof File,
        //   name: currentFile?.name,
        //   size: currentFile?.size
        // });
      }, 100);
    }
  };

  const handleRemoveImage = (name) => {
    // Clean up the preview URL to prevent memory leaks
    if (imagePreviews[name]) {
      URL.revokeObjectURL(imagePreviews[name]);
    }
    
    setFormData(prev => ({ ...prev, [name]: null }));
    setImagePreviews(prev => ({ ...prev, [name]: null }));
    setErrors(prev => ({ ...prev, [name]: '' }));
    setUploadingFiles(prev => ({ ...prev, [name]: false }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    // Validate all fields
    const newErrors = {};
    const requiredFields = [
      'agencyCompany', 'salutation', 'name', 'email', 'agentCountry', 
      'agentCity', 'agentAddress', 'countryCode', 'phoneNumber', 
      'serviceCountries', 'idCard', 'cardNumber', 'idProof', 'password'
    ];

    requiredFields.forEach(field => {
      const error = validateField(field, formData[field]);
      if (error) newErrors[field] = error;
    });

    setErrors(newErrors);
    setTouched(requiredFields.reduce((acc, field) => ({ ...acc, [field]: true }), {}));

    if (Object.keys(newErrors).length === 0) {
      try {
        // Transform form data to match API format
        const transformedData = {
          company_name: formData.agencyCompany,
          salutation: formData.salutation?.replace('.', ''), // Remove the dot from "Mr." or "Mrs."
          name: formData.name,
          email: formData.email,
          country: formData.serviceCountries?.map(country => country.name) || [],
          user_country: formData.agentCountry?.name || '',
          city: formData.agentCity || '',
          agent_address: formData.agentAddress,
          code: formData.countryCode?.dialCode?.replace('+', '') || '', // Remove the + from dial code
          phone: formData.phoneNumber,
          id_card: formData.idCard,
          card_number: formData.cardNumber,
          password: formData.password,
          agent_image: formData.agencyLogo, // Pass the actual File object
          image: formData.idProof, // Pass the actual File object
        };

        // console.log("Transformed data for API:", {
        //   ...transformedData,
        //   agent_image: transformedData.agent_image ? {
        //     name: transformedData.agent_image.name,
        //     size: transformedData.agent_image.size,
        //     type: transformedData.agent_image.type,
        //     isFile: transformedData.agent_image instanceof File
        //   } : null,
        //   image: transformedData.image ? {
        //     name: transformedData.image.name,
        //     size: transformedData.image.size,
        //     type: transformedData.image.type,
        //     isFile: transformedData.image instanceof File
        //   } : null
        // });
        
        // Validate that required files are present and are File objects
        if (!transformedData.agent_image || !(transformedData.agent_image instanceof File)) {
          toast.error('❌ Please upload your agency logo');
          setLoading(false);
          return;
        }
        
        if (!transformedData.image || !(transformedData.image instanceof File)) {
          toast.error('❌ Please upload your ID proof');
          setLoading(false);
          return;
        }
        
        // console.log('Files validation passed:', {
        //   agent_image: {
        //     name: transformedData.agent_image.name,
        //     size: transformedData.agent_image.size,
        //     type: transformedData.agent_image.type
        //   },
        //   image: {
        //     name: transformedData.image.name,
        //     size: transformedData.image.size,
        //     type: transformedData.image.type
        //   }
        // });
        
                  try {
                   // console.log("transformedDatafinal", transformedData);
           
           const responseOTP = await dispatch(sendOTPSlice(transformedData)).unwrap();
           if (responseOTP.success) {
            setSuccess(true);
            toast.success('🎉 Registration successful! Please verify your OTP');
            
            // Clean up preview URLs to prevent memory leaks
            Object.values(imagePreviews).forEach(url => {
              if (url) URL.revokeObjectURL(url);
            });
            
            // Prepare user data for OTP verification
            const userDataForOTP = {
              email: formData.email,
              phone: formData.phoneNumber,
              name: formData.name,
              company: formData.agencyCompany,
              // Add any other data needed for OTP verification
              ...transformedData
            };
            //console.log('Passing user data for OTP:', userDataForOTP);
            
            setTimeout(() => {
              setFormData({
                agencyCompany: '',
                salutation: '',
                name: '',
                email: '',
                agentCountry: null,
                agentCity: null,
                agentAddress: '',
                countryCode: null,
                phoneNumber: '',
                serviceCountries: [],
                idCard: '',
                cardNumber: '',
                agencyLogo: null,
                idProof: null,
                password: ''
              });
              setSuccess(false);
              setTouched({});
              setCurrentStep(1);
              // Clear image previews
              setImagePreviews({ agencyLogo: null, idProof: null });
              navigate('/verify-otp', { state: { userData: userDataForOTP } });
            }, 3000);
           
         } else {
             const errorMessage = responseOTP.message || 'Registration failed. Please try again.';
             toast.error(`❌ ${errorMessage}`);
           }
         } catch (error) {
           //console.error('Registration error:', error);
           const errorMessage = error.message || 'Network error. Please check your connection and try again.';
           toast.error(`❌ ${errorMessage}`);
         }
        
        // Reset form after success
        
      } catch (error) {
        toast.error('❌ Registration failed. Please try again.');
      }
    } else {
      toast.error('⚠️ Please fix the errors below');
    }

    setLoading(false);
  };

  if (loadingCountries) {
    return (
      <Box
        sx={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          background: 'linear-gradient(145deg, #667eea 0%, #764ba2 100%)',
        }}
      >
        <Card sx={{ p: 4, textAlign: 'center', borderRadius: 3 }}>
          <CircularProgress size={60} sx={{ color: '#667eea', mb: 2 }} />
          <Typography variant="h6" color="primary">
            Loading Countries Data...
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Fetching latest country information from our servers
          </Typography>
        </Card>
      </Box>
    );
  }

  return (
    <Box
      sx={{
        minHeight: '100vh',
        bgcolor: '#f4f7fb',
        py: { xs: 1, md: 1.5 },
        px: { xs: 1, sm: 1.5 },
      }}
    >
      <FormShell
        sx={{
          maxWidth: { xs: '100%', sm: 720, md: 780 },
          mx: 'auto',
          position: 'relative',
        }}
      >
          {/* Back Button */}
          <IconButton
            onClick={() => navigate('/')}
            size="small"
            sx={{
              position: 'absolute',
              top: { xs: 10, sm: 12 },
              left: { xs: 10, sm: 14 },
              color: NAVY,
              border: '1px solid #dce4f0',
              borderRadius: '6px',
              width: 30,
              height: 30,
              zIndex: 1,
              '&:hover': { bgcolor: '#e8eef6' },
            }}
          >
            <ArrowBack sx={{ fontSize: 16 }} />
          </IconButton>

          <Box
            sx={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              textAlign: 'center',
              mb: 1.5,
              pt: 0.5,
            }}
          >
            <Box
              component="img"
              src="/Images/logo.png"
              alt="TravClicks"
              sx={{
                height: { xs: 40, sm: 48 },
                width: 'auto',
                objectFit: 'contain',
                mb: 0.75,
              }}
            />
            <Typography
              sx={{
                fontWeight: 700,
                color: NAVY,
                fontSize: '1rem',
                lineHeight: 1.2,
              }}
            >
              Agent Registration
            </Typography>
            <Typography sx={{ color: '#64748b', fontSize: '0.75rem', lineHeight: 1.3, mt: 0.25 }}>
              Join our travel trade partner network
            </Typography>

            {loading && (
              <Box sx={{ width: '100%', maxWidth: 220, mt: 1 }}>
                <LinearProgress 
                  variant="determinate" 
                  value={(currentStep / 4) * 100}
                  sx={{
                    height: 3,
                    borderRadius: 2,
                    bgcolor: '#e2e8f0',
                    '& .MuiLinearProgress-bar': {
                      bgcolor: NAVY,
                      borderRadius: 2,
                    }
                  }}
                />
                <Typography variant="caption" sx={{ mt: 0.25, display: 'block', color: NAVY, fontWeight: 600, fontSize: '0.7rem' }}>
                  Step {currentStep} of 4
                </Typography>
              </Box>
            )}
          </Box>

          {success && (
            <Alert
              severity="success"
              icon={<CheckCircle sx={{ fontSize: 18 }} />}
              sx={{ 
                mb: 1.25,
                borderRadius: 1,
                py: 0,
                '& .MuiAlert-message': { py: 0.75, fontSize: '0.8rem' },
              }}
            >
              Registration completed successfully. Form will reset shortly.
            </Alert>
          )}

          <form onSubmit={handleSubmit}>
            {/* Basic Information Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <BusinessIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    Basic Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} md={6} lg={4}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Agency Company *"
                      placeholder="Agency company name"
                      value={formData.agencyCompany}
                      onChange={(e) => handleInputChange('agencyCompany', e.target.value)}
                      onBlur={() => handleBlur('agencyCompany')}
                      error={!!errors.agencyCompany}
                      helperText={errors.agencyCompany}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6} md={3} lg={2}>
                    <FormControl fullWidth size="small" error={!!errors.salutation}>
                      <InputLabel sx={{ fontWeight: 500, color: '#64748b', fontSize: '0.875rem' }}>Salutation *</InputLabel>
                      <Select
                        value={formData.salutation}
                        onChange={(e) => handleInputChange('salutation', e.target.value)}
                        onBlur={() => handleBlur('salutation')}
                        label="Salutation *"
                        sx={{
                          borderRadius: 1.5,
                          fontSize: '0.875rem',
                          '& .MuiOutlinedInput-notchedOutline': { borderColor: '#dce4f0' },
                        }}
                      >
                        <MenuItem value="Mr." sx={{ fontSize: '0.875rem' }}>Mr.</MenuItem>
                        <MenuItem value="Mrs." sx={{ fontSize: '0.875rem' }}>Mrs.</MenuItem>
                      </Select>
                      {errors.salutation && <FormHelperText>{errors.salutation}</FormHelperText>}
                    </FormControl>
                  </Grid>

                  <Grid item xs={12} sm={6} md={3} lg={3}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Name *"
                      placeholder="Full name"
                      value={formData.name}
                      onChange={(e) => handleInputChange('name', e.target.value)}
                      onBlur={() => handleBlur('name')}
                      error={!!errors.name}
                      helperText={errors.name}
                    />
                  </Grid>

                  <Grid item xs={12} md={6} lg={3}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Email Address *"
                      placeholder="email@example.com"
                      value={formData.email}
                      onChange={(e) => handleInputChange('email', e.target.value)}
                      onBlur={() => handleBlur('email')}
                      error={!!errors.email}
                      helperText={errors.email}
                      type="email"
                    />
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* Location Information Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <LocationIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    Location Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} sm={6} lg={4}>
                    <ColorfulAutocomplete
                      size="small"
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name}`}
                      value={formData.agentCountry}
                      onChange={(_, value) => handleInputChange('agentCountry', value)}
                      onBlur={() => handleBlur('agentCountry')}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          size="small"
                          label="Agent Country *"
                          placeholder="Select country"
                          error={!!errors.agentCountry}
                          helperText={errors.agentCountry}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props} sx={{ fontSize: '0.875rem' }}>
                          <span style={{ marginRight: 8 }}>{option.flag}</span>
                          {option.name}
                        </Box>
                      )}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6} lg={4}>
                    <ColorfulAutocomplete
                      size="small"
                      options={availableCities}
                      value={formData.agentCity}
                      onChange={(_, value) => handleInputChange('agentCity', value)}
                      onBlur={() => handleBlur('agentCity')}
                      disabled={!formData.agentCountry}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          size="small"
                          label="Agent City *"
                          placeholder="Select city"
                          error={!!errors.agentCity}
                          helperText={errors.agentCity || (!formData.agentCountry ? 'Select country first' : '')}
                        />
                      )}
                    />
                  </Grid>

                  <Grid item xs={12} lg={4}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Agent Address *"
                      placeholder="Complete address"
                      value={formData.agentAddress}
                      onChange={(e) => handleInputChange('agentAddress', e.target.value)}
                      onBlur={() => handleBlur('agentAddress')}
                      error={!!errors.agentAddress}
                      helperText={errors.agentAddress}
                      multiline
                      minRows={1}
                      maxRows={2}
                    />
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* Contact Information Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <PhoneIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    Contact Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} sm={6} lg={4}>
                    <ColorfulAutocomplete
                      size="small"
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name} (${option.dialCode})`}
                      value={formData.countryCode}
                      onChange={(_, value) => handleInputChange('countryCode', value)}
                      onBlur={() => handleBlur('countryCode')}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          size="small"
                          label="Country Code *"
                          placeholder="Country code"
                          error={!!errors.countryCode}
                          helperText={errors.countryCode}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props} sx={{ fontSize: '0.875rem' }}>
                          <span style={{ marginRight: 8 }}>{option.flag}</span>
                          {option.name} ({option.dialCode})
                        </Box>
                      )}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6} lg={4}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Phone Number *"
                      placeholder="Phone number"
                      value={formData.phoneNumber}
                      onChange={(e) => handleInputChange('phoneNumber', e.target.value)}
                      onBlur={() => handleBlur('phoneNumber')}
                      error={!!errors.phoneNumber}
                      helperText={errors.phoneNumber}
                      InputProps={{
                        startAdornment: formData.countryCode && (
                          <InputAdornment position="start" sx={{ color: NAVY, fontWeight: 600, fontSize: '0.875rem' }}>
                            {formData.countryCode.dialCode}
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Grid>

                  <Grid item xs={12} lg={4}>
                    <ColorfulAutocomplete
                      multiple
                      size="small"
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name}`}
                      value={formData.serviceCountries}
                      onChange={(_, value) => handleInputChange('serviceCountries', value)}
                      onBlur={() => handleBlur('serviceCountries')}
                      renderTags={(value, getTagProps) =>
                        value.map((option, index) => (
                          <ColorfulChip
                            variant="filled"
                            size="small"
                            label={`${option.flag} ${option.name}`}
                            {...getTagProps({ index })}
                            key={option.code}
                          />
                        ))
                      }
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          size="small"
                          label="Service Countries *"
                          placeholder="Select countries"
                          error={!!errors.serviceCountries}
                          helperText={errors.serviceCountries}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props} sx={{ fontSize: '0.875rem' }}>
                          <span style={{ marginRight: 8 }}>{option.flag}</span>
                          {option.name}
                        </Box>
                      )}
                    />
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* Document Information Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <BusinessIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    Document Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} sm={6}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="ID Card *"
                      placeholder="ID card information"
                      value={formData.idCard}
                      onChange={(e) => handleInputChange('idCard', e.target.value)}
                      onBlur={() => handleBlur('idCard')}
                      error={!!errors.idCard}
                      helperText={errors.idCard}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Card Number *"
                      placeholder="Card number"
                      value={formData.cardNumber}
                      onChange={(e) => handleInputChange('cardNumber', e.target.value)}
                      onBlur={() => handleBlur('cardNumber')}
                      error={!!errors.cardNumber}
                      helperText={errors.cardNumber}
                    />
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* File Upload Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <UploadIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    File Uploads
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} sm={6}>
                    <Typography variant="caption" fontWeight={600} sx={{ color: '#475569', display: 'block', mb: 0.5 }}>
                      Agency Logo (Optional)
                    </Typography>
                    <ColorfulUploadBox error={!!errors.agencyLogo}>
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 1, flexWrap: 'wrap' }}>
                        <CloudUploadIcon sx={{ fontSize: 18, color: NAVY }} />
                        <Typography variant="body2" fontWeight={600} sx={{ fontSize: '0.75rem', color: '#334155' }}>
                          {formData.agencyLogo ? formData.agencyLogo.name : 'Upload logo · Max 2MB'}
                        </Typography>
                        <GradientButton
                          component="label"
                          size="small"
                          sx={{ py: 0.25, px: 1.25, fontSize: '0.7rem', minHeight: 28 }}
                        >
                          Choose
                          <VisuallyHiddenInput
                            type="file"
                            accept="image/*"
                            onChange={(e) => handleFileUpload('agencyLogo', e.target.files[0])}
                          />
                        </GradientButton>
                        {imagePreviews.agencyLogo && (
                          <Box sx={{ display: 'flex', alignItems: 'center', position: 'relative' }}>
                            <img
                              src={imagePreviews.agencyLogo}
                              alt="Agency Logo Preview"
                              style={{ 
                                width: 32, 
                                height: 32, 
                                objectFit: 'cover', 
                                borderRadius: 4, 
                                border: '1px solid #e2e8f0',
                              }}
                            />
                            <IconButton
                              onClick={() => handleRemoveImage('agencyLogo')}
                              size="small"
                              sx={{
                                position: 'absolute',
                                top: -6,
                                right: -6,
                                bgcolor: 'white',
                                border: '1px solid #e2e8f0',
                                width: 16,
                                height: 16,
                                '&:hover': { bgcolor: '#f8fafc' },
                              }}
                            >
                              <CloseIcon sx={{ fontSize: 10, color: '#ef4444' }} />
                            </IconButton>
                          </Box>
                        )}
                      </Box>
                    </ColorfulUploadBox>
                    {errors.agencyLogo && (
                      <Typography variant="caption" color="error" sx={{ mt: 0.5, display: 'block' }}>
                        {errors.agencyLogo}
                      </Typography>
                    )}
                  </Grid>

                  <Grid item xs={12} sm={6}>
                    <Typography variant="caption" fontWeight={600} sx={{ color: '#475569', display: 'block', mb: 0.5 }}>
                      ID Proof * (Required)
                    </Typography>
                    <ColorfulUploadBox error={!!errors.idProof}>
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 1, flexWrap: 'wrap' }}>
                        <CloudUploadIcon sx={{ fontSize: 18, color: NAVY }} />
                        <Typography variant="body2" fontWeight={600} sx={{ fontSize: '0.75rem', color: '#334155' }}>
                          {formData.idProof ? formData.idProof.name : 'Upload ID proof · Max 2MB'}
                        </Typography>
                        <GradientButton
                          component="label"
                          size="small"
                          sx={{ py: 0.25, px: 1.25, fontSize: '0.7rem', minHeight: 28 }}
                        >
                          Choose
                          <VisuallyHiddenInput
                            type="file"
                            accept="image/*"
                            onChange={(e) => handleFileUpload('idProof', e.target.files[0])}
                          />
                        </GradientButton>
                        {imagePreviews.idProof && (
                          <Box sx={{ display: 'flex', alignItems: 'center', position: 'relative' }}>
                            <img
                              src={imagePreviews.idProof}
                              alt="ID Proof Preview"
                              style={{ 
                                width: 32, 
                                height: 32, 
                                objectFit: 'cover', 
                                borderRadius: 4, 
                                border: '1px solid #e2e8f0',
                              }}
                            />
                            <IconButton
                              onClick={() => handleRemoveImage('idProof')}
                              size="small"
                              sx={{
                                position: 'absolute',
                                top: -6,
                                right: -6,
                                bgcolor: 'white',
                                border: '1px solid #e2e8f0',
                                width: 16,
                                height: 16,
                                '&:hover': { bgcolor: '#f8fafc' },
                              }}
                            >
                              <CloseIcon sx={{ fontSize: 10, color: '#ef4444' }} />
                            </IconButton>
                          </Box>
                        )}
                      </Box>
                    </ColorfulUploadBox>
                    {errors.idProof && (
                      <Typography variant="caption" color="error" sx={{ mt: 0.5, display: 'block' }}>
                        {errors.idProof}
                      </Typography>
                    )}
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* Security Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <LockIcon />
                  <Typography variant="subtitle2" fontWeight={700} sx={{ color: NAVY, fontSize: '0.8rem' }}>
                    Security
                  </Typography>
                </IconContainer>

                <Grid container spacing={1}>
                  <Grid item xs={12} sm={6}>
                    <StyledTextField
                      fullWidth
                      size="small"
                      label="Password *"
                      placeholder="6–8 characters"
                      type={showPassword ? 'text' : 'password'}
                      value={formData.password}
                      onChange={(e) => handleInputChange('password', e.target.value)}
                      onBlur={() => handleBlur('password')}
                      error={!!errors.password}
                      helperText={errors.password || 'Letters, numbers, and special characters allowed'}
                      InputProps={{
                        endAdornment: (
                          <InputAdornment position="end">
                            <IconButton
                              onClick={() => setShowPassword(!showPassword)}
                              edge="end"
                              size="small"
                              sx={{ color: NAVY }}
                            >
                              {showPassword ? <VisibilityOff fontSize="small" /> : <Visibility fontSize="small" />}
                            </IconButton>
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Grid>
                </Grid>
              </CardContent>
            </SectionCard>

            {/* Submit Button */}
            <Box sx={{ mt: 1.25, display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 1 }}>
              <Button
                onClick={() => navigate('/login')}
                size="small"
                sx={{
                  textTransform: 'none',
                  color: '#64748b',
                  fontWeight: 500,
                  fontSize: '0.75rem',
                }}
              >
                Already registered? Sign in
              </Button>
              <GradientButton
                type="submit"
                disabled={loading || success}
                sx={{
                  minWidth: 120,
                }}
              >
                {loading ? (
                  <>
                    <CircularProgress size={18} sx={{ mr: 1, color: 'white' }} />
                    Registering...
                  </>
                ) : success ? (
                  <>
                    <CheckCircle sx={{ mr: 0.75, fontSize: 18 }} />
                    Complete
                  </>
                ) : (
                  'Register'
                )}
              </GradientButton>
            </Box>
          </form>
      </FormShell>
    </Box>
  );
};

export default RegistrationForm;
