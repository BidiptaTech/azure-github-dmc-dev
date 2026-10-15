const fs = require('fs');
const p = 'public/js/single-tour-package/lite/hotel.js';
let s = fs.readFileSync(p, 'utf8');
const start = s.indexOf("    <div class=\"col-md-5 d-flex align-items-end gap-2 flex-wrap\">' +");
const endMarker = "  <div class=\"stp-lite-hotel-breakup d-none\" data-hotel-breakup-panel>' +";
const end = s.indexOf(endMarker, start);
if (start < 0 || end < 0) {
  console.error('markers not found', start, end);
  process.exit(1);
}
const neu =
  "    <div class=\"col-md-7 hotel-child-pricing-section d-none\" data-guest-child-ui>' +\n" +
  "            '      <div class=\"d-flex flex-wrap gap-3 align-items-center\" style=\"min-height:31px;\">' +\n" +
  "            '        <div class=\"form-check hotel-child-with-bed-wrap d-none mb-0\">' +\n" +
  "            '          <input class=\"form-check-input hotel-chk-child-with-bed\" type=\"checkbox\">' +\n" +
  "            '          <label class=\"form-check-label\" style=\"font-size:0.78rem;\">Child with Bed <small class=\"text-muted hotel-child-with-bed-label\"></small></label>' +\n" +
  "            '        </div>' +\n" +
  "            '        <div class=\"form-check hotel-child-without-bed-wrap d-none mb-0\">' +\n" +
  "            '          <input class=\"form-check-input hotel-chk-child-without-bed\" type=\"checkbox\">' +\n" +
  "            '          <label class=\"form-check-label\" style=\"font-size:0.78rem;\">Child without Bed <small class=\"text-muted hotel-child-without-bed-label\"></small></label>' +\n" +
  "            '        </div>' +\n" +
  "            '      </div>' +\n" +
  "            '    </div>' +\n" +
  "            '  </div>' +\n" +
  "            '  <div class=\"row g-2 mb-2\">' +\n" +
  "            '    <div class=\"col-md-12 d-flex align-items-end gap-2 flex-wrap\">' +\n" +
  "            '      <button type=\"button\" class=\"btn btn-sm stp-lite-get-price-btn hotel-get-price-btn\">' +\n" +
  "            '        <i class=\"ri-price-tag-3-line me-1\"></i>Get Price' +\n" +
  "            '      </button>' +\n" +
  "            '      <button type=\"button\" class=\"btn btn-sm btn-primary hotel-add-btn\" disabled>' +\n" +
  "            '        <i class=\"ri-add-line me-1\"></i>Add Hotel' +\n" +
  "            '      </button>' +\n" +
  "            '      <span class=\"stp-lite-loader hotel-price-loader\"><span class=\"spinner-border spinner-border-sm\"></span> Calculating…</span>' +\n" +
  "            '    </div>' +\n" +
  "            '  </div>' +\n" +
  "            '  ";
s = s.slice(0, start) + neu + s.slice(end);
fs.writeFileSync(p, s);
console.log('patched hotel child beds before get price');
