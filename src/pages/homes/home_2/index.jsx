import Hero2 from "@/components/booking-enquiry";

import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Direct Enquiry || TravClicks - Travel Technology Transformed",
  description: "TravClicks - Travel Technology Transformed",
};

const home_2 = () => {
  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      {/* <Header11 /> */}
      {/* End Header 1 */}

      <Hero2 />
      {/* End Hero 1 */}

      {/* <CallToActions /> — removed; hero now fills viewport via masthead -type-2 */}

      {/* <DefaultFooter /> */}
      {/* End Footer Section */}
    </>
  );
};

export default home_2;
