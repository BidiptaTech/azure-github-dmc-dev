import React from "react";
import DashboardPage from "../../../../components/dashboard/dashboard/db-dashboard";

import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Dashboard || TravClicks - Travel Technology Transformed",
  description: "TravClicks - Travel Technology Transformed",
};

export default function DBDashboard() {
  return (
    <>
    <div className="header-margin">
      <MetaComponent meta={metadata} />
      <DashboardPage />
      </div>
    </>
  );
}
