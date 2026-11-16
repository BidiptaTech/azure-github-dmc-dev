import { useEffect, useRef, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import {
  LockOutlined,
  PublicOutlined,
  TimelineOutlined,
  EmailOutlined,
  Visibility,
  VisibilityOffOutlined,
  ArrowForward,
  PhoneIphone,
} from "@mui/icons-material";
import { CircularProgress } from "@mui/material";
import {
  setName,
  setEmail1,
  setAuthenticated,
  setAgentId,
} from "@/pages/login/loginSlice";
import { loginUser } from "@/slice/common/authSlices";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { setDmcFromAuth } from "@/slice/dmc/dmcSlice";
import { ClearLists } from "@/slice/common/TourlistSlice";
import { resetAgentList } from "@/slice/common/agentListSlice";
import "@/styles/dmc-signin.scss";

const CLOUD_FEATURES = [
  {
    label: "01 / CENTRALISE",
    title: "Bring your operation together",
    body: "Connect the original enquiry, its quote and every confirmed service in one booking record.",
    points: [
      "Centralize bookings, suppliers & inventory",
      "Automate approvals, vouchers & confirmations",
      "Real-time dashboards for ops and finance",
    ],
  },
  {
    label: "02 / COORDINATE",
    title: "One Platform for Faster Operations",
    body: "Standardize workflows across teams, reduce manual follow-ups, and give each booking a clear owner and status.",
    points: [
      "Role-based task flows & approvals",
      "Instant vouchers and supplier communication",
      "Less rework with centralized data",
    ],
  },
  {
    label: "03 / GROW",
    title: "Built for Profitable Growth",
    body: "Track costs and margins, respond to quotes faster, and make decisions from the same booking and finance records.",
    points: [
      "Margin visibility from quote to invoice",
      "Accurate reporting for management",
      "Scale without increasing overhead",
    ],
  },
];

const DESK_FEATURES = [
  {
    label: "01 / SALES",
    title: "Quote and convert",
    body: "Capture FIT and group enquiries, build priced itineraries, negotiate revisions with agents and track proposals through confirmation.",
    points: [
      "AI-assisted enquiry capture and draft replies",
      "Quote revisions and approvals",
    ],
  },
  {
    label: "02 / PRODUCTS",
    title: "Manage rates and inventory",
    body: "Maintain contracted hotels, attractions and on-ground suppliers. Build packages and manage rates, room blocks and service availability.",
    points: [
      "Hotels, activities, vehicles and guides",
      "Supplier rates and group allotments",
    ],
  },
  {
    label: "03 / OPERATIONS",
    title: "Coordinate every service",
    body: "Manage rooming lists and vouchers, assign jobs to drivers and guides in TravBiz, and share itinerary and pickup updates in TravHorse.",
    points: [
      "Role-based tasks, dispatch and live updates",
      "Connected apps for guests and local teams",
    ],
  },
  {
    label: "04 / FINANCE",
    title: "See costs and margins",
    body: "Track supplier costs, selling prices and margins across currencies. Give finance the booking and supplier details needed for invoicing and reporting.",
    points: [
      "Quote-to-invoice margin visibility",
      "Multi-currency costing and reporting",
    ],
  },
];

function OperationsNetwork({ paused, onTogglePause }) {
  return (
    <figure
      className="dmc-operations"
      id="dmc-operations"
      {...(paused ? { "data-paused": true } : {})}
      aria-labelledby="dmc-operations-title"
    >
      <div className="dmc-ops-header">
        <div>
          <span className="dmc-ops-indicator" />
          <span id="dmc-operations-title">TravOS Operations Network</span>
        </div>
        <div className="dmc-ops-controls">
          <span className="dmc-ops-caption">ILLUSTRATIVE SYSTEM VIEW</span>
          <button
            type="button"
            className="dmc-motion-control"
            onClick={onTogglePause}
          >
            {paused ? "Resume animation" : "Pause animation"}
          </button>
        </div>
      </div>

      <div className="dmc-system-view">
        <svg
          className="dmc-system-graphic"
          viewBox="0 0 720 306"
          preserveAspectRatio="none"
          aria-hidden="true"
        >
          <defs>
            <pattern
              id="dmc-system-grid"
              width="24"
              height="24"
              patternUnits="userSpaceOnUse"
            >
              <path
                d="M24 0H0V24"
                fill="none"
                stroke="#6f9bfd"
                strokeOpacity=".12"
              />
            </pattern>
            <radialGradient id="dmc-core-light">
              <stop stopColor="#4d6bff" stopOpacity=".56" />
              <stop offset="1" stopColor="#1c4bce" stopOpacity="0" />
            </radialGradient>
            <linearGradient id="dmc-trace-light">
              <stop stopColor="#9ba4ff" />
              <stop offset=".55" stopColor="#728bfc" />
              <stop offset="1" stopColor="#72efec" />
            </linearGradient>
            <path
              id="dmc-left-tracks"
              d="M136 64H217L267 115H305 M136 153H305 M136 242H217L267 191H305"
            />
            <path
              id="dmc-right-tracks"
              d="M415 115H453L503 64H584 M415 153H584 M415 191H453L503 242H584"
            />
          </defs>
          <rect width="720" height="306" fill="url(#dmc-system-grid)" />
          <ellipse
            cx="360"
            cy="153"
            rx="222"
            ry="147"
            fill="url(#dmc-core-light)"
          />
          <path
            className="dmc-minor-circuit"
            d="M40 26h183l39 35m-222 219h183l39-35M680 26H497l-39 35m222 219H497l-39-35M360 9v44m0 200v44"
            fill="none"
            stroke="#7796d9"
            opacity=".26"
          />
          <g fill="none" stroke="#789bfb" opacity=".36">
            <ellipse
              cx="360"
              cy="153"
              rx="191"
              ry="75"
              transform="rotate(-16 360 153)"
            />
            <ellipse cx="360" cy="153" rx="153" ry="116" />
            <ellipse
              cx="360"
              cy="153"
              rx="125"
              ry="125"
              strokeDasharray="2 9"
            />
          </g>
          <g fill="none" stroke="url(#dmc-trace-light)">
            <use className="dmc-trace-base" href="#dmc-left-tracks" />
            <use className="dmc-trace-base" href="#dmc-right-tracks" />
            <use
              className="dmc-trace-signal dmc-signal-in"
              href="#dmc-left-tracks"
            />
            <use
              className="dmc-trace-signal dmc-signal-out"
              href="#dmc-right-tracks"
            />
          </g>
          <g>
            <circle
              cx="360"
              cy="153"
              r="98"
              fill="none"
              stroke="#6f90f8"
              opacity=".31"
            />
            <circle
              className="dmc-reactor-orbit"
              cx="360"
              cy="153"
              r="88"
              fill="none"
              stroke="#a5b7ff"
              strokeDasharray="98 28 17 23 38 349"
              strokeWidth="2"
            />
            <circle
              className="dmc-reactor-orbit reverse"
              cx="360"
              cy="153"
              r="76"
              fill="none"
              stroke="#71ebe6"
              strokeDasharray="28 13 46 12 16 12 36 314"
              strokeWidth="2"
            />
            <circle
              className="dmc-reactor-pulse"
              cx="360"
              cy="153"
              r="64"
              fill="none"
              stroke="#72e3f4"
              strokeWidth="2"
            />
          </g>
        </svg>

        <div className="dmc-system-core" aria-hidden="true">
          <svg className="dmc-brand-core" viewBox="0 0 460 470">
            <defs>
              <linearGradient id="dmc-core-pink" x1="28%" y1="5%" x2="75%" y2="100%">
                <stop stopColor="#ff95df" />
                <stop offset=".63" stopColor="#fc77cc" />
                <stop offset="1" stopColor="#ec58b5" />
              </linearGradient>
              <linearGradient id="dmc-core-green" x1="80%" y1="9%" x2="28%" y2="100%">
                <stop stopColor="#e3ff9c" />
                <stop offset=".54" stopColor="#b9f691" />
                <stop offset="1" stopColor="#83de8b" />
              </linearGradient>
              <filter id="dmc-blue-network-detail" colorInterpolationFilters="sRGB">
                <feGaussianBlur
                  in="SourceGraphic"
                  stdDeviation="2.2"
                  result="softened"
                />
                <feComposite
                  in="SourceGraphic"
                  in2="softened"
                  operator="arithmetic"
                  k2="1.75"
                  k3="-.75"
                />
              </filter>
              <clipPath id="dmc-blue-edge">
                <circle cx="165" cy="226" r="155.4" />
              </clipPath>
            </defs>
            <g className="dmc-disc-beat dmc-traveller-disc">
              <g className="dmc-disc-rotation">
                <circle cx="315" cy="144" r="139" fill="url(#dmc-core-pink)" />
              </g>
            </g>
            <g className="dmc-disc-beat dmc-provider-disc">
              <g className="dmc-disc-rotation">
                <circle cx="312" cy="326" r="137" fill="url(#dmc-core-green)" />
              </g>
            </g>
            <g className="dmc-disc-beat dmc-dmc-disc">
              <g className="dmc-disc-rotation" clipPath="url(#dmc-blue-edge)">
                <image
                  filter="url(#dmc-blue-network-detail)"
                  href="/Images/dmc-signin/travclicks-core-reference.jpeg"
                  x="-309"
                  y="-280"
                  width="1024"
                  height="1024"
                />
                <g className="dmc-blue-pulses">
                  <circle cx="111" cy="186" r="8" />
                  <circle cx="169" cy="215" r="8" />
                  <circle cx="238" cy="210" r="8" />
                  <circle cx="211" cy="255" r="5" />
                  <circle cx="168" cy="271" r="5" />
                </g>
              </g>
            </g>
          </svg>
          <strong>TravOS</strong>
        </div>

        <ul className="dmc-system-modules" aria-label="Connected TravOS modules">
          <li className="dmc-module left upper">
            <span className="dmc-module-dot" />
            Enquiries
          </li>
          <li className="dmc-module left middle">
            <span className="dmc-module-dot" />
            Quotations
          </li>
          <li className="dmc-module left lower">
            <span className="dmc-module-dot" />
            Bookings
          </li>
          <li className="dmc-module right upper">
            <span className="dmc-module-dot" />
            Suppliers
          </li>
          <li className="dmc-module right middle">
            <span className="dmc-module-dot" />
            Dispatch
          </li>
          <li className="dmc-module right lower">
            <span className="dmc-module-dot" />
            Trip updates
          </li>
        </ul>
      </div>

      <ul className="dmc-core-key" aria-label="Travclicks circle colours">
        <li>
          <span className="dmc-key-dot dmc-key-dmc" />
          DMC operators
        </li>
        <li>
          <span className="dmc-key-dot dmc-key-traveller" />
          Travellers
        </li>
        <li>
          <span className="dmc-key-dot dmc-key-provider" />
          On-ground service providers
        </li>
      </ul>

      <figcaption className="dmc-app-strip">
        <div className="dmc-connected-app">
          <span className="dmc-app-icon guest">
            <PhoneIphone sx={{ fontSize: 15 }} />
          </span>
          <div>
            <strong>
              TravHorse <span>GUEST APP · iOS & Android</span>
            </strong>
            <p>Itineraries, pickup alerts and on-trip support</p>
          </div>
        </div>
        <div className="dmc-connected-app">
          <span className="dmc-app-icon biz">
            <PhoneIphone sx={{ fontSize: 15 }} />
          </span>
          <div>
            <strong>
              TravBiz <span>SERVICE DELIVERY APP · iOS & Android</span>
            </strong>
            <p>Assigned jobs and updates for drivers, guides and service partners</p>
          </div>
        </div>
      </figcaption>
    </figure>
  );
}

function DmcLoginDialog({ open, onClose }) {
  const dialogRef = useRef(null);
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { loginStatus, loginError } = useSelector((state) => state.auth);

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const busy = isLoading || loginStatus === "loading";

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    if (open && !dialog.open) dialog.showModal();
    if (!open && dialog.open) dialog.close();
  }, [open]);

  useEffect(() => {
    if (!open) {
      setEmail("");
      setPassword("");
      setShowPassword(false);
      setIsLoading(false);
    }
  }, [open]);

  const handleLogin = async (event) => {
    event.preventDefault();
    setIsLoading(true);
    dispatch(resetPackages());
    dispatch(resetAgentList());
    dispatch(ClearLists());

    const result = await dispatch(loginUser({ email, password }));

    if (loginUser.fulfilled.match(result)) {
      const {
        agent_id: agentId,
        userRole,
        dmcId,
        dmcLogo,
        dmcCompanyName,
      } = result.payload;

      dispatch(setAgentId(agentId));
      dispatch(setName(email.split("@")[0]));
      dispatch(setEmail1(email));
      dispatch(setAuthenticated(true));

      if (dmcId && userRole !== "Agent") {
        dispatch(
          setDmcFromAuth({
            dmcId,
            dmcLogo,
            dmcCompanyName,
          })
        );
      }

      onClose();
      navigate("/app");
    } else {
      setIsLoading(false);
    }
  };

  return (
    <dialog
      ref={dialogRef}
      className="dmc-portal-dialog"
      aria-labelledby="dmc-signin-title"
      onClose={onClose}
      onClick={(event) => {
        if (event.target === dialogRef.current && !busy) onClose();
      }}
    >
      <button
        type="button"
        className="dmc-portal-close"
        aria-label="Close sign-in"
        onClick={onClose}
        disabled={busy}
      >
        ×
      </button>

      <div className="dmc-card-label">
        <span className="dmc-lock-badge">
          <LockOutlined sx={{ fontSize: 16 }} />
        </span>
        <span>TRAVCLICKS CLOUD</span>
      </div>

      <h2 id="dmc-signin-title">Sign in to Travclicks Cloud</h2>
      <p className="dmc-signin-intro">
        Access your organisation’s TravOS DMC workspace.
      </p>

      <form onSubmit={handleLogin} autoComplete="on">
        <label className="dmc-field-label" htmlFor="dmc-login-email">
          Email
        </label>
        <div className="dmc-field">
          <EmailOutlined sx={{ fontSize: 17, pointerEvents: "none" }} />
          <input
            id="dmc-login-email"
            name="email"
            type="email"
            required
            placeholder="Enter your email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            autoComplete="username"
            readOnly={false}
          />
        </div>

        <label className="dmc-field-label" htmlFor="dmc-login-password">
          Password
        </label>
        <div className="dmc-field">
          <LockOutlined sx={{ fontSize: 17, pointerEvents: "none" }} />
          <input
            id="dmc-login-password"
            name="password"
            type={showPassword ? "text" : "password"}
            required
            placeholder="Enter your password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            autoComplete="current-password"
            readOnly={false}
          />
          <button
            type="button"
            className="dmc-show-password"
            aria-label={showPassword ? "Hide password" : "Show password"}
            onClick={() => setShowPassword((value) => !value)}
          >
            {showPassword ? (
              <VisibilityOffOutlined sx={{ fontSize: 17 }} />
            ) : (
              <Visibility sx={{ fontSize: 17 }} />
            )}
          </button>
        </div>

        {loginError && (
          <p className="dmc-result dmc-result-error" role="alert">
            {loginError}
          </p>
        )}

        <button className="dmc-signin-submit" type="submit" disabled={busy}>
          {busy ? (
            <>
              <CircularProgress size={18} sx={{ color: "white" }} />
              Signing in...
            </>
          ) : (
            <>
              Sign In <ArrowForward sx={{ fontSize: 18 }} />
            </>
          )}
        </button>
      </form>

      <div className="dmc-card-footer">
        <LockOutlined sx={{ fontSize: 13, color: "#3159e8" }} />
        <span>
          Protected by enterprise-grade encryption & secure authentication.
        </span>
      </div>
    </dialog>
  );
}

