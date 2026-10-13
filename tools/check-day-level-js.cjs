const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
const s = fs.readFileSync(file, 'utf8');
const a = s.indexOf("@push('scripts')");
const chunk = s.slice(a);
const re = /<script>([\s\S]*?)<\/script>/g;
let js = '';
let m;
while ((m = re.exec(chunk))) {
  if (m[1].includes('multiCityPlans')) {
    js = m[1];
    break;
  }
}

// Simulate blade currency substitution
js = js
  .replace(/@json\((?:[^()]|\([^()]*\))*\)/g, '"x"')
  .replace(/\{\{\s*\$dmcCurrency\s*\}\}/g, 'IDR')
  .replace(/\{\{[\s\S]*?\}\}/g, '""')
  .replace(/\{!![\s\S]*?!!\}/g, 'null');

try {
  new Function(js);
  console.log('PARSE OK after blade sim');
} catch (e) {
  console.log('PARSE FAIL', e.message);
}

// Brace balance
let depth = 0, line = 1, col = 0, state = 'code';
for (let i = 0; i < js.length; i++) {
  const ch = js[i], next = js[i+1];
  if (ch === '\n') { line++; col = 0; } else col++;
  if (state === 'code') {
    if (ch === '/' && next === '/') { state = 'lc'; i++; continue; }
    if (ch === '/' && next === '*') { state = 'bc'; i++; continue; }
    if (ch === "'") { state = 'sq'; continue; }
    if (ch === '"') { state = 'dq'; continue; }
    if (ch === '`') { state = 'tq'; continue; }
    if (ch === '{') depth++;
    if (ch === '}') {
      depth--;
      if (depth < 0) {
        console.log('extra } at', line, col);
        depth = 0;
      }
    }
  } else if (state === 'lc' && ch === '\n') state = 'code';
  else if (state === 'bc' && ch === '*' && next === '/') { state = 'code'; i++; }
  else if (state === 'sq') {
    if (ch === '\\') { i++; continue; }
    if (ch === "'") state = 'code';
  } else if (state === 'dq') {
    if (ch === '\\') { i++; continue; }
    if (ch === '"') state = 'code';
  } else if (state === 'tq') {
    if (ch === '\\') { i++; continue; }
    if (ch === '`') state = 'code';
    // template expression
    if (ch === '$' && next === '{') {
      // crude: skip until matching } at depth 0 of expr — nested templates exist!
    }
  }
}
console.log('final depth', depth, 'end state', state);

// Find unmatched template backticks with a better scanner
function scanTemplates(src) {
  const stack = [];
  let i = 0, line = 1, col = 0;
  let mode = 'code'; // code | sq | dq | tq | lc | bc
  const issues = [];
  while (i < src.length) {
    const ch = src[i], next = src[i + 1];
    if (ch === '\n') { line++; col = 0; } else col++;
    if (mode === 'code') {
      if (ch === '/' && next === '/') { mode = 'lc'; i += 2; continue; }
      if (ch === '/' && next === '*') { mode = 'bc'; i += 2; continue; }
      if (ch === "'") { mode = 'sq'; i++; continue; }
      if (ch === '"') { mode = 'dq'; i++; continue; }
      if (ch === '`') {
        stack.push({ type: 'tq', line, col });
        mode = 'tq';
        i++;
        continue;
      }
    } else if (mode === 'lc') {
      if (ch === '\n') mode = 'code';
    } else if (mode === 'bc') {
      if (ch === '*' && next === '/') { mode = 'code'; i += 2; continue; }
    } else if (mode === 'sq') {
      if (ch === '\\') { i += 2; continue; }
      if (ch === "'") mode = 'code';
    } else if (mode === 'dq') {
      if (ch === '\\') { i += 2; continue; }
      if (ch === '"') mode = 'code';
    } else if (mode === 'tq') {
      if (ch === '\\') { i += 2; continue; }
      if (ch === '`') {
        stack.pop();
        // return to previous mode: if still in tq nest via ${}, stay in expression code
        mode = stack.length && stack[stack.length - 1].type === 'expr' ? 'code' : (stack.length && stack[stack.length - 1].type === 'tq' ? 'tq' : 'code');
        // Actually after closing tq, if we're inside ${}, mode should be code (expression)
        if (stack.length && stack[stack.length - 1].type === 'expr') mode = 'code';
        else if (stack.length && stack[stack.length - 1].type === 'tq') mode = 'tq';
        else mode = 'code';
        i++;
        continue;
      }
      if (ch === '$' && next === '{') {
        stack.push({ type: 'expr', line, col });
        mode = 'code';
        i += 2;
        continue;
      }
    }
    // handle } closing expr when in code and stack top is expr
    if (mode === 'code' && ch === '}' && stack.length && stack[stack.length - 1].type === 'expr') {
      stack.pop();
      mode = 'tq';
    }
    // handle { in code increasing — skip, only care expr markers
    i++;
  }
  if (stack.length) {
    console.log('UNCLOSED stack', stack.slice(-5));
  } else {
    console.log('templates balanced');
  }
}
scanTemplates(js);
