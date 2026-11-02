import TourProperties from "@/components/restaurants/tour-list-v1/TourProperties";
import Sidebar from "@/components/restaurants/tour-list-v1/Sidebar";
import MainFilterSearchBox from "@/components/restaurants/tour-list-v1/MainFilterSearchBox";
import MetaComponent from "@/components/common/MetaComponent";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TripWorkspaceShell from "@/components/workspace/TripWorkspaceShell";
import "@/styles/hotelListHero.css";

const metadata = {
  title: "Restaurant List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const TourListPage2 = () => {
  return (
    <div className="pw-trip-workspace">
      <MetaComponent meta={metadata} />

      <div className="header-margin" aria-hidden>
        <div className="container">
          <div className="hotel-list-lite-stack">
            <div className="hotel-list-lite-panel">
              <CustomStepper variant="lite" />
              <TourStatus variant="lite" />
            </div>
          </div>
        </div>
      </div>

      <div className="hotel-list-top" aria-hidden>
        <div className="container">
          <div className="hotel-list-hero">
            <MainFilterSearchBox layout="hero" />
          </div>
        </div>
      </div>

      <TripWorkspaceShell
        filters={
          <>
            <h3>Find dining</h3>
            <Sidebar />
          </>
        }
      >
        <div className="pw-results-head">
          <h2>Dining</h2>
          <small>Live inventory · restaurants</small>
        </div>
        <div className="row y-gap-30">
          <TourProperties />
        </div>
      </TripWorkspaceShell>
    </div>
  );
};

export default TourListPage2;