function DmcSignIn() {
  const [paused, setPaused] = useState(false);
  const [loginOpen, setLoginOpen] = useState(false);

  return (
    <div className="dmc-signin-page">
      <header className="dmc-topbar">
        <div className="dmc-nav-shell">
          <Link className="dmc-portal-home" to="/" aria-label="Travclicks homepage">
            <img
              className="dmc-header-brand"
              src="/Images/logo.png"
              alt="Travclicks"
              width="240"
              height="96"
            />
          </Link>
          <span className="dmc-header-product">
            TravOS <span>DMC workspace</span>
          </span>
          <button
            type="button"
            className="dmc-login-btn"
            onClick={() => setLoginOpen(true)}
          >
            DMC Log In
          </button>
        </div>
      </header>

      <main className="dmc-workspace">
        <section className="dmc-intro" aria-labelledby="dmc-platform-title">
          <p className="dmc-eyebrow">
            <span /> For DMCs, inbound travel agents & tour operators
          </p>
          <h1 id="dmc-platform-title">
            Run Every Moving Part <em>of a Traveler’s Journey.</em>
          </h1>
          <p className="dmc-intro-lede">
            From the first enquiry to the final transfer, manage quotations,
            bookings, suppliers and service delivery in one DMC workspace.
          </p>
          <p className="dmc-cloud-byline">
            <span /> Travclicks Cloud
          </p>
        </section>

        <OperationsNetwork
          paused={paused}
          onTogglePause={() => setPaused((value) => !value)}
        />

        <section className="dmc-cloud-platform" aria-labelledby="dmc-cloud-title">
          <div className="dmc-cloud-trust" aria-label="Travclicks Cloud platform">
            <p>
              <LockOutlined sx={{ fontSize: 18 }} /> Enterprise-grade security
            </p>
            <p>
              <PublicOutlined sx={{ fontSize: 18 }} /> Global infrastructure
            </p>
            <p>
              <TimelineOutlined sx={{ fontSize: 18 }} /> Real-time operations
              platform
            </p>
          </div>

          <div className="dmc-cloud-heading">
            <p className="dmc-eyebrow">
              <span /> Travclicks Cloud
            </p>
            <h2 id="dmc-cloud-title">
              The Smart Way to <em>Run Your DMC</em>
            </h2>
            <p>
              Travclicks Cloud brings suppliers, reservations, finance and
              operations into one secure platform so your teams move faster and
              serve better.
            </p>
          </div>

          <div className="dmc-cloud-grid">
            {CLOUD_FEATURES.map((feature) => (
              <article className="dmc-cloud-feature" key={feature.label}>
                <span className="dmc-cloud-number">{feature.label}</span>
                <h3>{feature.title}</h3>
                <p>{feature.body}</p>
                <ul>
                  {feature.points.map((point) => (
                    <li key={point}>{point}</li>
                  ))}
                </ul>
              </article>
            ))}
          </div>

          <section className="dmc-cloud-desk" aria-labelledby="dmc-desk-title">
            <div className="dmc-cloud-heading">
              <p className="dmc-eyebrow">
                <span /> Inside TravOS
              </p>
              <h2 id="dmc-desk-title">
                One booking connects <em>every team.</em>
              </h2>
              <p>
                Sales, product, operations and finance work from the same trip
                record, with the details each team needs to do its job.
              </p>
            </div>

            <div className="dmc-cloud-desk-grid">
              {DESK_FEATURES.map((feature) => (
                <article className="dmc-cloud-feature" key={feature.label}>
                  <span className="dmc-cloud-number">{feature.label}</span>
                  <h3>{feature.title}</h3>
                  <p>{feature.body}</p>
                  <ul>
                    {feature.points.map((point) => (
                      <li key={point}>{point}</li>
                    ))}
                  </ul>
                </article>
              ))}
            </div>
          </section>

          <div className="dmc-cloud-audience">
            <div>
              <p className="dmc-eyebrow">Powering inbound travel</p>
              <h2>Built for DMCs, inbound travel agents and tour operators.</h2>
              <p>
                Give reservations, operations and finance a shared view of FIT
                and group bookings, from proposal to final service.
              </p>
            </div>
            <a
              className="dmc-demo-link"
              href="mailto:admin@travclicks.com?subject=TravOS%20demo%20request"
            >
              Request a Demo →
            </a>
          </div>
        </section>
      </main>

      <DmcLoginDialog open={loginOpen} onClose={() => setLoginOpen(false)} />
    </div>
  );
}

export default DmcSignIn;
