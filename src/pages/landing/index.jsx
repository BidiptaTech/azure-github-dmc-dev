import "@/styles/landing-concept.scss";
import MetaComponent from "@/components/common/MetaComponent";
import { LandingSymbols } from "@/components/landing/LandingIcons";
import LandingHeader from "@/components/landing/LandingHeader";
import LandingHero from "@/components/landing/LandingHero";
import {
  LandingJourney,
  LandingPlatform,
  LandingMobile,
  LandingDestinations,
  LandingProgrammes,
  LandingEndCta,
  LandingFooter,
} from "@/components/landing/LandingSections";

const metadata = {
  title: "Travclicks | Travel operations platform",
  description:
    "Build a client trip, work with your DMC, and keep travellers informed as local teams deliver the itinerary. Travel operations technology for agents and DMCs.",
};

const LandingPage = () => {
  return (
    <div className="landing-concept">
      <MetaComponent meta={metadata} />
      <LandingSymbols />
      <LandingHeader />
      <LandingHero />
      <LandingJourney />
      <LandingPlatform />
      <LandingMobile />
      <LandingDestinations />
      <LandingProgrammes />
      <LandingEndCta />
      <LandingFooter />
    </div>
  );
};

export default LandingPage;
