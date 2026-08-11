import Hero2 from "@/components/booking-enquiry";

import CallToActions from "@/components/common/CallToActions";

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

      <CallToActions />

      {/* End Call To Actions Section */}

      {/* <DefaultFooter /> */}
      {/* End Footer Section */}
    </>
  );
};

export default home_2;
