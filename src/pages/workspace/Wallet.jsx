import { useMemo, useState } from "react";

const DEMO_ACTIVITY = [
  {
    id: 1,
    label: "Hotel hold — Tan family holiday",
    amount: "- S$420.00",
    when: "Today",
  },
  {
    id: 2,
    label: "Wallet top-up",
    amount: "+ S$1,000.00",
    when: "2 days ago",
  },
  {
    id: 3,
    label: "Attraction tickets — Sharma city escape",
    amount: "- S$180.00",
    when: "Last week",
  },
];

export default function Wallet() {
  const [account, setAccount] = useState("credit");

  const balances = useMemo(() => {
    if (account === "cash") {
      return { credit: "S$0.00", prepaid: "S$250.00", available: "S$250.00" };
    }
    return {
      credit: "S$5,000.00",
      prepaid: "S$1,250.00",
      available: "S$4,580.00",
    };
  }, [account]);

  return (
    <div>
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Wallet &amp; payments</div>
          <h1 className="partner-title">Wallet &amp; payments</h1>
          <p className="partner-lede">
            DMC credit, prepaid funds and every transaction — clearly separated.
          </p>
        </div>
      </div>

      <section className="partner-card">
        <div className="partner-toolbar">
          <div className="partner-field" style={{ minWidth: 260 }}>
            <label htmlFor="pw-dmc-account">View DMC account</label>
            <select
              id="pw-dmc-account"
              value={account}
              onChange={(e) => setAccount(e.target.value)}
            >
              <option value="credit">Travclicks Demo DMC · Credit</option>
              <option value="cash">Demo DMC · No credit</option>
            </select>
          </div>
          <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
            <button type="button" className="partner-btn">
              Export CSV ↓
            </button>
            <button type="button" className="partner-btn">
              Simulate wallet top-up
            </button>
            <button type="button" className="partner-btn">
              Simulate credit repayment
            </button>
          </div>
        </div>

        <div className="partner-summary-grid">
          <div className="partner-stat" style={{ cursor: "default" }}>
            <div className="partner-stat__label">Credit limit</div>
            <div className="partner-stat__value">{balances.credit}</div>
          </div>
          <div className="partner-stat" style={{ cursor: "default" }}>
            <div className="partner-stat__label">Prepaid funds</div>
            <div className="partner-stat__value">{balances.prepaid}</div>
          </div>
          <div className="partner-stat" style={{ cursor: "default" }}>
            <div className="partner-stat__label">Available to spend</div>
            <div className="partner-stat__value">{balances.available}</div>
          </div>
        </div>
      </section>

      <section className="partner-card">
        <h2 className="partner-card__title">Account activity</h2>
        <p className="partner-card__sub">
          Demo data for this browser only — not connected to live payments.
        </p>
        <div className="partner-list">
          {DEMO_ACTIVITY.map((row) => (
            <div className="partner-list-item" key={row.id}>
              <div>
                <h3>{row.label}</h3>
                <p>{row.when}</p>
              </div>
              <strong>{row.amount}</strong>
            </div>
          ))}
        </div>
      </section>

      <section className="partner-card">
        <h2 className="partner-card__title">Account terms</h2>
        <p className="partner-card__sub">
          Credit belongs to the selected DMC and cannot be spent with another
          partner. Prepaid funds are separate from the credit limit.
        </p>
      </section>

      <section className="partner-card">
        <h2 className="partner-card__title">Pending bank transfers</h2>
        <p className="partner-card__sub">
          Demo-only verification controls. In production, only an authorised
          finance user or verified gateway event can confirm funds.
        </p>
        <p style={{ margin: 0, color: "var(--pw-muted)" }}>
          No transfers awaiting verification.
        </p>
      </section>
    </div>
  );
}
