import React, { useRef, useState, useEffect } from "react";
import {
  Button,
  Skeleton,
  Paper,
  Typography,
  Box,
  Grid,
  Divider,
  Card,
  CardContent,
  Container,
  useTheme,
  Stack,
  Chip,
  styled,
  Avatar,
  LinearProgress,
} from "@mui/material";
import { Modal } from "antd";
import AccountCircleIcon from "@mui/icons-material/AccountCircle";
import { Table, Empty } from "antd";
import CheckCircleOutlinedIcon from "@mui/icons-material/CheckCircleOutlined";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import QuestionAnswerIcon from "@mui/icons-material/QuestionAnswer";
import IconButton from "@mui/material/IconButton";
import HotelIcon from "@mui/icons-material/Hotel";
import LocalShippingIcon from "@mui/icons-material/LocalShipping";
import TourIcon from "@mui/icons-material/Tour";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import SummarizeIcon from "@mui/icons-material/Summarize";
import SingleBedIcon from "@mui/icons-material/SingleBed";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import StraightenIcon from "@mui/icons-material/Straighten";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import PeopleIcon from "@mui/icons-material/People";
import WorkIcon from "@mui/icons-material/Work";
import SchoolIcon from "@mui/icons-material/School";
import FlightTakeoffIcon from "@mui/icons-material/FlightTakeoff";
import FlightLandIcon from "@mui/icons-material/FlightLand";
import BadgeIcon from "@mui/icons-material/Badge";
import AttractionsIcon from "@mui/icons-material/Attractions";
import BrunchDiningIcon from "@mui/icons-material/BrunchDining";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import TimerIcon from "@mui/icons-material/Timer";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import PersonIcon from "@mui/icons-material/Person";
import CribIcon from "@mui/icons-material/Crib";
import DoNotDisturbIcon from "@mui/icons-material/DoNotDisturb";
import HourglassEmptyIcon from "@mui/icons-material/HourglassEmpty";
import ArrowUpwardIcon from "@mui/icons-material/ArrowUpward";
import ArrowDownwardIcon from "@mui/icons-material/ArrowDownward";
import { saveAs } from "file-saver";
import { jsPDF } from "jspdf";
import html2canvas from "html2canvas";
import { PDFDocument } from "pdf-lib";
import ManIcon from "@mui/icons-material/Man";
import WomanIcon from "@mui/icons-material/Woman";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import BabyChangingStationIcon from "@mui/icons-material/BabyChangingStation";
import KingBedIcon from "@mui/icons-material/KingBed";
import BedIcon from "@mui/icons-material/Bed";
import CheckIcon from "@mui/icons-material/Check";
import RoomServiceIcon from "@mui/icons-material/RoomService";
import FreeBreakfastIcon from "@mui/icons-material/FreeBreakfast";
import DinnerDiningIcon from "@mui/icons-material/DinnerDining";
import LunchDiningIcon from "@mui/icons-material/LunchDining";
import NoteIcon from "@mui/icons-material/Note";
import EmailIcon from "@mui/icons-material/Email";
import PhoneIcon from "@mui/icons-material/Phone";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import AccessTimeFilledIcon from "@mui/icons-material/AccessTimeFilled";
import PublicIcon from "@mui/icons-material/Public";
import DriveEtaIcon from "@mui/icons-material/DriveEta";
import AirlineSeatReclineNormalIcon from "@mui/icons-material/AirlineSeatReclineNormal";
import ScheduleIcon from "@mui/icons-material/Schedule";
import TodayIcon from "@mui/icons-material/Today";
import ShareIcon from "@mui/icons-material/Share";
import StarIcon from "@mui/icons-material/Star";
import SpeedIcon from "@mui/icons-material/Speed";
import InfoIcon from "@mui/icons-material/Info";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";
import DescriptionIcon from "@mui/icons-material/Description";
import MoneyIcon from "@mui/icons-material/Money";
import ElderlyIcon from "@mui/icons-material/Elderly";
import FamilyRestroomIcon from "@mui/icons-material/FamilyRestroom";
import GroupIcon from "@mui/icons-material/Group";
import LocalActivityIcon from "@mui/icons-material/LocalActivity";
import HomeWorkIcon from "@mui/icons-material/HomeWork";
import PlaceIcon from "@mui/icons-material/Place";
import AlternateEmailIcon from "@mui/icons-material/AlternateEmail";
import PhoneInTalkIcon from "@mui/icons-material/PhoneInTalk";
import LanguageIcon from "@mui/icons-material/Language";
import StarsIcon from "@mui/icons-material/Stars";
import PaymentIcon from "@mui/icons-material/Payment";
import WatchLaterIcon from "@mui/icons-material/WatchLater";
import TranslateIcon from "@mui/icons-material/Translate";
import AutoStoriesIcon from "@mui/icons-material/AutoStories";
import EmojiEventsIcon from "@mui/icons-material/EmojiEvents";
import DoneIcon from "@mui/icons-material/Done";
import api, { endpoints } from "../../../../../services/api";
import { useSelector } from "react-redux";

// Helper function to format dates in "Month DD, YYYY" format
const formatDate = (dateString) => {
  if (!dateString) return "N/A";

  try {
    // Handle different date formats
    let date;

    // If it's already a Date object
    if (dateString instanceof Date) {
      date = dateString;
    }
    // Handle DD/MM/YYYY format
    else if (typeof dateString === "string" && dateString.includes("/")) {
      const [day, month, year] = dateString.split("/").map(Number);
      date = new Date(year, month - 1, day); // Month is 0-based in JS
    }
    // Handle YYYY-MM-DD format
    else if (typeof dateString === "string" && dateString.includes("-")) {
      date = new Date(dateString);
    }
    // Default fallback
    else {
      date = new Date(dateString);
    }

    // Check if date is valid
    if (isNaN(date.getTime())) {
      return String(dateString); // Return original string if parsing failed
    }

    // Format as "Month DD, YYYY"
    return date.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric",
    });
  } catch (error) {
    console.error("Error formatting date:", error);
    return String(dateString); // Convert to string on error
  }
};

