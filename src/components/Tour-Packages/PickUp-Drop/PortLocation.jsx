import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
//import PortLocation2 from "./PortLocation2";

const PortLocation = ({
  pickUpLocation,
  setPickUpLocation,
  setType,
  setId,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setpickId,
  setdropId,
  setDropoffFromAutocomplete,
  disabled = false,
  portType,
}) => {
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);

  const portCityData = useSelector((state) =>
    portType === "Entry Port"
      ? state.pickupDrop.portCityData
      : state.pickupDrop.zone.data?.ports
  );

  // Store the initial ports data in a ref to maintain it after selection
  const [initialPortsLoaded, setInitialPortsLoaded] = useState(false);
  const [cachedPorts, setCachedPorts] = useState([]);

  useEffect(() => {
    // If we have valid port data and haven't cached it yet
    if (
      portCityData &&
      Array.isArray(portCityData) &&
      portCityData.length > 0 &&
      !initialPortsLoaded
    ) {
      setCachedPorts(portCityData);
      setInitialPortsLoaded(true);
    } else if (
      portCityData?.data &&
      Array.isArray(portCityData.data) &&
      portCityData.data.length > 0 &&
      !initialPortsLoaded
    ) {
      setCachedPorts(portCityData.data);
      setInitialPortsLoaded(true);
    }
  }, [portCityData, initialPortsLoaded]);

  // Use cached ports if current ports array is empty
  const portsArray = Array.isArray(portCityData)
    ? portCityData.length > 0
      ? portCityData
      : cachedPorts
    : portCityData?.data && portCityData.data.length > 0
    ? portCityData.data
    : cachedPorts;

  console.log("ports", portsArray);
  const [showDropdown, setShowDropdown] = useState(false);
  const [searchText, setSearchText] = useState("");

  // Grouped data by type
  const groupedPorts =
    portsArray && Array.isArray(portsArray)
      ? portsArray.reduce((acc, port) => {
          if (!port) return acc;
          const type = port.type || "Other";
          if (!acc[type]) acc[type] = [];
          acc[type].push(port);
          return acc;
        }, {})
      : {};

  const handleSelect = (port) => {
    if (!port || disabled) return;
    setPickUpLocation(port.port_name);
    setType("port");
    setId(port.port_id);
    if (portType === "Entry Port") {
      setpickId(port.port_id);
    } else {
      setdropId(port.port_id);
    }
    setSearchText(port.port_name);
    setShowDropdown(false);
    setPickupFromAutocomplete(true);
  };

  // Add useEffect to handle editing after selection
  useEffect(() => {
    // Only trigger when user is modifying a selection
    if (pickUpLocation && searchText !== pickUpLocation) {
      // Reset selection state when user starts editing
      setPickupFromAutocomplete(false);
    }
  }, [searchText, pickUpLocation]);

  const filteredPorts = Object.entries(groupedPorts).reduce(
    (acc, [type, ports]) => {
      const filtered = ports.filter(
        (p) =>
          p &&
          p.port_name &&
          p.port_name.toLowerCase().includes((searchText || "").toLowerCase())
      );
      if (filtered.length > 0) acc[type] = filtered;
      return acc;
    },
    {}
  );

  return (
    <div className={`searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0 relative ${disabled ? "disabled-input" : ""}`}>
      <div className="d-flex justify-between">
        <div className="flex-1 mr-10">
          <div className="d-flex relative">
            <i className={`icon-location-2 text-20 ${disabled ? "text-gray-400" : "text-light-1"}`}></i>
            <div className="ml-5 flex-grow-1 w-full" style={{ minWidth: "160px" }}>
              <input
                type="text"
                placeholder={portType === "Entry Port" ? "Where is your pick up?" : "Where is your drop off"}
                value={searchText}
                onFocus={() => !disabled && setShowDropdown(true)}
                onChange={(e) => {
                  if (disabled) return;
                  
                  const newValue = e.target.value;
                  setSearchText(newValue);
                  setShowDropdown(true);
                  
                  // If we had a selection but now we're editing it
                  if (pickUpLocation && newValue !== pickUpLocation) {
                    setPickupFromAutocomplete(false);
                    // Don't clear pickUpLocation yet - only when selection is confirmed
                  }
                }}
                className={`js-search js-dd-focus w-full text-15 ${disabled ? "bg-gray-100 border-gray-200 text-gray-400" : "border-gray-300"} rounded `}
                style={{ minWidth: "160px" }}
                disabled={disabled}
              />
              {showDropdown && !disabled && (
                <div 
                  className="absolute bg-white border rounded shadow mt-2 z-50 w-full location-dropdown" 
                  style={{ 
                    minWidth: "250px",
                    maxHeight: "280px",
                    overflowY: "auto",
                    scrollbarWidth: "thin",
                    scrollbarColor: "#bbb #f1f1f1"
                  }}
                >
                  {Object.entries(filteredPorts).map(([type, ports]) => (
                    <div key={type}>
                      <div className="category-heading">
                        {type}
                      </div>
                      {ports.map((port) => (
                        <div
                          key={port.port_id}
                          className="px-3 py-2 hover:bg-gray-200 cursor-pointer transition-colors duration-200"
                          onClick={() => handleSelect(port)}
                        >
                          {port.port_name}
                        </div>
                      ))}
                    </div>
                  ))}
                  {Object.keys(filteredPorts).length === 0 && (
                    <div className="px-3 py-2 text-gray-500">
                      No ports found
                    </div>
                  )}
                </div>
              )}
              {/* Optional error message */}
              {validationTriggered && !pickUpLocation && !disabled && (
                <div className="text-red-500 mt-1 text-sm">
                  *Select location from dropdown
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
      <style jsx>{`
        .category-heading {
            padding: 8px 12px;
            font-weight: 600;
            background-color: #f5f5f5;
            color: #333;
            border-bottom: 1px solid #eee;
          }
          
        .disabled-input {
          opacity: 0.8;
          cursor: not-allowed;
        }
        
        /* Custom scrollbar for location dropdown */
        .location-dropdown::-webkit-scrollbar {
          width: 8px;
        }
        
        .location-dropdown::-webkit-scrollbar-track {
          background: #f1f1f1;
          border-radius: 4px;
        }
        
        .location-dropdown::-webkit-scrollbar-thumb {
          background: #bbb;
          border-radius: 4px;
          transition: background 0.3s ease;
        }
        
        .location-dropdown::-webkit-scrollbar-thumb:hover {
          background: #888;
        }
      `}</style>
    </div>
  );
};

export default PortLocation;
