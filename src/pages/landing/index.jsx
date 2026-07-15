import LandingHero from "@/components/hero/landing-hero";
import LandingNavbar from "@/components/header/LandingNavbar";
import AboutCompany from "@/components/home/AboutCompany";
import PopularDestinations from "@/components/home/PopularDestinations";
import StatsSection from "@/components/home/StatsSection";
import PopularTours from "@/components/home/PopularTours";
import TravelServices from "@/components/home/TravelServices";
import TestimonialsSection from "@/components/home/TestimonialsSection";
import WhyChooseUs from "@/components/home/WhyChooseUs";
import BlogNewsSection from "@/components/home/BlogNewsSection";
import InstagramFeed from "@/components/home/InstagramFeed";
import Newsletter from "@/components/home/Newsletter";
import CallToActions from "@/components/common/CallToActions";
import Footer from "@/components/common/Footer";
import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Travclicks - Your Adventure Starts Here",
  description: "Discover extraordinary destinations, create unforgettable memories, and explore the world with confidence. Join thousands of happy travelers who trust TravClan for their adventures.",
};

const LandingPage = () => {
  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      <LandingNavbar />
      {/* End Navigation */}

      <LandingHero />
      {/* End Landing Hero */}

      <AboutCompany />
      {/* End About Company */}

      <PopularDestinations />
      {/* End Popular Destinations */}

      <StatsSection />
      {/* End Stats Section */}

      <PopularTours />
      {/* End Popular Tours */}

      <WhyChooseUs />
      {/* End Why Choose Us */}

    

      <TestimonialsSection />
      {/* End Testimonials */}

              <BlogNewsSection />
        {/* End Blog News Section */}

        <InstagramFeed />
   

        {/* <TravelServices />


      <Newsletter />
   

      <CallToActions /> */}
     

      <Footer />
      {/* End Footer */}
    </>
  );
};

export default LandingPage; 