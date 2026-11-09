/** Check only the newly inserted hotel/arrival JS chunks */
const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
const s = fs.readFileSync(file, 'utf8');

function extract(startMark, endMark) {
  const a = s.indexOf(startMark);
  const b = s.indexOf(endMark, a);
  if (a < 0 || b < 0) throw new Error('missing ' + startMark);
  return s.slice(a, b);
}

const chunks = [
  extract('        function hotelBelongsToCityGroup', '        // Backward-compat wrapper'),
  extract('        function buildHotelRowHtml', '        function isTransferOnlyPlaceholderItem'),
  extract('        function updateAllDayTransferVisibility', '        function hydrateAllDayTransferCityOptions'),
];

for (let i = 0; i < chunks.length; i++) {
  try {
    new Function(chunks[i]);
    console.log('chunk', i, 'OK', chunks[i].length);
  } catch (e) {
    console.log('chunk', i, 'FAIL', e.message);
    console.log(chunks[i].slice(0, 300));
  }
}
