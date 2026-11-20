import { useEffect, useState } from "react";

const DestinationNetworkMap = () => {
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    const operations = document.querySelector("#operations");
    const motionControl = document.querySelector("#motion-control");
    if (!operations || !motionControl) return undefined;

    const onPauseClick = () => {
      const nextPaused = operations.toggleAttribute("data-paused");
      setPaused(nextPaused);
      motionControl.textContent = nextPaused
        ? "Resume animation"
        : "Pause animation";
    };

    motionControl.hidden = false;
    motionControl.addEventListener("click", onPauseClick);

    const script = document.createElement("script");
    script.src = `/agent-signin/world-map.js?v=${Date.now()}`;
    script.async = false;
    document.body.appendChild(script);

    return () => {
      motionControl.removeEventListener("click", onPauseClick);
      script.remove();
      if (operations.isConnected) {
        delete operations.dataset.mapReady;
      }
    };
  }, []);

  return (
    <figure className="agent-operations" id="operations" aria-labelledby="operations-title">
      <div className="ops-header">
        <div>
          <span className="ops-indicator" />
          <span id="operations-title">Destination network</span>
        </div>
        <div className="ops-controls">
          <span className="ops-caption">ILLUSTRATIVE ROUTES</span>
          <button id="motion-control" type="button" aria-controls="operations">
            {paused ? "Resume animation" : "Pause animation"}
          </button>
        </div>
      </div>

      <div className="journey-map">
        <svg
          className="map-art"
          viewBox="0 0 960 480"
          role="group"
          aria-labelledby="world-title world-desc"
        >
          <title id="world-title">World tourism destinations</title>
          <desc id="world-desc">
            A shaded-relief world map with tourism cities, illustrated landmarks
            and simulated flights, cruise ships and trains. Map imagery from
            Natural Earth.
          </desc>
          <defs>
            <pattern
              id="map-grid"
              width="80"
              height="40"
              patternUnits="userSpaceOnUse"
            >
              <path
                d="M80 0H0V40"
                fill="none"
                stroke="#749efa"
                strokeOpacity=".18"
              />
            </pattern>
            <filter
              id="landmark-outline"
              colorInterpolationFilters="sRGB"
            >
              <feMorphology
                in="SourceAlpha"
                operator="dilate"
                radius=".65"
                result="silhouette"
              />
              <feFlood floodColor="#000" result="black" />
              <feComposite
                in="black"
                in2="silhouette"
                operator="in"
                result="edge"
              />
              <feMerge>
                <feMergeNode in="edge" />
                <feMergeNode in="SourceGraphic" />
              </feMerge>
            </filter>
          </defs>
          <rect width="960" height="480" fill="url(#map-grid)" />
          <image
            className="world-imagery"
            href="/Images/agent-signin/world-natural-hd.webp"
            width="960"
            height="480"
          />
          <g id="map-routes" visibility="hidden" aria-hidden="true" />
          <g id="map-cities" aria-label="Destinations" />
          <g id="map-vapor" aria-hidden="true" />
          <g id="map-planes" aria-hidden="true" />
          <g id="map-transit" aria-hidden="true" />
          <g id="map-steam" aria-hidden="true" />
          <g id="map-popups" aria-live="polite" />
          <g id="map-city-label" visibility="hidden" />
        </svg>
        <span className="map-credit">Map imagery · Natural Earth</span>
      </div>
    </figure>
  );
};

export default DestinationNetworkMap;
