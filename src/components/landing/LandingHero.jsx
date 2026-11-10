import { Link } from "react-router-dom";
import { Icon } from "./LandingIcons";

const LandingHero = () => {
  return (
    <section className="hero" aria-labelledby="hero-title">
      <div className="wrap hero-inner">
        <div className="hero-copy">
          <p className="kicker hero-kicker">
            <span /> FOR TRAVEL AGENTS &amp; TOUR OPERATORS
          </p>
          <h1 id="hero-title">Build the trip. Stay connected.</h1>
          <p className="hero-lede">
            Build a client trip, work with your destination management company
            (DMC), and keep travellers informed as local teams deliver the
            itinerary.
          </p>
          <div className="hero-actions">
            <Link className="button primary" to="/demo">
              Try the agent demo <Icon name="arrow" />
            </Link>
            <a className="button ghost" href="#journey">
              See how it connects
            </a>
          </div>
        </div>

        <div className="hero-art">
          <svg
            className="orbits"
            viewBox="0 0 650 550"
            fill="none"
            aria-hidden="true"
          >
            <ellipse
              cx="353"
              cy="245"
              rx="285"
              ry="165"
              transform="rotate(-29 353 245)"
            />
            <ellipse
              cx="353"
              cy="245"
              rx="318"
              ry="206"
              transform="rotate(-29 353 245)"
            />
            <ellipse
              cx="353"
              cy="245"
              rx="355"
              ry="244"
              transform="rotate(-29 353 245)"
            />
            <circle cx="580" cy="75" r="5" className="orb" />
          </svg>

          <div
            className="screen"
            aria-label="Illustrative trip shared across the Travclicks platform"
          >
            <div className="screen-top">
              <strong>
                <span className="screen-brand">T</span> Travclicks
              </strong>
              <span className="sample">ILLUSTRATIVE TRIP</span>
            </div>
            <div className="screen-content">
              <div className="screen-nav" aria-hidden="true">
                <span className="selected">▥</span>
                <span>◎</span>
                <span>⌁</span>
                <span>▤</span>
              </div>
              <div className="screen-main">
                <div className="screen-heading">
                  <div>
                    <small>AGENT WORKSPACE</small>
                    <h2>Singapore &amp; Bangkok</h2>
                  </div>
                  <span className="tiny-action">2 destinations</span>
                </div>
                <div className="worklist trip-activity">
                  <div className="worklist-title">
                    One trip, shared across teams <span>Example activity</span>
                  </div>
                  <div className="workrow">
                    <span className="journey-mark lilac">
                      <Icon name="route" />
                    </span>
                    <div>
                      <b>Agent requests a custom quote</b>
                      <small>Dates, travellers and services by destination</small>
                    </div>
                    <span className="pill lilac">Agent portal</span>
                  </div>
                  <div className="workrow">
                    <span className="journey-mark sand">
                      <Icon name="stack" />
                    </span>
                    <div>
                      <b>DMC confirms the airport transfer</b>
                      <small>Local operator assigns the service</small>
                    </div>
                    <span className="pill sand">TravOS</span>
                  </div>
                  <div className="workrow">
                    <span className="journey-mark green">
                      <Icon name="phone" />
                    </span>
                    <div>
                      <b>Driver marks arrival at pickup</b>
                      <small>Ground team updates the transfer status</small>
                    </div>
                    <span className="pill green">TravBiz</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="float-card">
            <span className="icon-block">
              <Icon name="phone" />
            </span>
            <div>
              <strong>TravHorse · Traveller update</strong>
              <small>Your airport pickup is ready</small>
            </div>
          </div>
        </div>
      </div>

      <div className="hero-base">
        <div className="wrap lifecycle">
          <span>
            <Icon name="globe" /> Connected by Travclicks
          </span>
          <span>Agent portal</span>
          <span>TravOS for DMCs</span>
          <span>iOS &amp; Android apps</span>
        </div>
      </div>
    </section>
  );
};

export default LandingHero;
