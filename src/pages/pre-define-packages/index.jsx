import PreDefinePackages from '@/components/pre-define-packages'; 
import React from 'react';
import Header11 from '@/components/header/header-11';
import DefaultFooter from '@/components/footer/default';
import CallToActions from "@/components/common/CallToActions";
import { useSelector } from 'react-redux';

export default function PreDefinePackagesPage() {
  const userRole = useSelector((state) => state.auth.userRole);
  console.log("PreDefinePackagesPage userRole:", userRole);

  return (
    <>
      <div className="header-margin"></div>
      {/* header top margin */}

      <Header11 />
      {/* End Header 11 */}

      <div className="tour-packages-page">
        <PreDefinePackages />
      </div>
      
      <CallToActions />
      {/* End Call To Actions Section */}

      {userRole && userRole.trim() === "Agent" && <DefaultFooter />}
      {/* End Footer Section */}
    </>
  )
}
