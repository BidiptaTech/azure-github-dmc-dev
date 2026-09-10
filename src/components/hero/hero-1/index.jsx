import MainFilterSearchBox from "./MainFilterSearchBox";

const sideServices = [
  { icon: "icon-bed", label: "Hotels", color: "#3554D1", top: "18%", delay: "0s" },
  { icon: "icon-tickets", label: "Attractions", color: "#0d9488", top: "42%", delay: "0.4s" },
  { icon: "icon-car", label: "Transfers", color: "#c2410c", top: "66%", delay: "0.8s" },
];

const sideServicesRight = [
  { icon: "icon-man", label: "Guides", color: "#1d4ed8", top: "22%", delay: "0.2s" },
  { icon: "icon-food", label: "Dining", color: "#b45309", top: "46%", delay: "0.6s" },
  { icon: "icon-yatch", label: "Activities", color: "#0369a1", top: "70%", delay: "1s" },
];

const bottomServices = [
  { icon: "icon-bed", label: "Hotels", color: "#3554D1" },
  { icon: "icon-tickets", label: "Attractions", color: "#0d9488" },
  { icon: "icon-car", label: "Transfers", color: "#c2410c" },
  { icon: "icon-man", label: "Guides", color: "#1d4ed8" },
  { icon: "icon-food", label: "Dining", color: "#b45309" },
  { icon: "icon-yatch", label: "Activities", color: "#0369a1" },
];

const ServiceChip = ({ icon, label, color, style = {}, float = false, delay = "0s" }) => (
  <div
    className={float ? "hero-service-float d-none d-xl-flex" : "d-inline-flex"}
    style={{
      alignItems: "center",
      gap: 10,
      padding: "10px 14px",
      borderRadius: 14,
      background: "rgba(255,255,255,0.92)",
      border: "1px solid rgba(15, 23, 42, 0.06)",
      boxShadow: "0 10px 28px rgba(15, 23, 42, 0.08)",
      backdropFilter: "blur(6px)",
      animation: float ? `heroFloat 5.5s ease-in-out infinite` : undefined,
      animationDelay: float ? delay : undefined,
      ...style,
    }}
  >
    <span
      style={{
        width: 36,
        height: 36,
        borderRadius: 10,
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        background: `${color}14`,
        color,
        flexShrink: 0,
      }}
    >
      <i className={`${icon} text-16`} />
    </span>
    <span
      style={{
        fontSize: 13,
        fontWeight: 600,
        color: "#0f172a",
        whiteSpace: "nowrap",
      }}
    >
      {label}
    </span>
  </div>
);

const index = () => {
  return (
    <section
      className="masthead -type-1 z-5"
      style={{
        position: "relative",
        minHeight: "auto",
        paddingTop: 0,
        paddingBottom: 8,
        background: "#ffffff",
        overflow: "hidden",
      }}
    >
      <style>{`
        @keyframes heroFloat {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-8px); }
        }
        .hero-service-float {
          position: absolute;
          z-index: 1;
          pointer-events: none;
        }
        .hero-search-stage {
          position: relative;
        }
        .hero-search-stage::before,
        .hero-search-stage::after {
          content: "";
          position: absolute;
          width: 180px;
          height: 180px;
          border-radius: 50%;
          pointer-events: none;
          z-index: 0;
        }
        .hero-search-stage::before {
          left: -40px;
          bottom: 20px;
          background: radial-gradient(circle, rgba(53, 84, 209, 0.08) 0%, transparent 70%);
        }
        .hero-search-stage::after {
          right: -40px;
          bottom: 40px;
          background: radial-gradient(circle, rgba(13, 148, 136, 0.08) 0%, transparent 70%);
        }
      `}</style>

      {/* Asymmetric layered wave — not the Goibibo oval bulge */}
      <div
        aria-hidden="true"
        className="hero-curve-band"
        style={{
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          height: 420,
          zIndex: 0,
          pointerEvents: "none",
          overflow: "hidden",
        }}
      >
        {/* Solid wave fill */}
        <svg
          viewBox="0 0 1440 420"
          preserveAspectRatio="none"
          style={{
            position: "absolute",
            inset: 0,
            width: "100%",
            height: "100%",
            display: "block",
          }}
        >
          <defs>
            <linearGradient id="heroWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stopColor="#1e3a8a" />
              <stop offset="48%" stopColor="#3554D1" />
              <stop offset="100%" stopColor="#4c6fff" />
            </linearGradient>
          </defs>
          {/* Main asymmetric shoreline curve */}
          <path
            fill="url(#heroWaveGrad)"
            d="M0,0 H1440 V280 C1280,348 1120,390 960,372 C780,348 640,280 480,264 C300,246 160,300 0,348 Z"
          />
          {/* Soft secondary crest for depth */}
          <path
            fill="rgba(255,255,255,0.08)"
            d="M0,248 C180,200 320,186 480,224 C680,270 820,328 1020,312 C1180,300 1320,262 1440,226 V280 C1280,348 1120,390 960,372 C780,348 640,280 480,264 C300,246 160,300 0,348 Z"
          />
        </svg>
      </div>

      <div className="container" style={{ position: "relative", zIndex: 2 }}>
        <div className="row justify-center">
          <div className="col-xl-11 col-12">
            <div
              className="text-center"
              style={{ paddingTop: 56, paddingBottom: 28 }}
            >
              <h1
                className="text-50 lg:text-36 md:text-28"
                data-aos="fade-up"
                style={{ color: "#ffffff", fontWeight: 700 }}
              >
                Plan &amp; book tours for your clients
              </h1>
              <p
                className="mt-10 md:mt-15"
                data-aos="fade-up"
                data-aos-delay="100"
                style={{ color: "rgba(255,255,255,0.9)" }}
              >
                Set destinations, dates and pax — then add hotels, transfers and experiences
              </p>
            </div>

            <div
              className="hero-search-stage tabs -underline js-tabs"
              data-aos="fade-up"
              data-aos-delay="200"
              style={{
                marginTop: 8,
                paddingBottom: 8,
                paddingLeft: 0,
                paddingRight: 0,
              }}
            >
              <div
                className="hero-search-card-wrap"
                style={{
                  position: "relative",
                  zIndex: 2,
                  maxWidth: 980,
                  margin: "0 auto",
                }}
              >
                {/* Side chips hug the search card */}
                {sideServices.map((item) => (
                  <ServiceChip
                    key={`L-${item.label}`}
                    {...item}
                    float
                    style={{ left: -132, top: item.top }}
                  />
                ))}
                {sideServicesRight.map((item) => (
                  <ServiceChip
                    key={`R-${item.label}`}
                    {...item}
                    float
                    style={{ right: -132, top: item.top }}
                  />
                ))}

                <MainFilterSearchBox />
              </div>

              {/* Bottom service strip — tight under the card */}
              <div
                className="d-flex flex-wrap justify-center"
                style={{
                  gap: 10,
                  marginTop: 4,
                  position: "relative",
                  zIndex: 2,
                }}
                data-aos="fade-up"
                data-aos-delay="300"
              >
                {bottomServices.map((item) => (
                  <ServiceChip key={`B-${item.label}`} {...item} />
                ))}
              </div>

              <p
                className="text-center"
                style={{
                  marginTop: 6,
                  marginBottom: 0,
                  fontSize: 13,
                  color: "#64748b",
                  position: "relative",
                  zIndex: 2,
                }}
              >
                Build complete itineraries — hotels, attractions, transfers, guides &amp; more
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default index;
