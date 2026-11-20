const fs = require('fs');
const p = 'resources/views/layouts/sidebar.blade.php';
let s = fs.readFileSync(p, 'utf8');
const lines = s.split(/\n/);

// Show exact name + address lines
[2889, 2890, 2891, 2919, 2920, 2925, 2926, 2927].forEach((n) => {
  console.log(n, [...lines[n - 1]].map((c) => c.codePointAt(0).toString(16)).join(' '));
  console.log('   ', JSON.stringify(lines[n - 1]));
});

// Replace by line numbers (1-based) for reliability
const next = lines.slice();
next[2890 - 1] = '                strip: /[^a-zA-Z\\u00C0-\\u024F\\s\\-]/g,';
next[2891 - 1] = '                valid: /^[a-zA-Z\\u00C0-\\u024F\\s\\-]*$/,';
next[2920 - 1] = '                strip: /[<>"\\\\\\s]/g,';
next[2926 - 1] = '                strip: /[^a-zA-Z\\u00C0-\\u024F0-9\\s.,#\\-\\/]/g,';
next[2927 - 1] = '                valid: /^[a-zA-Z\\u00C0-\\u024F0-9\\s.,#\\-\\/]*$/,';

fs.writeFileSync(p, next.join('\n'));
console.log('patched lines 2890-2891, 2920, 2926-2927');
[2890, 2891, 2920, 2926, 2927].forEach((n) => {
  console.log(n, JSON.stringify(next[n - 1]));
});
