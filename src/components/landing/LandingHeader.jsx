import { useState } from "react";
import { Link } from "react-router-dom";
import { Icon } from "./LandingIcons";

const NAV_LINKS = [
  { href: "#journey", label: "How it connects" },
  { href: "#platform", label: "TravOS & platform" },
  { href: "#mobile", label: "Mobile apps" },
  { href: "#destinations", label: "Destinations" },
];

const LandingHeader = () => {
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <header className="site-header">
      <div className="wrap nav-shell">
        <a href="#" className="brand" aria-label="Travclicks home">
          <img src="/Images/logo.png" alt="Travclicks" width="240" />
        </a>

        <button
          type="button"
          className="nav-toggle"
          aria-expanded={menuOpen}
          aria-controls="main-nav"
          aria-label="Menu"
          onClick={() => setMenuOpen((open) => !open)}
        >
          <span className="nav-toggle-text">Menu </span>
          <span aria-hidden="true">☰</span>
        </button>

        <nav
          id="main-nav"
          className={menuOpen ? "is-open" : undefined}
          aria-label="Main navigation"
        >
          {NAV_LINKS.map((link) => (
            <a
              key={link.href}
              href={link.href}
              onClick={() => setMenuOpen(false)}
            >
              {link.label}
            </a>
          ))}
        </nav>

        <Link className="signin" to="/login" aria-label="Agent sign in">
          <span className="signin-prefix">Agent </span>
          Sign in <Icon name="arrow" className="lc-icon external-arrow" />
        </Link>
      </div>
    </header>
  );
};

export default LandingHeader;
