import React, { useState, useEffect, useRef } from 'react';
import { Box, Typography } from '@mui/material';
import PersonIcon from '@mui/icons-material/Person';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import RemoveCircleOutlineIcon from '@mui/icons-material/RemoveCircleOutline';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import { useDispatch, useSelector } from 'react-redux';
import { setAdultCount, setChildCount } from "../../../slice/localtour/Localslice";

import './passengerStyles.css'; // We'll create this CSS file next

const SimplePassenger = ({ 
  adultsMax = 1, 
  childrenMax = 0, 
  seatingCapacity = 0,
  onAdultsChange,
  onChildrenChange,
  initialAdults,
  initialChildren
}) => {
  const dispatch = useDispatch();
  
  // Set initial values from props, state or defaults
  const reduxAdults = useSelector(state => state.localtour.adultCount) || 1;
  const reduxChildren = useSelector(state => state.localtour.childCount) || 0;
  
  const [adults, setAdults] = useState(initialAdults !== undefined ? initialAdults : reduxAdults);
  const [children, setChildren] = useState(initialChildren !== undefined ? initialChildren : reduxChildren);
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const componentRef = useRef(null);
  const dropdownRef = useRef(null);
  
  // Calculate total passengers and remaining capacity
  const totalPassengers = adults + children;
  const atCapacity = seatingCapacity > 0 && totalPassengers >= seatingCapacity;
  
  // Enhanced close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (componentRef.current && !componentRef.current.contains(event.target)) {
        setDropdownOpen(false);
      }
    };
    
    // Use both mousedown and click events for better coverage
    if (dropdownOpen) {
      document.addEventListener('mousedown', handleClickOutside, true); // Capture phase
      document.addEventListener('click', handleClickOutside, true); // Capture phase
      
      // Debug logging
      console.log("SimplePassenger dropdown listener added");
    }
    
    return () => {
      document.removeEventListener('mousedown', handleClickOutside, true);
      document.removeEventListener('click', handleClickOutside, true);
      console.log("SimplePassenger dropdown listener removed");
    };
  }, [dropdownOpen]);
  
  // Update Redux when values change
  useEffect(() => {
    dispatch(setAdultCount(adults));
    if (onAdultsChange) onAdultsChange(adults);
  }, [adults, dispatch, onAdultsChange]);
  
  useEffect(() => {
    dispatch(setChildCount(children));
    if (onChildrenChange) onChildrenChange(children);
  }, [children, dispatch, onChildrenChange]);
  
  // Handle counter changes
  const handleAdultChange = (increment) => {
    const newValue = increment ? adults + 1 : adults - 1;
    const newTotal = newValue + children;
    
    if (newValue < 1) return; // Minimum 1 adult
    if (seatingCapacity > 0 && newTotal > seatingCapacity) return; // Check capacity
    if (newValue > adultsMax) return; // Check max adults
    
    setAdults(newValue);
  };
  
  const handleChildrenChange = (increment) => {
    const newValue = increment ? children + 1 : children - 1;
    const newTotal = adults + newValue;
    
    if (newValue < 0) return; // Minimum 0 children
    if (seatingCapacity > 0 && newTotal > seatingCapacity) return; // Check capacity
    if (newValue > childrenMax) return; // Check max children
    
    setChildren(newValue);
  };
  
  return (
    <div ref={componentRef} className="simple-passenger-container">
      {/* Trigger card */}
      <div 
        className="passenger-card"
        onClick={(e) => {
          e.stopPropagation();
          e.preventDefault();
          if (e.nativeEvent) e.nativeEvent.stopImmediatePropagation();
          console.log("SimplePassenger card clicked");
          setDropdownOpen(!dropdownOpen);
          return false;
        }}
        onMouseDown={(e) => {
          e.stopPropagation();
          e.preventDefault();
          if (e.nativeEvent) e.nativeEvent.stopImmediatePropagation();
        }}
      >
        <div className="passenger-counts">
          <div className="passenger-count-item">
            <PersonIcon style={{ color: '#1976d2' }} />
            <span>{adults}</span>
          </div>
          
          <div className="passenger-count-item">
            <ChildCareIcon style={{ color: '#1976d2' }} />
            <span>{children}</span>
          </div>
          
          {seatingCapacity > 0 && (
            <div className={`capacity-indicator ${atCapacity ? 'at-capacity' : ''}`}>
              {totalPassengers}/{seatingCapacity}
            </div>
          )}
        </div>
      </div>
      
      {/* Dropdown content */}
      {dropdownOpen && (
        <div className="passenger-dropdown" ref={dropdownRef}>
          <div className="passenger-dropdown-header">
            <h3>Number of passengers</h3>
            {seatingCapacity > 0 && (
              <div className={`capacity-badge ${atCapacity ? 'at-capacity' : ''}`}>
                {totalPassengers}/{seatingCapacity}
              </div>
            )}
          </div>
          
          {atCapacity && (
            <div className="capacity-alert">
              Maximum seating capacity reached
            </div>
          )}
          
          <div className="counter-container">
            <div className="counter-label">
              <PersonIcon style={{ color: '#1976d2' }} />
              <div>
                <div>Adults</div>
                <div className="age-label">Age 12+</div>
              </div>
            </div>
            <div className="counter-controls">
              <button 
                className={`counter-btn ${adults <= 1 ? 'disabled' : ''}`}
                onClick={() => handleAdultChange(false)}
                disabled={adults <= 1}
              >
                <RemoveCircleOutlineIcon />
              </button>
              <span className="counter-value">{adults}</span>
              <button 
                className={`counter-btn ${adults >= adultsMax || atCapacity ? 'disabled' : ''}`}
                onClick={() => handleAdultChange(true)}
                disabled={adults >= adultsMax || atCapacity}
              >
                <AddCircleOutlineIcon />
              </button>
            </div>
          </div>
          
          <div className="counter-container">
            <div className="counter-label">
              <ChildCareIcon style={{ color: '#1976d2' }} />
              <div>
                <div>Children</div>
                <div className="age-label">Ages 0-11</div>
              </div>
            </div>
            <div className="counter-controls">
              <button 
                className={`counter-btn ${children <= 0 ? 'disabled' : ''}`}
                onClick={() => handleChildrenChange(false)}
                disabled={children <= 0}
              >
                <RemoveCircleOutlineIcon />
              </button>
              <span className="counter-value">{children}</span>
              <button 
                className={`counter-btn ${children >= childrenMax || atCapacity ? 'disabled' : ''}`}
                onClick={() => handleChildrenChange(true)}
                disabled={children >= childrenMax || atCapacity}
              >
                <AddCircleOutlineIcon />
              </button>
            </div>
          </div>
          
          {seatingCapacity > 0 && (
            <div className="capacity-note">
              Vehicle capacity: {seatingCapacity} passengers
            </div>
          )}
          
          <button 
            className="done-btn"
            onClick={() => setDropdownOpen(false)}
          >
            Done
          </button>
        </div>
      )}
    </div>
  );
};

export default SimplePassenger; 