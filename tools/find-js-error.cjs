/** Find JS syntax error location in create.blade.php */
const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
const s = fs.readFileSync(file, 'utf8');
const a = s.indexOf("@push('scripts')");
const b = s.lastIndexOf('@endpush');
const chunk = s.slice(a, b);
const re = /<script>([\s\S]*?)<\/script>/g;
let js = '';
let scriptStartInFile = -1;
let m;
while ((m = re.exec(chunk))) {
  if (m[1].includes('multiCityPlans')) {
    js = m[1];
    scriptStartInFile = a + m.index + '<script>'.length;
    break;
  }
}
if (!js) {
  console.error('no script');
  process.exit(1);
}

// Binary search for error
let lo = 0;
let hi = js.length;
let lastErr = null;
while (lo < hi - 1) {
  const mid = Math.floor((lo + hi) / 2);
  const slice = js.slice(0, mid);
  try {
    new Function(slice);
    lo = mid;
  } catch (e) {
    lastErr = e;
    hi = mid;
  }
}
// Expand around lo
const start = Math.max(0, lo - 200);
const end = Math.min(js.length, lo + 200);
const snippet = js.slice(start, end);
const lineNum = js.slice(0, lo).split('\n').length;
console.log('Approx error near JS line', lineNum, 'offset', lo);
console.log('---snippet---');
console.log(snippet);
console.log('---err---');
console.log(lastErr && lastErr.message);

// Also check for weird chars around hotel fns
const idx = js.indexOf('function renderHotelCityBlocks');
console.log('renderHotelCityBlocks at', idx);
console.log(JSON.stringify(js.slice(idx, idx + 80)));
