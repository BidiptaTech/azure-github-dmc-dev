import { useSelector } from "react-redux";
import Index1 from "./filter-box1/index1";
import Index2 from "./filter-box2/index2";
import Index1Zone from "./filter-box1/index1zone";
import Index2Zone from "./filter-box2/index2Zone";

const SidebarRight1 = ({ activity }) => {
  const type = useSelector((state) => state.pickupDrop.selectionType);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  console.log("abcd", type);

  return (
    <div className="d-flex justify-end js-pin-content">
      <div className="w-360 lg:w-full d-flex flex-column items-center">
        <div className="px-30 py-30 rounded-4 border-light bg-white shadow-4">
          <div className="row y-gap-20 pt-30">
            {type === "Entry Port" && portZoneType === "" ? (
              <Index1 />
            ) : type === "Entry Port" && portZoneType === "zone" ? (
              <Index1Zone />
            ) : type === "Exit Port" && portZoneType === "" ? (
              <Index2 />
            ) : type === "Exit Port" && portZoneType === "zone" ? (
              <Index2Zone />
            ) : null}
          </div>

          {/* <div className="d-flex items-center pt-20">
            <div className="size-40 flex-center bg-light-2 rounded-full">
              <i className="icon-heart text-16 text-green-2" />
            </div>
            <div className="text-14 lh-16 ml-10">
              94% of travelers recommend this experience
            </div>
          </div> */}
        </div>

        {/* <div className="px-30">
          <div className="text-14 text-light-1 mt-30">
            Not sure? You can cancel this reservation up to 24 hours in advance
            for a full refund.
          </div>
        </div> */}
      </div>
    </div>
  );
};

export default SidebarRight1;
