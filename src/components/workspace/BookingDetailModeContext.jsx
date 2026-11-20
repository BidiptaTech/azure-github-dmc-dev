import { createContext, useContext } from "react";

const BookingDetailModeContext = createContext({
  proposalReview: false,
  hideCancel: false,
});

export function BookingDetailModeProvider({
  proposalReview = false,
  children,
}) {
  return (
    <BookingDetailModeContext.Provider
      value={{ proposalReview, hideCancel: Boolean(proposalReview) }}
    >
      {children}
    </BookingDetailModeContext.Provider>
  );
}

export function useBookingDetailMode() {
  return useContext(BookingDetailModeContext);
}
