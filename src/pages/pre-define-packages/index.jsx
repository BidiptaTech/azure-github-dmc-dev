import PreDefinePackages from '@/components/pre-define-packages'; 
import React, { useEffect, useState } from 'react';
import Header11 from '@/components/header/header-11';
import DefaultFooter from '@/components/footer/default';
import CallToActions from "@/components/common/CallToActions";
import { useSelector } from 'react-redux';

export default function PreDefinePackagesPage() {
  const userRole = useSelector((state) => state.auth.userRole);
  const [isVisible, setIsVisible] = useState(false);
  // console.log("PreDefinePackagesPage userRole:", userRole);

  useEffect(() => {
    // Trigger animation after component mounts
    const timer = setTimeout(() => {
      setIsVisible(true);
    }, 100);

    return () => clearTimeout(timer);
  }, []);

  return (
    <>
      <div className="header-margin"></div>
      {/* header top margin */}

      <Header11 />
      {/* End Header 11 */}

      {/* Background Image Container */}
      <div className="pre-define-packages-background">
        <div className="background-image-overlay"></div>
        <div className={`tour-packages-page slide-up-animation ${isVisible ? 'is-visible' : ''}`}>
          <PreDefinePackages />
        </div>
      </div>
      
      {/* <CallToActions /> */}
      {/* End Call To Actions Section */}

      {userRole && userRole.trim() === "Agent" && <DefaultFooter />}
      {/* End Footer Section */}
    </>
  )
}
