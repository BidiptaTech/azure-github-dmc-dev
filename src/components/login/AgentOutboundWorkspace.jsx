import { useState } from "react";
import DemoRequestDialog from "@/components/login/DemoRequestDialog";

const WORKFLOW = [
  {
    label: "01 / SALES",
    title: "Sell FIT & group travel",
    body: "Create a client brief, track open enquiries and build single- or multi-city programmes for individuals or groups.",
  },
  {
    label: "02 / PRODUCTS & QUOTES",
    title: "Build and negotiate proposals",
    body: "Start with a ready-made package or tailor a trip around stays, activities and transfers. Agree on rates, inclusions and itinerary changes with your DMC.",
  },
  {
    label: "03 / OPERATIONS",
    title: "Manage bookings & handover",
    body: "Keep confirmed services, traveller requirements and booking details together. Share on-trip details and updates with clients through TravHorse.",
  },
  {
    label: "04 / FINANCE",
    title: "Manage booking payments",
    body: "Review payment options, keep track of payment status and check available wallet or credit arrangements where offered.",
  },
];

const AgentOutboundWorkspace = () => {
  const [demoOpen, setDemoOpen] = useState(false);

  return (
    <section className="agent-overview" aria-labelledby="agent-overview-title">
      <p className="agent-eyebrow">
        <span /> THE OUTBOUND WORKSPACE
      </p>
      <h2 id="agent-overview-title">From the first brief to the final booking.</h2>
      <p className="agent-overview-lede">
        Manage a single tailor-made FIT trip or coordinate group departures across
        multiple destinations with the same DMC workflow.
      </p>

      <div className="agent-workflow">
        {WORKFLOW.map((item) => (
          <article key={item.label}>
            <span>{item.label}</span>
            <h3>{item.title}</h3>
            <p>{item.body}</p>
          </article>
        ))}
      </div>

      <button
        type="button"
        className="agent-demo-link"
        onClick={() => setDemoOpen(true)}
      >
        Request a Demo <span aria-hidden="true">→</span>
      </button>

      <DemoRequestDialog open={demoOpen} onClose={() => setDemoOpen(false)} />
    </section>
  );
};

export default AgentOutboundWorkspace;
