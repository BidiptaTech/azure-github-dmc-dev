// import React from "react";
// import { useLocation } from "react-router-dom";

// export default function TimeSlot({ setSelectedTime }) {
//   const location = useLocation();
//   const attraction = location.state?.attraction || {};

//   //  console.log("Attraction Data sk imran:", attraction);

//   // Extract and parse open_time and close_time safely
//   const openTimes = attraction.open_time ? JSON.parse(attraction.open_time) : [];
//   const closeTimes = attraction.close_time ? JSON.parse(attraction.close_time) : [];

//   return (
//     <>
//     <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
//       <select
//         className="custom_input-picker w-100 px-10 py-10 border-light rounded-4 bg-white"
//         onChange={(e) => setSelectedTime(e.target.value)} // Use passed setSelectedTime prop
//       >
//         <option value="">Select Time</option>

//         {/* Open Time Options */}
//         {openTimes.length > 0 && (
//           <>
//             <option disabled>--- Booking Times ---</option>
//             {openTimes.map((time, index) => (
//               <option key={index} value={time}>
//                 {time}
//               </option>
//             ))}
//           </>
//         )}

//         {/* Close Time Options (Disabled, non-selectable) */}
//         {closeTimes.length > 0 && (
//           <>
//             <option disabled>--- Close Times (Disabled) ---</option>
//             {closeTimes.map((time, index) => (
//               <option key={index} value={time} disabled>
//                 {time} {/* Close times are disabled */}
//               </option>
//             ))}
//           </>
//         )}
//       </select>
//     </div>
//     </>
//   );
// }
