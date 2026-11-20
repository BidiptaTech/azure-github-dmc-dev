const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
const buf = fs.readFileSync(file);
// Find the arrival line bytes
const s = buf.toString('utf8');
const needle = "if (label === 'day arrival' || label === 'arrival') return 'Arrival';";
const idx = s.indexOf(needle);
console.log('found ascii needle', idx);
// Also search loosely
const re = /if \(label === .day arrival./g;
let m;
while ((m = re.exec(s))) {
  const lineStart = s.lastIndexOf('\n', m.index) + 1;
  const lineEnd = s.indexOf('\n', m.index);
  const line = s.slice(lineStart, lineEnd);
  console.log('line', JSON.stringify(line));
  for (let i = 0; i < line.length; i++) {
    const cp = line.codePointAt(i);
    if (cp > 127 || cp === 39 || cp === 34 || (i >= 30 && i <= 45)) {
      // print interesting
    }
  }
  [...line].forEach((ch, i) => {
    const cp = ch.codePointAt(0);
    if (cp === 39 || cp === 34 || cp > 127 || (i >= 28 && i <= 42)) {
      console.log(i, JSON.stringify(ch), 'U+' + cp.toString(16));
    }
  });
}

// Check for any non-UTF8 / replacement chars near getItinerary
const area = s.indexOf('function getItineraryCityGroupsForUi');
const slice = buf.slice(area, area + 2000);
let weird = [];
for (let i = 0; i < slice.length; i++) {
  // look for UTF-8 continuation issues later; check for 0xC2 0x97 windows dash etc
}
console.log('file size', buf.length);

// Look for Windows-1252 smart sequences misread
const suspect = /[\x80-\x9F]/;
const text = buf.toString('latin1');
const jsStart = text.indexOf('function getItineraryCityGroupsForUi');
const jsArea = text.slice(jsStart, jsStart + 3000);
for (let i = 0; i < jsArea.length; i++) {
  const c = jsArea.charCodeAt(i);
  if (c >= 0x80 && c <= 0x9f) {
    console.log('C1 control', i, c.toString(16));
  }
}
