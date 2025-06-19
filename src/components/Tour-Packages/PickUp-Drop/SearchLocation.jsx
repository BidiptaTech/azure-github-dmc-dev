import React, { useEffect } from "react";
import { useDispatch } from "react-redux";
import EntryPortSearch from "./EntryPortSearch";
import ExitPortSearch from "./ExitPortSearch";

const SearchLocation = ({ Location, portType, portType1 }) => {
  const dispatch = useDispatch();
  
  // Check and log the Google Maps API availability
  useEffect(() => {
    const checkGoogleMapsApi = () => {
      if (!window.google) {
        console.error("Google Maps API not loaded at all!");
        return false;
      }
      
      if (!window.google.maps) {
        console.error("Google Maps core not loaded!");
        return false;
      }
      
      if (!window.google.maps.places) {
        console.error("Google Maps Places library not loaded!");
        return false;
      }
      
      console.log("Google Maps API (with Places) is fully loaded and available");
      return true;
    };
    
    // Check immediately and after a small delay to ensure API has time to load
    const isLoaded = checkGoogleMapsApi();
    
    if (!isLoaded) {
      // Try again after a delay in case API is still loading
      const timer = setTimeout(() => {
        checkGoogleMapsApi();
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, []);

  return (
    <>
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid-v2 items-center">
          {/* Render Entry Port component if portType is "Entry Port" */}
          {portType === "Entry Port" && (
            <div className="entry-port-container">
              <h4 className="text-18 fw-500 mb-10">Entry Port Services</h4>
              <EntryPortSearch Location={Location} />
            </div>
          )}
          
          {/* Render Exit Port component if portType1 is "Exit Port" */}
          {portType1 === "Exit Port" && (
            <div className="exit-port-container">
              <h4 className="text-18 fw-500 mb-10">Exit Port Services</h4>
              <ExitPortSearch Location={Location} />
            </div>
          )}
        </div>
      </div>

      <style jsx>{`
        .button-grid-v2 {
          display: grid;
          grid-template-columns: 1fr;
          gap: 20px;
          width: 100%;
          max-width: 1730px;
        }

        .entry-port-container,
        .exit-port-container {
          width: 100%;
          border-bottom: 1px solid #e5e5e5;
          padding-bottom: 20px;
          margin-bottom: 20px;
        }

        .exit-port-container:last-child {
          border-bottom: none;
          margin-bottom: 0;
          padding-bottom: 0;
        }

        @media (max-width: 767px) {
          .button-grid-v2 {
            gap: 15px;
          }
        }

        @media (max-width: 480px) {
          .mainSearch {
            padding: 10px !important;
          }

          .button-grid-v2 {
            gap: 10px;
          }

          .text-18 {
            font-size: 16px;
          }
        }
      `}</style>
    </>
  );
};

export default SearchLocation;
