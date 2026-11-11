/**
 * Quotation client-copy pricing helpers.
 * Applies flat markup/discount proportionally across booked services
 * (same behaviour as the dashboard Upcoming/Pending print flow).
 */

const SERVICE_KEYS = [
  "hotel",
  "entry_port",
  "exit_port",
  "travel_point",
  "travel_hourly",
  "attraction",
  "guide",
  "restaurant",
  "local_transport",
];

function getServiceLists(data) {
  if (!data || typeof data !== "object") {
    return SERVICE_KEYS.map(() => []);
  }
  return SERVICE_KEYS.map(
    (key) => data[key] || data.data?.[key] || []
  );
}

function applyItemAdjustment(item, discount) {
  if (!item?.totalPrice) return;

  item.basePrice = item.totalPrice;

  const itemMarkupAmount = item.calculatedMarkup || 0;
  const markedUpPrice = parseFloat(item.totalPrice) + itemMarkupAmount;
  item.markedUpPrice = markedUpPrice.toString();

  const itemDiscountAmount = item.calculatedDiscount || 0;
  item.totalPrice = (markedUpPrice - itemDiscountAmount).toString();

  item.markupAmount = itemMarkupAmount.toString();
  if (discount > 0) {
    item.discountAmount = itemDiscountAmount.toString();
  }

  delete item.calculatedMarkup;
  delete item.calculatedDiscount;
}

/**
 * Sum all service totalPrice values on a bookings payload.
 */
export function sumBookingServicePrices(bookings) {
  if (!bookings || typeof bookings !== "object") return 0;

  let total = 0;
  getServiceLists(bookings).forEach((list) => {
    list.forEach((item) => {
      if (item?.totalPrice) {
        total += parseFloat(item.totalPrice);
      }
    });
  });
  return total;
}

/**
 * Deep-clone bookings and apply flat markup/discount across services.
 * Returns the modified clone (does not mutate the original).
 */
export function applyQuotationMarkupDiscount(bookings, markup = 0, discount = 0) {
  if (!bookings || typeof bookings !== "object") return null;

  const modifiedData = JSON.parse(JSON.stringify(bookings));
  const [
    hotels,
    entryPorts,
    exitPorts,
    travelPoints,
    travelHourly,
    attractions,
    guides,
    restaurants,
    localTransports,
  ] = getServiceLists(modifiedData);

  let totalOriginalPrice = 0;
  [
    hotels,
    entryPorts,
    exitPorts,
    travelPoints,
    travelHourly,
    attractions,
    guides,
    restaurants,
    localTransports,
  ].forEach((list) => {
    list.forEach((item) => {
      if (item?.totalPrice) {
        totalOriginalPrice += parseFloat(item.totalPrice);
      }
    });
  });

  if (totalOriginalPrice === 0) {
    modifiedData.markupAmount = markup;
    modifiedData.discountAmount = discount;
    modifiedData.markupPercentage = 0;
    modifiedData.discountPercentage = 0;
    return modifiedData;
  }

  const markupPercentage = (markup / totalOriginalPrice) * 100;
  const discountPercentage = (discount / totalOriginalPrice) * 100;

  const items = [
    ...hotels,
    ...entryPorts,
    ...exitPorts,
    ...travelPoints,
    ...travelHourly,
    ...attractions,
    ...guides,
    ...restaurants,
    ...localTransports,
  ].filter((item) => item.totalPrice);

  items.sort((a, b) => parseFloat(b.totalPrice) - parseFloat(a.totalPrice));

  let totalAppliedMarkup = 0;
  items.forEach((item) => {
    item.calculatedMarkup = Math.floor(
      (parseFloat(item.totalPrice) / totalOriginalPrice) * markup
    );
    totalAppliedMarkup += item.calculatedMarkup;
  });

  let remainingMarkupCents = markup - totalAppliedMarkup;
  for (let i = 0; i < items.length && remainingMarkupCents > 0; i++) {
    items[i].calculatedMarkup += 1;
    remainingMarkupCents -= 1;
  }

  let totalAppliedDiscount = 0;
  items.forEach((item) => {
    item.calculatedDiscount = Math.floor(
      (parseFloat(item.totalPrice) / totalOriginalPrice) * discount
    );
    totalAppliedDiscount += item.calculatedDiscount;
  });

  let remainingDiscountCents = discount - totalAppliedDiscount;
  for (let i = 0; i < items.length && remainingDiscountCents > 0; i++) {
    items[i].calculatedDiscount += 1;
    remainingDiscountCents -= 1;
  }

  hotels.forEach((item) => applyItemAdjustment(item, discount));
  entryPorts.forEach((item) => applyItemAdjustment(item, discount));
  exitPorts.forEach((item) => applyItemAdjustment(item, discount));
  travelPoints.forEach((item) => applyItemAdjustment(item, discount));
  attractions.forEach((item) => applyItemAdjustment(item, discount));
  travelHourly.forEach((item) => applyItemAdjustment(item, discount));
  localTransports.forEach((item) => applyItemAdjustment(item, discount));
  guides.forEach((item) => applyItemAdjustment(item, discount));
  restaurants.forEach((item) => applyItemAdjustment(item, discount));

  modifiedData.markupAmount = markup;
  modifiedData.discountAmount = discount;
  modifiedData.markupPercentage = markupPercentage;
  modifiedData.discountPercentage = discountPercentage;

  return modifiedData;
}
