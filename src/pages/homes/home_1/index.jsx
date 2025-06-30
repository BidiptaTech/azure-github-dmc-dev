// import DefaultFooter from "@/components/footer/default";
// import Header11 from "@/components/header/header-11";
import Hero1 from "@/components/hero/hero-1";

import CallToActions from "@/components/common/CallToActions";

import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Home || TravClicks - Travel Technology Transformed",
  description: "TravClicks - Travel Technology Transformed",
};

const Home_1 = () => {
  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      {/* <Header11 /> */}
      {/* End Header 1 */}

      <Hero1 />
      {/* End Hero 1 */}

      <CallToActions />

      {/* End Call To Actions Section */}

      {/* <DefaultFooter /> */}
      {/* End Footer Section */}
    </>
  );
};

export default Home_1;
