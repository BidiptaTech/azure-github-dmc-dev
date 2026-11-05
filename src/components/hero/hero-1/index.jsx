import MainFilterSearchBox from "./MainFilterSearchBox";

/**
 * Shared Plan & book hero — concept trip-card layout.
 * Pass `searchSlot` to swap MainFilterSearchBox (e.g. Tour Packages SearchForm).
 * Search submit / routing stay in MainFilterSearchBox (unchanged).
 */
const index = ({
  showPageIntro = true,
  pageKicker = "Start with the essentials",
  pageTitle = "One trip. Everything connected.",
  pageSubtitle = "Start with a destination, then choose stays, transfers and experiences.",
  kicker = "Your next client journey",
  title = "Where are we taking them?",
  subtitle = "Find stays, add experiences and build a complete trip with your DMC.",
  footerNote = null,
  searchSlot = null,
  showServiceChips: _showServiceChips = false,
} = {}) => {
  return (
    <section className="hero1-trip">
      <style>{`
        .hero1-trip {
          position: relative;
          min-height: 0;
          height: auto;
          padding: 0;
          background: transparent;
          overflow: visible;
        }
        .hero1-trip__wrap {
          width: 100%;
          max-width: none;
          padding: 0;
        }
        .hero1-trip__intro {
          margin: 0 0 12px;
          max-width: 40rem;
        }
        .hero1-trip__page-kicker {
          font-size: 11px;
          letter-spacing: 1.8px;
          font-weight: 700;
          text-transform: uppercase;
          color: #7788ad;
          margin: 0;
        }
        .hero1-trip__page-title {
          font-size: clamp(24px, 3vw, 30px);
          letter-spacing: -1px;
          font-weight: 700;
          margin: 6px 0;
          line-height: 1.15;
          color: #192944;
        }
        .hero1-trip__page-sub {
          margin: 0;
          color: #748097;
          line-height: 1.45;
          max-width: 36rem;
          font-size: 14px;
        }
        .hero1-trip__card {
          background: linear-gradient(110deg, #122958, #2551bd);
          border-radius: 18px;
          margin: 0;
          padding: 24px 24px 12px;
          color: #fff;
          position: relative;
          overflow: visible;
          min-height: 0;
          height: auto;
          display: block;
          width: 100%;
          box-sizing: border-box;
        }
        .hero1-trip__card::after {
          content: "↗";
          position: absolute;
          right: 20px;
          top: -36px;
          font-size: 160px;
          opacity: 0.08;
          pointer-events: none;
          line-height: 1;
        }
        .hero1-trip__kicker {
          font-size: 10px;
          letter-spacing: 1.6px;
          font-weight: 700;
          text-transform: uppercase;
          color: #a9c2ff;
          margin: 0;
          position: relative;
          z-index: 1;
        }
        .hero1-trip__title {
          font-size: clamp(20px, 2.4vw, 24px);
          font-weight: 700;
          margin: 6px 0 0;
          letter-spacing: -0.4px;
          position: relative;
          z-index: 1;
          line-height: 1.2;
        }
        .hero1-trip__sub {
          color: #c2d0ee;
          margin: 4px 0 12px;
          line-height: 1.45;
          position: relative;
          z-index: 1;
          max-width: 40rem;
          font-size: 13px;
        }
        .hero1-trip__search {
          position: relative;
          z-index: 2;
        }
        /* Kill global .hero-search-reserve 120px bottom padding inside trip card */
        .hero1-trip .hero-search-reserve {
          margin: 0 !important;
          padding-bottom: 0 !important;
        }
        .hero1-trip__search .hero-search-bar {
          width: 100% !important;
          max-width: none !important;
          padding: 8px !important;
          border-radius: 12px !important;
          box-shadow: none !important;
          display: grid !important;
          grid-template-columns: 1.4fr 1fr 1fr 0.8fr auto !important;
          gap: 5px !important;
        }
        .hero1-trip__footer {
          margin: 10px 0 0;
          text-align: center;
          font-size: 13px;
          color: #64748b;
        }
        .hero1-trip .hero-room-options {
          margin: 10px 0 0 !important;
          padding: 0 !important;
          position: relative;
          z-index: 2;
        }
        .hero1-trip__search .searchMenu-loc,
        .hero1-trip__search .searchMenu-guests {
          padding-top: 8px !important;
          padding-bottom: 8px !important;
        }
        .hero1-trip__search .searchMenu-date > div[style] {
          padding-top: 10px !important;
          padding-bottom: 10px !important;
        }
        .hero1-trip .hero-room-options__btn {
          appearance: none;
          border: 0;
          background: transparent;
          color: #fff;
          font-weight: 600;
          font-size: 13px;
          cursor: pointer;
          padding: 0;
          margin: 0;
          text-align: left;
          line-height: 1.3;
        }
        .hero1-trip .hero-room-options__btn:hover {
          text-decoration: underline;
        }
        @media (max-width: 900px) {
          .hero1-trip__search .hero-search-bar {
            grid-template-columns: 1fr !important;
          }
          .hero1-trip__search .searchMenu-date {
            grid-column: auto !important;
            grid-template-columns: 1fr 1fr !important;
            border-right: 0 !important;
          }
          .hero1-trip .hero-search-bar__cta {
            width: 100%;
          }
          .hero1-trip .hero-search-bar__cta button {
            width: 100%;
            min-height: 44px;
          }
        }
        @media (max-width: 767px) {
          .hero1-trip__intro {
            margin-bottom: 10px;
          }
          .hero1-trip__card {
            padding: 14px 12px 8px;
            border-radius: 12px;
          }
          .hero1-trip .hero-room-options {
            margin-top: 8px !important;
          }
          .hero1-trip__card::after {
            font-size: 120px;
            right: 4px;
          }
          .hero1-trip__sub {
            margin-bottom: 10px;
          }
        }
      `}</style>

      <div className="hero1-trip__wrap" style={{ position: "relative", zIndex: 2 }}>
        {showPageIntro && (
          <div className="hero1-trip__intro" data-aos="fade-up">
            {pageKicker ? (
              <div className="hero1-trip__page-kicker">{pageKicker}</div>
            ) : null}
            <h1 className="hero1-trip__page-title">{pageTitle}</h1>
            {pageSubtitle ? (
              <p className="hero1-trip__page-sub">{pageSubtitle}</p>
            ) : null}
          </div>
        )}

        <div className="hero1-trip__card" data-aos="fade-up" data-aos-delay="100">
          {kicker ? <div className="hero1-trip__kicker">{kicker}</div> : null}
          <h2 className="hero1-trip__title">{title}</h2>
          {subtitle ? <p className="hero1-trip__sub">{subtitle}</p> : null}

          <div className="hero1-trip__search tabs -underline js-tabs">
            {searchSlot || <MainFilterSearchBox />}
          </div>
        </div>

        {footerNote ? (
          <p className="hero1-trip__footer" data-aos="fade-up" data-aos-delay="200">
            {footerNote}
          </p>
        ) : null}
      </div>
    </section>
  );
};

export default index;
