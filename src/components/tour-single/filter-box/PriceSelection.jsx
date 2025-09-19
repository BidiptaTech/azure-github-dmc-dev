import React, { useState, useRef, useEffect } from "react";
import PaymentsIcon from '@mui/icons-material/Payments';
import DirectionsBusIcon from '@mui/icons-material/DirectionsBus';
import LocalAttractionIcon from '@mui/icons-material/LocalActivity';
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';

const PriceSelection = ({ options, selectedOption, onChange }) => {
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const dropdownRef = useRef(null);
  
  const selectedOptionObj = options.find(opt => opt.value === selectedOption) || { label: "Select Price Option" };

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const getIcon = (value) => {
    const iconProps = { fontSize: "small", style: { marginRight: 8, color: selectedOption === value ? "#3554D1" : "#64748B" } };
    switch(value) {
      case "withoutTraveller": return <LocalAttractionIcon {...iconProps} />;
      case "withPrivate": return <DirectionsBusIcon {...iconProps} />;
      case "withShare": return <AirportShuttleIcon {...iconProps} />;
      default: return <PaymentsIcon {...iconProps} />;
    }
  };

  const handleOptionSelect = (value) => {
    onChange(value);
    setIsDropdownOpen(false);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16" ref={dropdownRef}>
      <div 
        onClick={() => setIsDropdownOpen(!isDropdownOpen)}
        className="d-flex align-items-center"
        style={{ 
          backgroundColor: "white",
          border: "1px solid #e5e7eb",
          borderRadius: "8px",
          padding: "10px 12px",
          cursor: "pointer",
          transition: "all 0.2s",
          boxShadow: isDropdownOpen ? "0 4px 12px rgba(0,0,0,0.1)" : "none"
        }}
      >
        {selectedOption ? getIcon(selectedOption) : <PaymentsIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />}
        <span style={{ fontWeight: 500, color: "#1a1a1a" }}>{selectedOptionObj.label}</span>
        <i className={`icon-chevron-sm-${isDropdownOpen ? "up" : "down"} text-7 ml-10`} style={{ marginLeft: "auto" }}></i>
      </div>
      
      {isDropdownOpen && (
        <div style={{
          position: 'absolute',
          zIndex: 1000,
          marginTop: '2px',
          boxShadow: '0 4px 16px rgba(0,0,0,0.1)',
          borderRadius: '8px',
          backgroundColor: 'white',
          width: 'calc(100% - 32px)',
          maxHeight: '250px',
          overflowY: 'auto'
        }}>
          {options.map((option, index) => (
            <div
              key={index}
              onClick={() => handleOptionSelect(option.value)}
              style={{
                padding: '10px 12px',
                cursor: 'pointer',
                backgroundColor: selectedOption === option.value ? 'rgba(53, 84, 209, 0.05)' : 'white',
                borderBottom: index < options.length - 1 ? '1px solid #f3f4f6' : 'none',
                display: 'flex',
                alignItems: 'center'
              }}
            >
              {getIcon(option.value)}
              <div>
                <div style={{ fontWeight: selectedOption === option.value ? 600 : 500, fontSize: '14px' }}>
                  {option.label}
                </div>
                {option.description && (
                  <div style={{ color: '#64748B', fontSize: '12px' }}>
                    {option.description}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default PriceSelection; 