const fs = require('fs');
const lines = fs.readFileSync('resources/views/layouts/sidebar.blade.php', 'utf8').split(/\n/);
for (const n of [2887, 2888, 2889, 2890, 2891, 2892, 2914, 2915, 2916, 2926, 2927]) {
  const l = lines[n - 1];
  console.log('---', n);
  console.log(JSON.stringify(l));
  [...l].forEach((ch, i) => {
    const c = ch.codePointAt(0);
    if (c > 127 || ch === "'" || ch === '"' || ch === '/' || ch === '`') {
      console.log(i, JSON.stringify(ch), 'U+' + c.toString(16));
    }
  });
}

// Try to extract and parse the sidebar script containing ctpLeadGuestRules
const s = fs.readFileSync('resources/views/layouts/sidebar.blade.php', 'utf8');
const idx = s.indexOf('function ctpSyncTourTypeFromPax');
const scriptStart = s.lastIndexOf('<script', idx);
const scriptEnd = s.indexOf('</script>', idx);
let js = s.slice(s.indexOf('>', scriptStart) + 1, scriptEnd);
js = js.replace(/@json\((?:[^()]|\([^()]*\))*\)/g, 'null');
js = js.replace(/\{\{[\s\S]*?\}\}/g, '""');
js = js.replace(/\{!![\s\S]*?!!\}/g, 'null');
try {
  new Function(js);
  console.log('SIDEBAR SCRIPT PARSE OK');
} catch (e) {
  console.log('SIDEBAR SCRIPT PARSE FAIL', e.message);
}

// Also parse just the rules object area
const start = lines.slice(2887 - 1, 2930).join('\n');
try {
  new Function(start);
  console.log('rules snippet OK');
} catch (e) {
  console.log('rules snippet FAIL', e.message);
}
