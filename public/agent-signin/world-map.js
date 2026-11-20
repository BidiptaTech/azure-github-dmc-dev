(function () {
  const boot = document.querySelector('#operations');
  if (!boot || boot.dataset.mapReady === '1') return;
  boot.dataset.mapReady = '1';
  ['#map-cities','#map-routes','#map-planes','#map-popups','#map-transit','#map-vapor','#map-steam','#map-city-label'].forEach((sel) => {
    const el = document.querySelector(sel);
    if (el) el.replaceChildren();
  });
const cityData = [
  ['Vancouver',49.28,-123.12],['Toronto',43.65,-79.38],['New York',40.71,-74.01],['Boston',42.36,-71.06],
  ['Chicago',41.88,-87.63],['San Francisco',37.77,-122.42],['Los Angeles',34.05,-118.24],['Las Vegas',36.17,-115.14],
  ['Honolulu',21.31,-157.86],['Orlando',28.54,-81.38],['Miami',25.76,-80.19],['Mexico City',19.43,-99.13],
  ['Cancún',21.16,-86.85],['Havana',23.11,-82.37],['Panama City',8.98,-79.52],['Cartagena',10.39,-75.48],
  ['Lima',-12.05,-77.04],['Cusco',-13.53,-71.97],['Rio de Janeiro',-22.91,-43.17],['Buenos Aires',-34.60,-58.38],
  ['Santiago',-33.45,-70.67],['Reykjavík',64.15,-21.94],['Dublin',53.35,-6.26],['London',51.51,-.13],
  ['Paris',48.86,2.35],['Amsterdam',52.37,4.90],['Berlin',52.52,13.41],['Copenhagen',55.68,12.57],
  ['Stockholm',59.33,18.07],['Prague',50.08,14.44],['Vienna',48.21,16.37],['Budapest',47.50,19.04],
  ['Lisbon',38.72,-9.14],['Madrid',40.42,-3.70],['Barcelona',41.39,2.17],['Rome',41.90,12.50],
  ['Venice',45.44,12.32],['Athens',37.98,23.73],['Istanbul',41.01,28.98],['Marrakech',31.63,-8.00],
  ['Cairo',30.04,31.24],['Dakar',14.72,-17.45],['Nairobi',-1.29,36.82],['Zanzibar City',-6.17,39.19],
  ['Johannesburg',-26.20,28.05],['Cape Town',-33.92,18.42],['Dubai',25.20,55.27],['Doha',25.29,51.53],
  ['Muscat',23.59,58.41],['Delhi',28.61,77.21],['Jaipur',26.91,75.79],['Mumbai',19.08,72.88],
  ['Kathmandu',27.72,85.32],['Colombo',6.93,79.85],['Malé',4.18,73.51],['Bangkok',13.76,100.50],
  ['Phuket',7.88,98.39],['Hanoi',21.03,105.85],['Ho Chi Minh City',10.82,106.63],['Siem Reap',13.36,103.86],
  ['Luang Prabang',19.88,102.14],['Kuala Lumpur',3.14,101.69],['Singapore',1.35,103.82],['Jakarta',-6.21,106.85],
  ['Denpasar',-8.65,115.22],['Manila',14.60,120.98],['Hong Kong',22.32,114.17],['Taipei',25.03,121.57],
  ['Shanghai',31.23,121.47],['Beijing',39.90,116.41],['Seoul',37.57,126.98],['Tokyo',35.68,139.69],
  ['Osaka',34.69,135.50],['Sydney',-33.87,151.21],['Melbourne',-37.81,144.96],['Perth',-31.95,115.86],
  ['Auckland',-36.85,174.76],['Queenstown',-45.03,168.66],
  ['Agra',27.18,78.04],['Giza',29.98,31.13],['Chichén Itzá',20.68,-88.57],['Machu Picchu',-13.16,-72.55],
  ['Vientiane',17.97,102.60],['Samarkand',39.65,66.96],['Petra',30.33,35.44],['Yangon',16.87,96.20],
  ["Pyongyang", 39.04, 125.75],
  ["Ulaanbaatar", 47.92, 106.92],
  ["Bandar Seri Begawan", 4.89, 114.94],
  ["Dili", -8.56, 125.57],
  ["Mazar-i-Sharif", 36.71, 67.11],
  ["Savar", 23.86, 90.26],
  ["Paro", 27.43, 89.41],
  ["Tehran", 35.69, 51.39],
  ["Islamabad", 33.69, 73.05],
  ["Astana", 51.17, 71.43],
  ["Tokmok", 42.84, 75.3],
  ["Dushanbe", 38.56, 68.77],
  ["Ashgabat", 37.96, 58.33],
  ["Yerevan", 40.18, 44.51],
  ["Baku", 40.41, 49.87],
  ["Manama", 26.22, 50.58],
  ["Paphos", 34.78, 32.42],
  ["Tbilisi", 41.72, 44.83],
  ["Samarra", 34.2, 43.88],
  ["Haifa", 32.79, 34.99],
  ["Kuwait City", 29.38, 47.99],
  ["Baalbek", 34.01, 36.21],
  ["Riyadh", 24.71, 46.68],
  ["Bethlehem", 31.7, 35.21],
  ["Damascus", 33.51, 36.29],
  ["Sana’a", 15.37, 44.19]
];

const landmarks = {
  Singapore:['mbs','Marina Bay Sands'], Paris:['eiffel','Eiffel Tower'], Agra:['taj','Taj Mahal'],
  Bangkok:['wat-arun','Wat Arun'], Dubai:['burj','Burj Khalifa'],
  London:['big-ben','Big Ben'], 'New York':['liberty','Statue of Liberty'], Sydney:['opera','Sydney Opera House'],
  Rome:['colosseum','Colosseum'], Tokyo:['tokyo','Tokyo Tower'], Giza:['pyramids','Pyramids of Giza'],
  'Kuala Lumpur':['petronas','Petronas Twin Towers'], 'San Francisco':['golden-gate','Golden Gate Bridge'],
  'Rio de Janeiro':['christ','Christ the Redeemer'], Athens:['parthenon','Parthenon'],
  Istanbul:['blue-mosque','Blue Mosque'], Barcelona:['sagrada','Sagrada Família'], 'Siem Reap':['angkor','Angkor Wat'],
  'Cape Town':['table-mountain','Table Mountain'], Auckland:['sky-tower','Sky Tower'], 'Hong Kong':['victoria-harbour','Victoria Harbour'],
  Beijing:['great-wall','Great Wall'], Shanghai:['pearl-tower','Oriental Pearl Tower'], Taipei:['taipei','Taipei 101'],
  Seoul:['gyeongbokgung','Gyeongbokgung Palace'], Denpasar:['bali-gate','Balinese split gate'],
  'Los Angeles':['hollywood','Hollywood Sign'], Toronto:['cn-tower','CN Tower'], 'Chichén Itzá':['chichen','El Castillo'],
  Kathmandu:['boudha','Boudhanath Stupa'], Colombo:['lotus','Lotus Tower'], Malé:['water-villa','Maldivian water villa'],
  'Ho Chi Minh City':['central-post','Central Post Office'], Vientiane:['stupa','Pha That Luang'], Yangon:['shwedagon','Shwedagon Pagoda'],
  Samarkand:['registan','Registan'], Petra:['petra','The Treasury'], Muscat:['muscat-mosque','Grand Mosque'],
  Manila:['intramuros','Intramuros'], Amsterdam:['windmill','Dutch windmill'], 'Machu Picchu':['machu-picchu','Inca citadel'],
  "Pyongyang":["juche", "Juche Tower"],
  "Ulaanbaatar":["khan", "Genghis Khan statue"],
  "Bandar Seri Begawan":["omar", "Omar Ali Saifuddien Mosque"],
  "Dili":["cristo-rei", "Cristo Rei of Dili"],
  "Mazar-i-Sharif":["blue-shrine", "Blue Mosque"],
  "Savar":["martyrs", "National Martyrs’ Memorial"],
  "Paro":["tigers-nest", "Tiger’s Nest Monastery"],
  "Tehran":["azadi", "Azadi Tower"],
  "Islamabad":["faisal", "Faisal Mosque"],
  "Astana":["baiterek", "Baiterek Tower"],
  "Tokmok":["burana", "Burana Tower"],
  "Dushanbe":["somoni", "Ismoil Somoni Monument"],
  "Ashgabat":["neutrality", "Monument of Neutrality"],
  "Yerevan":["cascade", "Yerevan Cascade"],
  "Baku":["flames", "Flame Towers"],
  "Manama":["bahrain-fort", "Bahrain Fort"],
  "Paphos":["paphos", "Paphos Castle"],
  "Tbilisi":["narikala", "Narikala Fortress"],
  "Samarra":["malwiya", "Al-Malwiya Minaret"],
  "Haifa":["bahai", "Bahá’í Gardens"],
  "Kuwait City":["kuwait-towers", "Kuwait Towers"],
  "Baalbek":["baalbek", "Temple of Jupiter"],
  "Doha":["qatar-museum", "Museum of Islamic Art"],
  "Riyadh":["kingdom", "Kingdom Centre"],
  "Bethlehem":["nativity", "Church of the Nativity"],
  "Damascus":["umayyad", "Umayyad Mosque"],
  "Sana’a":["sanaa", "Old City of Sana’a"]
};

const featured = new Set(['Los Angeles','New York','London','Rio de Janeiro','Cape Town','Dubai','Singapore','Tokyo','Sydney','Auckland']);
const svgNs = 'http://www.w3.org/2000/svg';
function svgElement(name, attributes) {
  const element = document.createElementNS(svgNs, name);
  for (const [key, value] of Object.entries(attributes)) element.setAttribute(key, value);
  return element;
}

function project(lat, lon) {
  return [(lon + 180) * 8 / 3, (90 - lat) * 8 / 3];
}
const cities = cityData.map(([name, lat, lon]) => {
  const [x,y] = project(lat,lon);
  return {name, lat, lon, x, y, landmark:landmarks[name]};
});
const cityLayer = document.querySelector('#map-cities');
const cityLabel = document.querySelector('#map-city-label');
const labelBackground = svgElement('rect',{rx:5,height:24});
const labelText = svgElement('text',{x:8,y:16});
cityLabel.append(labelBackground,labelText);
let activeDot;

function showCityLabel(city,dot) {
  activeDot = dot;
  labelText.textContent = city.name;
  const width = labelText.getComputedTextLength()+16;
  labelBackground.setAttribute('width',width);
  const x = Math.max(4,Math.min(956-width,city.x-width/2));
  const y = city.y < 37 ? city.y+10 : city.y-34;
  cityLabel.setAttribute('transform','translate('+x+' '+y+')');
  cityLabel.setAttribute('visibility','visible');
}

function hideCityLabel(dot) {
  if (dot !== activeDot) return;
  activeDot = undefined;
  cityLabel.setAttribute('visibility','hidden');
}

for (const city of cities) {
  const dot = svgElement('circle',{
    cx:city.x,cy:city.y,r:featured.has(city.name) ? 4.5 : 3.2,
    class:'city-dot',tabindex:0,role:'button','aria-label':city.name
  });
  const show = () => showCityLabel(city,dot);
  const hide = () => hideCityLabel(dot);
  dot.addEventListener('pointerenter',show);
  dot.addEventListener('pointerleave',event => {
    if (event.pointerType !== 'touch' && document.activeElement !== dot) hide();
  });
  dot.addEventListener('focus',show);
  dot.addEventListener('blur',hide);
  dot.addEventListener('click',show);
  dot.addEventListener('keydown',event => {
    if (event.key === 'Escape') hide();
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      show();
    }
  });
  cityLayer.append(dot);
}
document.addEventListener('pointerdown',event => {
  if (!cityLayer.contains(event.target)) hideCityLabel(activeDot);
});

const routeLayer = document.querySelector('#map-routes');
const planeLayer = document.querySelector('#map-planes');
const popupLayer = document.querySelector('#map-popups');
const mapPanel = document.querySelector('#operations');
const motionButton = document.querySelector('#motion-control');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const randomItem = items => items[Math.floor(Math.random() * items.length)];

function unitVector({lat,lon}) {
  const phi = lat * Math.PI / 180, lambda = lon * Math.PI / 180;
  return [Math.cos(phi) * Math.cos(lambda), Math.cos(phi) * Math.sin(lambda), Math.sin(phi)];
}

function routeGeometry(from, to) {
  const a = unitVector(from), b = unitVector(to);
  const angle = Math.acos(Math.max(-1, Math.min(1, a.reduce((sum,n,i) => sum+n*b[i], 0))));
  const denominator = Math.sin(angle);
  const points = [];
  for (let step = 0; step <= 36; step++) {
    const t = step / 36;
    const start = Math.sin((1-t)*angle) / denominator;
    const end = Math.sin(t*angle) / denominator;
    const [x,y,z] = a.map((component,i) => component*start + b[i]*end);
    points.push(project(Math.atan2(z, Math.hypot(x,y))*180/Math.PI, Math.atan2(y,x)*180/Math.PI));
  }
  return points.map(([x,y],i) => (i ? 'L' : 'M') + x.toFixed(2) + ' ' + y.toFixed(2)).join(' ');
}

function assignRoute(flight) {
  const from = randomItem(cities.filter(city => featured.has(city.name)));
  // A short longitudinal separation keeps the whole route visible within the map edges.
  const to = randomItem(cities.filter(city => Math.abs(city.lon-from.lon)<155 &&
    Math.hypot(city.x-from.x,city.y-from.y)>145));
  flight.path.setAttribute('d', routeGeometry(from,to));
  flight.length = flight.path.getTotalLength();
  flight.duration = 6300 + Math.random()*4200;
}

const flights = Array.from({length:4}, (_,index) => {
  const path = svgElement('path',{});
  const plane = svgElement('image',{
    class:'flight-plane',href:'/Images/agent-signin/passenger-plane.webp',
    x:-17,y:-17,width:34,height:34
  });
  routeLayer.append(path);
  planeLayer.append(plane);
  const flight = {path,plane,elapsed:-index*1400};
  assignRoute(flight);
  return flight;
});

// Sample passenger corridors. Intermediate points keep ship tracks offshore and rail tracks on land.
const seaLegs = [
  {
    name:'Rio de Janeiro → Santos → Montevideo', duration:8800,
    waypoints:[[-22.89,-43.18],[-22.99,-43.13],[-23.72,-43.72],[-24.62,-44.88],[-24.47,-45.86],
      [-24.00,-46.32],[-24.17,-46.28],[-25.76,-47.43],[-27.54,-47.92],[-29.88,-48.89],
      [-32.06,-50.85],[-34.38,-52.75],[-35.37,-54.22],[-35.18,-55.41],[-34.90,-56.22]]
  },
  {
    name:'Vancouver → Juneau · Alaska cruise', duration:8700,
    waypoints:[[49.29,-123.12],[49.33,-123.32],[49.57,-123.94],[49.90,-124.70],[50.13,-125.22],
      [50.42,-125.60],[50.73,-126.52],[51.00,-127.50],[52.25,-129.47],[54.00,-130.50],
      [55.27,-131.71],[56.48,-132.75],[57.48,-133.51],[58.17,-134.08],[58.29,-134.40]]
  },
  {
    name:'Miami → Nassau', duration:6400,
    waypoints:[[25.77,-80.17],[25.76,-80.04],[25.59,-79.49],[25.47,-78.71],[25.43,-78.07],
      [25.31,-77.64],[25.14,-77.38],[25.08,-77.34]]
  },
  {
    name:'Singapore → Port Klang → Penang', duration:7300,
    waypoints:[[1.25,103.82],[1.16,103.64],[1.22,103.36],[1.68,102.43],[2.43,101.47],
      [2.89,101.24],[3.19,100.88],[4.15,100.28],[5.12,100.14],[5.37,100.31],[5.42,100.35]]
  }
];
// Open-water legs are illustrative and stay clear of the coasts and map seam.
const oceanLegs = [
  [{name:'North Pacific crossing',duration:29000,
    waypoints:[[34,-170],[35,-156],[36,-144],[37,-132]]}],
  [{name:'South Atlantic crossing',duration:26000,
    waypoints:[[-21,-31],[-23,-21],[-25,-11],[-27,-1],[-28,7]]}],
  [{name:'Southern Indian Ocean crossing',duration:31000,
    waypoints:[[-42,69],[-43,82],[-43,95],[-42,108],[-41,119]]}],
  [{name:'North Atlantic crossing', duration:24000,
    waypoints:[[38,-63],[39,-54],[42,-44],[45,-34],[47,-25],[48,-16]]}],
  [{name:'South Pacific crossing', duration:27000,
    waypoints:[[-33,-145],[-34,-133],[-35,-121],[-36,-109],[-36,-97],[-35,-85]]}],
  [{name:'Indian Ocean crossing', duration:23000,
    waypoints:[[-22,53],[-23,62],[-23,71],[-24,81],[-24,90],[-25,99]]}]
];
const railLegs = [
  {
    name:'London → Paris · Eurostar', duration:6500,
    waypoints:[[51.53,-.13],[51.54,-.04],[51.44,.32],[51.14,.87],[51.08,1.15],[50.94,1.81],
      [50.64,3.08],[50.42,2.88],[49.89,2.70],[49.25,2.65],[48.88,2.36]]
  },
  {
    name:'Washington, DC → New York → Boston · Amtrak Acela', duration:7500,
    waypoints:[[38.90,-77.01],[39.29,-76.62],[39.63,-75.91],[39.74,-75.55],[39.96,-75.18],
      [40.22,-74.75],[40.73,-74.17],[40.75,-73.99],[41.05,-73.54],[41.30,-72.93],
      [41.35,-72.10],[41.82,-71.41],[42.35,-71.06]]
  },
  {
    name:'Shin-Osaka → Tokyo · Tokaido Shinkansen', duration:6200,
    waypoints:[[34.73,135.50],[34.99,135.76],[35.33,136.29],[35.17,136.88],[34.97,137.06],
      [34.70,137.74],[34.97,138.39],[35.10,138.91],[35.26,139.15],[35.51,139.62],[35.68,139.77]]
  },
  {
    name:'Moscow → Vladivostok · Trans-Siberian Railway', duration:15500,
    waypoints:[[55.76,37.65],[57.63,39.87],[58.60,49.67],[58.00,56.25],[56.84,60.61],
      [57.15,65.53],[55.00,73.36],[55.03,82.92],[56.02,92.87],[55.93,98.00],[52.29,104.28],
      [51.83,107.59],[52.03,113.50],[53.74,119.76],[53.98,123.92],[50.10,128.12],
      [48.48,135.07],[44.60,132.80],[43.12,131.88]]
  }
];
// Longer illustrative corridors give the whole train room to follow each bend.
const regionalRailLegs = [
  {name:'Chicago → Seattle',duration:14500,
    waypoints:[[41.88,-87.64],[43.04,-87.92],[44.95,-93.10],[46.88,-96.79],[48.23,-101.30],[48.55,-109.68],[48.41,-114.34],[47.66,-117.43],[47.60,-122.33]]},
  {name:'Los Angeles → Chicago',duration:15500,
    waypoints:[[34.06,-118.24],[34.90,-117.02],[35.19,-114.05],[35.20,-111.65],[35.53,-108.74],[35.08,-106.65],[37.17,-104.50],[38.05,-103.72],[38.06,-97.34],[39.08,-94.58],[41.88,-87.64]]},
  {name:'Paris → Madrid',duration:11500,
    waypoints:[[48.84,2.37],[47.39,.69],[46.58,.34],[44.84,-.58],[43.49,-1.47],[43.32,-1.98],[42.85,-2.67],[42.34,-3.70],[40.42,-3.70]]},
  {name:'Berlin → Vienna',duration:12000,
    waypoints:[[52.52,13.37],[51.34,12.38],[51.05,13.74],[50.65,14.04],[50.08,14.44],[49.90,15.40],[49.20,16.61],[48.75,16.88],[48.20,16.37]]},
  {name:'Buenos Aires → Córdoba',duration:12000,
    waypoints:[[-34.60,-58.37],[-34.16,-58.96],[-33.33,-60.22],[-32.95,-60.67],[-32.80,-61.80],[-32.63,-62.69],[-32.41,-63.25],[-31.42,-64.19]]},
  {name:'Santiago → Temuco',duration:12500,
    waypoints:[[-33.45,-70.68],[-34.17,-70.75],[-34.58,-70.99],[-35.43,-71.65],[-36.61,-72.11],[-37.47,-72.35],[-38.24,-72.45],[-38.74,-72.59]]}
];
const transitLayer = document.querySelector('#map-transit');
function setTransitLeg(transit) {
  const leg = transit.legs[transit.index];
  const points = leg.waypoints.map(([lat,lon]) => project(lat,lon));
  const end = points.at(-1);
  let geometry = 'M'+points[0].join(' ');
  for (let i=1; i<points.length-1; i++) {
    const [x,y] = points[i];
    const [nextX,nextY] = points[i+1];
    geometry += ' Q'+x+' '+y+' '+(x+nextX)/2+' '+(y+nextY)/2;
  }
  transit.path.setAttribute('d',geometry+' L'+end.join(' '));
  transit.caption.textContent = leg.name;
  transit.length = transit.path.getTotalLength();
  transit.duration = leg.duration;
}

const transitVehicles = [
  [seaLegs,'sea',1800],
  ...oceanLegs.map(([leg],index) => [
    [leg,{...leg,waypoints:[...leg.waypoints].reverse()}],'sea',4200+index*3300
  ]),
  ...railLegs.map((_,index) => [railLegs,'rail',1600+index*650,index]),
  ...regionalRailLegs.map((leg,index) => [
    [leg,{...leg,waypoints:[...leg.waypoints].reverse()}],'rail',2200+index*480
  ])
].map(([legs,mode,elapsed,index=0]) => {
  const path = svgElement('path',{});
  const wrapper = svgElement('g',{class:'transit-vehicle '+mode});
  const cars = mode === 'rail' ? Array.from({length:4},(_,i) =>
    svgElement('image',{href:i === 0 ? '/Images/agent-signin/rail-cab.webp' : '/Images/agent-signin/rail-carriage.webp',x:-3.8,y:-1.7,width:7.6,height:3.4,preserveAspectRatio:'none'})
  ) : [];
  const caption = svgElement('title',{});
  wrapper.append(caption);
  if (cars.length) wrapper.append(...cars.slice().reverse());
  else wrapper.append(svgElement('image',{href:'/Images/agent-signin/cruise-ship.webp',x:-18,y:-8,width:36,height:16}));
  routeLayer.prepend(path);
  transitLayer.append(wrapper);
  const transit = {legs,path,wrapper,caption,cars,index,elapsed};
  setTransitLeg(transit);
  return transit;
});

const vaporLayer = document.querySelector('#map-vapor');
const steamLayer = document.querySelector('#map-steam');
const vapor = [];

function emitVapor(vehicle, point, dx, dy, isPlane) {
  const length = Math.hypot(dx,dy) || 1;
  const forwardX = dx/length;
  const forwardY = dy/length;
  const tail = isPlane ? 8 : 15;
  const x = point.x-forwardX*tail;
  const y = point.y-forwardY*tail;
  if (isPlane) {
    const tips = [-2.1,2.1].map(offset => [x-forwardY*offset,y+forwardX*offset]);
    if (vehicle.previousTips) {
      const d = tips.map(([endX,endY],i) =>
        'M'+vehicle.previousTips[i].join(' ')+' L'+endX+' '+endY
      ).join(' ');
      const element = svgElement('path',{d,fill:'none',stroke:'#fff','stroke-width':1.15,'stroke-linecap':'round',opacity:.78});
      vaporLayer.append(element);
      vapor.push({element,age:0,lifetime:1250});
    }
    vehicle.previousTips = tips;
  } else {
    addShipPuff(vaporLayer,x,y,forwardX,forwardY,1.65,1080,0);
    addShipPuff(steamLayer,point.x-forwardX*5,point.y-forwardY*5,
      forwardX,forwardY,1.85,1050,8);
  }
}

function addShipPuff(layer,x,y,forwardX,forwardY,radius,lifetime,rise) {
  const element = svgElement('circle',{cx:x,cy:y,r:radius,fill:'#fff',opacity:.62});
  layer.append(element);
  vapor.push({element,age:0,lifetime,x,y,forwardX,forwardY,radius,rise});
}

function updateVapor(dt) {
  for (let i=vapor.length-1;i>=0;i--) {
    const puff = vapor[i];
    puff.age += dt;
    const progress = puff.age/puff.lifetime;
    if (progress >= 1) {
      puff.element.remove();
      vapor.splice(i,1);
      continue;
    }
    const fade = (1-progress)**1.5;
    const isPuff = puff.x !== undefined;
    puff.element.setAttribute('opacity',fade*(isPuff ? .62 : .78));
    if (isPuff) {
      puff.element.setAttribute('r',puff.radius+progress*2.3);
      puff.element.setAttribute('cx',puff.x-puff.forwardX*progress*4);
      puff.element.setAttribute('cy',puff.y-puff.forwardY*progress*4-progress*puff.rise);
    } else {
      puff.element.setAttribute('stroke-width',1.15+progress*.85);
    }
  }
}

function positionVehicle(element, vehicle, progress, dt, isPlane) {
  const {path,length} = vehicle;
  const distance = Math.max(0,progress*length);
  const point = path.getPointAtLength(distance);
  const behind = path.getPointAtLength(Math.max(0,distance-1));
  const ahead = path.getPointAtLength(Math.min(length,distance+1));
  const dx = ahead.x-behind.x;
  const dy = ahead.y-behind.y;
  const angle = Math.atan2(dy,dx)*180/Math.PI;
  element.setAttribute('transform','translate('+point.x+' '+point.y+') rotate('+angle+')');
  if (progress < 0) return;
  vehicle.vaporClock = (vehicle.vaporClock ?? 0)+dt;
  if (vehicle.vaporClock >= (isPlane ? 55 : 310)) {
    emitVapor(vehicle,point,dx,dy,isPlane);
    vehicle.vaporClock = 0;
  }
}

function positionTrain({cars,path,length},progress) {
  const spacing = 7;
  const startingDistance = Math.min(spacing*cars.length,length*.55);
  const lead = startingDistance+progress*(length-startingDistance);
  // Adjacent carriages share the same coupling point, including on curves.
  const couplings = Array.from({length:cars.length+1},(_,index) => {
    const distance = lead-index*spacing;
    const point = path.getPointAtLength(Math.max(0,distance));
    if (distance >= 0) return point;
    const ahead = path.getPointAtLength(Math.min(length,spacing));
    const tangent = Math.hypot(ahead.x-point.x,ahead.y-point.y) || 1;
    return {
      x:point.x+distance*(ahead.x-point.x)/tangent,
      y:point.y+distance*(ahead.y-point.y)/tangent
    };
  });
  cars.forEach((car,index) => {
    const front = couplings[index];
    const rear = couplings[index+1];
    const dx = front.x-rear.x;
    const dy = front.y-rear.y;
    const facing = dx < 0 ? -1 : 1;
    const tilt = Math.atan2(dy*facing,Math.abs(dx))*180/Math.PI;
    const width = Math.hypot(dx,dy)+.3;
    const x = (front.x+rear.x)/2;
    const y = (front.y+rear.y)/2;
    car.setAttribute('transform','translate('+x+' '+y+') rotate('+tilt+') scale('+facing+' 1)');
    car.setAttribute('width',width);
    car.setAttribute('x',-width/2);
  });
}

const landmarkCities = cities.filter(city => city.landmark);
const openingNames = ['Paris','Agra','Bangkok','Dubai','New York','London','Beijing','Tokyo','Giza','Sydney'];
const opening = openingNames.map(name => landmarkCities.find(city => city.name === name));
let cityQueue = [...opening].reverse();

function nextPopupCity() {
  if (!cityQueue.length) {
    cityQueue = [...landmarkCities];
    for (let i=cityQueue.length-1; i>0; i--) {
      const j = Math.floor(Math.random()*(i+1));
      [cityQueue[i],cityQueue[j]] = [cityQueue[j],cityQueue[i]];
    }
  }
  return cityQueue.pop();
}

function showPopup(city) {
  const [icon, landmarkName] = city.landmark;
  const scale = Math.max(1, Math.min(1.5, 530 / popupLayer.ownerSVGElement.clientWidth));
  const size = 104 * scale;
  // Near the map seam the model leans inward while the pulse stays on the marker.
  const modelX = Math.min(951-size*.6, Math.max(9+size*.6, city.x));
  const modelY = Math.max(size*1.07,city.y-size*.16);
  const labelWidth = Math.max(city.name.length*10,landmarkName.length*8.3)*scale;
  const labelX = Math.min(950-labelWidth/2,Math.max(10+labelWidth/2,city.x));
  const labelY = city.y + 23*scale;

  const popup = svgElement('g',{class:'tour-popup','data-city':city.name});
  popup.append(svgElement('circle',{class:'tour-pop-ring',cx:city.x,cy:city.y,r:9}));
  const model = svgElement('g',{transform:'translate('+modelX+' '+modelY+')'});
  const risingIcon = svgElement('g',{class:'landmark-rise'});
  risingIcon.style.setProperty('--pin-x',(city.x-modelX)+'px');
  risingIcon.style.setProperty('--pin-y',(city.y-modelY)+'px');
  risingIcon.append(svgElement('image',{
    x:-size/2,
    y:-size*235/256,
    href:'/Images/agent-signin/landmarks/'+icon+'.webp',
    width:size,
    height:size
  }));
  model.append(risingIcon);
  popup.append(model);

  const label = svgElement('g',{class:'tour-label',transform:'translate('+labelX+' '+labelY+') scale('+scale+')'});
  const cityText = svgElement('text',{x:0,y:0,class:'tour-city'});
  cityText.textContent = city.name;
  const landmarkText = svgElement('text',{x:0,y:16,class:'tour-landmark-name'});
  landmarkText.textContent = landmarkName;
  label.append(cityText,landmarkText);
  popup.append(label);
  popupLayer.replaceChildren(popup);
}

let animationFrame, previousTime, popupClock = 0;
showPopup(landmarkCities.find(city => city.name === 'Singapore'));
function frame(time) {
  const dt = previousTime === undefined ? 0 : Math.min(1000,time-previousTime);
  previousTime = time;
  updateVapor(dt);
  for (const flight of flights) {
    flight.elapsed += dt;
    if (flight.elapsed >= flight.duration) {
      flight.elapsed = 0;
      flight.previousTips = undefined;
      assignRoute(flight);
    }
    flight.plane.setAttribute('visibility',flight.elapsed<0 ? 'hidden' : 'visible');
    positionVehicle(flight.plane,flight,flight.elapsed/flight.duration,dt,true);
  }
  for (const transit of transitVehicles) {
    transit.elapsed += dt;
    if (transit.elapsed >= transit.duration) {
      transit.elapsed = 0;
      transit.index = (transit.index+1) % transit.legs.length;
      setTransitLeg(transit);
    }
    const progress = transit.elapsed/transit.duration;
    if (transit.cars.length) positionTrain(transit,progress);
    else positionVehicle(transit.wrapper,transit,progress,dt,false);
  }
  popupClock += dt;
  if (popupClock >= 4800) {
    showPopup(nextPopupCity());
    popupClock = 0;
  }
  animationFrame = requestAnimationFrame(frame);
}

function updateMotion() {
  cancelAnimationFrame(animationFrame);
  previousTime = undefined;
  const paused = reducedMotion.matches || mapPanel.hasAttribute('data-paused');
  planeLayer.style.display = reducedMotion.matches ? 'none' : '';
  transitLayer.style.display = planeLayer.style.display;
  vaporLayer.style.display = planeLayer.style.display;
  steamLayer.style.display = planeLayer.style.display;
  if (!paused) animationFrame = requestAnimationFrame(frame);
}
motionButton.addEventListener('click',updateMotion);
reducedMotion.addEventListener('change',updateMotion);
updateMotion();

})();