// Utility function to fetch static PDFs
const fetchStaticPdf = async (url) => {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Failed to fetch PDF: ${response.statusText}`);
    }
    return await response.arrayBuffer();
  } catch (error) {
    console.error(`Error fetching PDF from ${url}:`, error);
    throw error;
  }
};



// Updated utility function to generate PDF from DOM element using jsPDF with improved page breaks
const generateMainPdfBlob = async (element) => {
  return new Promise(async (resolve, reject) => {
    try {
      // Check if element is valid
      if (!element || typeof element !== "object" || !element.nodeType) {
        throw new Error(
          "Invalid DOM element provided. Make sure contentRef.current is a valid HTML element."
        );
      }
      
      // Add CSS class to improve PDF rendering - this forces sections to avoid page breaks
      const sections = element.querySelectorAll('.StyledCard');
      sections.forEach(section => {
        section.style.pageBreakInside = 'avoid';
        section.style.breakInside = 'avoid';
      });

      // Add specific handling for problematic sections
      const entryPorts = element.querySelectorAll('[data-section="entry-port"]');
      entryPorts.forEach(section => {
        section.style.pageBreakInside = 'avoid';
        section.style.breakInside = 'avoid';
        section.style.marginBottom = '20px'; // Add extra margin
      });

      const restaurants = element.querySelectorAll('[data-section="restaurant"]');
      restaurants.forEach(section => {
        section.style.pageBreakInside = 'avoid';
        section.style.breakInside = 'avoid';
        section.style.marginBottom = '20px'; // Add extra margin
        
        // Ensure meal types don't break
        const mealTypes = section.querySelectorAll('[data-content="meal-types"]');
        mealTypes.forEach(mt => {
          mt.style.pageBreakInside = 'avoid';
          mt.style.breakInside = 'avoid';
        });
      });

      // Capture the HTML content as a canvas with higher scale for better quality
      const canvas = await html2canvas(element, {
        scale: 2.5, // Increased scale for better quality
        useCORS: true,
        logging: false,
        allowTaint: true,
        letterRendering: true,
      });

      // Create PDF with A4 dimensions
      const pdf = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
      });

      // Get PDF page dimensions
      const pageWidth = pdf.internal.pageSize.getWidth();
      const pageHeight = pdf.internal.pageSize.getHeight();

      // Calculate image dimensions to fit the page width
      const imgWidth = pageWidth - 10; // Add small margin
      const imgHeight = (canvas.height * imgWidth) / canvas.width;

      // Calculate how many pages we need based on the image height
      const pagesCount = Math.ceil(imgHeight / (pageHeight - 10)); // 10mm margin for top and bottom

      // If it's a single page document
      if (pagesCount <= 1) {
        pdf.addImage(
          canvas.toDataURL("image/jpeg", 0.95),
          "JPEG",
          5, // Left margin of 5mm
          5, // Top margin of 5mm
          imgWidth,
          imgHeight
        );
      } else {
        // For multi-page documents
        for (let i = 0; i < pagesCount; i++) {
          // Add new page if it's not the first page
          if (i > 0) {
            pdf.addPage();
          }

          // Position calculation for showing the right part of the image on each page
          // Adding a small overlap between pages to prevent cutting content
          const sourceY = pageHeight * i * (canvas.height / imgHeight) - (i > 0 ? 10 : 0);
          const position = -pageHeight * i + (i > 0 ? 5 : 0); // Add small overlap between pages

          pdf.addImage(
            canvas.toDataURL("image/jpeg", 0.95),
            "JPEG",
            5, // Left margin of 5mm
            position,
            imgWidth,
            imgHeight
          );
        }
      }

      // Convert to blob
      const blob = pdf.output("blob");
      resolve(blob);
    } catch (error) {
      console.error("Error generating PDF from HTML:", error);
      reject(error);
    }
  });
};

// Utility function to convert Blob to ArrayBuffer
const blobToArrayBuffer = async (blob) => {
  return await blob.arrayBuffer();
};

// Enhanced utility function to merge PDFs with improved error handling
const mergePdfs = async (headerPdfBytes, contentPdfBytes, footerPdfBytes) => {
  try {
    // Create a new PDF document
    const mergedPdf = await PDFDocument.create();
    
    // Load content PDF first (this is required)
    let contentPdf;
    try {
      contentPdf = await PDFDocument.load(contentPdfBytes);
    } catch (contentError) {
      console.error("Error loading content PDF:", contentError);
      throw new Error("Failed to load content PDF. This is a required component.");
    }

    // Try to load header PDF if provided
    let headerPdf = null;
    if (headerPdfBytes) {
      try {
        headerPdf = await PDFDocument.load(headerPdfBytes);
      } catch (headerError) {
        console.warn("Error loading header PDF:", headerError.message);
        // Removed: console.log("Will proceed without header PDF");
      }
    }

    // Try to load footer PDF if provided
    let footerPdf = null;
    if (footerPdfBytes) {
      try {
        footerPdf = await PDFDocument.load(footerPdfBytes);
      } catch (footerError) {
        console.warn("Error loading footer PDF:", footerError.message);
        // Removed: console.log("Will proceed without footer PDF");
      }
    }

    // Copy pages from header PDF
    if (headerPdf) {
      try {
        const headerPages = await mergedPdf.copyPages(
          headerPdf,
          headerPdf.getPageIndices()
        );
        headerPages.forEach((page) => mergedPdf.addPage(page));
      } catch (headerCopyError) {
        console.warn("Error copying header pages:", headerCopyError.message);
      }
    }

    // Copy pages from content PDF (required)
    try {
      const contentPages = await mergedPdf.copyPages(
        contentPdf,
        contentPdf.getPageIndices()
      );
      contentPages.forEach((page) => mergedPdf.addPage(page));
    } catch (contentCopyError) {
      console.error("Error copying content pages:", contentCopyError);
      throw new Error("Failed to copy content pages to merged PDF");
    }

    // Copy pages from footer PDF
    if (footerPdf) {
      try {
        const footerPages = await mergedPdf.copyPages(
          footerPdf,
          footerPdf.getPageIndices()
        );
        footerPages.forEach((page) => mergedPdf.addPage(page));
      } catch (footerCopyError) {
        console.warn("Error copying footer pages:", footerCopyError.message);
      }
    }

    // Serialize the merged PDF with compression options
    const mergedPdfBytes = await mergedPdf.save({
      useObjectStreams: true,
      addDefaultPage: false,
    });
    return mergedPdfBytes;
  } catch (error) {
    console.error("Fatal error in PDF merge process:", error);
    // Return just the content PDF as a fallback
    return contentPdfBytes;
  }
};

// Utility function to download the merged PDF
const downloadMergedPdf = (pdfBytes, filename = "tour_details.pdf") => {
  const blob = new Blob([pdfBytes], { type: "application/pdf" });
  saveAs(blob, filename);
};

// Styled components for enhanced UI
const StyledCard = styled(Card)(({ theme }) => ({
  borderRadius: 12,
  boxShadow: "0 8px 16px rgba(0, 0, 0, 0.1)",
  transition: "transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out",
  overflow: "hidden",
  marginBottom: 24,
  "&:hover": {
    transform: "translateY(-5px)",
    boxShadow: "0 12px 20px rgba(0, 0, 0, 0.15)",
  },
}));

const StyledCardHeader = styled(Box)(({ theme }) => ({
  padding: "16px 24px",
  background: "linear-gradient(90deg, #1976d2 0%, #2196F3 100%)",
  color: "white",
}));

const IconWrapper = styled(Box)(({ theme }) => ({
  display: "flex",
  alignItems: "center",
  marginBottom: 8,
  "& svg": {
    marginRight: 8,
    color: "#1976d2",
  },
}));

const GradientDivider = styled(Divider)(({ theme }) => ({
  height: 3,
  background: "linear-gradient(90deg, #1976d2 0%, #2196F3 100%)",
  margin: "16px 0",
}));

// Add this function before handleMergedPdfDownload
const prepareContentForPdf = (contentElement) => {
  if (!contentElement) return;
  
  // Add global styles for better PDF rendering
  contentElement.style.fontFamily = 'Arial, sans-serif';
  
  // Add spacing between major sections
  const cards = contentElement.querySelectorAll('.StyledCard');
  cards.forEach((card, index) => {
    card.style.pageBreakInside = 'avoid';
    card.style.breakInside = 'avoid';
    card.style.marginBottom = '30px';
  });
  
  // Ensure entry and exit ports don't break
  const portSections = contentElement.querySelectorAll('[data-section="entry-port"], [data-section="exit-port"]');
  portSections.forEach(section => {
    section.style.pageBreakInside = 'avoid';
    section.style.breakInside = 'avoid';
    section.style.marginBottom = '25px';
  });
  
  // Handle restaurant sections and their meal types
  const restaurantSections = contentElement.querySelectorAll('[data-section="restaurant"]');
  restaurantSections.forEach(section => {
    section.style.pageBreakInside = 'avoid';
    section.style.breakInside = 'avoid';
    section.style.marginBottom = '25px';
    
    const mealTypes = section.querySelectorAll('[data-content="meal-types"]');
    mealTypes.forEach(mt => {
      mt.style.pageBreakInside = 'avoid';
      mt.style.breakInside = 'avoid';
    });
  });
  
  // Ensure hotel rooms don't break across pages
  const hotelRooms = contentElement.querySelectorAll('[data-content="hotel-room"]');
  hotelRooms.forEach(room => {
    room.style.pageBreakInside = 'avoid';
    room.style.breakInside = 'avoid';
  });

  return contentElement;
};

const PrintModal = ({
  isPrintModalVisible,
  handleCancelPrint,
  handleDownloadPDF,
  contentRef,
  viewDetailsStatus,
  DmcLogo,
  DmcName,
  displayId,
  bookings,
  modifiedPriceData,
  markupAmount,
  discountAmount,
  totalPrice,
  tourId,
  pricehide,
}) => {
  // Store the data in local state to ensure it persists through re-renders
  const [localBookings, setLocalBookings] = useState(bookings || {});
  const [localModifiedPriceData, setLocalModifiedPriceData] =
    useState(modifiedPriceData);
  const [localMarkupAmount, setLocalMarkupAmount] = useState(markupAmount || 0);
  const [localDiscountAmount, setLocalDiscountAmount] = useState(
    discountAmount || 0
  );
  const [localDisplayId, setLocalDisplayId] = useState(displayId);
  const [localTotalPrice, setLocalTotalPrice] = useState(totalPrice || 0);
  const [localPricehide, setLocalPricehide] = useState(pricehide);
  // Add loading state for PDF generation
  const [isGeneratingPdf, setIsGeneratingPdf] = useState(false);
  const [pdfProgress, setPdfProgress] = useState({ status: '', progress: 0 });
  const [pdfError, setPdfError] = useState(null);


  // Update local state when props change
  useEffect(() => {
    if (bookings && Object.keys(bookings).length > 0) {
      setLocalBookings(bookings);
    }
    if (modifiedPriceData && Object.keys(modifiedPriceData).length > 0) {
      setLocalModifiedPriceData(modifiedPriceData);
    }
    if (markupAmount !== undefined) {
      setLocalMarkupAmount(markupAmount);
    }
    if (discountAmount !== undefined) {
      setLocalDiscountAmount(discountAmount);
    }
    if (displayId) {
      setLocalDisplayId(displayId);
    }
    if (totalPrice !== undefined) {
      setLocalTotalPrice(totalPrice);
    }
    if (pricehide !== undefined) {
      setLocalPricehide(pricehide);
    }
  }, [
    bookings,
    modifiedPriceData,
    markupAmount,
    discountAmount,
    displayId,
    totalPrice,
    pricehide,
   
  ]);

  // When modal becomes visible, ensure we have the most recent data
  useEffect(() => {
    if (isPrintModalVisible) {
      // Only update if we have data
      if (bookings && Object.keys(bookings).length > 0) {
        console.log("Updating localBookings from bookings", bookings);
        setLocalBookings(bookings);
      }

      if (modifiedPriceData && Object.keys(modifiedPriceData).length > 0) {
        console.log(
          "Updating localModifiedPriceData from modifiedPriceData",
          modifiedPriceData
        );
        setLocalModifiedPriceData(modifiedPriceData);
      }

      // Always update these values if they exist
      if (markupAmount !== undefined) setLocalMarkupAmount(markupAmount);
      if (discountAmount !== undefined) setLocalDiscountAmount(discountAmount);
      if (displayId) setLocalDisplayId(displayId);
      if (totalPrice !== undefined) setLocalTotalPrice(totalPrice);
      if (pricehide !== undefined) setLocalPricehide(pricehide);
    }
  }, [
    isPrintModalVisible,
    bookings,
    modifiedPriceData,
    markupAmount,
    discountAmount,
    displayId,
    totalPrice,
    pricehide,
   
  ]);

  // Add a new useEffect to log state changes for debugging
  useEffect(() => {
    console.log("localBookings updated:", localBookings);
    console.log("localModifiedPriceData updated:", localModifiedPriceData);
    console.log("localPricehide updated:", localPricehide);
    console.log("pricehide prop received:", pricehide);
    console.log("pricehide type:", typeof pricehide);
      }, [localBookings, localModifiedPriceData, localPricehide, pricehide]);

  // Create an internal reference to the content
  const internalContentRef = useRef(null);
  const theme = useTheme();

  // Enhanced download handler that merges PDFs
  const handleMergedPdfDownload = async () => {
    // Reset states
    setPdfError(null);
    setIsGeneratingPdf(true);
    setPdfProgress({ status: 'Starting PDF generation...', progress: 5 });
    
    try {
      // Show progress message
      console.log("PDF generation process started...");
      
      // Check if internal content ref is valid
      if (!internalContentRef || !internalContentRef.current) {
        console.error("⚠️ Internal content reference is not valid.");
        setPdfError("Could not access page content. Please try again.");
        setIsGeneratingPdf(false);
        throw new Error("Internal content reference is not valid");
      }

      // Prepare content for PDF generation
      console.log("Preparing content for PDF generation...");
      setPdfProgress({ status: 'Preparing content...', progress: 15 });
      prepareContentForPdf(internalContentRef.current);

      // Generate the main content PDF first
      console.log("Generating content PDF from HTML...");
      setPdfProgress({ status: 'Generating content PDF...', progress: 30 });
      const contentBlob = await generateMainPdfBlob(internalContentRef.current)
        .catch(error => {
          console.error("⚠️ Error generating content PDF:", error);
          setPdfError("Failed to generate PDF from content. Please try again.");
          setIsGeneratingPdf(false);
          throw new Error("Failed to generate content PDF from HTML");
        });
      
      console.log("Converting content blob to array buffer...");
      setPdfProgress({ status: 'Processing content PDF...', progress: 50 });
      const contentPdfBytes = await blobToArrayBuffer(contentBlob)
        .catch(error => {
          console.error("⚠️ Error converting blob to array buffer:", error);
          setPdfError("Failed to process PDF content. Please try again.");
          setIsGeneratingPdf(false);
          throw new Error("Failed to convert content PDF blob to array buffer");
        });
      
      console.log("✅ Content PDF generated successfully");
      setPdfProgress({ status: 'Content PDF generated successfully', progress: 60 });

      // Try to get header and footer PDFs from API
      let headerPdfBytes = null;
      let footerPdfBytes = null;
      
      try {
        // Get country from tour destination or default to Singapore
        const country = displayData?.tour?.destination || 'Singapore';
        console.log("Getting PDF templates for country:", country);
        
        // Set a timeout for the API request
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
        
        // Fetch the PDF URLs from the API
        console.log("Fetching PDF template URLs from API...");
        const response = await api.get("/get-pdf", {
          headers: {
            'country': country,
          },
          signal: controller.signal,
          timeout: 15000,
        }).catch(error => {
          console.warn("⚠️ API request failed:", error.message);
          throw error;
        });
        
        // Clear the timeout
        clearTimeout(timeoutId);
        
        const data = response.data;
        console.log("✅ Received PDF template data:", data);
        
        if (data && data.header_pdf) {
          const headerUrl = data.header_pdf;
          console.log("Header PDF URL from API:", headerUrl);
          setPdfProgress({ status: 'Fetching header PDF...', progress: 65 });
          
          let headerFetchSuccess = false;
          
          // Attempt 1: Try direct fetch
          if (!headerFetchSuccess) {
            try {
              console.log("Attempting direct fetch for header PDF...");
              const directResponse = await fetch(headerUrl, {
                headers: { 'Accept': 'application/pdf' }
              });
              
              if (directResponse.ok) {
                headerPdfBytes = await directResponse.arrayBuffer();
                console.log("✅ Successfully downloaded header PDF directly");
                headerFetchSuccess = true;
              }
            } catch (directError) {
              console.log("❌ Direct header fetch failed:", directError.message);
            }
          }
          
          // Attempt 2: Try using a CORS proxy
          if (!headerFetchSuccess) {
            try {
              const proxyUrl = `https://api.allorigins.win/raw?url=${encodeURIComponent(headerUrl)}`;
              
              console.log("Fetching header PDF through CORS proxy...");
              const headerResponse = await fetch(proxyUrl);
              
              if (headerResponse.ok) {
                headerPdfBytes = await headerResponse.arrayBuffer();
                console.log("✅ Successfully downloaded header PDF from API");
                headerFetchSuccess = true;
              } else {
                throw new Error(`Failed to fetch through proxy: ${headerResponse.statusText}`);
              }
            } catch (proxyError) {
              console.warn("❌ Error fetching header PDF through proxy:", proxyError.message);
            }
          }
          
          // Attempt 3: Try alternative proxy
          if (!headerFetchSuccess) {
            try {
              console.log("Attempting alternative proxy for header PDF...");
              const altProxyUrl = `https://corsproxy.io/?${encodeURIComponent(headerUrl)}`;
              const altResponse = await fetch(altProxyUrl);
              
              if (altResponse.ok) {
                headerPdfBytes = await altResponse.arrayBuffer();
                console.log("✅ Successfully downloaded header PDF through alternative proxy");
                headerFetchSuccess = true;
              }
            } catch (altError) {
              console.log("❌ Alternative proxy failed:", altError.message);
            }
          }
          
          // Fallback to static PDF if all attempts failed
          if (!headerFetchSuccess) {
            console.log("Falling back to static header PDF...");
            try {
              headerPdfBytes = await fetchStaticPdf("/pdf/Singapore.pdf");
              console.log("✅ Static header PDF loaded successfully");
            } catch (staticError) {
              console.warn("❌ Could not load static header PDF either:", staticError.message);
            }
          }
          
          setPdfProgress({ status: 'Header PDF processed', progress: 70 });
        }
        
        if (data && data.footer_pdf) {
          const footerUrl = data.footer_pdf;
          console.log("Footer PDF URL from API:", footerUrl);
          setPdfProgress({ status: 'Fetching footer PDF...', progress: 75 });
          
          let footerFetchSuccess = false;
          
          // Attempt 1: Try direct fetch first (same as header)
          if (!footerFetchSuccess) {
            try {
              console.log("Attempting direct fetch for footer PDF...");
              const directResponse = await fetch(footerUrl, {
                headers: { 'Accept': 'application/pdf' }
              });
              
              if (directResponse.ok) {
                footerPdfBytes = await directResponse.arrayBuffer();
                console.log("✅ Successfully downloaded footer PDF directly");
                footerFetchSuccess = true;
              }
            } catch (directError) {
              console.log("❌ Direct footer fetch failed:", directError.message);
            }
          }
          
          // Attempt 2: Try using a CORS proxy (same as header)
          if (!footerFetchSuccess) {
            try {
              const proxyUrl = `https://api.allorigins.win/raw?url=${encodeURIComponent(footerUrl)}`;
              
              console.log("Fetching footer PDF through CORS proxy...");
              const footerResponse = await fetch(proxyUrl);
              
              if (footerResponse.ok) {
                footerPdfBytes = await footerResponse.arrayBuffer();
                console.log("✅ Successfully downloaded footer PDF from API");
                footerFetchSuccess = true;
              } else {
                throw new Error(`Failed to fetch through proxy: ${footerResponse.statusText}`);
              }
            } catch (proxyError) {
              console.warn("❌ Error fetching footer PDF through proxy:", proxyError.message);
            }
          }
          
          // Attempt 3: Try alternative proxy (same as header)
          if (!footerFetchSuccess) {
            try {
              console.log("Attempting alternative proxy for footer PDF...");
              const altProxyUrl = `https://corsproxy.io/?${encodeURIComponent(footerUrl)}`;
              const altResponse = await fetch(altProxyUrl);
              
              if (altResponse.ok) {
                footerPdfBytes = await altResponse.arrayBuffer();
                console.log("✅ Successfully downloaded footer PDF through alternative proxy");
                footerFetchSuccess = true;
              }
            } catch (altError) {
              console.log("❌ Alternative proxy failed:", altError.message);
            }
          }
          
          // Fallback to static PDF if all attempts failed (same as header)
          if (!footerFetchSuccess) {
            console.log("Falling back to static footer PDF...");
            try {
              footerPdfBytes = await fetchStaticPdf("/pdf/Maldives.pdf");
              console.log("✅ Static footer PDF loaded successfully");
            } catch (staticError) {
              console.warn("❌ Could not load static footer PDF either:", staticError.message);
            }
          }
          
          setPdfProgress({ status: 'Footer PDF processed', progress: 80 });
        }
      } catch (error) {
        console.warn("Error in PDF fetching process:", error.message);
        
        // If API fetch failed completely, try static PDFs
        if (!headerPdfBytes) {
          try {
            headerPdfBytes = await fetchStaticPdf("/pdf/Singapore.pdf");
            console.log("Static header PDF loaded as fallback");
          } catch (headerError) {
            console.warn("Could not load header PDF:", headerError.message);
          }
        }
        
        if (!footerPdfBytes) {
          try {
            footerPdfBytes = await fetchStaticPdf("/pdf/Maldives.pdf");
            console.log("Static footer PDF loaded as fallback");
          } catch (footerError) {
            console.warn("Could not load footer PDF:", footerError.message);
          }
        }
      }

      // Merge the PDFs if we have header or footer
      let finalPdfBytes = contentPdfBytes;
      
      if (headerPdfBytes || footerPdfBytes) {
        console.log("Merging PDFs...");
        setPdfProgress({ status: 'Merging PDFs...', progress: 85 });
        try {
          finalPdfBytes = await mergePdfs(
            headerPdfBytes,
            contentPdfBytes,
            footerPdfBytes
          );
          console.log("PDFs merged successfully");
          setPdfProgress({ status: 'PDFs merged successfully', progress: 95 });
        } catch (mergeError) {
          console.warn("Error merging PDFs, using content only:", mergeError.message);
          setPdfProgress({ status: 'Using content PDF only (merge failed)', progress: 90 });
          finalPdfBytes = contentPdfBytes;
        }
      } else {
        setPdfProgress({ status: 'Using content PDF only (no templates found)', progress: 90 });
      }
      
      // Download the PDF
      console.log("Downloading PDF...");
      setPdfProgress({ status: 'Initiating download...', progress: 99 });
      
      try {
        downloadMergedPdf(
          finalPdfBytes,
          `tour_details_${localDisplayId || displayId || "booking"}.pdf`
        );
        console.log("PDF download initiated");
        setPdfProgress({ status: 'Download complete!', progress: 100 });
        
        // Reset states after a short delay to show completed progress
        setTimeout(() => {
          setIsGeneratingPdf(false);
        }, 1500);
      } catch (downloadError) {
        console.error("Error downloading PDF:", downloadError);
        setPdfError("Failed to download PDF. Please try again.");
        setIsGeneratingPdf(false);
      }
      
      /* 
       * DEVELOPER NOTE:
       * --------------------------------------------------
       * This implementation uses a public CORS proxy to fetch PDFs from S3.
       * For a production environment, you should:
       * 
       * 1. Set up your own CORS proxy server
       * 2. Configure the S3 bucket's CORS settings
       * 3. Use a backend endpoint that fetches the PDFs server-side
       * 
       * Public CORS proxies like cors-anywhere may require temporary access 
       * or have usage limitations.
       * --------------------------------------------------
       */
      
    } catch (error) {
      console.error("Error generating PDF:", error);
      setPdfError(error.message || "Failed to generate PDF. Please try again.");
      setIsGeneratingPdf(false);

      // Fallback to original PDF generation if our method fails
      if (handleDownloadPDF) {
        console.log("Falling back to original PDF generation method");
        try {
          handleDownloadPDF();
        } catch (fallbackError) {
          console.error("Fallback PDF generation also failed:", fallbackError);
        }
      }
    }
  };

  // Helper function to extract data safely from bookings
  const getDataSafely = () => {
    // Use local state first, then props, with safe fallbacks
    const currentData =
      localModifiedPriceData || localBookings || bookings || {};

    // Check if we need to access the 'data' property
    if (
      currentData &&
      currentData.data &&
      Object.keys(currentData.data).length > 0
    ) {
      return currentData.data;
    }

    return currentData;
  };

  // Extract the data we'll be displaying
  const displayData = getDataSafely();


  return (
    <Modal
      title={
        <Box sx={{ textAlign: "center", py: 1 }}>
          <Typography
            variant="h4"
            sx={{
              fontWeight: "bold",
              background: "linear-gradient(90deg, #1976d2 0%, #2196F3 100%)",
              WebkitBackgroundClip: "text",
              WebkitTextFillColor: "transparent",
              textShadow: "0px 2px 4px rgba(0, 0, 0, 0.1)",
            }}
          >
            Tour Details Preview
          </Typography>
        </Box>
      }
      open={isPrintModalVisible}
      onCancel={handleCancelPrint}
      footer={[
        <Button
          key="cancel"
          onClick={handleCancelPrint}
          variant="outlined"
          disabled={isGeneratingPdf}
          sx={{
            margin: "0 10px",
            color: "#f44336",
            borderColor: "#f44336",
            borderRadius: 28,
            px: 3,
            "&:hover": {
              backgroundColor: "rgba(244, 67, 54, 0.08)",
              borderColor: "#d32f2f",
            },
          }}
          startIcon={<CancelOutlinedIcon />}
        >
          Cancel
        </Button>,
        isGeneratingPdf ? (
          <Box key="progress" sx={{ display: 'flex', flexDirection: 'column', width: '60%', mx: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
              <Typography variant="body2" color="primary">
                {pdfProgress.status}
              </Typography>
              <Typography variant="body2" color="primary">
                {pdfProgress.progress}%
              </Typography>
            </Box>
            <LinearProgress 
              variant="determinate" 
              value={pdfProgress.progress} 
              sx={{ height: 10, borderRadius: 5 }}
            />
          </Box>
        ) : (
          <Button
            key="download"
            onClick={handleMergedPdfDownload}
            variant="contained"
            sx={{
              margin: "0 10px",
              background: "linear-gradient(90deg, #1976d2 0%, #2196F3 100%)",
              borderRadius: 28,
              px: 3,
              boxShadow: "0 4px 10px rgba(33, 150, 243, 0.3)",
              "&:hover": {
                background: "linear-gradient(90deg, #0d6efd 0%, #1565c0 100%)",
                boxShadow: "0 6px 12px rgba(33, 150, 243, 0.4)",
              },
            }}
            startIcon={<SummarizeIcon />}
          >
            Download PDF
          </Button>
        ),
        pdfError && (
          <Typography key="error" variant="body2" color="error" sx={{ ml: 2 }}>
            {pdfError}
          </Typography>
        ),
      ]}
      width="90%"
      style={{ top: 20 }}
    >
      <div ref={internalContentRef} style={{ padding: "20px" }}>
        <Container maxWidth="lg">
          <Paper
            elevation={0}
            sx={{
              p: 4,
              mb: 3,
              borderRadius: 3,
              background: "linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%)",
              border: "1px solid rgba(25, 118, 210, 0.1)",
            }}
          >
            <Box sx={{ textAlign: "center", mb: 4 }}>
              {DmcLogo && (
                <Box sx={{ mb: 2, display: "flex", justifyContent: "center" }}>
                  <Avatar
                    src={DmcLogo}
                    alt={DmcName || "DMC Logo"}
                    sx={{ width: 80, height: 80, border: "2px solid #1976d2" }}
                  />
                </Box>
              )}
              <Typography
                variant="h3"
                sx={{
                  fontWeight: "bold",
                  mb: 1,
                  color: "#1976d2",
                }}
              >
                Tour Details
              </Typography>
              <Typography variant="h6" sx={{ color: "#666", fontWeight: 300 }}>
                Your Complete Travel Itinerary
              </Typography>
              <GradientDivider />
            </Box>

            <StyledCard>
              <StyledCardHeader>
                <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                  Booking Information
                </Typography>
              </StyledCardHeader>
              <CardContent sx={{ p: 3 }}>
                <Grid container spacing={3}>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <BadgeIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>Booking ID:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {localDisplayId ||
                        displayId ||
                        displayData?.display_id ||
                        "TOUR-12345"}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <AccountCircleIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>DMC Name:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {DmcName || "Sample DMC"}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <CalendarTodayIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>Date:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {formatDate(new Date())}
                    </Typography>
                  </Grid>
                </Grid>
              </CardContent>
            </StyledCard>

            <StyledCard>
              <StyledCardHeader>
                <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                  Tour Details
                </Typography>
              </StyledCardHeader>
              <CardContent sx={{ p: 3 }}>
                <Grid container spacing={3}>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <LocationOnIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>Destination:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {displayData?.tour?.destination || "Singapore"}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <AccessTimeIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>Duration:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {displayData?.tour?.check_in &&
                      displayData?.tour?.check_out ? (
                        (() => {
                          try {
                            const parseDate = (str) => {
                              const [day, month, year] = str
                                .split("/")
                                .map(Number);
                              return new Date(year, month - 1, day); // Month is 0-based in JS
                            };

                            const checkIn = parseDate(
                              displayData.tour.check_in
                            );
                            const checkOut = parseDate(
                              displayData.tour.check_out
                            );

                            if (isNaN(checkIn) || isNaN(checkOut)) {
                              return (
                                <Typography variant="body2">
                                  Invalid Dates
                                </Typography>
                              );
                            }

                            const timeDiff = checkOut - checkIn;
                            const nights = Math.floor(
                              timeDiff / (1000 * 60 * 60 * 24)
                            );
                            const totalDays = nights + 1;

                            return (
                              <Typography variant="body2">
                                {nights} Night{nights !== 1 ? "s" : ""} /{" "}
                                {totalDays} Day{totalDays !== 1 ? "s" : ""}
                              </Typography>
                            );
                          } catch (error) {
                            console.error("Error calculating duration:", error);
                            return <Typography variant="body2">N/A</Typography>;
                          }
                        })()
                      ) : (
                        <Typography variant="body2">N/A</Typography>
                      )}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={4}>
                    <IconWrapper>
                      <PeopleIcon fontSize="small" />
                      <Typography variant="subtitle1">
                        <strong>Travelers:</strong>
                      </Typography>
                    </IconWrapper>
                    <Typography variant="h6" sx={{ ml: 3, color: "#1976d2" }}>
                      {displayData?.tour?.total_pax || "0"} Travelers
                    </Typography>
                    {/* Display travelers details with icons */}
                    <Box
                      sx={{
                        ml: 3,
                        mt: 1,
                        display: "flex",
                        flexWrap: "wrap",
                        gap: 2,
                      }}
                    >
                      {displayData?.tour?.male > 0 && (
                        <Chip
                          icon={<ManIcon style={{ color: "#1976d2" }} />}
                          label={`${displayData?.tour?.male} Male${
                            displayData?.tour?.male > 1 ? "s" : ""
                          }`}
                          variant="outlined"
                          sx={{
                            borderColor: "#1976d2",
                            color: "#1976d2",
                            "& .MuiChip-icon": { color: "#1976d2" },
                            fontWeight: "medium",
                          }}
                        />
                      )}
                      {displayData?.tour?.female > 0 && (
                        <Chip
                          icon={<WomanIcon style={{ color: "#e91e63" }} />}
                          label={`${displayData?.tour?.female} Female${
                            displayData?.tour?.female > 1 ? "s" : ""
                          }`}
                          variant="outlined"
                          sx={{
                            borderColor: "#e91e63",
                            color: "#e91e63",
                            "& .MuiChip-icon": { color: "#e91e63" },
                            fontWeight: "medium",
                          }}
                        />
                      )}
                      {displayData?.tour?.child > 0 && (
                        <Chip
                          icon={<ChildCareIcon style={{ color: "#ff9800" }} />}
                          label={`${displayData?.tour?.child} Child${
                            displayData?.tour?.child > 1 ? "ren" : ""
                          }`}
                          variant="outlined"
                          sx={{
                            borderColor: "#ff9800",
                            color: "#ff9800",
                            "& .MuiChip-icon": { color: "#ff9800" },
                            fontWeight: "medium",
                          }}
                        />
                      )}
                      {displayData?.tour?.infant > 0 && (
                        <Chip
                          icon={
                            <BabyChangingStationIcon
                              style={{ color: "#4caf50" }}
                            />
                          }
                          label={`${displayData?.tour?.infant} Infant${
                            displayData?.tour?.infant > 1 ? "s" : ""
                          }`}
                          variant="outlined"
                          sx={{
                            borderColor: "#4caf50",
                            color: "#4caf50",
                            "& .MuiChip-icon": { color: "#4caf50" },
                            fontWeight: "medium",
                          }}
                        />
                      )}
                    </Box>
                  </Grid>
                </Grid>
              </CardContent>
            </StyledCard>

            <StyledCard>
              <StyledCardHeader>
                <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                  Service Details
                </Typography>
              </StyledCardHeader>
              <CardContent sx={{ p: 3 }}>
                {/* 1. Hotel Section - Only show if hotels exist */}
                {displayData?.hotel && displayData.hotel.length > 0 && (
                  <Box sx={{ mb: 4 }}>
                    <Typography
                      variant="h6"
                      sx={{
                        mb: 2,
                        display: "flex",
                        alignItems: "center",
                        color: "#1976d2",
                        fontWeight: "bold",
                      }}
                    >
                      <HotelIcon sx={{ mr: 1 }} /> Hotel Accommodations
                    </Typography>
                    {displayData.hotel.map((hotel, index) => (
                      <Box
                        key={index}
                        sx={{
                          mb: 3,
                          border: "1px solid rgba(25, 118, 210, 0.2)",
                          borderRadius: 2,
                          overflow: "hidden",
                          boxShadow: "0 3px 10px rgba(0,0,0,0.1)",
                          transition:
                            "transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out",
                          "&:hover": {
                            transform: "translateY(-3px)",
                            boxShadow: "0 5px 15px rgba(0,0,0,0.15)",
                          },
                        }}
                      >
                        {/* Hotel Header */}
                        <Box
                          sx={{
                            p: 2.5,
                            background:
                              "linear-gradient(135deg, rgba(25,118,210,0.15) 0%, rgba(255,255,255,0) 100%)",
                            borderBottom: "1px solid rgba(25, 118, 210, 0.15)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "space-between",
                          }}
                        >
                          <Box sx={{ display: "flex", alignItems: "center" }}>
                            <Avatar
                              sx={{
                                width: 70,
                                height: 70,
                                bgcolor: "#1976d2",
                                border: "3px solid #fff",
                                boxShadow: "0 3px 8px rgba(0,0,0,0.15)",
                                mr: 2.5,
                              }}
                            >
                              <HotelIcon sx={{ fontSize: 35 }} />
                            </Avatar>
                            <Box>
                              <Typography
                                variant="h6"
                                sx={{
                                  fontWeight: "bold",
                                  color: "#1976d2",
                                  fontSize: "1.25rem",
                                  letterSpacing: "0.5px",
                                }}
                              >
                                {hotel?.hotelDetails?.hotel_name ||
                                  "Luxury Hotel"}
                              </Typography>
                            </Box>
                          </Box>
                          <Box
                            sx={{
                              display: "flex",
                              flexDirection: "column",
                              alignItems: "flex-end",
                            }}
                          >
                            <Chip
                              icon={<StarsIcon />}
                              label={
                                hotel?.hotelDetails?.star_rating
                                  ? `${hotel.hotelDetails.star_rating}-Star`
                                  : "Luxury"
                              }
                              color="primary"
                              variant="outlined"
                              sx={{ fontWeight: "medium", mb: 1 }}
                            />
                          </Box>
                        </Box>

                        {/* Hotel Content */}
                        <Box sx={{ p: 3 }}>
                          {/* Stay Duration and Dates */}
                          <Paper
                            elevation={0}
                            sx={{
                              p: 2,
                              mb: 3,
                              borderRadius: 2,
                              bgcolor: "rgba(25, 118, 210, 0.04)",
                              border: "1px solid rgba(25, 118, 210, 0.15)",
                            }}
                          >
                            <Grid container spacing={3}>
                              <Grid item xs={12} sm={6} md={3}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    flexDirection: "column",
                                    alignItems: "center",
                                    textAlign: "center",
                                    p: 1.5,
                                    borderRadius: 2,
                                    bgcolor: "white",
                                    boxShadow: "0 2px 5px rgba(0,0,0,0.08)",
                                  }}
                                >
                                  <CalendarTodayIcon
                                    sx={{
                                      color: "#1976d2",
                                      fontSize: 28,
                                      mb: 1,
                                    }}
                                  />
                                  <Typography
                                    variant="subtitle2"
                                    color="text.secondary"
                                    gutterBottom
                                  >
                                    Check-in
                                  </Typography>
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "medium",
                                      color: "#1976d2",
                                    }}
                                  >
                                    {formatDate(hotel?.bookingDate?.[0])}
                                  </Typography>
                                </Box>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    flexDirection: "column",
                                    alignItems: "center",
                                    textAlign: "center",
                                    p: 1.5,
                                    borderRadius: 2,
                                    bgcolor: "white",
                                    boxShadow: "0 2px 5px rgba(0,0,0,0.08)",
                                  }}
                                >
                                  <CalendarTodayIcon
                                    sx={{
                                      color: "#f44336",
                                      fontSize: 28,
                                      mb: 1,
                                    }}
                                  />
                                  <Typography
                                    variant="subtitle2"
                                    color="text.secondary"
                                    gutterBottom
                                  >
                                    Check-out
                                  </Typography>
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "medium",
                                      color: "#f44336",
                                    }}
                                  >
                                    {formatDate(hotel?.bookingDate?.[1])}
                                  </Typography>
                                </Box>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    flexDirection: "column",
                                    alignItems: "center",
                                    textAlign: "center",
                                    p: 1.5,
                                    borderRadius: 2,
                                    bgcolor: "white",
                                    boxShadow: "0 2px 5px rgba(0,0,0,0.08)",
                                  }}
                                >
                                  <AccessTimeIcon
                                    sx={{
                                      color: "#4caf50",
                                      fontSize: 28,
                                      mb: 1,
                                    }}
                                  />
                                  <Typography
                                    variant="subtitle2"
                                    color="text.secondary"
                                    gutterBottom
                                  >
                                    Duration
                                  </Typography>
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "medium",
                                      color: "#4caf50",
                                    }}
                                  >
                                    {hotel?.bookingDate?.[0] &&
                                    hotel?.bookingDate?.[1]
                                      ? (() => {
                                          const checkIn = new Date(
                                            hotel.bookingDate[0]
                                          );
                                          const checkOut = new Date(
                                            hotel.bookingDate[1]
                                          );
                                          const timeDiff = checkOut - checkIn;
                                          const nights = Math.floor(
                                            timeDiff / (1000 * 60 * 60 * 24)
                                          );
                                          return `${nights} Night${
                                            nights !== 1 ? "s" : ""
                                          }`;
                                        })()
                                      : "N/A"}
                                  </Typography>
                                </Box>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                    {localPricehide === "0" && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    flexDirection: "column",
                                    alignItems: "center",
                                    textAlign: "center",
                                    p: 1.5,
                                    borderRadius: 2,
                                    bgcolor: "white",
                                    boxShadow: "0 2px 5px rgba(0,0,0,0.08)",
                                  }}
                                >
                                  <AttachMoneyIcon
                                    sx={{
                                      color: "#ff9800",
                                      fontSize: 28,
                                      mb: 1,
                                    }}
                                  />
                                  <Typography
                                    variant="subtitle2"
                                    color="text.secondary"
                                    gutterBottom
                                  >
                                    Total Price
                                  </Typography>
                              
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "bold",
                                      color: "#ff9800",
                                    }}
                                  >
                                    SGD {hotel.totalPrice || "N/A"}
                                  </Typography>
                                
                                </Box>
                                  )}

                              </Grid>
                            </Grid>
                          </Paper>

                          {/* Rooms */}
                          {hotel?.rooms && hotel.rooms.length > 0 && (
                            <Box sx={{ mt: 3 }}>
                              <Typography
                                variant="subtitle1"
                                sx={{
                                  fontWeight: "bold",
                                  mb: 2,
                                  pl: 1.5,
                                  py: 0.5,
                                  borderLeft: "4px solid #1976d2",
                                  borderRadius: "0 4px 4px 0",
                                  bgcolor: "rgba(25, 118, 210, 0.08)",
                                  display: "flex",
                                  alignItems: "center",
                                }}
                              >
                                <SingleBedIcon
                                  sx={{ mr: 1, color: "#1976d2" }}
                                />
                                Room Details ({hotel.rooms.length}{" "}
                                {hotel.rooms.length === 1 ? "Room" : "Rooms"})
                              </Typography>

                              {hotel.rooms.map((room, roomIndex) => (
                                <Paper
                                  key={roomIndex}
                                  data-content="hotel-room"
                                  elevation={0}
                                  sx={{
                                    mb: 2.5,
                                    overflow: "hidden",
                                    borderRadius: 2,
                                    border: "1px solid rgba(25, 118, 210, 0.2)",
                                    boxShadow: "0 2px 8px rgba(0,0,0,0.06)",
                                    transition: "box-shadow 0.3s ease",
                                    "&:hover": {
                                      boxShadow: "0 4px 12px rgba(0,0,0,0.12)",
                                    },
                                  }}
                                >
                                  {/* Room Header */}
                                  <Box
                                    sx={{
                                      p: 2,
                                      bgcolor: "rgba(25, 118, 210, 0.06)",
                                      borderBottom:
                                        "1px solid rgba(25, 118, 210, 0.1)",
                                      display: "flex",
                                      alignItems: "center",
                                      justifyContent: "space-between",
                                    }}
                                  >
                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <HotelIcon
                                        fontSize="small"
                                        sx={{ color: "#1976d2", mr: 1 }}
                                      />
                                      <Typography
                                        variant="subtitle1"
                                        sx={{
                                          fontWeight: "bold",
                                          color: "#1976d2",
                                        }}
                                      >
                                        {room.room_type || "Standard"} Room
                                      </Typography>
                                    </Box>
                                    <Chip
                                      label={`Room ${roomIndex + 1}`}
                                      size="small"
                                      color="primary"
                                      variant="outlined"
                                    />
                                  </Box>

                                  {/* Room Content */}
                                  <Box sx={{ p: 2.5 }}>
                                    {/* Beds Section */}
                                    {room.beds && room.beds.length > 0 && (
                                      <Box>
                                        <Typography
                                          variant="body2"
                                          sx={{
                                            fontWeight: "medium",
                                            color: "#555",
                                            mb: 1.5,
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <BedIcon
                                            fontSize="small"
                                            sx={{
                                              color: "#1976d2",
                                              mr: 0.8,
                                            }}
                                          />
                                          Bed Configuration ({room.beds.length}{" "}
                                          {room.beds.length === 1
                                            ? "Bed"
                                            : "Beds"}
                                          )
                                        </Typography>

                                        <Grid container spacing={2}>
                                          {room.beds.map((bed, bedIndex) => (
                                            <Grid
                                              item
                                              xs={12}
                                              md={6}
                                              key={bedIndex}
                                            >
                                              <Paper
                                                elevation={0}
                                                variant="outlined"
                                                sx={{
                                                  p: 2,
                                                  borderColor: bed.bed_type
                                                    ?.toLowerCase()
                                                    .includes("king")
                                                    ? "rgba(156, 39, 176, 0.3)"
                                                    : "rgba(33, 150, 243, 0.3)",
                                                  bgcolor: bed.bed_type
                                                    ?.toLowerCase()
                                                    .includes("king")
                                                    ? "rgba(156, 39, 176, 0.03)"
                                                    : "rgba(33, 150, 243, 0.03)",
                                                  borderRadius: 2,
                                                  transition:
                                                    "transform 0.2s ease",
                                                  "&:hover": {
                                                    transform:
                                                      "translateY(-2px)",
                                                  },
                                                }}
                                              >
                                                {/* Bed Type */}
                                                <Box
                                                  sx={{
                                                    display: "flex",
                                                    alignItems: "center",
                                                    mb: 1.5,
                                                    pb: 1.5,
                                                    borderBottom:
                                                      "1px dashed rgba(0, 0, 0, 0.1)",
                                                  }}
                                                >
                                                  {bed.bed_type
                                                    ?.toLowerCase()
                                                    .includes("king") ? (
                                                    <KingBedIcon
                                                      sx={{
                                                        color: "#9c27b0",
                                                        mr: 1.5,
                                                        fontSize: "1.8rem",
                                                      }}
                                                    />
                                                  ) : (
                                                    <SingleBedIcon
                                                      sx={{
                                                        color: "#2196f3",
                                                        mr: 1.5,
                                                        fontSize: "1.8rem",
                                                      }}
                                                    />
                                                  )}
                                                  <Box>
                                                    <Typography
                                                      variant="subtitle2"
                                                      color="text.secondary"
                                                      sx={{
                                                        fontSize: "0.75rem",
                                                      }}
                                                    >
                                                      Bed Type
                                                    </Typography>
                                                    <Typography
                                                      variant="body1"
                                                      sx={{
                                                        fontWeight: "medium",
                                                        color: bed.bed_type
                                                          ?.toLowerCase()
                                                          .includes("king")
                                                          ? "#9c27b0"
                                                          : "#2196f3",
                                                      }}
                                                    >
                                                      {bed.bed_type ||
                                                        "Standard Bed"}
                                                    </Typography>
                                                  </Box>
                                                </Box>

                                                {/* Occupancy Details */}
                                                <Grid container spacing={2}>
                                                  <Grid item xs={6}>
                                                    <Box
                                                      sx={{
                                                        textAlign: "center",
                                                        p: 1,
                                                      }}
                                                    >
                                                      <PeopleIcon
                                                        sx={{
                                                          color: "#555",
                                                          mb: 0.5,
                                                        }}
                                                      />
                                                      <Typography
                                                        variant="caption"
                                                        display="block"
                                                        color="text.secondary"
                                                      >
                                                        Current Occupancy
                                                      </Typography>
                                                      <Typography
                                                        variant="body2"
                                                        sx={{
                                                          fontWeight: "bold",
                                                        }}
                                                      >
                                                        {bed.head_count || 0}{" "}
                                                        person(s)
                                                      </Typography>
                                                    </Box>
                                                  </Grid>
                                                  <Grid item xs={6}>
                                                    <Box
                                                      sx={{
                                                        textAlign: "center",
                                                        p: 1,
                                                      }}
                                                    >
                                                      <GroupIcon
                                                        sx={{
                                                          color: "#555",
                                                          mb: 0.5,
                                                        }}
                                                      />
                                                      <Typography
                                                        variant="caption"
                                                        display="block"
                                                        color="text.secondary"
                                                      >
                                                        Maximum Capacity
                                                      </Typography>
                                                      <Typography
                                                        variant="body2"
                                                        sx={{
                                                          fontWeight: "bold",
                                                        }}
                                                      >
                                                        {bed.max_occupancy || 0}{" "}
                                                        person(s)
                                                      </Typography>
                                                    </Box>
                                                  </Grid>
                                                </Grid>

                                                {/* Baby Cot */}
                                                {bed.baby_cot > 0 && (
                                                  <Box
                                                    sx={{
                                                      display: "flex",
                                                      alignItems: "center",
                                                      justifyContent:
                                                        "space-between",
                                                      mt: 1.5,
                                                      pt: 1.5,
                                                      borderTop:
                                                        "1px dashed rgba(0, 0, 0, 0.1)",
                                                    }}
                                                  >
                                                    <Box
                                                      sx={{
                                                        display: "flex",
                                                        alignItems: "center",
                                                      }}
                                                    >
                                                      <BabyChangingStationIcon
                                                        sx={{
                                                          color: "#e91e63",
                                                          mr: 1,
                                                        }}
                                                      />
                                                      <Typography variant="body2">
                                                        Baby Cot
                                                      </Typography>
                                                    </Box>
                                                    <Chip
                                                      icon={<CheckIcon />}
                                                      label={`${bed.baby_cot} Available`}
                                                      size="small"
                                                      color="secondary"
                                                      sx={{
                                                        fontWeight: "medium",
                                                      }}
                                                    />
                                                  </Box>
                                                )}

                                                {/* Meal Plans */}
                                                {bed.mealTypes &&
                                                  bed.mealTypes.length > 0 && (
                                                    <Box
                                                      sx={{
                                                        mt: 2,
                                                        pt: 2,
                                                        borderTop:
                                                          "1px dashed rgba(0, 0, 0, 0.1)",
                                                      }}
                                                    >
                                                      <Typography
                                                        variant="body2"
                                                        sx={{
                                                          display: "flex",
                                                          alignItems: "center",
                                                          fontWeight: "medium",
                                                          color: "#555",
                                                          mb: 1,
                                                        }}
                                                      >
                                                        <RoomServiceIcon
                                                          fontSize="small"
                                                          sx={{
                                                            color: "#ff9800",
                                                            mr: 0.8,
                                                          }}
                                                        />
                                                        Meal Plan Included:
                                                      </Typography>
                                                      <Stack
                                                        direction="row"
                                                        spacing={1}
                                                        flexWrap="wrap"
                                                        useFlexGap
                                                        sx={{ gap: 0.8 }}
                                                      >
                                                        {bed.mealTypes.map(
                                                          (meal, mealIndex) => {
                                                            let icon = (
                                                              <RoomServiceIcon fontSize="small" />
                                                            );
                                                            let color =
                                                              "primary";
                                                            let bgColor =
                                                              "rgba(33, 150, 243, 0.08)";

                                                            if (
                                                              meal
                                                                .toLowerCase()
                                                                .includes(
                                                                  "breakfast"
                                                                )
                                                            ) {
                                                              icon = (
                                                                <FreeBreakfastIcon fontSize="small" />
                                                              );
                                                              color = "success";
                                                              bgColor =
                                                                "rgba(76, 175, 80, 0.08)";
                                                            } else if (
                                                              meal
                                                                .toLowerCase()
                                                                .includes(
                                                                  "lunch"
                                                                )
                                                            ) {
                                                              icon = (
                                                                <LunchDiningIcon fontSize="small" />
                                                              );
                                                              color = "warning";
                                                              bgColor =
                                                                "rgba(255, 152, 0, 0.08)";
                                                            } else if (
                                                              meal
                                                                .toLowerCase()
                                                                .includes(
                                                                  "dinner"
                                                                )
                                                            ) {
                                                              icon = (
                                                                <DinnerDiningIcon fontSize="small" />
                                                              );
                                                              color = "error";
                                                              bgColor =
                                                                "rgba(244, 67, 54, 0.08)";
                                                            } else if (
                                                              meal
                                                                .toLowerCase()
                                                                .includes(
                                                                  "room only"
                                                                )
                                                            ) {
                                                              icon = (
                                                                <HotelIcon fontSize="small" />
                                                              );
                                                              color = "default";
                                                              bgColor =
                                                                "rgba(97, 97, 97, 0.08)";
                                                            }

                                                            return (
                                                              <Chip
                                                                key={mealIndex}
                                                                icon={icon}
                                                                label={meal}
                                                                size="small"
                                                                color={color}
                                                                sx={{
                                                                  mt: 0.5,
                                                                  bgcolor:
                                                                    bgColor,
                                                                  fontWeight:
                                                                    "medium",
                                                                  border:
                                                                    "none",
                                                                }}
                                                              />
                                                            );
                                                          }
                                                        )}
                                                      </Stack>
                                                    </Box>
                                                  )}
                                              </Paper>
                                            </Grid>
                                          ))}
                                        </Grid>
                                      </Box>
                                    )}
                                  </Box>
                                </Paper>
                              ))}
                            </Box>
                          )}

                          {/* Special Requests */}
                          {/* {hotel?.specialRequests && (
                            <Box sx={{ mt: 3 }}>
                              <Typography
                                variant="subtitle1"
                                sx={{
                                  fontWeight: "bold",
                                  mb: 2,
                                  pl: 1.5,
                                  py: 0.5,
                                  borderLeft: "4px solid #f44336",
                                  borderRadius: "0 4px 4px 0",
                                  bgcolor: "rgba(244, 67, 54, 0.08)",
                                  display: "flex",
                                  alignItems: "center"
                                }}
                              >
                                <NoteIcon sx={{ mr: 1, color: "#f44336" }} />
                                Special Requests
                              </Typography>
                              <Paper
                                variant="outlined"
                                sx={{
                                  p: 2.5,
                                  borderColor: "rgba(244, 67, 54, 0.2)",
                                  bgcolor: "rgba(244, 67, 54, 0.02)",
                                  borderRadius: 2,
                                }}
                              >
                                <Box sx={{ display: "flex" }}>
                                  <QuestionAnswerIcon sx={{ color: "#f44336", mr: 1.5, mt: 0.3 }} />
                                  <Typography variant="body2">
                                    {hotel.specialRequests}
                                  </Typography>
                                </Box>
                              </Paper>
                            </Box>
                          )} */}
                        </Box>
                      </Box>
                    ))}
                  </Box>
                )}

                {/* 2. Entry and Exit Port Section - Only show if either entry or exit ports exist */}
                {((displayData?.entry_port &&
                  displayData.entry_port.length > 0) ||
                  (displayData?.exit_port &&
                    displayData.exit_port.length > 0)) && (
                  <Box sx={{ mb: 4 }}>
                    <Typography
                      variant="h6"
                      sx={{
                        mb: 2,
                        display: "flex",
                        alignItems: "center",
                        color: "#1976d2",
                        fontWeight: "bold",
                      }}
                    >
                      <LocalShippingIcon sx={{ mr: 1 }} /> Entry and Exit Port
                    </Typography>

                    {/* Entry Port - Only show if entry ports exist */}
                    {displayData?.entry_port &&
                      displayData.entry_port.length > 0 && (
                        <>
                          <Typography
                            variant="subtitle1"
                            sx={{
                              mb: 1.5,
                              fontWeight: "medium",
                              pl: 1,
                              borderLeft: "3px solid #1976d2",
                            }}
                          >
                            <FlightLandIcon
                              sx={{
                                mr: 1,
                                fontSize: "1.2rem",
                                verticalAlign: "text-bottom",
                              }}
                            />{" "}
                            Entry Port
                          </Typography>
                          {displayData.entry_port.map((port, index) => (
                            <Box
                              key={index}
                              data-section="entry-port"
                              sx={{
                                mb: 3,
                                border: "1px solid rgba(25, 118, 210, 0.2)",
                                borderRadius: 2,
                                overflow: "hidden",
                                boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                              }}
                            >
                              {/* Entry Port Header */}
                              <Box
                                sx={{
                                  p: 2,
                                  background:
                                    "linear-gradient(90deg, rgba(25,118,210,0.1) 0%, rgba(255,255,255,0) 100%)",
                                  borderBottom:
                                    "1px solid rgba(25, 118, 210, 0.1)",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "space-between",
                                }}
                              >
                                <Box
                                  sx={{ display: "flex", alignItems: "center" }}
                                >
                                  <Avatar
                                    sx={{
                                      bgcolor: "#1976d2",
                                      width: 50,
                                      height: 50,
                                      border: "2px solid #fff",
                                      boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                      mr: 2,
                                    }}
                                  >
                                    <LocalShippingIcon fontSize="medium" />
                                  </Avatar>
                                  <Box>
                                    <Typography
                                      variant="h6"
                                      sx={{
                                        fontWeight: "bold",
                                        color: "#1976d2",
                                      }}
                                    >
                                      {port.entrypickup || "Pick-up Point"}
                                    </Typography>
                                  </Box>
                                </Box>
                                <Chip
                                  label={formatDate(port.bookingDate)}
                                  color="primary"
                                  variant="outlined"
                                  icon={<TodayIcon />}
                                  sx={{ fontWeight: "medium" }}
                                />
                              </Box>

                              {/* Entry Port Content */}
                              <Box sx={{ p: 2 }}>
                                <Grid container spacing={2}>
                                  {/* Transfer Details */}
                                  <Grid item xs={12} md={6}>
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        mb: 1.5,
                                        fontWeight: "medium",
                                        color: "#555",
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <InfoIcon
                                        fontSize="small"
                                        sx={{ mr: 0.5 }}
                                      />
                                      Transfer Details
                                    </Typography>

                                    <Paper
                                      variant="outlined"
                                      sx={{
                                        p: 2,
                                        borderColor: "rgba(25, 118, 210, 0.2)",
                                        bgcolor: "white",
                                      }}
                                    >
                                      <Grid container spacing={2}>
                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "flex-start",
                                              mb: 1,
                                            }}
                                          >
                                            <LocationOnIcon
                                              fontSize="small"
                                              sx={{
                                                color: "#1976d2",
                                                mt: 0.5,
                                                mr: 1,
                                              }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>From:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.entrypickup || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "flex-start",
                                              mb: 1,
                                            }}
                                          >
                                            <LocationOnIcon
                                              fontSize="small"
                                              sx={{
                                                color: "#f44336",
                                                mt: 0.5,
                                                mr: 1,
                                              }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>To:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.entrydropoff || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <AccessTimeFilledIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Time:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.entrytime || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <PeopleIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Passengers:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.adults
                                                  ? `${port.adults} Adult${
                                                      port.adults > 1 ? "s" : ""
                                                    }`
                                                  : "0 Adults"}
                                                {port.children &&
                                                port.children !== "0"
                                                  ? `, ${port.children} Child${
                                                      port.children > 1
                                                        ? "ren"
                                                        : ""
                                                    }`
                                                  : ""}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>
                                      </Grid>
                                    </Paper>
                                  </Grid>

                                  {/* Vehicle Details */}
                                  <Grid item xs={12} md={6}>
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        mb: 1.5,
                                        fontWeight: "medium",
                                        color: "#555",
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <DirectionsCarIcon
                                        fontSize="small"
                                        sx={{ mr: 0.5 }}
                                      />
                                      Vehicle Details
                                    </Typography>

                                    <Paper
                                      variant="outlined"
                                      sx={{
                                        p: 2,
                                        borderColor: "rgba(25, 118, 210, 0.2)",
                                        bgcolor: "white",
                                      }}
                                    >
                                      <Grid container spacing={2}>
                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                              mb: 1,
                                            }}
                                          >
                                            <DriveEtaIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Vehicle:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.vehicles_name || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        {port.service_details && (
                                          <>
                                            <Grid item xs={12} sm={6}>
                                              <Box
                                                sx={{
                                                  display: "flex",
                                                  alignItems: "center",
                                                }}
                                              >
                                                <AirlineSeatReclineNormalIcon
                                                  fontSize="small"
                                                  sx={{
                                                    color: "#1976d2",
                                                    mr: 1,
                                                  }}
                                                />
                                                <Box>
                                                  <Typography
                                                    variant="body2"
                                                    color="text.secondary"
                                                  >
                                                    <strong>
                                                      Vehicle Type:
                                                    </strong>
                                                  </Typography>
                                                  <Typography variant="body1">
                                                    {port.service_details
                                                      .vehicle_type || "N/A"}
                                                  </Typography>
                                                </Box>
                                              </Box>
                                            </Grid>

                                            <Grid item xs={12} sm={6}>
                                              <Box
                                                sx={{
                                                  display: "flex",
                                                  alignItems: "center",
                                                }}
                                              >
                                                <StarIcon
                                                  fontSize="small"
                                                  sx={{
                                                    color: "#1976d2",
                                                    mr: 1,
                                                  }}
                                                />
                                                <Box>
                                                  <Typography
                                                    variant="body2"
                                                    color="text.secondary"
                                                  >
                                                    <strong>Model:</strong>
                                                  </Typography>
                                                  <Typography variant="body1">
                                                    {port.service_details
                                                      .vehicle_model || "N/A"}
                                                  </Typography>
                                                </Box>
                                              </Box>
                                            </Grid>
                                          </>
                                        )}

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <ShareIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Transfer Type:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.type || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                        {localPricehide === "0" && (

                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <AttachMoneyIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Price:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                SGD {port.totalPrice || "N/A"}
                                              </Typography>
                                            
                                            </Box>

                                          </Box>
                                          )}

                                        </Grid>
                                      </Grid>
                                    </Paper>
                                  </Grid>

                                  {/* Special Requests */}
                                  {/* {port.specialRequests && (
                                    <Grid item xs={12}>
                                      <Typography
                                        variant="subtitle2"
                                        sx={{
                                          mb: 1,
                                          fontWeight: "medium",
                                          color: "#555",
                                          display: "flex",
                                          alignItems: "center"
                                        }}
                                      >
                                        <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                        Special Requests
                                      </Typography>
                                      
                                      <Paper
                                        variant="outlined"
                                        sx={{
                                          p: 2,
                                          borderColor: "rgba(25, 118, 210, 0.2)",
                                          bgcolor: "rgba(25, 118, 210, 0.02)",
                                        }}
                                      >
                                    <Typography variant="body2">
                                          {port.specialRequests}
                                    </Typography>
                                      </Paper>
                                </Grid>
                                  )} */}
                                </Grid>
                              </Box>
                            </Box>
                          ))}
                        </>
                      )}

                    {/* Exit Port - Only show if exit ports exist */}
                    {displayData?.exit_port &&
                      displayData.exit_port.length > 0 && (
                        <>
                          <Typography
                            variant="subtitle1"
                            sx={{
                              mb: 1.5,
                              fontWeight: "medium",
                              pl: 1,
                              borderLeft: "3px solid #f44336",
                            }}
                          >
                            <FlightTakeoffIcon
                              sx={{
                                mr: 1,
                                fontSize: "1.2rem",
                                verticalAlign: "text-bottom",
                                color: "#f44336",
                              }}
                            />{" "}
                            Exit Port
                          </Typography>
                          {displayData.exit_port.map((port, index) => (
                            <Box
                              key={index}
                              data-section="exit-port"
                              sx={{
                                mb: 3,
                                border: "1px solid rgba(244, 67, 54, 0.2)",
                                borderRadius: 2,
                                overflow: "hidden",
                                boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                              }}
                            >
                              {/* Exit Port Header */}
                              <Box
                                sx={{
                                  p: 2,
                                  background:
                                    "linear-gradient(90deg, rgba(244,67,54,0.1) 0%, rgba(255,255,255,0) 100%)",
                                  borderBottom:
                                    "1px solid rgba(244, 67, 54, 0.1)",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "space-between",
                                }}
                              >
                                <Box
                                  sx={{ display: "flex", alignItems: "center" }}
                                >
                                  <Avatar
                                    sx={{
                                      bgcolor: "#f44336",
                                      width: 50,
                                      height: 50,
                                      border: "2px solid #fff",
                                      boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                      mr: 2,
                                    }}
                                  >
                                    <FlightTakeoffIcon fontSize="medium" />
                                  </Avatar>
                                  <Box>
                                    <Typography
                                      variant="h6"
                                      sx={{
                                        fontWeight: "bold",
                                        color: "#f44336",
                                      }}
                                    >
                                      {port.exitpickup ||
                                        port.pickup_location ||
                                        "Pick-up Point"}
                                    </Typography>
                                  </Box>
                                </Box>
                                <Chip
                                  label={formatDate(
                                    port.exitpickupdate ||
                                      port.bookingDate ||
                                      port.date
                                  )}
                                  color="error"
                                  variant="outlined"
                                  icon={<TodayIcon />}
                                  sx={{ fontWeight: "medium" }}
                                />
                              </Box>

                              {/* Exit Port Content */}
                              <Box sx={{ p: 2 }}>
                                <Grid container spacing={2}>
                                  {/* Transfer Details */}
                                  <Grid item xs={12} md={6}>
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        mb: 1.5,
                                        fontWeight: "medium",
                                        color: "#555",
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <InfoIcon
                                        fontSize="small"
                                        sx={{ mr: 0.5 }}
                                      />
                                      Transfer Details
                                    </Typography>

                                    <Paper
                                      variant="outlined"
                                      sx={{
                                        p: 2,
                                        borderColor: "rgba(244, 67, 54, 0.2)",
                                        bgcolor: "white",
                                      }}
                                    >
                                      <Grid container spacing={2}>
                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "flex-start",
                                              mb: 1,
                                            }}
                                          >
                                            <LocationOnIcon
                                              fontSize="small"
                                              sx={{
                                                color: "#f44336",
                                                mt: 0.5,
                                                mr: 1,
                                              }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>From:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.exitpickup ||
                                                  port.pickup_location ||
                                                  "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "flex-start",
                                              mb: 1,
                                            }}
                                          >
                                            <LocationOnIcon
                                              fontSize="small"
                                              sx={{
                                                color: "#f44336",
                                                mt: 0.5,
                                                mr: 1,
                                              }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>To:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.exitdropoff ||
                                                  port.drop_location ||
                                                  "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <AccessTimeFilledIcon
                                              fontSize="small"
                                              sx={{ color: "#f44336", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Time:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.entrytime ||
                                                  port.time ||
                                                  "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <PeopleIcon
                                              fontSize="small"
                                              sx={{ color: "#f44336", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Passengers:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.adults
                                                  ? `${port.adults} Adult${
                                                      port.adults > 1 ? "s" : ""
                                                    }`
                                                  : "0 Adults"}
                                                {port.children &&
                                                port.children !== "0"
                                                  ? `, ${port.children} Child${
                                                      port.children > 1
                                                        ? "ren"
                                                        : ""
                                                    }`
                                                  : ""}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>
                                      </Grid>
                                    </Paper>
                                  </Grid>

                                  {/* Vehicle Details */}
                                  <Grid item xs={12} md={6}>
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        mb: 1.5,
                                        fontWeight: "medium",
                                        color: "#555",
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <DirectionsCarIcon
                                        fontSize="small"
                                        sx={{ mr: 0.5 }}
                                      />
                                      Vehicle Details
                                    </Typography>

                                    <Paper
                                      variant="outlined"
                                      sx={{
                                        p: 2,
                                        borderColor: "rgba(244, 67, 54, 0.2)",
                                        bgcolor: "white",
                                      }}
                                    >
                                      <Grid container spacing={2}>
                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                              mb: 1,
                                            }}
                                          >
                                            <DriveEtaIcon
                                              fontSize="small"
                                              sx={{ color: "#f44336", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Vehicle:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.vehicles_name ||
                                                  port.vehicle_type ||
                                                  "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        {port.service_details && (
                                          <>
                                            <Grid item xs={12} sm={6}>
                                              <Box
                                                sx={{
                                                  display: "flex",
                                                  alignItems: "center",
                                                }}
                                              >
                                                <AirlineSeatReclineNormalIcon
                                                  fontSize="small"
                                                  sx={{
                                                    color: "#f44336",
                                                    mr: 1,
                                                  }}
                                                />
                                                <Box>
                                                  <Typography
                                                    variant="body2"
                                                    color="text.secondary"
                                                  >
                                                    <strong>
                                                      Vehicle Type:
                                                    </strong>
                                                  </Typography>
                                                  <Typography variant="body1">
                                                    {port.service_details
                                                      .vehicle_type || "N/A"}
                                                  </Typography>
                                                </Box>
                                              </Box>
                                            </Grid>

                                            <Grid item xs={12} sm={6}>
                                              <Box
                                                sx={{
                                                  display: "flex",
                                                  alignItems: "center",
                                                }}
                                              >
                                                <StarIcon
                                                  fontSize="small"
                                                  sx={{
                                                    color: "#f44336",
                                                    mr: 1,
                                                  }}
                                                />
                                                <Box>
                                                  <Typography
                                                    variant="body2"
                                                    color="text.secondary"
                                                  >
                                                    <strong>Model:</strong>
                                                  </Typography>
                                                  <Typography variant="body1">
                                                    {port.service_details
                                                      .vehicle_model || "N/A"}
                                                  </Typography>
                                                </Box>
                                              </Box>
                                            </Grid>
                                          </>
                                        )}

                                        <Grid item xs={12} sm={6}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <ShareIcon
                                              fontSize="small"
                                              sx={{ color: "#f44336", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Transfer Type:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {port.type || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>

                                        <Grid item xs={12} sm={6}>
                                        {localPricehide === "0" && (

                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <AttachMoneyIcon
                                              fontSize="small"
                                              sx={{ color: "#f44336", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Price:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                SGD {port.totalPrice || "N/A"}
                                              </Typography>
                                            </Box>
                                          </Box>
                                          )}

                                        </Grid>
                                      </Grid>
                                    </Paper>
                                  </Grid>

                                  {/* Special Requests */}
                                  {/* {port.specialRequests && (
                                    <Grid item xs={12}>
                                      <Typography
                                        variant="subtitle2"
                                        sx={{
                                          mb: 1,
                                          fontWeight: "medium",
                                          color: "#555",
                                          display: "flex",
                                          alignItems: "center"
                                        }}
                                      >
                                        <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                        Special Requests
                                      </Typography>
                                      
                                      <Paper
                                        variant="outlined"
                                        sx={{
                                          p: 2,
                                          borderColor: "rgba(244, 67, 54, 0.2)",
                                          bgcolor: "rgba(244, 67, 54, 0.02)",
                                        }}
                                      >
                                    <Typography variant="body2">
                                          {port.specialRequests}
                                    </Typography>
                                      </Paper>
                                </Grid>
                                  )} */}
                                </Grid>
                              </Box>
                            </Box>
                          ))}
                        </>
                      )}
                  </Box>
                )}

                {/* 3. Attractions Section - Only show if attractions exist */}
                {displayData?.attraction &&
                  displayData.attraction.length > 0 && (
                    <Box sx={{ mb: 4 }}>
                      <Typography
                        variant="h6"
                        sx={{
                          mb: 2,
                          display: "flex",
                          alignItems: "center",
                          color: "#1976d2",
                          fontWeight: "bold",
                        }}
                      >
                        <AttractionsIcon sx={{ mr: 1 }} /> Attraction &
                        Experiences
                      </Typography>
                      {displayData.attraction.map((attraction, index) => (
                        <Box
                          key={index}
                          sx={{
                            mb: 3,
                            border: "1px solid rgba(25, 118, 210, 0.2)",
                            borderRadius: 2,
                            overflow: "hidden",
                            boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                          }}
                        >
                          {/* Attraction Header */}
                          <Box
                            sx={{
                              p: 2,
                              background:
                                "linear-gradient(90deg, rgba(25,118,210,0.1) 0%, rgba(255,255,255,0) 100%)",
                              borderBottom: "1px solid rgba(25, 118, 210, 0.1)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                            }}
                          >
                            <Box sx={{ display: "flex", alignItems: "center" }}>
                              <Avatar
                                sx={{
                                  bgcolor: "#1976d2",
                                  width: 60,
                                  height: 60,
                                  border: "2px solid #fff",
                                  boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                  mr: 2,
                                }}
                              >
                                <AttractionsIcon />
                              </Avatar>
                              <Box>
                                <Typography
                                  variant="h6"
                                  sx={{ fontWeight: "bold", color: "#1976d2" }}
                                >
                                  {attraction?.AttractionName ||
                                    "Attraction Name"}
                                </Typography>
                              </Box>
                            </Box>
                            <Chip
                              label={formatDate(attraction.bookingDate)}
                              color="primary"
                              variant="outlined"
                              icon={<TodayIcon />}
                              sx={{ fontWeight: "medium" }}
                            />
                          </Box>

                          {/* Master Image - Removed and image shown in avatar instead */}

                          {/* Attraction Content */}
                          <Box sx={{ p: 2 }}>
                            <Grid container spacing={2}>
                              {/* Ticket Details */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <ConfirmationNumberIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Ticket Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          mb: 1,
                                        }}
                                      >
                                        <LocalActivityIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Ticket Name:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {attraction.ticketName ||
                                              "Standard Ticket"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <AccessTimeFilledIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Visit Time:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {attraction.visitTime || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <InfoIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Ticket ID:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {attraction.ticketId || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    {attraction.nri && (
                                      <Grid item xs={12} sm={6}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <HomeWorkIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Residency Status:</strong>
                                            </Typography>
                                            <Typography
                                              variant="body1"
                                              sx={{
                                                textTransform: "capitalize",
                                              }}
                                            >
                                              {attraction.nri || "N/A"}
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>
                                    )}

                                    <Grid item xs={12} sm={6}>
                                    {localPricehide === "0" && (

                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <AttachMoneyIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Total Price:</strong>
                                          </Typography>
                                          <Typography
                                            variant="body1"
                                            sx={{ fontWeight: "medium" }}
                                          >
                                            SGD {attraction.totalPrice || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                          )}

                                    </Grid>
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Visitor Details - Only show if there are visitors */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <GroupIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Visitor Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                    height: "100%",
                                    display: "flex",
                                    flexDirection: "column",
                                    justifyContent: "center",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    {parseInt(attraction.adultCount || 0) >
                                      0 && (
                                      <Grid
                                        item
                                        xs={
                                          attraction.childCount > 0 ||
                                          attraction.seniorCount > 0
                                            ? 4
                                            : 12
                                        }
                                      >
                                        <Paper
                                          elevation={0}
                                          sx={{
                                            p: 1.5,
                                            borderRadius: 2,
                                            border:
                                              "1px solid rgba(25, 118, 210, 0.2)",
                                            display: "flex",
                                            flexDirection: "column",
                                            alignItems: "center",
                                            bgcolor: "rgba(25, 118, 210, 0.05)",
                                          }}
                                        >
                                          <Avatar
                                            sx={{
                                              bgcolor: "#1976d2",
                                              width: 40,
                                              height: 40,
                                              mb: 1,
                                            }}
                                          >
                                            <PersonIcon />
                                          </Avatar>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            Adults
                                          </Typography>
                                          <Typography
                                            variant="h6"
                                            sx={{
                                              fontWeight: "medium",
                                              color: "#1976d2",
                                            }}
                                          >
                                            {attraction.adultCount || 0}
                                          </Typography>
                                        </Paper>
                                      </Grid>
                                    )}

                                    {parseInt(attraction.childCount || 0) >
                                      0 && (
                                      <Grid
                                        item
                                        xs={
                                          attraction.adultCount > 0 &&
                                          attraction.seniorCount > 0
                                            ? 4
                                            : attraction.adultCount > 0 ||
                                              attraction.seniorCount > 0
                                            ? 8
                                            : 12
                                        }
                                      >
                                        <Paper
                                          elevation={0}
                                          sx={{
                                            p: 1.5,
                                            borderRadius: 2,
                                            border:
                                              "1px solid rgba(255, 152, 0, 0.2)",
                                            display: "flex",
                                            flexDirection: "column",
                                            alignItems: "center",
                                            bgcolor: "rgba(255, 152, 0, 0.05)",
                                          }}
                                        >
                                          <Avatar
                                            sx={{
                                              bgcolor: "#ff9800",
                                              width: 40,
                                              height: 40,
                                              mb: 1,
                                            }}
                                          >
                                            <ChildCareIcon />
                                          </Avatar>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            Children
                                          </Typography>
                                          <Typography
                                            variant="h6"
                                            sx={{
                                              fontWeight: "medium",
                                              color: "#ff9800",
                                            }}
                                          >
                                            {attraction.childCount || 0}
                                          </Typography>
                                        </Paper>
                                      </Grid>
                                    )}

                                    {parseInt(attraction.seniorCount || 0) >
                                      0 && (
                                      <Grid
                                        item
                                        xs={
                                          attraction.adultCount > 0 &&
                                          attraction.childCount > 0
                                            ? 4
                                            : attraction.adultCount > 0 ||
                                              attraction.childCount > 0
                                            ? 8
                                            : 12
                                        }
                                      >
                                        <Paper
                                          elevation={0}
                                          sx={{
                                            p: 1.5,
                                            borderRadius: 2,
                                            border:
                                              "1px solid rgba(158, 158, 158, 0.2)",
                                            display: "flex",
                                            flexDirection: "column",
                                            alignItems: "center",
                                            bgcolor:
                                              "rgba(158, 158, 158, 0.05)",
                                          }}
                                        >
                                          <Avatar
                                            sx={{
                                              bgcolor: "#9e9e9e",
                                              width: 40,
                                              height: 40,
                                              mb: 1,
                                            }}
                                          >
                                            <ElderlyIcon />
                                          </Avatar>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            Seniors
                                          </Typography>
                                          <Typography
                                            variant="h6"
                                            sx={{
                                              fontWeight: "medium",
                                              color: "#757575",
                                            }}
                                          >
                                            {attraction.seniorCount || 0}
                                          </Typography>
                                        </Paper>
                                      </Grid>
                                    )}

                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          justifyContent: "center",
                                          p: 1.5,
                                          mt: 1,
                                          borderRadius: 2,
                                          bgcolor: "rgba(25, 118, 210, 0.08)",
                                          border:
                                            "1px dashed rgba(25, 118, 210, 0.3)",
                                        }}
                                      >
                                        <FamilyRestroomIcon
                                          sx={{
                                            color: "#1976d2",
                                            mr: 1,
                                            fontSize: "1.5rem",
                                          }}
                                        />
                                        <Typography
                                          variant="h6"
                                          sx={{
                                            color: "#1976d2",
                                            fontWeight: "medium",
                                          }}
                                        >
                                          {parseInt(
                                            attraction.adultCount || 0
                                          ) +
                                            parseInt(
                                              attraction.childCount || 0
                                            ) +
                                            parseInt(
                                              attraction.seniorCount || 0
                                            )}{" "}
                                          Visitors
                                        </Typography>
                                      </Box>
                                    </Grid>
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Special Requests */}
                              {/* {attraction.specialRequests && (
                                <Grid item xs={12}>
                                  <Typography
                                    variant="subtitle2"
                                    sx={{
                                      mb: 1.5,
                                      fontWeight: "medium",
                                      color: "#555",
                                      display: "flex",
                                      alignItems: "center"
                                    }}
                                  >
                                    <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                    Special Requests
                                  </Typography>
                                  
                                  <Paper
                                    variant="outlined"
                                    sx={{
                                      p: 2,
                                      borderColor: "rgba(25, 118, 210, 0.2)",
                                      bgcolor: "rgba(25, 118, 210, 0.02)",
                                    }}
                                  >
                                <Typography variant="body2">
                                      {attraction.specialRequests}
                                </Typography>
                                  </Paper>
                            </Grid>
                              )} */}
                            </Grid>
                          </Box>
                        </Box>
                      ))}
                    </Box>
                  )}

                {/* 4. Tour Guide Section - Only show if guides exist */}
                {displayData?.guide && displayData.guide.length > 0 && (
                  <Box sx={{ mb: 4 }}>
                    <Typography
                      variant="h6"
                      sx={{
                        mb: 2,
                        display: "flex",
                        alignItems: "center",
                        color: "#1976d2",
                        fontWeight: "bold",
                      }}
                    >
                      <BadgeIcon sx={{ mr: 1 }} /> Tour Guide
                    </Typography>
                    {displayData.guide.map((guide, index) => (
                      <Box
                        key={index}
                        sx={{
                          mb: 3,
                          border: "1px solid rgba(25, 118, 210, 0.2)",
                          borderRadius: 2,
                          overflow: "hidden",
                          boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                        }}
                      >
                        {/* Guide Header */}
                        <Box
                          sx={{
                            p: 2,
                            background:
                              "linear-gradient(90deg, rgba(25,118,210,0.1) 0%, rgba(255,255,255,0) 100%)",
                            borderBottom: "1px solid rgba(25, 118, 210, 0.1)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "space-between",
                          }}
                        >
                          <Box sx={{ display: "flex", alignItems: "center" }}>
                            <Avatar
                              sx={{
                                bgcolor: "#1976d2",
                                width: 60,
                                height: 60,
                                boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                mr: 2,
                              }}
                            >
                              <BadgeIcon fontSize="large" />
                            </Avatar>
                            <Box>
                              <Typography
                                variant="h6"
                                sx={{ fontWeight: "bold", color: "#1976d2" }}
                              >
                                {guide.guide_name || "Tour Guide"}
                              </Typography>
                              {guide.service_details?.languages && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    mt: 0.5,
                                  }}
                                >
                                  <TranslateIcon
                                    fontSize="small"
                                    sx={{ color: "#757575", mr: 0.5 }}
                                  />
                                  <Typography
                                    variant="body2"
                                    color="text.secondary"
                                  >
                                    {guide.service_details.languages ||
                                      "English"}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>
                          <Chip
                            label={formatDate(guide.bookingDate)}
                            color="primary"
                            variant="outlined"
                            icon={<TodayIcon />}
                            sx={{ fontWeight: "medium" }}
                          />
                        </Box>

                        {/* Guide Content */}
                        <Box sx={{ p: 2 }}>
                          <Grid container spacing={2}>
                            {/* Guide Details */}
                            <Grid item xs={12} md={6}>
                              <Typography
                                variant="subtitle2"
                                sx={{
                                  mb: 1.5,
                                  fontWeight: "medium",
                                  color: "#555",
                                  display: "flex",
                                  alignItems: "center",
                                }}
                              >
                                <InfoIcon fontSize="small" sx={{ mr: 0.5 }} />
                                Guide Details
                              </Typography>

                              <Paper
                                variant="outlined"
                                sx={{
                                  p: 2,
                                  borderColor: "rgba(25, 118, 210, 0.2)",
                                  bgcolor: "white",
                                }}
                              >
                                <Grid container spacing={2}>
                                  {guide.service_details?.email && (
                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          mb: 1,
                                        }}
                                      >
                                        <AlternateEmailIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Email:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {guide.service_details.email}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  )}

                                  {guide.service_details?.contact_no && (
                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <PhoneInTalkIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Contact:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {guide.service_details.contact_no}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  )}

                                  {guide.service_details?.experience && (
                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <EmojiEventsIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Experience:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {guide.service_details.experience}{" "}
                                            years
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  )}

                                  {guide.service_details?.languages && (
                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <TranslateIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Languages:</strong>
                                          </Typography>
                                          <Stack
                                            direction="row"
                                            spacing={1}
                                            sx={{ mt: 0.5 }}
                                            flexWrap="wrap"
                                            useFlexGap
                                          >
                                            {guide.service_details.languages
                                              .split(",")
                                              .map((language, i) => (
                                                <Chip
                                                  key={i}
                                                  label={language.trim()}
                                                  size="small"
                                                  color="primary"
                                                  variant="outlined"
                                                  icon={<DoneIcon />}
                                                />
                                              ))}
                                          </Stack>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  )}
                                </Grid>
                              </Paper>
                            </Grid>

                            {/* Service Details */}
                            <Grid item xs={12} md={6}>
                              <Typography
                                variant="subtitle2"
                                sx={{
                                  mb: 1.5,
                                  fontWeight: "medium",
                                  color: "#555",
                                  display: "flex",
                                  alignItems: "center",
                                }}
                              >
                                <AutoStoriesIcon
                                  fontSize="small"
                                  sx={{ mr: 0.5 }}
                                />
                                Service Details
                              </Typography>

                              <Paper
                                variant="outlined"
                                sx={{
                                  p: 2,
                                  borderColor: "rgba(25, 118, 210, 0.2)",
                                  bgcolor: "white",
                                }}
                              >
                                <Grid container spacing={2}>
                                  {/* <Grid item xs={12}>
                                    <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                                      <PlaceIcon fontSize="small" sx={{ color: "#1976d2", mr: 1 }} />
                                      <Box>
                                        <Typography variant="body2" color="text.secondary">
                                          <strong>Location:</strong>
                                        </Typography>
                                        <Typography variant="body1">
                                          {guide.entrypickup || "N/A"}
                                        </Typography>
                                      </Box>
                                    </Box>
                                  </Grid> */}

                                  <Grid item xs={12}>
                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
                                        mb: 1,
                                      }}
                                    >
                                      <WatchLaterIcon
                                        fontSize="small"
                                        sx={{ color: "#1976d2", mr: 1 }}
                                      />
                                      <Box>
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          <strong>Service Hours:</strong>
                                        </Typography>
                                        <Typography variant="body1">
                                          {guide.Night_Start_Time} -{" "}
                                          {guide.Night_End_Time} ({guide.hours}{" "}
                                          hours)
                                        </Typography>
                                      </Box>
                                    </Box>
                                  </Grid>

                                  <Grid item xs={12} sm={6}>
                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <GroupIcon
                                        fontSize="small"
                                        sx={{ color: "#1976d2", mr: 1 }}
                                      />
                                      <Box>
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          <strong>Group Size:</strong>
                                        </Typography>
                                        <Typography variant="body1">
                                          {guide.adults || 0}{" "}
                                          {parseInt(guide.adults) === 1
                                            ? "Person"
                                            : "People"}
                                          {guide.children &&
                                          parseInt(guide.children) > 0
                                            ? `, ${guide.children} Children`
                                            : ""}
                                        </Typography>
                                      </Box>
                                    </Box>
                                  </Grid>

                                  <Grid item xs={12} sm={6}>
                                  {localPricehide === "0" && (

                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <AttachMoneyIcon
                                        fontSize="small"
                                        sx={{ color: "#1976d2", mr: 1 }}
                                      />
                                      <Box>
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          <strong>Price:</strong>
                                        </Typography>
                                        <Typography
                                          variant="body1"
                                          sx={{ fontWeight: "medium" }}
                                        >
                                          SGD {guide.totalPrice || "N/A"}
                                        </Typography>
                                      </Box>
                                    </Box>
                                        )}

                                  </Grid>

                                  {/* {guide.Tax && parseFloat(guide.Tax) > 0 && (
                                    <Grid item xs={12} sm={6}>
                                      <Box sx={{ display: "flex", alignItems: "center" }}>
                                        <PaymentIcon fontSize="small" sx={{ color: "#1976d2", mr: 1 }} />
                                        <Box>
                                          <Typography variant="body2" color="text.secondary">
                                            <strong>Tax:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            ${guide.Tax}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  )} */}
                                </Grid>
                              </Paper>
                            </Grid>

                            {/* Special Requests */}
                            {/* {guide.specialRequests && (
                              <Grid item xs={12}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center"
                                  }}
                                >
                                  <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                  Special Requests
                                </Typography>
                                
                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "rgba(25, 118, 210, 0.02)",
                                  }}
                                >
                              <Typography variant="body2">
                                    {guide.specialRequests}
                              </Typography>
                                </Paper>
                          </Grid>
                            )} */}
                          </Grid>
                        </Box>
                      </Box>
                    ))}
                  </Box>
                )}

                {/* 5. Restaurants Section - Only show if restaurants exist */}
                {displayData?.restaurant &&
                  displayData.restaurant.length > 0 && (
                    <Box sx={{ mb: 4 }}>
                      <Typography
                        variant="h6"
                        sx={{
                          mb: 2,
                          display: "flex",
                          alignItems: "center",
                          color: "#1976d2",
                          fontWeight: "bold",
                        }}
                      >
                        <RestaurantIcon sx={{ mr: 1 }} /> Restaurants
                      </Typography>
                      {displayData.restaurant.map((restaurant, index) => (
                        <Box
                          key={index}
                          data-section="restaurant"
                          sx={{
                            mb: 3,
                            border: "1px solid rgba(25, 118, 210, 0.2)",
                            borderRadius: 2,
                            overflow: "hidden",
                            boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                          }}
                        >
                          {/* Restaurant Header */}
                          <Box
                            sx={{
                              p: 2,
                              background:
                                "linear-gradient(90deg, rgba(25,118,210,0.1) 0%, rgba(255,255,255,0) 100%)",
                              borderBottom: "1px solid rgba(25, 118, 210, 0.1)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                            }}
                          >
                            <Box sx={{ display: "flex", alignItems: "center" }}>
                              <Avatar
                                sx={{
                                  bgcolor: "#1976d2",
                                  width: 60,
                                  height: 60,
                                  boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                  mr: 2,
                                }}
                              >
                                <RestaurantIcon fontSize="large" />
                              </Avatar>
                              <Box>
                                <Typography
                                  variant="h6"
                                  sx={{ fontWeight: "bold", color: "#1976d2" }}
                                >
                                  {restaurant?.restaurantName ||
                                    "Restaurant Name"}
                                </Typography>
                              </Box>
                            </Box>
                            <Chip
                              label={formatDate(restaurant.bookingDate)}
                              color="primary"
                              variant="outlined"
                              icon={<TodayIcon />}
                              sx={{ fontWeight: "medium" }}
                            />
                          </Box>

                          {/* Restaurant Content */}
                          <Box sx={{ p: 2 }}>
                            <Grid container spacing={2}>
                              {/* Booking Details */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <InfoIcon fontSize="small" sx={{ mr: 0.5 }} />
                                  Booking Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          mb: 1,
                                        }}
                                      >
                                        <BrunchDiningIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Meal Type:</strong>
                                          </Typography>
                                          <Typography
                                            variant="body1"
                                            sx={{ textTransform: "capitalize" }}
                                          >
                                            {restaurant?.mealType || "N/A"}
                                            {restaurant?.mealSpecificType
                                              ? ` (${restaurant.mealSpecificType})`
                                              : ""}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <AccessTimeFilledIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Visit Time:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {restaurant.visitTime || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <PeopleIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Guests:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {restaurant.adultCount
                                              ? `${
                                                  restaurant.adultCount
                                                } Adult${
                                                  restaurant.adultCount > 1
                                                    ? "s"
                                                    : ""
                                                }`
                                              : "0 Adults"}
                                            {restaurant.childCount &&
                                            parseInt(restaurant.childCount) > 0
                                              ? `, ${
                                                  restaurant.childCount
                                                } Child${
                                                  restaurant.childCount > 1
                                                    ? "ren"
                                                    : ""
                                                }`
                                              : ""}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                    {localPricehide === "0" && (

                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <AttachMoneyIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Price:</strong>
                                          </Typography>
                                          <Typography
                                            variant="body1"
                                            sx={{ fontWeight: "medium" }}
                                          >
                                            SGD{" "}
                                            {restaurant.totalPrice ||
                                              restaurant.mealPrice ||
                                              "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                          )}

                                    </Grid>
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Restaurant Details */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <RestaurantIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Restaurant Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    {restaurant.service_details?.cuisine && (
                                      <Grid item xs={12}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                            mb: 1,
                                          }}
                                        >
                                          <DinnerDiningIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Cuisine:</strong>
                                            </Typography>
                                            <Typography variant="body1">
                                              {
                                                restaurant.service_details
                                                  .cuisine
                                              }
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>
                                    )}

                                    {restaurant.mealType &&
                                      restaurant.service_details && (
                                        <Grid item xs={12}>
                                          <Box
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                            }}
                                          >
                                            <AccessTimeIcon
                                              fontSize="small"
                                              sx={{ color: "#1976d2", mr: 1 }}
                                            />
                                            <Box>
                                              <Typography
                                                variant="body2"
                                                color="text.secondary"
                                              >
                                                <strong>Service Hours:</strong>
                                              </Typography>
                                              <Typography variant="body1">
                                                {(() => {
                                                  const details =
                                                    restaurant.service_details;
                                                  if (
                                                    restaurant.mealType?.toLowerCase() ===
                                                      "breakfast" &&
                                                    details.opening_time_bf &&
                                                    details.closing_time_bf
                                                  ) {
                                                    return `${details.opening_time_bf.slice(
                                                      0,
                                                      5
                                                    )} - ${details.closing_time_bf.slice(
                                                      0,
                                                      5
                                                    )}`;
                                                  } else if (
                                                    restaurant.mealType?.toLowerCase() ===
                                                      "lunch" &&
                                                    details.opening_time_lunch &&
                                                    details.closing_time_lunch
                                                  ) {
                                                    return `${details.opening_time_lunch.slice(
                                                      0,
                                                      5
                                                    )} - ${details.closing_time_lunch.slice(
                                                      0,
                                                      5
                                                    )}`;
                                                  } else if (
                                                    restaurant.mealType?.toLowerCase() ===
                                                      "dinner" &&
                                                    details.opening_time_dinner &&
                                                    details.closing_time_dinner
                                                  ) {
                                                    return `${details.opening_time_dinner.slice(
                                                      0,
                                                      5
                                                    )} - ${details.closing_time_dinner.slice(
                                                      0,
                                                      5
                                                    )}`;
                                                  } else {
                                                    return "Hours vary";
                                                  }
                                                })()}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </Grid>
                                      )}

                                    {/* Price based on meal type */}
                                    {/* <Grid item xs={12}>
                                      <Box sx={{ display: "flex", alignItems: "center" }}>
                                        <MoneyIcon fontSize="small" sx={{ color: "#1976d2", mr: 1 }} />
                                        <Box>
                                          <Typography variant="body2" color="text.secondary">
                                            <strong>Standard Price:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {(() => {
                                              const details = restaurant.service_details;
                                              if (restaurant.mealType?.toLowerCase() === 'breakfast' && details.bf_price) {
                                                return `$${details.bf_price}`;
                                              } else if (restaurant.mealType?.toLowerCase() === 'lunch' && details.lunch_price) {
                                                return `$${details.lunch_price}`;
                                              } else if (restaurant.mealType?.toLowerCase() === 'dinner' && details.dinner_price) {
                                                return `$${details.dinner_price}`;
                                              } else {
                                                return "Price varies";
                                              }
                                            })()}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid> */}
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Meal Items */}
                              {restaurant.MealDescription &&
                                restaurant.MealDescription.length > 0 && (
                                  <Grid item xs={12} data-content="meal-types">
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        mb: 1.5,
                                        fontWeight: "medium",
                                        color: "#555",
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <LunchDiningIcon
                                        fontSize="small"
                                        sx={{ mr: 0.5 }}
                                      />
                                      Meal Items
                                    </Typography>

                                    <Paper
                                      variant="outlined"
                                      sx={{
                                        p: 2,
                                        borderColor: "rgba(25, 118, 210, 0.2)",
                                        bgcolor: "white",
                                      }}
                                    >
                                      <Grid container spacing={2}>
                                        {restaurant.MealDescription.map(
                                          (meal, mealIndex) => (
                                            <Grid
                                              item
                                              xs={12}
                                              sm={6}
                                              md={4}
                                              key={mealIndex}
                                            >
                                              <Paper
                                                variant="outlined"
                                                sx={{
                                                  p: 1.5,
                                                  borderColor:
                                                    meal.category ===
                                                    "Alcoholic"
                                                      ? "rgba(156, 39, 176, 0.2)"
                                                      : "rgba(76, 175, 80, 0.2)",
                                                  bgcolor:
                                                    meal.category ===
                                                    "Alcoholic"
                                                      ? "rgba(156, 39, 176, 0.05)"
                                                      : "rgba(76, 175, 80, 0.05)",
                                                }}
                                              >
                                                <Box
                                                  sx={{
                                                    display: "flex",
                                                    alignItems: "center",
                                                    mb: 1,
                                                  }}
                                                >
                                                  {meal.category ===
                                                  "Alcoholic" ? (
                                                    <LunchDiningIcon
                                                      sx={{
                                                        color: "#9c27b0",
                                                        mr: 1,
                                                      }}
                                                    />
                                                  ) : (
                                                    <FreeBreakfastIcon
                                                      sx={{
                                                        color: "#4caf50",
                                                        mr: 1,
                                                      }}
                                                    />
                                                  )}
                                                  {/* <Typography variant="body1" sx={{ fontWeight: "medium" }}>
                                                {meal.name || "Meal Item"}
                                              </Typography> */}
                                                </Box>
                                                <Stack
                                                  direction="row"
                                                  spacing={1}
                                                  sx={{ mt: 1 }}
                                                >
                                                  <Chip
                                                    label={
                                                      meal.category || "Regular"
                                                    }
                                                    size="small"
                                                    color={
                                                      meal.category ===
                                                      "Alcoholic"
                                                        ? "secondary"
                                                        : "success"
                                                    }
                                                    variant="outlined"
                                                  />
                                                  <Chip
                                                    label={
                                                      meal.item_type ||
                                                      "Regular"
                                                    }
                                                    size="small"
                                                    color="primary"
                                                    variant="outlined"
                                                  />
                                                </Stack>
                                              </Paper>
                                            </Grid>
                                          )
                                        )}
                                      </Grid>
                                    </Paper>
                                  </Grid>
                                )}

                              {/* Special Requests */}
                              {/* {restaurant.specialRequests && (
                                <Grid item xs={12}>
                                  <Typography
                                    variant="subtitle2"
                                    sx={{
                                      mb: 1.5,
                                      fontWeight: "medium",
                                      color: "#555",
                                      display: "flex",
                                      alignItems: "center"
                                    }}
                                  >
                                    <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                    Special Requests
                                  </Typography>
                                  
                                  <Paper
                                    variant="outlined"
                                    sx={{
                                      p: 2,
                                      borderColor: "rgba(25, 118, 210, 0.2)",
                                      bgcolor: "rgba(25, 118, 210, 0.02)",
                                    }}
                                  >
                                <Typography variant="body2">
                                      {restaurant.specialRequests}
                                </Typography>
                                  </Paper>
                            </Grid>
                              )} */}
                            </Grid>
                          </Box>
                        </Box>
                      ))}
                    </Box>
                  )}

                {/* 6. Local Tour Section - Only show if travel points exist */}
                {displayData?.travel_point &&
                  displayData.travel_point.length > 0 && (
                    <Box sx={{ mb: 4 }}>
                      <Typography
                        variant="h6"
                        sx={{
                          mb: 2,
                          display: "flex",
                          alignItems: "center",
                          color: "#1976d2",
                          fontWeight: "bold",
                        }}
                      >
                        <TourIcon sx={{ mr: 1 }} /> Local Transfer
                      </Typography>
                      {displayData.travel_point.map((transfer, index) => (
                        <Box
                          key={index}
                          sx={{
                            mb: 3,
                            border: "1px solid rgba(25, 118, 210, 0.2)",
                            borderRadius: 2,
                            overflow: "hidden",
                            boxShadow: "0 2px 8px rgba(0,0,0,0.08)",
                          }}
                        >
                          {/* Transfer Header */}
                          <Box
                            sx={{
                              p: 2,
                              background:
                                "linear-gradient(90deg, rgba(25,118,210,0.1) 0%, rgba(255,255,255,0) 100%)",
                              borderBottom: "1px solid rgba(25, 118, 210, 0.1)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                            }}
                          >
                            <Box sx={{ display: "flex", alignItems: "center" }}>
                              <Avatar
                                sx={{
                                  bgcolor: "#1976d2",
                                  width: 60,
                                  height: 60,
                                  boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                  mr: 2,
                                }}
                              >
                                <DirectionsCarIcon fontSize="large" />
                              </Avatar>
                              <Box>
                                <Typography
                                  variant="h6"
                                  sx={{ fontWeight: "bold", color: "#1976d2" }}
                                >
                                  {transfer.vehicles_name || "Vehicle"}
                                </Typography>
                              </Box>
                            </Box>
                            <Chip
                              label={formatDate(
                                transfer.bookingDate || transfer.pickupdate
                              )}
                              color="primary"
                              variant="outlined"
                              icon={<TodayIcon />}
                              sx={{ fontWeight: "medium" }}
                            />
                          </Box>

                          {/* Transfer Content */}
                          <Box sx={{ p: 2 }}>
                            <Grid container spacing={2}>
                              {/* Location Details */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <LocationOnIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Location Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "flex-start",
                                          mb: 1,
                                        }}
                                      >
                                        <LocationOnIcon
                                          fontSize="small"
                                          sx={{
                                            color: "#1976d2",
                                            mt: 0.5,
                                            mr: 1,
                                          }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Pickup Location:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.entrypickup || "N/A"}
                                          </Typography>
                                          {transfer.PickupPlaceid && (
                                            <Typography
                                              variant="caption"
                                              color="text.secondary"
                                            >
                                              Coordinates:{" "}
                                              {transfer.PickupPlaceid.lat},{" "}
                                              {transfer.PickupPlaceid.lng}
                                            </Typography>
                                          )}
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "flex-start",
                                          mb: 1,
                                        }}
                                      >
                                        <LocationOnIcon
                                          fontSize="small"
                                          sx={{
                                            color: "#f44336",
                                            mt: 0.5,
                                            mr: 1,
                                          }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Dropoff Location:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.entrydropoff || "N/A"}
                                          </Typography>
                                          {transfer.DropoffPlaceid && (
                                            <Typography
                                              variant="caption"
                                              color="text.secondary"
                                            >
                                              Coordinates:{" "}
                                              {transfer.DropoffPlaceid.lat},{" "}
                                              {transfer.DropoffPlaceid.lng}
                                            </Typography>
                                          )}
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <AccessTimeFilledIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Pickup Time:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.entrytime || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                        }}
                                      >
                                        <StraightenIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Distance:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.distance
                                              ? `${transfer.distance} km`
                                              : "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Vehicle Details */}
                              <Grid item xs={12} md={6}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <DirectionsCarIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Vehicle Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "white",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          mb: 1,
                                        }}
                                      >
                                        <DriveEtaIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Vehicle Type:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.service_details
                                              ?.vehicle_type || "N/A"}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    <Grid item xs={12} sm={6}>
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          mb: 1,
                                        }}
                                      >
                                        <SpeedIcon
                                          fontSize="small"
                                          sx={{ color: "#1976d2", mr: 1 }}
                                        />
                                        <Box>
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            <strong>Model:</strong>
                                          </Typography>
                                          <Typography variant="body1">
                                            {transfer.service_details
                                              ?.vehicle_model || "N/A"}
                                            {transfer.service_details
                                              ?.model_year
                                              ? ` (${transfer.service_details.model_year})`
                                              : ""}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>

                                    {transfer.service_details
                                      ?.vehicle_plate_no && (
                                      <Grid item xs={12} sm={6}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <ConfirmationNumberIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Plate Number:</strong>
                                            </Typography>
                                            <Typography variant="body1">
                                              {
                                                transfer.service_details
                                                  .vehicle_plate_no
                                              }
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>
                                    )}

                                    {transfer.service_details
                                      ?.seating_capacity && (
                                      <Grid item xs={12} sm={6}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <AirlineSeatReclineNormalIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Seating Capacity:</strong>
                                            </Typography>
                                            <Typography variant="body1">
                                              {
                                                transfer.service_details
                                                  .seating_capacity
                                              }{" "}
                                              Persons
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>
                                    )}
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Pricing Details */}
                              <Grid item xs={12}>
                                <Typography
                                  variant="subtitle2"
                                  sx={{
                                    mb: 1.5,
                                    fontWeight: "medium",
                                    color: "#555",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  <AttachMoneyIcon
                                    fontSize="small"
                                    sx={{ mr: 0.5 }}
                                  />
                                  Pricing Details
                                </Typography>

                                <Paper
                                  variant="outlined"
                                  sx={{
                                    p: 2,
                                    borderColor: "rgba(25, 118, 210, 0.2)",
                                    bgcolor: "rgba(25, 118, 210, 0.02)",
                                  }}
                                >
                                  <Grid container spacing={2}>
                                    {/* <Grid item xs={12} sm={6} md={3}>
                                      <Box sx={{ p: 1.5, borderRadius: 2, bgcolor: "white", border: "1px dashed rgba(25, 118, 210, 0.3)" }}>
                                        <Typography variant="body2" color="text.secondary">Base Price</Typography>
                                        <Typography variant="h6" sx={{ color: "#1976d2", fontWeight: "medium" }}>
                                          ${transfer.basePrice || "N/A"}
                                        </Typography>
                                      </Box>
                                    </Grid> */}

                                    {/* {transfer.Tax && (
                                      <Grid item xs={12} sm={6} md={3}>
                                        <Box sx={{ p: 1.5, borderRadius: 2, bgcolor: "white", border: "1px dashed rgba(25, 118, 210, 0.3)" }}>
                                          <Typography variant="body2" color="text.secondary">Tax</Typography>
                                          <Typography variant="h6" sx={{ color: "#1976d2", fontWeight: "medium" }}>
                                            ${transfer.Tax || "0"}
                                          </Typography>
                                        </Box>
                                      </Grid>
                                    )} */}

                                    {/* {transfer.markedUpPrice && (
                                      <Grid item xs={12} sm={6} md={3}>
                                        <Box sx={{ p: 1.5, borderRadius: 2, bgcolor: "white", border: "1px dashed rgba(25, 118, 210, 0.3)" }}>
                                          <Typography variant="body2" color="text.secondary">Marked Price</Typography>
                                          <Typography variant="h6" sx={{ color: "#1976d2", fontWeight: "medium" }}>
                                            ${transfer.markedUpPrice}
                                          </Typography>
                                        </Box>
                                      </Grid>
                                    )} */}

                                    <Grid item xs={12} sm={6} md={3}>
                                    {localPricehide === "0" && (

                                      <Box
                                        sx={{
                                          p: 1.5,
                                          borderRadius: 2,
                                          bgcolor: "rgba(76, 175, 80, 0.1)",
                                          border:
                                            "1px solid rgba(76, 175, 80, 0.3)",
                                        }}
                                      >
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          Total Price
                                        </Typography>
                                        <Typography
                                          variant="h6"
                                          sx={{
                                            color: "#1976d2",
                                            fontWeight: "bold",
                                          }}
                                        >
                                          SGD {transfer.totalPrice || "N/A"}
                                        </Typography>
                                      </Box>
                                        )}

                                    </Grid>
                                  </Grid>
                                </Paper>
                              </Grid>

                              {/* Transfer Time Details */}
                              {(transfer.Night_Start_Time ||
                                transfer.Night_End_Time) && (
                                <Grid item xs={12}>
                                  <Typography
                                    variant="subtitle2"
                                    sx={{
                                      mb: 1.5,
                                      fontWeight: "medium",
                                      color: "#555",
                                      display: "flex",
                                      alignItems: "center",
                                    }}
                                  >
                                    <ScheduleIcon
                                      fontSize="small"
                                      sx={{ mr: 0.5 }}
                                    />
                                    Service Hours
                                  </Typography>

                                  <Paper
                                    variant="outlined"
                                    sx={{
                                      p: 2,
                                      borderColor: "rgba(25, 118, 210, 0.2)",
                                      bgcolor: "white",
                                    }}
                                  >
                                    <Grid container spacing={2}>
                                      <Grid item xs={12} sm={6}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <WatchLaterIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Service Hours:</strong>
                                            </Typography>
                                            <Typography variant="body1">
                                              {transfer.Night_Start_Time
                                                ? transfer.Night_Start_Time.slice(
                                                    0,
                                                    5
                                                  )
                                                : "N/A"}{" "}
                                              -
                                              {transfer.Night_End_Time
                                                ? transfer.Night_End_Time.slice(
                                                    0,
                                                    5
                                                  )
                                                : "N/A"}
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>

                                      <Grid item xs={12} sm={6}>
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                          }}
                                        >
                                          <PeopleIcon
                                            fontSize="small"
                                            sx={{ color: "#1976d2", mr: 1 }}
                                          />
                                          <Box>
                                            <Typography
                                              variant="body2"
                                              color="text.secondary"
                                            >
                                              <strong>Passengers:</strong>
                                            </Typography>
                                            <Typography variant="body1">
                                              {transfer.adults
                                                ? `${transfer.adults} Adult${
                                                    transfer.adults > 1
                                                      ? "s"
                                                      : ""
                                                  }`
                                                : "0 Adults"}
                                              {transfer.children &&
                                              transfer.children !== "0"
                                                ? `, ${
                                                    transfer.children
                                                  } Child${
                                                    transfer.children > 1
                                                      ? "ren"
                                                      : ""
                                                  }`
                                                : ""}
                                            </Typography>
                                          </Box>
                                        </Box>
                                      </Grid>
                                    </Grid>
                                  </Paper>
                                </Grid>
                              )}

                              {/* Special Requests */}
                              {/* {transfer.specialRequests && (
                                <Grid item xs={12}>
                                  <Typography
                                    variant="subtitle2"
                                    sx={{
                                      mb: 1.5,
                                      fontWeight: "medium",
                                      color: "#555",
                                      display: "flex",
                                      alignItems: "center"
                                    }}
                                  >
                                    <NoteIcon fontSize="small" sx={{ mr: 0.5 }} />
                                    Special Requests
                                  </Typography>
                                  
                                  <Paper
                                    variant="outlined"
                                    sx={{
                                      p: 2,
                                      borderColor: "rgba(25, 118, 210, 0.2)",
                                      bgcolor: "rgba(25, 118, 210, 0.02)",
                                    }}
                                  >
                                <Typography variant="body2">
                                      {transfer.specialRequests}
                                </Typography>
                                  </Paper>
                            </Grid>
                              )} */}
                            </Grid>
                          </Box>
                        </Box>
                      ))}
                    </Box>
                  )}

                {/* 7. Price Details Card */}
                <StyledCard>
                  <StyledCardHeader>
                    <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                      Price Details
                    </Typography>
                  </StyledCardHeader>
                  <CardContent sx={{ p: 3 }}>
                    <Grid container spacing={3}>
                      <Grid item xs={12} md={8}>
                        <Stack spacing={2}>
                          <Box
                            sx={{
                              display: "flex",
                              justifyContent: "space-between",
                              p: 1,
                              borderRadius: 1,
                              bgcolor: "rgba(33, 150, 243, 0.05)",
                            }}
                          >
                            <Typography>
                              <strong>Base Price:</strong>
                            </Typography>
                            <Typography>
                              SGD {localTotalPrice || totalPrice || 1500}
                            </Typography>
                          </Box>
                          <Box
                            sx={{
                              display: "flex",
                              justifyContent: "space-between",
                              p: 1,
                              borderRadius: 1,
                            }}
                          >
                            <Typography>
                              <strong>Taxes & Fees:</strong>
                            </Typography>
                            <Typography>-</Typography>
                          </Box>
                          {(localMarkupAmount > 0 || markupAmount > 0) && (
                            <Box
                              sx={{
                                display: "flex",
                                justifyContent: "space-between",
                                p: 1,
                                borderRadius: 1,
                                bgcolor: "rgba(33, 150, 243, 0.05)",
                              }}
                            >
                              <Typography>
                                <strong>Additional Services:</strong>
                              </Typography>
                              <Typography>
                                SGD {localMarkupAmount || markupAmount}
                              </Typography>
                            </Box>
                          )}
                          {(localDiscountAmount > 0 || discountAmount > 0) && (
                            <Box
                              sx={{
                                display: "flex",
                                justifyContent: "space-between",
                                p: 1,
                                borderRadius: 1,
                              }}
                            >
                              <Typography>
                                <strong>Discount:</strong>
                              </Typography>
                              <Typography color="error">
                                SGD {localDiscountAmount || discountAmount}
                              </Typography>
                            </Box>
                          )}
                          <Divider sx={{ my: 1 }} />
                          <Box
                            sx={{
                              display: "flex",
                              justifyContent: "space-between",
                              p: 2,
                              borderRadius: 2,
                              bgcolor: "rgba(46, 125, 50, 0.1)",
                              border: "1px dashed #2e7d32",
                            }}
                          >
                            <Typography variant="h6">
                              <strong>Total Price:</strong>
                            </Typography>
                            <Typography
                              variant="h6"
                              sx={{ color: "#2e7d32", fontWeight: "bold" }}
                            >
                              SGD{" "}
                              {(localTotalPrice || totalPrice || 1700) +
                                (localMarkupAmount || markupAmount || 0) -
                                (localDiscountAmount || discountAmount || 0)}
                            </Typography>
                          </Box>
                        </Stack>
                      </Grid>
                      <Grid
                        item
                        xs={12}
                        md={4}
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                        }}
                      >
                        <Box
                          sx={{
                            width: "100%",
                            p: 3,
                            textAlign: "center",
                            borderRadius: 2,
                            border: "1px solid rgba(33, 150, 243, 0.2)",
                            bgcolor: "rgba(33, 150, 243, 0.05)",
                          }}
                        >
                          <CheckCircleIcon
                            sx={{ fontSize: 48, color: "#2e7d32", mb: 1 }}
                          />
                          <Typography
                            variant="h6"
                            sx={{ fontWeight: "bold", color: "#2e7d32" }}
                          >
                            Booking Confirmed
                          </Typography>
                          <Typography variant="body2" color="textSecondary">
                            Your tour package is confirmed and ready for your
                            adventure!
                          </Typography>
                        </Box>
                      </Grid>
                    </Grid>
                  </CardContent>
                </StyledCard>

                <Box
                  sx={{
                    textAlign: "center",
                    mt: 4,
                    p: 2,
                    borderRadius: 2,
                    bgcolor: "rgba(33, 150, 243, 0.05)",
                  }}
                >
                  <Typography variant="body2" color="textSecondary">
                    Thank you for booking with us!
                  </Typography>
                  {/* <Typography
                    variant="body2"
                    sx={{ mt: 1, fontWeight: "medium", color: "#1976d2" }}
                  >
                    support@yourtravelagency.com | +1 (123) 456-7890
                  </Typography> */}
                </Box>
              </CardContent>
            </StyledCard>
          </Paper>
        </Container>
      </div>
    </Modal>
  );
};

export default PrintModal;
