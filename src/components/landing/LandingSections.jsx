import { useState } from "react";
import { Link } from "react-router-dom";
import { Icon } from "./LandingIcons";
import {
  LANDING_MARKETS,
  LANDING_PROGRAMMES,
} from "@/pages/landing/landingCountries";

export const LandingJourney = () => (
  <section className="section wrap" id="journey">
    <div className="section-head">
      <div>
        <p className="kicker">01 / HOW IT CONNECTS</p>
        <h2>One itinerary, from quote to pickup.</h2>
      </div>
      <p>
        See what happens to a trip after you send the enquiry. Each team works
        from the details it needs to deliver the same journey.
      </p>
    </div>
    <ol className="journey-steps">
      <li>
        <span className="step-index">01</span>
        <h3>Build or request</h3>
        <p>
          Start with a ready-made package, build a single- or multi-city trip,
          or ask your DMC for a tailor-made quote. Set dates and traveller
          details for each destination.
        </p>
        <span className="tagline">AGENT PORTAL</span>
      </li>
      <li>
        <span className="step-index">02</span>
        <h3>Quote and confirm</h3>
        <p>
          Your DMC reviews the brief, prepares the itinerary and coordinates
          hotels, activities, transfers and guides. You have a proposal to
          review with your client.
        </p>
        <span className="tagline">TRAVOS</span>
      </li>
      <li>
        <span className="step-index">03</span>
        <h3>Assign and deliver</h3>
        <p>
          The DMC assigns services to local teams. Drivers and guides receive
          job details on mobile and can update their progress.
        </p>
        <span className="tagline">TRAVOS + TRAVBIZ</span>
      </li>
      <li>
        <span className="step-index">04</span>
        <h3>Keep clients informed</h3>
        <p>
          Travellers can see their day-by-day itinerary, pickup details and
          service updates, with messaging when they need help.
        </p>
        <span className="tagline">TRAVHORSE</span>
      </li>
    </ol>
  </section>
);

export const LandingPlatform = () => (
  <section className="section band" id="platform">
    <div className="wrap">
      <div className="section-head">
        <div>
          <p className="kicker">02 / WHY TRAVCLICKS</p>
          <h2>Know what happens behind your booking.</h2>
        </div>
        <p>
          Give your DMC a clear brief, keep track of your enquiries and see how
          the services your client booked are handed over to local teams.
        </p>
      </div>
      <div className="capabilities">
        <article>
          <span className="icon-block">
            <Icon name="route" />
          </span>
          <h3>Agent portal</h3>
          <p>
            Plan multi-city FIT and group journeys, request custom quotes and
            keep enquiries, bookings and payment options in one place.
          </p>
          <span className="tagline">PLAN, QUOTE &amp; MANAGE</span>
        </article>
        <article>
          <span className="icon-block teal">
            <Icon name="stack" />
          </span>
          <h3>TravOS</h3>
          <p>
            Your DMC’s operating system connects quotes and bookings to hotel
            and activity inventory, rooming lists, vouchers, suppliers and
            service assignments.
          </p>
          <span className="tagline">DMC OPERATING SYSTEM</span>
        </article>
        <article>
          <span className="icon-block purple">
            <Icon name="spark" />
          </span>
          <h3>AI Auto Responder</h3>
          <p>
            Turns incoming emails into structured enquiries and drafts replies
            and quotations for the DMC team to review.
          </p>
          <span className="tagline">FASTER ENQUIRY HANDLING</span>
        </article>
      </div>
    </div>
  </section>
);

export const LandingMobile = () => (
  <section className="section wrap" id="mobile">
    <div className="section-head">
      <div>
        <p className="kicker">03 / THE MOBILE EXPERIENCE</p>
        <h2>Two apps. One connected trip.</h2>
      </div>
      <p>
        The people taking the trip and the teams delivering it each get a mobile
        view designed around what they need on the day.
      </p>
    </div>
    <div className="app-grid">
      <article className="app-card">
        <div className="app-copy">
          <span className="app-role">FOR YOUR CLIENTS</span>
          <h3>TravHorse</h3>
          <p>
            Give clients one place to check what’s next and get help while they
            travel.
          </p>
          <ul>
            <li>Daily itinerary and service details</li>
            <li>Pickup alerts and trip updates</li>
            <li>Messaging and emergency contacts</li>
          </ul>
          <span className="app-platform">
            <Icon name="phone" /> iOS &amp; Android
          </span>
        </div>
        <div className="phone-preview" aria-label="Illustrative TravHorse app screen">
          <div className="phone-header">
            TravHorse <span>TRAVELLER APP</span>
          </div>
          <div className="phone-body">
            <small>YOUR ITINERARY</small>
            <strong>Today’s journey</strong>
            <div className="phone-service">
              <Icon name="pin" />
              <div>
                <b>Airport pickup</b>
                <span>Changi Airport · 10:30</span>
              </div>
            </div>
            <div className="phone-state green">Driver has arrived</div>
          </div>
        </div>
      </article>

      <article className="app-card operations-app">
        <div className="app-copy">
          <span className="app-role">FOR LOCAL TEAMS</span>
          <h3>TravBiz</h3>
          <p>
            Give drivers, guides and local partners the job details they need to
            deliver each service.
          </p>
          <ul>
            <li>Assigned jobs and pickup details</li>
            <li>On the way, arrived and completed status</li>
            <li>Service updates for the DMC team</li>
          </ul>
          <span className="app-platform">
            <Icon name="phone" /> iOS &amp; Android
          </span>
        </div>
        <div className="phone-preview" aria-label="Illustrative TravBiz app screen">
          <div className="phone-header">
            TravBiz <span>OPERATIONS APP</span>
          </div>
          <div className="phone-body">
            <small>YOUR ASSIGNED JOB</small>
            <strong>Airport transfer</strong>
            <div className="phone-service">
              <Icon name="route" />
              <div>
                <b>Changi Airport</b>
                <span>Pickup · 10:30</span>
              </div>
            </div>
            <div className="phone-state lilac">Arrival status updated</div>
          </div>
        </div>
      </article>
    </div>
  </section>
);

