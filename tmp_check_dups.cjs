const fs = require('fs');
const c = fs.readFileSync('c:/xampp/htdocs/Azure_new_files/resources/views/enquiryform_pro/edit.blade.php', 'utf8');
const idx = c.indexOf('function editArrivalDeparture');
const end = c.indexOf('function setArrDepSelectByIdOrName');
const chunk = c.slice(idx, end);
const re = /\bconst\s+(\w+)/g;
const names = {};
let m;
while ((m = re.exec(chunk))) {
  names[m[1]] = (names[m[1]] || 0) + 1;
}
const dups = Object.entries(names).filter(([, n]) => n > 1);
console.log('dups', dups);
console.log('const count', Object.keys(names).length, 'chunk len', chunk.length);

// Also scan whole file for Identifier already declared patterns: consecutive same-name const in same function is hard;
// look for lines that declare same const name twice within 30 lines
const lines = c.split(/\n/);
const recent = {};
for (let i = 0; i < lines.length; i++) {
  const cm = lines[i].match(/^\s*const\s+(\w+)\s*=/);
  if (!cm) continue;
  const name = cm[1];
  if (recent[name] !== undefined && i - recent[name] < 40) {
    console.log('NEAR_DUP', name, 'lines', recent[name] + 1, 'and', i + 1);
  }
  recent[name] = i;
}
