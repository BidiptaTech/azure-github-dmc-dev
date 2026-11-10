export const LandingSymbols = () => (
  <svg className="symbols" aria-hidden="true">
    <symbol id="lc-arrow" viewBox="0 0 24 24">
      <path d="M6 18 18 6M9 6h9v9" />
    </symbol>
    <symbol id="lc-globe" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" />
      <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
    </symbol>
    <symbol id="lc-stack" viewBox="0 0 24 24">
      <path d="M4 8.5 12 4l8 4.5-8 4.5L4 8.5Z" />
      <path d="m4 12.5 8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" />
    </symbol>
    <symbol id="lc-spark" viewBox="0 0 24 24">
      <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8" />
    </symbol>
    <symbol id="lc-route" viewBox="0 0 24 24">
      <circle cx="6" cy="6" r="2.5" />
      <circle cx="18" cy="18" r="2.5" />
      <path d="M8.5 7.5c3 0 4 3 5.5 5.5s2.5 5.5 5.5 5.5" />
    </symbol>
    <symbol id="lc-pin" viewBox="0 0 24 24">
      <path d="M12 21s-7-5.4-7-11a7 7 0 1 1 14 0c0 5.6-7 11-7 11Z" />
      <circle cx="12" cy="10" r="2.5" />
    </symbol>
    <symbol id="lc-phone" viewBox="0 0 24 24">
      <rect x="7" y="2.5" width="10" height="19" rx="2" />
      <path d="M10 18.5h4" />
    </symbol>
    <symbol id="lc-check" viewBox="0 0 24 24">
      <path d="m5 12 5 5L20 7" />
    </symbol>
  </svg>
);

export const Icon = ({ name, className = "lc-icon" }) => (
  <svg className={className} aria-hidden="true">
    <use href={`#lc-${name}`} />
  </svg>
);
