import TourPackages from '@/components/Tour-Packages'; 
import React from 'react';
import Header11 from '@/components/header/header-11';
import DefaultFooter from '@/components/footer/default';

export default function TourPackagesPage() {
  return (
    <>
      <Header11 />
    <div className="tour-packages-page">
      
      <TourPackages />
    </div>
    <DefaultFooter />
    </>
  )
}
