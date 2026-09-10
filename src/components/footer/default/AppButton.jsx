const AppButton = () => {
  const appContent = [
    {
      id: 1,
      icon: "icon-apple",
      link: "https://www.apple.com/app-store/",
      text: "Download on the",
      market: "Apple Store",
      colClass: "",
    },
    {
      id: 2,
      icon: "icon-play-market",
      link: "https://play.google.com/store/apps/?hl=en&gl=US",
      text: "Get in on",
      market: "Google Play",
      colClass: "mt-20",
    },
  ];

  return (
    <>
      {appContent.map((item) => (
        <div
          className={`d-flex items-center px-20 py-10 rounded-4 ${item.colClass}`}
          key={item.id}
          style={{
            border: "1px solid rgba(255,255,255,0.25)",
            background: "rgba(255,255,255,0.06)",
          }}
        >
          <i className={`${item.icon} text-24`} style={{ color: "#fff" }} />
          <a href={item.link} className="ml-20 d-block" style={{ color: "#fff" }}>
            <div className="text-14" style={{ color: "rgba(255,255,255,0.7)" }}>
              {item.text}
            </div>
            <div className="text-15 lh-1 fw-500" style={{ color: "#fff" }}>
              {item.market}
            </div>
          </a>
        </div>
      ))}
    </>
  );
};

export default AppButton;
