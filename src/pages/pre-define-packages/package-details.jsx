import React from 'react';
import PackageDetailsContainer from '../../components/pre-define-packages/details';
import Header11 from '@/components/header/header-11';
import DefaultFooter from '@/components/footer/default';
import { Box } from '@mui/material';

const PackageDetails = () => {
 

  return (
    <>
      <div className="header-margin"></div>
      {/* header top margin */}

      <Header11 />
      {/* End Header 11 */}
 
      <Box sx={{ mt: 10 }}>
        <PackageDetailsContainer />
      </Box>



      <DefaultFooter />
      {/* End Footer Section */}
    </>
  );
};

export default PackageDetails; 