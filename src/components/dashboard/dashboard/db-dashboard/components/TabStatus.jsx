import * as React from "react";
import { useState } from "react";
import {
  PendingActions,
  EventNote,
  CheckCircle,
  ArrowDropDown,
  History,
} from "@mui/icons-material";
import Pending from "./Pending";
import Upcoming from "./Upcoming";
import Completed from "./Completed";
import Deleted from "./Deleted";

export default function TabStatus() {
  const [activeTab, setActiveTab] = useState(0);

  const handleTabClick = (index) => {
    setActiveTab(index);
  };

  // Tab items with icons
  const tabItems = [
    { label: "Ongoing", icon: <EventNote sx={{ fontSize: 20 }} /> },
    { label: "Upcoming", icon: <PendingActions sx={{ fontSize: 20 }} /> },
    // { label: "Completed", icon: <CheckCircle sx={{ fontSize: 20 }} /> },
    { label: "Past", icon: <History sx={{ fontSize: 20 }} /> },
  ];

  // Define color styles for normal and active states
  const tabStyles = {
    Upcoming: {
      normal: {
        backgroundColor: "rgba(255, 152, 0, 0.1)",
        color: "#FF9800",
        border: "1px solid rgba(255, 152, 0, 0.3)",
        boxShadow: "none",
      },
      active: {
        backgroundColor: "white",
        color: "#FF9800",
        border: "1px solid #FF9800",
        boxShadow: "0 4px 10px rgba(255, 152, 0, 0.15)",
      },
    },
    Ongoing: {
      normal: {
        backgroundColor: "rgba(33, 150, 243, 0.1)",
        color: "#2196F3",
        border: "1px solid rgba(33, 150, 243, 0.3)",
        boxShadow: "none",
      },
      active: {
        backgroundColor: "white",
        color: "#2196F3",
        border: "1px solid #2196F3",
        boxShadow: "0 4px 10px rgba(33, 150, 243, 0.15)",
      },
    },
    // Completed: {
    //   normal: {
    //     backgroundColor: "rgba(76, 175, 80, 0.1)",
    //     color: "#4CAF50",
    //     border: "1px solid rgba(76, 175, 80, 0.3)",
    //     boxShadow: "none",
    //   },
    //   active: {
    //     backgroundColor: "white",
    //     color: "#4CAF50",
    //     border: "1px solid #4CAF50",
    //     boxShadow: "0 4px 10px rgba(76, 175, 80, 0.15)",
    //   },
    // },
    Past: {
      normal: {
        backgroundColor: "rgba(244, 67, 54, 0.1)",
        color: "#F44336",
        border: "1px solid rgba(244, 67, 54, 0.3)",
        boxShadow: "none",
      },
      active: {
        backgroundColor: "white",
        color: "#F44336",
        border: "1px solid #F44336",
        boxShadow: "0 4px 10px rgba(244, 67, 54, 0.15)",
      },
    },
  };

  return (
    <>
      <div className="tabs-container">
        <div
          className="tabs-controls"
          style={{
            display: "flex",
            gap: "12px",
            marginBottom: "5px",
            padding: "0 8px",
          }}
        >
          {tabItems.map((item, index) => {
            const isActive = activeTab === index;
            return (
              <button
                key={index}
                className={`tab-button ${isActive ? "active" : ""}`}
                onClick={() => handleTabClick(index)}
                style={{
                  ...(isActive
                    ? tabStyles[item.label].active
                    : tabStyles[item.label].normal),
                  padding: "10px 20px",
                  borderRadius: "8px",
                  transition: "all 0.3s ease",
                  display: "flex",
                  alignItems: "center",
                  gap: "8px",
                  fontWeight: isActive ? "600" : "500",
                  fontSize: "14px",
                  transform: isActive ? "translateY(-2px)" : "none",
                  position: "relative",
                  overflow: "hidden",
                }}
              >
                {item.icon}
                {item.label}
                {isActive && <ArrowDropDown />}

                {/* Ripple effect for the active tab */}
                {isActive && (
                  <div
                    className="ripple-effect"
                    style={{
                      position: "absolute",
                      top: "50%",
                      left: "50%",
                      width: "200%",
                      height: "200%",
                      transform: "translate(-50%, -50%)",
                      backgroundColor:
                        tabStyles[item.label].normal.backgroundColor,
                      borderRadius: "50%",
                      animation: "ripple 1.5s infinite",
                      opacity: 0.3,
                      zIndex: -1,
                    }}
                  ></div>
                )}
              </button>
            );
          })}
        </div>
      </div>

      {/* Content Wrapper */}
      <div
        className="tab-content"
        style={{
          background: `linear-gradient(135deg, ${
            tabStyles[tabItems[activeTab].label].normal.color
          }20 0%, white 100%)`,
          padding: "24px",
          borderRadius: "16px",
          boxShadow: "0 4px 20px rgba(0, 0, 0, 0.05)",
          animation: "fadeIn 0.5s ease-in-out",
          minHeight: "400px",
        }}
      >
        {activeTab === 0 && <Upcoming />}
        {activeTab === 1 && <Pending />}
        {/* {activeTab === 2 && <Completed />} */}
        {activeTab === 2 && <Deleted />}
      </div>

      {/* Animations */}
      <style jsx global>{`
        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        @keyframes ripple {
          0% {
            transform: translate(-50%, -50%) scale(0);
            opacity: 0.4;
          }
          100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0;
          }
        }

        @keyframes bounce {
          0%,
          100% {
            transform: translateY(0);
          }
          50% {
            transform: translateY(-5px);
          }
        }
      `}</style>
    </>
  );
}
