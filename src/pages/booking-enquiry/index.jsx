import BookingEnquiry from '@/components/booking-enquiry';
import React from 'react';
import Header11 from '@/components/header/header-11';
import DefaultFooter from '@/components/footer/default';

export default function BookingEnquiryPage() {
  return (
    <>
      <Header11 />
      <div className="booking-enquiry-page">
        <BookingEnquiry />
      </div>
      <DefaultFooter />
    </>
  )
}