import { useState } from "react";
import MainFilterSearchBox from "@/components/tour-list/tour-list-v1/MainFilterSearchBox";
import TourProperties from "@/components/tour-list/tour-list-v1/TourProperties";
import Sidebar from "@/components/tour-list/tour-list-v1/Sidebar";
import MetaComponent from "@/components/common/MetaComponent";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TripWorkspaceShell from "@/components/workspace/TripWorkspaceShell";

const metadata = {
  title: "Attraction List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const TourListPage1 = () => {
  const [filters, setFilters] = useState({
    priceRange: { min: 0, max: 1000 },
    reviewScores: [],
    timeOfDay: [],
  });

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

      <TripWorkspaceShell
        searchBox={<MainFilterSearchBox layout="workspace" />}
        filters={
          <>
            <h3>Find experiences</h3>
            <Sidebar />
          </>
        }
      >
        <div className="pw-results-head">
          <h2>Attraction & Experiences</h2>
          <small>Live inventory · attractions</small>
        </div>
        <div className="row y-gap-30">
          <TourProperties />
        </div>
      </TripWorkspaceShell>
    </div>
  );
};

export default TourListPage1;
