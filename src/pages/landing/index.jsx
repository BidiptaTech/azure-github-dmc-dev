import LandingHero from "@/components/hero/landing-hero";
import LandingNavbar from "@/components/header/LandingNavbar";
import AboutCompany from "@/components/home/AboutCompany";
import PopularDestinations from "@/components/home/PopularDestinations";
import PopularTours from "@/components/home/PopularTours";
import {
  SUPPORTED_COUNTRIES,
  LANDING_HERO_SLIDES,
  LANDING_DESTINATIONS,
  LANDING_TOURS,
} from "./landingCountries";
import TestimonialsSection from "@/components/home/TestimonialsSection";
import WhyChooseUs from "@/components/home/WhyChooseUs";
import Footer from "@/components/common/Footer";
import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "TravClicks — B2B Destination Management",
  description:
    "Professional destination management across Singapore, Indonesia, and Australia. Hotels, transfers, guides, and packages for travel trade partners.",
};

const LandingPage = () => {
  return (
    <>
      <MetaComponent meta={metadata} />

      <LandingNavbar />

      <LandingHero heroSlides={LANDING_HERO_SLIDES} />

      <AboutCompany />

      <PopularDestinations
        countries={SUPPORTED_COUNTRIES}
        destinations={LANDING_DESTINATIONS}
      />

      <PopularTours tours={LANDING_TOURS} />

      <WhyChooseUs />

      <TestimonialsSection />

      <Footer />
    </>
  );
};

export default LandingPage;
