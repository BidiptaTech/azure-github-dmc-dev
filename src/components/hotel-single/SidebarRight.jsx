import GoogleMapReact from "google-map-react";
import { useState, useRef, useEffect } from "react";
import { Tabs, Tab } from "@mui/material";

// Define marker colors by port type
const PORT_COLORS = {
  entry: "blue", // Entry ports - blue
  exit: "green",  // Exit ports - green
  other: "purple" // Other ports - purple
};

// Define icon by port type
const PORT_ICONS = {
  "Seaport": "icon-anchor",
  "Airport": "icon-airplane",
  "RailwayStation": "icon-train",
  "BusStation": "icon-bus",
  "Metro": "icon-subway",
  "Port": "icon-dock",
  "Ferry": "icon-ship",
  "Transit": "icon-route",
  "default": "icon-navigation"
};

// Get port color based on tab type
const getPortColor = (tabIndex) => {
  if (tabIndex === 0) return PORT_COLORS.entry;
  if (tabIndex === 1) return PORT_COLORS.exit;
  return PORT_COLORS.other;
};

// Get icon based on port type
const getPortIcon = (type) => {
  return PORT_ICONS[type] || PORT_ICONS.default;
};

const LocationMarker = ({ color = "red", type = "hotel" }) => (
  <div className="marker-icon">
    {type === "hotel" ? (
      <div className="size-40 flex-center bg-red-1 rounded-full">
        <i className="icon-location text-16 text-white"></i>
      </div>
    ) : (
      <i className={`icon-location text-24 text-${color}-1`}></i>
    )}
  </div>
);

const PlaceMarker = ({ text, active, color = "blue" }) => (
  <div className={`marker-icon ${active ? "pulse" : ""}`} style={{ transform: "translate(-50%, -100%)" }}>
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center'
    }}>
      <div style={{
        backgroundColor: color === "blue" ? '#4285F4' : color === "green" ? '#34A853' : color === "purple" ? '#7B1FA2' : '#4285F4',
        color: 'white',
        padding: '6px 10px',
        borderRadius: '4px',
        fontSize: '12px',
        fontWeight: 'bold',
        boxShadow: '0 2px 6px rgba(0,0,0,0.3)',
        marginBottom: '4px',
        minWidth: '80px',
        textAlign: 'center'
      }}>
        {text}
      </div>
      <div style={{
        width: '0',
        height: '0',
        borderLeft: '8px solid transparent',
        borderRight: '8px solid transparent',
        borderTop: `8px solid ${color === "blue" ? '#4285F4' : color === "green" ? '#34A853' : color === "purple" ? '#7B1FA2' : '#4285F4'}`,
        marginBottom: '4px'
      }}></div>
      <div style={{
        width: '16px',
        height: '16px',
        backgroundColor: 'white',
        border: `4px solid ${color === "blue" ? '#4285F4' : color === "green" ? '#34A853' : color === "purple" ? '#7B1FA2' : '#4285F4'}`,
        borderRadius: '50%',
        boxShadow: '0 2px 6px rgba(0,0,0,0.3)'
      }}></div>
    </div>
  </div>
);

const WaypointMarker = ({ color = "blue" }) => (
  <div className={`size-12 flex-center bg-${color === "blue" ? "blue" : color === "green" ? "green" : color === "purple" ? "purple" : "blue"}-1 rounded-full`}></div>
);

