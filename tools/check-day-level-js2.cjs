const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
const s = fs.readFileSync(file, 'utf8');
const lines = s.split(/\n/);

// Hypothesis A: browser lines are document-absolute; script starts ~4371
// Hypothesis B: browser lines are 1-based within the inline script tag
const scriptStartBlade = lines.findIndex((l) => l.trim() === '<script>' && lines[lines.findIndex(x => x.includes("@push('scripts')"))]) ;
// find the main <script> after push scripts that contains cacheAllCities
let mainScriptBlade = -1;
for (let i = 0; i < lines.length; i++) {
  if (lines[i].includes('<script>') && !lines[i].includes('application/json')) {
    // peek ahead
    const window = lines.slice(i, i + 5).join('\n');
    if (window.includes('__editPayloadEl') || (i + 1 < lines.length && lines[i + 1].includes('__editPayloadEl'))) {
      mainScriptBlade = i + 1; // 1-based line of <script> tag? content starts i+2
      console.log('main script tag at blade', i + 1);
      break;
    }
  }
}

const contentStart = lines.findIndex((l) => l.includes("function cacheAllCities"));
console.log('cacheAllCities blade', contentStart + 1);

// If Chrome reports script-local line numbers:
function bladeFromScriptLine(scriptLine) {
  // script content starts at line after <script>
  const start = mainScriptBlade; // line number of <script> tag (1-based) => content at start+1
  return start + scriptLine; // if scriptLine 1 is first line inside script
}

// Try both mappings for 3743
for (const label of ['doc-offset-2365', 'script-local']) {
  let blade;
  if (label === 'doc-offset-2365') blade = 3743 - 2365;
  else blade = (mainScriptBlade + 1) + 3743 - 1; // content line 1 = mainScriptBlade+1
  console.log('\n', label, '=> blade', blade);
  const line = lines[blade - 1] || '';
  console.log(JSON.stringify(line));
  console.log('len', line.length, 'col35', JSON.stringify(line.slice(34, 36)), 'code', line.codePointAt(34));
  // show nearby
  for (let i = blade - 3; i <= blade + 2; i++) {
    if (i > 0 && i <= lines.length) console.log(i + '|' + lines[i - 1]);
  }
}

// Also dump script-local line 3743 chars carefully for unexpected tokens
const scriptContentStart = mainScriptBlade; // 0-based index of <script> line
const firstContentIdx = scriptContentStart + 1; // first line inside script
const targetIdx = firstContentIdx + 3743 - 1;
console.log('\nPrecise script-local 3743 => blade', targetIdx + 1);
const t = lines[targetIdx] || '';
console.log(JSON.stringify(t));
[...t].forEach((ch, idx) => {
  if (idx >= 30 && idx <= 45) {
    console.log(idx, JSON.stringify(ch), 'U+' + ch.codePointAt(0).toString(16));
  }
});
