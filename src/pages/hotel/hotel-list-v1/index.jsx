import { useSelector } from "react-redux";
import MainFilterSearchBox from "@/components/hotel-list/hotel-list-v1/MainFilterSearchBox";
import TopHeaderFilter from "@/components/hotel-list/hotel-list-v1/TopHeaderFilter";
import HotelProperties from "@/components/hotel-list/hotel-list-v1/HotelProperties";
import Sidebar from "@/components/hotel-list/hotel-list-v1/Sidebar";
import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import TripWorkspaceShell from "@/components/workspace/TripWorkspaceShell";

const metadata = {
  title: "Hotel List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const HotelListPage1 = () => {
  const hotelsdata = useSelector((state) => state.hotels);
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  const searchState = useSelector((state) => state.hotels?.searchState);
  const hotelCount = Array.isArray(hotelsdata?.hotels)
    ? hotelsdata.hotels.length
    : 0;
  const adults = tourdetails?.adult || searchState?.guests?.adults || 2;
  const children = tourdetails?.child || searchState?.guests?.children || 0;

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
            <h3>Find your client&apos;s stay</h3>
            <Sidebar />
          </>
        }
      >
        <div className="pw-results-head">
          <h2>
            {hotelCount > 0
              ? `${hotelCount} hotel${hotelCount === 1 ? "" : "s"} found`
              : "Hotels"}
          </h2>
          <small>
            Live inventory · {adults} adult{adults === 1 ? "" : "s"}
            {children
              ? `, ${children} child${children === 1 ? "" : "ren"}`
              : ""}
          </small>
        </div>
        <TopHeaderFilter hotelsdata={hotelsdata} />
        <div className="row y-gap-30">
          <HotelProperties />
        </div>
      </TripWorkspaceShell>
    </div>
  );
};

export default HotelListPage1;