const SidebarRight = ({ hotelDetails}) => {
  console.log(hotelDetails, "hotelDetails");
  
  // Ensure latitude and longitude are properly parsed as numbers
  const hotelLat = parseFloat(hotelDetails?.latitude) || 19.106;
  const hotelLng = parseFloat(hotelDetails?.longitude) || 73.009;
  
  console.log("Hotel coordinates:", hotelLat, hotelLng);
  
  const [activeTab, setActiveTab] = useState(0);
  const [hoveredPlace, setHoveredPlace] = useState(null);
  const [activePlace, setActivePlace] = useState(null);
  const [routeDetails, setRouteDetails] = useState(null);
  const mapRef = useRef(null);
  const [mapsApi, setMapsApi] = useState(null);
  const [map, setMap] = useState(null);
  const directionsServiceRef = useRef(null);
  const directionsRendererRef = useRef(null);
  
  // Add state for processed port data
  const [processedEntryPorts, setProcessedEntryPorts] = useState([]);
  const [processedExitPorts, setProcessedExitPorts] = useState([]);
  const [processedOtherPorts, setProcessedOtherPorts] = useState([]);
  
  // Add map center and zoom state to force re-render when needed
  const [mapCenter, setMapCenter] = useState({
    lat: hotelLat,
    lng: hotelLng
  });
  const [mapZoom, setMapZoom] = useState(14);

  // Get current active color based on tab
  const activeColor = getPortColor(activeTab);

  const handleTabChange = (event, newValue) => {
    setActiveTab(newValue);
    setHoveredPlace(null);
    setActivePlace(null);
    if (directionsRendererRef.current) {
      directionsRendererRef.current.setMap(null);
    }
  };

  const handlePlaceHover = (place) => {
    setHoveredPlace(place);
  };

  const handlePlaceClick = (place) => {
    if (activePlace === place) {
      setActivePlace(null);
      if (directionsRendererRef.current) {
        directionsRendererRef.current.setMap(null);
      }
      setRouteDetails(null);
    } else {
      setActivePlace(place);
      if (mapsApi && map) {
        calculateAndDisplayRoute(place);
      }
    }
  };

  const hotelName = hotelDetails?.hotel_name || "Super Townhouse OAK Kopar Khairane Navi Mumbai Formerly IVY Hotel";

  // Update map center when hotel coordinates change
  useEffect(() => {
    if (hotelDetails?.latitude && hotelDetails?.longitude) {
      const newLat = parseFloat(hotelDetails.latitude);
      const newLng = parseFloat(hotelDetails.longitude);
      
      if (!isNaN(newLat) && !isNaN(newLng)) {
        console.log("Updating map center to:", newLat, newLng);
        setMapCenter({
          lat: newLat,
          lng: newLng
        });
      } else {
        console.warn("Invalid coordinates from API:", hotelDetails.latitude, hotelDetails.longitude);
      }
    }
  }, [hotelDetails?.latitude, hotelDetails?.longitude]);

  // Generate waypoints between hotel and destination
  const generateWaypoints = (destination, pointCount = 2) => {
    if (!destination || !destination.latitude || !destination.longitude) {
      return [];
    }

    const destLat = parseFloat(destination.latitude);
    const destLng = parseFloat(destination.longitude);
    
    if (isNaN(destLat) || isNaN(destLng)) {
      console.warn("Invalid destination coordinates:", destination.latitude, destination.longitude);
      return [];
    }

    // Calculate distance between points
    const latDiff = (destLat - hotelLat) / (pointCount + 1);
    const lngDiff = (destLng - hotelLng) / (pointCount + 1);
    
    const waypoints = [];
    for (let i = 1; i <= pointCount; i++) {
      // Add some small random offset for more natural paths (±0.002 degrees)
      const randomLat = (Math.random() - 0.5) * 0.004;
      const randomLng = (Math.random() - 0.5) * 0.004;
      
      waypoints.push({
        lat: hotelLat + (latDiff * i) + randomLat,
        lng: hotelLng + (lngDiff * i) + randomLng
      });
    }
    
    return waypoints;
  };

  // Process port data
  useEffect(() => {
    if (hotelDetails) {
      // Process entry ports
      if (hotelDetails.entry_port && Array.isArray(hotelDetails.entry_port)) {
        const processedPorts = hotelDetails.entry_port.map((port, index) => {
          // Calculate an estimated duration based on distance (assuming 30km/h average speed)
          const distance = parseFloat(port.distance) || 0;
          const minutes = Math.round(distance * 2); // Rough calculation: 2 min per km
          const duration = minutes <= 60 
            ? `${minutes} min` 
            : `${Math.floor(minutes/60)} hr ${minutes % 60} min`;
          
          // Calculate miles from kilometers
          const miles = (distance * 0.621371).toFixed(1);
          
          const lat = parseFloat(port.latitude);
          const lng = parseFloat(port.longitude);
          
          if (isNaN(lat) || isNaN(lng)) {
            console.warn(`Invalid coordinates for entry port ${port.port_name}:`, port.latitude, port.longitude);
          }
          
          return {
            id: index + 1,
            port_name: port.port_name || "Unknown Port",
            type: port.type || "Port",
            portColor: PORT_COLORS.entry,
            portIcon: getPortIcon(port.type || "Port"),
            distance: port.distance,
            miles: miles,
            duration: duration,
            lat: lat || hotelLat + 0.01 * (index + 1), // Fallback to derived position if invalid
            lng: lng || hotelLng + 0.01 * (index + 1),
            waypoints: generateWaypoints(port, 2)
          };
        });
        setProcessedEntryPorts(processedPorts);
      }
      
      // Process exit ports
      if (hotelDetails.exit_port && Array.isArray(hotelDetails.exit_port)) {
        const processedPorts = hotelDetails.exit_port.map((port, index) => {
          const distance = parseFloat(port.distance) || 0;
          const minutes = Math.round(distance * 2);
          const duration = minutes <= 60 
            ? `${minutes} min` 
            : `${Math.floor(minutes/60)} hr ${minutes % 60} min`;
          
          // Calculate miles from kilometers
          const miles = (distance * 0.621371).toFixed(1);
          
          const lat = parseFloat(port.latitude);
          const lng = parseFloat(port.longitude);
          
          if (isNaN(lat) || isNaN(lng)) {
            console.warn(`Invalid coordinates for exit port ${port.port_name}:`, port.latitude, port.longitude);
          }
          
          return {
            id: index + 100, // Different ID range to avoid conflicts
            port_name: port.port_name || "Unknown Port",
            type: port.type || "Port",
            portColor: PORT_COLORS.exit,
            portIcon: getPortIcon(port.type || "Port"),
            distance: port.distance,
            miles: miles,
            duration: duration,
            lat: lat || hotelLat - 0.01 * (index + 1), // Fallback to derived position if invalid
            lng: lng || hotelLng - 0.01 * (index + 1),
            waypoints: generateWaypoints(port, 2)
          };
        });
        setProcessedExitPorts(processedPorts);
      }
      
      // Process other ports
      if (hotelDetails.other_port && Array.isArray(hotelDetails.other_port)) {
        const processedPorts = hotelDetails.other_port.map((port, index) => {
          const distance = parseFloat(port.distance) || 0;
          const minutes = Math.round(distance * 2);
          const duration = minutes <= 60 
            ? `${minutes} min` 
            : `${Math.floor(minutes/60)} hr ${minutes % 60} min`;
          
          // Calculate miles from kilometers
          const miles = (distance * 0.621371).toFixed(1);
          
          const lat = parseFloat(port.latitude);
          const lng = parseFloat(port.longitude);
          
          if (isNaN(lat) || isNaN(lng)) {
            console.warn(`Invalid coordinates for other port ${port.type || port.port_name}:`, port.latitude, port.longitude);
          }
          
          return {
            id: index + 200, // Different ID range
            type: port.type || 'Transit Point',
            port_name: port.port_name,
            portColor: PORT_COLORS.other,
            portIcon: getPortIcon(port.type || "Transit"),
            distance: port.distance,
            miles: miles,
            duration: duration,
            lat: lat || hotelLat + 0.005 * (index + 1) * Math.cos(index), // Fallback with circular pattern
            lng: lng || hotelLng + 0.005 * (index + 1) * Math.sin(index),
            waypoints: generateWaypoints(port, port.type === 'Airport' ? 3 : 2) // More waypoints for airports
          };
        });
        setProcessedOtherPorts(processedPorts);
      }
    }
  }, [hotelDetails, hotelLat, hotelLng]);

  const getActiveTabPlaces = () => {
    if (activeTab === 0) return processedEntryPorts;
    if (activeTab === 1) return processedExitPorts;
    return processedOtherPorts;
  };

  // Calculate and display route using Google Directions API
  const calculateAndDisplayRoute = (place) => {
    if (!place || !place.lat || !place.lng || !place.waypoints) {
      console.error("Invalid place or waypoints data", place);
      return;
    }
    
    if (!directionsServiceRef.current) {
      directionsServiceRef.current = new mapsApi.DirectionsService();
    }
    
    if (!directionsRendererRef.current) {
      directionsRendererRef.current = new mapsApi.DirectionsRenderer({
        suppressMarkers: true,
        polylineOptions: {
          strokeColor: place.portColor === PORT_COLORS.entry ? '#4285F4' : 
                       place.portColor === PORT_COLORS.exit ? '#34A853' : '#7B1FA2',
          strokeOpacity: 0.8,
          strokeWeight: 5
        }
      });
    }
    
    // Create waypoints from place data
    const waypoints = place.waypoints.map(point => ({
      location: new mapsApi.LatLng(point.lat, point.lng),
      stopover: false
    }));
    
    directionsServiceRef.current.route(
      {
        origin: new mapsApi.LatLng(mapCenter.lat, mapCenter.lng),
        destination: new mapsApi.LatLng(place.lat, place.lng),
        waypoints: waypoints,
        optimizeWaypoints: true,
        travelMode: mapsApi.TravelMode.DRIVING,
      },
      (response, status) => {
        if (status === "OK") {
          directionsRendererRef.current.setDirections(response);
          directionsRendererRef.current.setMap(map);
          
          // Get route details
          const route = response.routes[0];
          const leg = route.legs[0];
          setRouteDetails({
            distance: leg.distance.text,
            duration: leg.duration.text,
            steps: leg.steps
          });
        } else {
          console.error("Directions request failed due to " + status);
        }
      }
    );
  };

  const handleApiLoaded = (map, maps) => {
    console.log("Google Maps API loaded successfully");
    setMap(map);
    setMapsApi(maps);
    mapRef.current = { map, maps };
    
    // Set map options for better visibility
    map.setOptions({
      styles: [
        {
          featureType: "poi",
          elementType: "labels",
          stylers: [{ visibility: "on" }]
        },
        {
          featureType: "transit",
          elementType: "labels",
          stylers: [{ visibility: "on" }]
        },
        {
          featureType: "road",
          elementType: "labels",
          stylers: [{ visibility: "on" }]
        }
      ],
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false
    });
  };

  // If an active place is selected and API is loaded, display route
  useEffect(() => {
    if (activePlace && mapsApi && map) {
      calculateAndDisplayRoute(activePlace);
    }
  }, [activePlace, mapsApi, map]);

  // Get current places to display based on active tab
  const currentPlaces = getActiveTabPlaces();

  return (
    <aside className="ml-50 lg:ml-0">
      <div className="px-30 py-30 border-light rounded-4 shadow-4">
        {/* What's nearby title and search */}
        <h3 className="text-18 fw-500 mb-10">What's nearby?</h3>
        
        <div className="d-flex items-center mb-20">
          <div className="size-40 flex-center bg-red-1 rounded-full mr-10">
            <i className="icon-location text-16 text-white"></i>
          </div>
          <div className="text-14 fw-500">{hotelName}</div>
        </div>

        {/* Google Map */}
        <div className="mb-20" style={{ height: "300px", position: "relative" }}>
          <GoogleMapReact
            bootstrapURLKeys={{ 
              key: "", // Add your Google Maps API key here
              libraries: ['places', 'geometry', 'drawing', 'visualization']
            }}
            center={mapCenter}
            zoom={mapZoom}
            yesIWantToUseGoogleMapApiInternals
            onGoogleApiLoaded={({ map, maps }) => handleApiLoaded(map, maps)}
            options={{
              fullscreenControl: false,
              zoomControl: true,
              mapTypeControl: false,
              scaleControl: true,
              rotateControl: false,
              streetViewControl: false
            }}
          >
            {/* Hotel Marker */}
            <LocationMarker
              lat={mapCenter.lat}
              lng={mapCenter.lng}
              type="hotel"
            />
            
            {/* Destination Marker - only show when place is active */}
            {activePlace && (
              <PlaceMarker
                key={activePlace.id}
                lat={activePlace.lat}
                lng={activePlace.lng}
                text={activePlace.port_name || activePlace.type}
                active={true}
                color={activePlace.portColor}
              />
            )}
            
            {/* Waypoint Markers - only when route is active */}
            {activePlace && activePlace.waypoints && activePlace.waypoints.map((point, idx) => (
              <WaypointMarker 
                key={`wp-${activePlace.id}-${idx}`}
                lat={point.lat}
                lng={point.lng}
                color={activePlace.portColor}
              />
            ))}
            
            {/* Hovered Place Marker */}
            {hoveredPlace && hoveredPlace !== activePlace && (
              <PlaceMarker
                key={`hover-${hoveredPlace.id}`}
                lat={hoveredPlace.lat}
                lng={hoveredPlace.lng}
                text={hoveredPlace.port_name || hoveredPlace.type}
                active={false}
                color={hoveredPlace.portColor}
              />
            )}
          </GoogleMapReact>
          
          <div className="d-flex flex-center absolute top-0 right-0 bg-white px-10 py-5 text-14 fw-500 rounded-4 mr-10 mt-10">
            <a href="#" className="text-blue-1">SEE MAP</a>
          </div>
          
          {/* Route info overlay when route is active */}
          {activePlace && (
            <div className="absolute bottom-0 left-0 right-0 bg-white bg-opacity-90 p-10">
              <div className="d-flex justify-between">
                <div className="text-14 fw-500 d-flex align-items-center">
                  {activePlace.port_name}
                  {activePlace.type && (
                    <span className={`badge bg-${activePlace.portColor}-1 text-white ml-10 px-10 py-5 rounded-4 text-10 d-flex align-items-center`}>
                      <i className={`${activePlace.portIcon} mr-5 text-12`}></i>
                      {activePlace.type}
                    </span>
                  )}
                </div>
                <div className="text-14 text-blue-1 fw-500">
                  {activePlace.duration} ({activePlace.distance}km / {activePlace.miles}mi)
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Tabs Navigation */}
        <div>
          <Tabs 
            value={activeTab} 
            onChange={handleTabChange}
            className="custom-tabs"
            TabIndicatorProps={{
              style: { backgroundColor: '#3554D1', height: 0 }
            }}
            sx={{
              '& .MuiTabs-flexContainer': {
                borderBottom: '1px solid #EAEEF3'
              }
            }}
          >
            <Tab 
              label={
                <div className="d-flex align-items-center">
                  <span className="size-20 flex-center bg-blue-1 rounded-full mr-8">
                    <i className="icon-arrow-top-right text-10 text-white"></i>
                  </span>
                  Entry Port
                </div>
              }
              sx={{
                textTransform: 'none',
                fontWeight: 500,
                fontSize: '15px',
                color: activeTab === 0 ? '#3554D1' : '#697488',
                borderBottom: activeTab === 0 ? '2px solid #3554D1' : 'none',
                marginRight: 2,
                padding: '10px 5px',
                minWidth: 'auto'
              }}
            />
            <Tab 
              label={
                <div className="d-flex align-items-center">
                  <span className="size-20 flex-center bg-red-1 rounded-full mr-8">
                    <i className="icon-minus text-14 text-white"></i>
                  </span>
                  Exit Port
                </div>
              } 
              sx={{
                textTransform: 'none',
                fontWeight: 500,
                fontSize: '15px',
                color: activeTab === 1 ? '#3554D1' : '#697488',
                borderBottom: activeTab === 1 ? '2px solid #3554D1' : 'none',
                marginRight: 2,
                padding: '10px 5px',
                minWidth: 'auto'
              }}
            />
            <Tab 
              label={
                <div className="d-flex align-items-center">
                  <span className="size-20 flex-center bg-purple-1 rounded-full mr-8">
                    <i className="icon-plus text-14 text-white"></i>
                  </span>
                  Other Port
                </div>
              } 
              sx={{
                textTransform: 'none',
                fontWeight: 500,
                fontSize: '15px',
                color: activeTab === 2 ? '#3554D1' : '#697488',
                borderBottom: activeTab === 2 ? '2px solid #3554D1' : 'none',
                padding: '10px 5px',
                minWidth: 'auto'
              }}
            />
          </Tabs>

          {/* Tab Content */}
          <div className="tab-content mt-15">
            {activeTab === 0 && (
              <div>
                {processedEntryPorts.length > 0 ? (
                  processedEntryPorts.map((place) => (
                    <div 
                      className={`d-flex justify-between py-10 border-bottom-light cursor-pointer ${hoveredPlace === place ? 'bg-light-2' : ''} ${activePlace === place ? 'bg-blue-light' : ''}`} 
                      key={place.id}
                      onMouseEnter={() => handlePlaceHover(place)}
                      onMouseLeave={() => setHoveredPlace(null)}
                      onClick={() => handlePlaceClick(place)}
                    >
                      <div className="text-14 d-flex align-items-center">
                        {place.port_name}
                        {place.type && (
                          <span className="badge bg-blue-1 text-white ml-10 px-10 py-5 rounded-4 text-10 d-flex align-items-center">
                            <i className={`${place.portIcon} mr-5 text-12`}></i>
                            {place.type}
                          </span>
                        )}
                      </div>
                      <div className="d-flex align-items-center">
                        <div className="text-14 text-blue-1 mr-10">{place.duration}</div>
                        <div className="text-14 text-light-1">{place.distance}km ({place.miles}mi)</div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="py-20 text-center text-14 text-light-1">No entry ports available</div>
                )}
              </div>
            )}

            {activeTab === 1 && (
              <div>
                {processedExitPorts.length > 0 ? (
                  processedExitPorts.map((place) => (
                    <div 
                      className={`d-flex justify-between py-10 border-bottom-light cursor-pointer ${hoveredPlace === place ? 'bg-light-2' : ''} ${activePlace === place ? 'bg-blue-light' : ''}`} 
                      key={place.id}
                      onMouseEnter={() => handlePlaceHover(place)}
                      onMouseLeave={() => setHoveredPlace(null)}
                      onClick={() => handlePlaceClick(place)}
                    >
                      <div className="text-14 d-flex align-items-center">
                        {place.port_name}
                        {place.type && (
                          <span className="badge bg-red-1 text-white ml-10 px-10 py-5 rounded-4 text-10 d-flex align-items-center">
                            <i className={`${place.portIcon} mr-5 text-12`}></i>
                            {place.type}
                          </span>
                        )}
                      </div>
                      <div className="d-flex align-items-center">
                        <div className="text-14 text-blue-1 mr-10">{place.duration}</div>
                        <div className="text-14 text-light-1">{place.distance}km ({place.miles}mi)</div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="py-20 text-center text-14 text-light-1">No exit ports available</div>
                )}
              </div>
            )}

            {activeTab === 2 && (
              <div>
                {processedOtherPorts.length > 0 ? (
                  processedOtherPorts.map((transport) => (
                    <div 
                      className={`d-flex justify-between py-10 border-bottom-light cursor-pointer ${hoveredPlace === transport ? 'bg-light-2' : ''} ${activePlace === transport ? 'bg-blue-light' : ''}`} 
                      key={transport.id}
                      onMouseEnter={() => handlePlaceHover(transport)}
                      onMouseLeave={() => setHoveredPlace(null)}
                      onClick={() => handlePlaceClick(transport)}
                    >
                      <div className="text-14 d-flex align-items-center">
                        {transport.port_name || transport.type}
                        {transport.type && transport.port_name && (
                          <span className="badge bg-purple-1 text-white ml-10 px-10 py-5 rounded-4 text-10 d-flex align-items-center">
                            <i className={`${transport.portIcon} mr-5 text-12`}></i>
                            {transport.type}
                          </span>
                        )}
                      </div>
                      <div className="d-flex align-items-center">
                        <div className="text-14 text-blue-1 mr-10">{transport.duration}</div>
                        <div className="text-14 text-light-1">{transport.distance}km ({transport.miles}mi)</div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="py-20 text-center text-14 text-light-1">No other ports available</div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </aside>
  );
};

export default SidebarRight;
