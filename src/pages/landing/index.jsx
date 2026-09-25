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
  title: "Travclicks — AI-Powered Travel Technology",
  description:
    "AI-powered travel solutions for travel agencies. Automate operations, accelerate sales, and deliver real-time traveler experiences across Singapore, Malaysia, Thailand, Indonesia, Australia, New Zealand, Laos, Cambodia, and UAE.",
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
