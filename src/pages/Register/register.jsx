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
  Divider,
  LinearProgress,
  Card,
  CardContent,
  Avatar
} from '@mui/material';
import {
  CloudUpload as CloudUploadIcon,
  Visibility,
  VisibilityOff,
  CheckCircle,
  Error as ErrorIcon,
  Business as BusinessIcon,
  Person as PersonIcon,
  Email as EmailIcon,
  LocationOn as LocationIcon,
  Phone as PhoneIcon,
  Flag as FlagIcon,
  Upload as UploadIcon,
  Lock as LockIcon,
  Public as PublicIcon,
  Close as CloseIcon
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { toast } from 'react-toastify';
import { registerSlice, sendOTPSlice } from '@/slice/common/RegisterSlice';

// Styled components with colorful gradients and modern design
const GradientPaper = styled(Paper)(({ theme }) => ({
  background: 'linear-gradient(145deg, #667eea 0%, #764ba2 100%)',
  padding: theme.spacing(0.5),
  borderRadius: theme.spacing(3),
  boxShadow: '0 20px 40px rgba(102, 126, 234, 0.4)',
}));

const InnerPaper = styled(Paper)(({ theme }) => ({
  background: 'linear-gradient(145deg, #ffffff 0%, #f8faff 100%)',
  padding: theme.spacing(4),
  borderRadius: theme.spacing(2.5),
  position: 'relative',
  overflow: 'hidden',
  '&::before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: '4px',
    // background: 'linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #f9ca24, #f0932b, #eb4d4b)',
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

const ColorfulAutocomplete = styled(Autocomplete)(({ theme }) => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: theme.spacing(1.5),
    background: 'linear-gradient(145deg,rgb(252, 252, 252) 0%,rgb(255, 255, 255) 100%)',
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
  background: 'linear-gradient(135deg,rgb(248, 248, 248) 0%,rgb(255, 255, 255) 100%)',
  borderRadius: theme.spacing(2),
  padding: theme.spacing(1.5, 4),
  fontSize: '1.1rem',
  fontWeight: 700,
  textTransform: 'none',
  boxShadow: '0 8px 25px rgba(102, 126, 234, 0.4)',
  transition: 'all 0.3s ease',
  '&:hover': {
    background: 'linear-gradient(135deg,rgb(214, 207, 253) 0%,rgb(205, 214, 255) 100%)',
    transform: 'translateY(-3px)',
    boxShadow: '0 12px 35px rgba(102, 126, 234, 0.6)',
  },
  '&:disabled': {
    background: 'linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%)',
    transform: 'none',
    boxShadow: 'none',
  }
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
  border: `3px dashed ${error ? '#f56565' : 'transparent'}`,
  borderRadius: theme.spacing(2),
  padding: theme.spacing(3),
  textAlign: 'center',
  background: error 
    ? 'linear-gradient(145deg, #fed7d7 0%, #feb2b2 50%, #fc8181 100%)'
    : 'linear-gradient(145deg,rgb(255, 255, 255) 0%,rgb(231, 231, 231) 50%,rgb(226, 226, 226) 100%)',
  transition: 'all 0.3s ease',
  cursor: 'pointer',
  '&:hover': {
    transform: 'translateY(-3px)',
    boxShadow: error 
      ? '0 10px 30px rgba(245, 101, 101, 0.3)'
      : '0 10px 30px rgba(129, 230, 217, 0.3)',
  },
}));

const SectionCard = styled(Card)(({ theme }) => ({
  background: 'linear-gradient(145deg, #ffffff 0%, #f7fafc 100%)',
  borderRadius: theme.spacing(2),
  boxShadow: '0 8px 32px rgba(102, 126, 234, 0.1)',
  marginBottom: theme.spacing(3),
  border: '1px solid rgba(102, 126, 234, 0.1)',
  transition: 'all 0.3s ease',
  '&:hover': {
    transform: 'translateY(-2px)',
    boxShadow: '0 12px 40px rgba(102, 126, 234, 0.15)',
  }
}));

const ColorfulChip = styled(Chip)(({ theme }) => ({
  background: 'linear-gradient(135deg,rgb(21, 180, 253) 0%,rgb(41, 207, 212) 100%)',
  color: 'white',
  fontWeight: 600,
  '& .MuiChip-deleteIcon': {
    color: 'white',
  },
}));

const IconContainer = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(1),
  '& .MuiSvgIcon-root': {
    marginRight: theme.spacing(1),
    color: '#667eea',
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
  console.log("formDataregister", formData);

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
        console.error('Error fetching countries:', error);
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
      
      console.log(`File uploaded for ${name}:`, {
        name: file.name,
        size: file.size,
        type: file.type,
        lastModified: file.lastModified,
        isFile: file instanceof File
      });
      
      // Verify file is properly stored in form data
      setTimeout(() => {
        const currentFile = formData[name];
        console.log(`Verification - ${name} in formData:`, {
          exists: !!currentFile,
          isFile: currentFile instanceof File,
          name: currentFile?.name,
          size: currentFile?.size
        });
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

        console.log("Transformed data for API:", {
          ...transformedData,
          agent_image: transformedData.agent_image ? {
            name: transformedData.agent_image.name,
            size: transformedData.agent_image.size,
            type: transformedData.agent_image.type,
            isFile: transformedData.agent_image instanceof File
          } : null,
          image: transformedData.image ? {
            name: transformedData.image.name,
            size: transformedData.image.size,
            type: transformedData.image.type,
            isFile: transformedData.image instanceof File
          } : null
        });
        
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
        
        console.log('Files validation passed:', {
          agent_image: {
            name: transformedData.agent_image.name,
            size: transformedData.agent_image.size,
            type: transformedData.agent_image.type
          },
          image: {
            name: transformedData.image.name,
            size: transformedData.image.size,
            type: transformedData.image.type
          }
        });
        
                  try {
           
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
            console.log('Passing user data for OTP:', userDataForOTP);
            
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
           console.error('Registration error:', error);
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
        background: 'linear-gradient(145deg,rgb(208, 217, 255) 0%,rgb(237, 219, 255) 100%)',
        py: 4,
        px: 2,
        position: 'relative',
        '&::before': {
          content: '""',
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="4"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
        }
      }}
    >
      <GradientPaper
        sx={{
          maxWidth: 900,
          mx: 'auto',
          position: 'relative',
          zIndex: 1,
        }}
      >
        <InnerPaper>
          <Box sx={{ textAlign: 'center', mb: 4 }}>
            <Avatar
              sx={{
                width: 80,
                height: 80,
                mx: 'auto',
                mb: 2,
                background: 'linear-gradient(135deg,rgb(0, 12, 63) 0%,rgb(27, 48, 241) 100%)',
              }}
            >
              <PersonIcon sx={{ fontSize: 40 }} />
            </Avatar>
            
            <Typography
              variant="h3"
              gutterBottom
              sx={{
                fontWeight: 800,
                background: 'linear-gradient(135deg, rgb(2, 22, 116) 100%, rgb(2, 22, 116) 100%)',
                backgroundClip: 'text',
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent',
                mb: 1
              }}
            >
              Agent Registration
            </Typography>
            
            <Typography
              variant="h6"
              sx={{
                color: '#4a5568',
                fontWeight: 400,
                mb: 3,
              }}
            >
              Join our global network of travel professionals
            </Typography>

            {loading && (
              <Box sx={{ mb: 3 }}>
                <LinearProgress 
                  variant="determinate" 
                  value={(currentStep / 4) * 100}
                  sx={{
                    height: 8,
                    borderRadius: 4,
                    background: 'rgba(102, 126, 234, 0.1)',
                    '& .MuiLinearProgress-bar': {
                      background: 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
                      borderRadius: 4,
                    }
                  }}
                />
                <Typography variant="body2" color="primary" sx={{ mt: 1, fontWeight: 600 }}>
                  Step {currentStep} of 4 - Processing your registration...
                </Typography>
              </Box>
            )}
          </Box>

          {success && (
            <Alert
              severity="success"
              icon={<CheckCircle />}
              sx={{ 
                mb: 3,
                borderRadius: 2,
                background: 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)',
                color: 'white',
                '& .MuiAlert-icon': {
                  color: 'white',
                }
              }}
            >
              🎉 Registration completed successfully! Welcome to our platform! Form will reset shortly.
            </Alert>
          )}

          <form onSubmit={handleSubmit}>
            {/* Basic Information Section */}
            <SectionCard>
              <CardContent>
                <IconContainer>
                  <BusinessIcon />
                  <Typography variant="h6" fontWeight={700} color="primary">
                    Basic Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12}>
                    <StyledTextField
                      fullWidth
                      label="Agency Company *"
                      placeholder="Enter your agency company name"
                      value={formData.agencyCompany}
                      onChange={(e) => handleInputChange('agencyCompany', e.target.value)}
                      onBlur={() => handleBlur('agencyCompany')}
                      error={!!errors.agencyCompany}
                      helperText={errors.agencyCompany}
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <BusinessIcon color="primary" />
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Grid>

                  <Grid item xs={12} sm={4}>
                    <FormControl fullWidth error={!!errors.salutation}>
                      <InputLabel sx={{ fontWeight: 600, color: '#5a67d8' }}>Salutation *</InputLabel>
                      <Select
                        value={formData.salutation}
                        onChange={(e) => handleInputChange('salutation', e.target.value)}
                        onBlur={() => handleBlur('salutation')}
                        label="Salutation *"
                        sx={{
                          borderRadius: 1.5,
                          background: 'linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%)',
                        }}
                      >
                        <MenuItem value="Mr.">👨 Mr.</MenuItem>
                        <MenuItem value="Mrs.">👩 Mrs.</MenuItem>
                      </Select>
                      {errors.salutation && <FormHelperText>{errors.salutation}</FormHelperText>}
                    </FormControl>
                  </Grid>

                  <Grid item xs={12} sm={8}>
                    <StyledTextField
                      fullWidth
                      label="Name *"
                      placeholder="Enter agent's full name"
                      value={formData.name}
                      onChange={(e) => handleInputChange('name', e.target.value)}
                      onBlur={() => handleBlur('name')}
                      error={!!errors.name}
                      helperText={errors.name}
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <PersonIcon color="primary" />
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Grid>

                  <Grid item xs={12}>
                    <StyledTextField
                      fullWidth
                      label="Email Address *"
                      placeholder="Enter email address (must include @ and .com)"
                      value={formData.email}
                      onChange={(e) => handleInputChange('email', e.target.value)}
                      onBlur={() => handleBlur('email')}
                      error={!!errors.email}
                      helperText={errors.email}
                      type="email"
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <EmailIcon color="primary" />
                          </InputAdornment>
                        ),
                      }}
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
                  <Typography variant="h6" fontWeight={700} color="primary">
                    Location Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6}>
                    <ColorfulAutocomplete
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name}`}
                      value={formData.agentCountry}
                      onChange={(_, value) => handleInputChange('agentCountry', value)}
                      onBlur={() => handleBlur('agentCountry')}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          label="Agent Country *"
                          placeholder="Search and select country"
                          error={!!errors.agentCountry}
                          helperText={errors.agentCountry}
                          InputProps={{
                            ...params.InputProps,
                            startAdornment: (
                              <InputAdornment position="start">
                                <FlagIcon color="primary" />
                              </InputAdornment>
                            ),
                          }}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props} sx={{ '& > span': { mr: 2 } }}>
                          <span>{option.flag}</span>
                          {option.name}
                        </Box>
                      )}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6}>
                    <ColorfulAutocomplete
                      options={availableCities}
                      value={formData.agentCity}
                      onChange={(_, value) => handleInputChange('agentCity', value)}
                      onBlur={() => handleBlur('agentCity')}
                      disabled={!formData.agentCountry}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          label="Agent City *"
                          placeholder="Search and select city"
                          error={!!errors.agentCity}
                          helperText={errors.agentCity || (!formData.agentCountry ? 'Select country first' : '')}
                          InputProps={{
                            ...params.InputProps,
                            startAdornment: (
                              <InputAdornment position="start">
                                <LocationIcon color="primary" />
                              </InputAdornment>
                            ),
                          }}
                        />
                      )}
                    />
                  </Grid>

                  <Grid item xs={12}>
                    <StyledTextField
                      fullWidth
                      label="Agent Address *"
                      placeholder="Enter complete address"
                      value={formData.agentAddress}
                      onChange={(e) => handleInputChange('agentAddress', e.target.value)}
                      onBlur={() => handleBlur('agentAddress')}
                      error={!!errors.agentAddress}
                      helperText={errors.agentAddress}
                      multiline
                      rows={2}
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start" sx={{ alignSelf: 'flex-start', mt: 1 }}>
                            <LocationIcon color="primary" />
                          </InputAdornment>
                        ),
                      }}
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
                  <Typography variant="h6" fontWeight={700} color="primary">
                    Contact Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6}>
                    <ColorfulAutocomplete
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name} (${option.dialCode})`}
                      value={formData.countryCode}
                      onChange={(_, value) => handleInputChange('countryCode', value)}
                      onBlur={() => handleBlur('countryCode')}
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          label="Country Code *"
                          placeholder="Search country code"
                          error={!!errors.countryCode}
                          helperText={errors.countryCode}
                          InputProps={{
                            ...params.InputProps,
                            startAdornment: (
                              <InputAdornment position="start">
                                <PhoneIcon color="primary" />
                              </InputAdornment>
                            ),
                          }}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props}>
                          <span style={{ marginRight: 8 }}>{option.flag}</span>
                          {option.name} ({option.dialCode})
                        </Box>
                      )}
                    />
                  </Grid>

                  <Grid item xs={12} sm={6}>
                    <StyledTextField
                      fullWidth
                      label="Phone Number *"
                      placeholder="Enter phone number (numbers only)"
                      value={formData.phoneNumber}
                      onChange={(e) => handleInputChange('phoneNumber', e.target.value)}
                      onBlur={() => handleBlur('phoneNumber')}
                      error={!!errors.phoneNumber}
                      helperText={errors.phoneNumber}
                      InputProps={{
                        startAdornment: formData.countryCode && (
                          <InputAdornment position="start" sx={{ color: '#667eea', fontWeight: 600 }}>
                            {formData.countryCode.dialCode}
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Grid>

                  <Grid item xs={12}>
                    <ColorfulAutocomplete
                      multiple
                      options={countries}
                      getOptionLabel={(option) => `${option.flag} ${option.name}`}
                      value={formData.serviceCountries}
                      onChange={(_, value) => handleInputChange('serviceCountries', value)}
                      onBlur={() => handleBlur('serviceCountries')}
                      renderTags={(value, getTagProps) =>
                        value.map((option, index) => (
                          <ColorfulChip
                            variant="filled"
                            label={`${option.flag} ${option.name}`}
                            {...getTagProps({ index })}
                            key={option.code}
                          />
                        ))
                      }
                      renderInput={(params) => (
                        <StyledTextField
                          {...params}
                          label="Service Countries *"
                          placeholder="Select multiple countries"
                          error={!!errors.serviceCountries}
                          helperText={errors.serviceCountries}
                          InputProps={{
                            ...params.InputProps,
                            startAdornment: (
                              <>
                                <InputAdornment position="start">
                                  <PublicIcon color="primary" />
                                </InputAdornment>
                                {params.InputProps.startAdornment}
                              </>
                            ),
                          }}
                        />
                      )}
                      renderOption={(props, option) => (
                        <Box component="li" {...props}>
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
                  <Typography variant="h6" fontWeight={700} color="primary">
                    Document Information
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6}>
                    <StyledTextField
                      fullWidth
                      label="ID Card *"
                      placeholder="Enter ID card information"
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
                      label="Card Number *"
                      placeholder="Enter card number (letters and numbers)"
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
                  <Typography variant="h6" fontWeight={700} color="primary">
                    File Uploads
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle1" gutterBottom fontWeight={600} color="primary">
                      📄 Agency Logo (Optional)
                    </Typography>
                    <ColorfulUploadBox error={!!errors.agencyLogo}>
                      <CloudUploadIcon sx={{ fontSize: 48, color: '#4fd1c7', mb: 1 }} />
                      <Typography variant="body1" gutterBottom fontWeight={600}>
                        {formData.agencyLogo ? formData.agencyLogo.name : 'Click to upload agency logo'}
                      </Typography>
                      <Typography variant="caption" color="text.secondary">
                        Max size: 2MB | Image files only
                      </Typography>
                      <GradientButton
                        component="label"
                        startIcon={<CloudUploadIcon />}
                        sx={{ mt: 2 }}
                      >
                        Choose File
                        <VisuallyHiddenInput
                          type="file"
                          accept="image/*"
                          onChange={(e) => handleFileUpload('agencyLogo', e.target.files[0])}
                        />
                      </GradientButton>
                      {imagePreviews.agencyLogo && (
                        <Box sx={{ mt: 2, display: 'flex', justifyContent: 'center', position: 'relative' }}>
                          <img
                            src={imagePreviews.agencyLogo}
                            alt="Agency Logo Preview"
                            style={{ width: 64, height: 64, objectFit: 'cover', borderRadius: 8, boxShadow: '0 2px 8px rgba(0,0,0,0.08)' }}
                          />
                          <IconButton
                            onClick={() => handleRemoveImage('agencyLogo')}
                            sx={{
                              position: 'absolute',
                              top: -8,
                              right: -8,
                              backgroundColor: 'rgba(255, 255, 255, 0.9)',
                              border: '1px solid #e2e8f0',
                              width: 24,
                              height: 24,
                              '&:hover': {
                                backgroundColor: 'rgba(255, 255, 255, 1)',
                              }
                            }}
                          >
                            <CloseIcon sx={{ fontSize: 16, color: '#e53e3e' }} />
                          </IconButton>
                        </Box>
                      )}
                    </ColorfulUploadBox>
                    {errors.agencyLogo && (
                      <Typography variant="caption" color="error" sx={{ mt: 1, display: 'block', fontWeight: 600 }}>
                        {errors.agencyLogo}
                      </Typography>
                    )}
                  </Grid>

                  <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle1" gutterBottom fontWeight={600} color="primary">
                      🆔 ID Proof * (Required)
                    </Typography>
                    <ColorfulUploadBox error={!!errors.idProof}>
                      <CloudUploadIcon sx={{ fontSize: 48, color: '#4fd1c7', mb: 1 }} />
                      <Typography variant="body1" gutterBottom fontWeight={600}>
                        {formData.idProof ? formData.idProof.name : 'Click to upload ID proof'}
                      </Typography>
                      <Typography variant="caption" color="text.secondary">
                        Max size: 2MB | Image files only
                      </Typography>
                      <GradientButton
                        component="label"
                        startIcon={<CloudUploadIcon />}
                        sx={{ mt: 2 }}
                      >
                        Choose File
                        <VisuallyHiddenInput
                          type="file"
                          accept="image/*"
                          onChange={(e) => handleFileUpload('idProof', e.target.files[0])}
                        />
                      </GradientButton>
                      {imagePreviews.idProof && (
                        <Box sx={{ mt: 2, display: 'flex', justifyContent: 'center', position: 'relative' }}>
                          <img
                            src={imagePreviews.idProof}
                            alt="ID Proof Preview"
                            style={{ width: 64, height: 64, objectFit: 'cover', borderRadius: 8, boxShadow: '0 2px 8px rgba(0,0,0,0.08)' }}
                          />
                          <IconButton
                            onClick={() => handleRemoveImage('idProof')}
                            sx={{
                              position: 'absolute',
                              top: -8,
                              right: -8,
                              backgroundColor: 'rgba(255, 255, 255, 0.9)',
                              border: '1px solid #e2e8f0',
                              width: 24,
                              height: 24,
                              '&:hover': {
                                backgroundColor: 'rgba(255, 255, 255, 1)',
                              }
                            }}
                          >
                            <CloseIcon sx={{ fontSize: 16, color: '#e53e3e' }} />
                          </IconButton>
                        </Box>
                      )}
                    </ColorfulUploadBox>
                    {errors.idProof && (
                      <Typography variant="caption" color="error" sx={{ mt: 1, display: 'block', fontWeight: 600 }}>
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
                  <Typography variant="h6" fontWeight={700} color="primary">
                    Security
                  </Typography>
                </IconContainer>

                <Grid container spacing={3}>
                  <Grid item xs={12}>
                    <StyledTextField
                      fullWidth
                      label="Password *"
                      placeholder="Enter password (6-8 characters)"
                      type={showPassword ? 'text' : 'password'}
                      value={formData.password}
                      onChange={(e) => handleInputChange('password', e.target.value)}
                      onBlur={() => handleBlur('password')}
                      error={!!errors.password}
                      helperText={errors.password || 'Password can contain text, numbers, and special characters'}
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <LockIcon color="primary" />
                          </InputAdornment>
                        ),
                        endAdornment: (
                          <InputAdornment position="end">
                            <IconButton
                              onClick={() => setShowPassword(!showPassword)}
                              edge="end"
                              sx={{ color: '#667eea' }}
                            >
                              {showPassword ? <VisibilityOff /> : <Visibility />}
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
            <Box sx={{ mt: 4, display: 'flex', justifyContent: 'center' }}>
              <GradientButton
                type="submit"
                size="large"
                disabled={loading || success}
                sx={{
                  minWidth: 250,
                  py: 2,
                  fontSize: '1.2rem',
                  fontWeight: 700,
                }}
              >
                {loading ? (
                  <>
                    <CircularProgress size={24} sx={{ mr: 2, color: 'white' }} />
                    Registering...
                  </>
                ) : success ? (
                  <>
                    <CheckCircle sx={{ mr: 1 }} />
                    Registration Complete! 🎉
                  </>
                ) : (
                  <>
                    <PersonIcon sx={{ mr: 1 }} />
                    Register Now
                  </>
                )}
              </GradientButton>
            </Box>
          </form>
        </InnerPaper>
      </GradientPaper>
    </Box>
  );
};

export default RegistrationForm;
