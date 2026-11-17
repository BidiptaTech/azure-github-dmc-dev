import { useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";

const NAV_LINKS = [
  { href: "#journey", label: "How it connects" },
  { href: "#platform", label: "TravOS & platform" },
  { href: "#mobile", label: "Mobile apps" },
  { href: "#destinations", label: "Destinations" },
];

const LandingHeader = () => {
  const [menuOpen, setMenuOpen] = useState(false);
  const [signInOpen, setSignInOpen] = useState(false);
  const signInRef = useRef(null);

  useEffect(() => {
    if (!signInOpen) return undefined;

    const onPointerDown = (event) => {
      if (signInRef.current && !signInRef.current.contains(event.target)) {
        setSignInOpen(false);
      }
    };

    const onKeyDown = (event) => {
      if (event.key === "Escape") setSignInOpen(false);
    };

    document.addEventListener("pointerdown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("pointerdown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, [signInOpen]);

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

        <details
          ref={signInRef}
          className="signin-picker"
          open={signInOpen}
          onToggle={(event) => setSignInOpen(event.currentTarget.open)}
        >
          <summary className="signin signin-primary">SIGN IN</summary>
          <div className="signin-options">
            <Link to="/agent/sign-in" onClick={() => setSignInOpen(false)}>
              TA Partner Sign In
            </Link>
            <a
              href="https://dev.travclicks.com/backadm-dmc/login"
              onClick={() => setSignInOpen(false)}
            >
              DMC Sign In
            </a>
          </div>
        </details>
      </div>
    </header>
  );
};

export default LandingHeader;