export const LandingDestinations = () => {
  const marketNames = Object.keys(LANDING_MARKETS);
  const [activeMarket, setActiveMarket] = useState(marketNames[0]);

  return (
    <section id="destinations" className="section wrap">
      <div className="section-head">
        <div>
          <p className="kicker">04 / DESTINATIONS</p>
          <h2>Explore partner destinations.</h2>
        </div>
        <p>
          Explore city stays, ground services and multi-stop programmes across
          Asia-Pacific and the UAE.
        </p>
      </div>
      <div className="market-grid" aria-label="Destination markets">
        {marketNames.map((market) => (
          <button
            key={market}
            type="button"
            aria-pressed={activeMarket === market}
            onClick={() => setActiveMarket(market)}
          >
            <Icon name="pin" /> {market}
          </button>
        ))}
      </div>
      <div className="market-detail" aria-live="polite">
        <strong>{activeMarket}</strong>
        <p>{LANDING_MARKETS[activeMarket]}</p>
      </div>
    </section>
  );
};

export const LandingProgrammes = () => {
  const [selected, setSelected] = useState(null);

  return (
    <>
      <section id="programmes" className="section band">
        <div className="wrap">
          <div className="section-head">
            <div>
              <p className="kicker">05 / SAMPLE ITINERARIES</p>
              <h2>See what you can build.</h2>
            </div>
            <p>
              Explore sample programmes for client proposals and multi-city
              trips.
            </p>
          </div>
          <div className="programmes">
            {LANDING_PROGRAMMES.map((programme, index) => (
              <article className="programme" key={programme.title}>
                <div className="programme-photo">
                  <img
                    src={programme.image}
                    alt=""
                    loading="lazy"
                    width="850"
                    height="510"
                  />
                  <span>{programme.market}</span>
                </div>
                <div className="programme-body">
                  <p className="kicker">{programme.duration}</p>
                  <h3>{programme.title}</h3>
                  <button
                    type="button"
                    className="text-link"
                    aria-haspopup="dialog"
                    onClick={() => setSelected(index)}
                  >
                    View itinerary <Icon name="arrow" />
                  </button>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      {selected !== null && (
        <div
          className="programme-dialog-backdrop"
          role="presentation"
          onClick={() => setSelected(null)}
        >
          <div
            className="programme-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="programme-title"
            onClick={(event) => event.stopPropagation()}
          >
            <button
              type="button"
              className="close"
              aria-label="Close"
              onClick={() => setSelected(null)}
            >
              ×
            </button>
            <p className="kicker">
              {LANDING_PROGRAMMES[selected].market} ·{" "}
              {LANDING_PROGRAMMES[selected].duration}
            </p>
            <h2 id="programme-title">{LANDING_PROGRAMMES[selected].title}</h2>
            <p>Illustrative itinerary</p>
            <ol>
              {LANDING_PROGRAMMES[selected].stops.map((stop) => (
                <li key={stop}>{stop}</li>
              ))}
            </ol>
          </div>
        </div>
      )}
    </>
  );
};

export const LandingEndCta = () => (
  <section className="wrap end-cta">
    <div>
      <p className="kicker">AGENT WORKSPACE</p>
      <h2>See it from the agent’s side.</h2>
      <p>Build a multi-city trip or try a custom quote with sample data.</p>
    </div>
    <Link className="button primary" to="/demo">
      Open the agent demo <Icon name="arrow" />
    </Link>
  </section>
);

export const LandingFooter = () => (
  <footer className="site-footer">
    <div className="wrap footer-inner">
      <div>
        <img src="/Images/logo.png" alt="Travclicks" width="117" />
        <p>Travel operations technology for agents and DMCs.</p>
      </div>
      <div>
        <a href="mailto:admin@travclicks.com">Contact Travclicks</a>
        <Link to="/login">Agent sign in</Link>
        <small>Travel operations platform</small>
      </div>
    </div>
  </footer>
);
