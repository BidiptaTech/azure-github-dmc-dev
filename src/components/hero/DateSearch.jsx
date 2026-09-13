import React, { useState, useEffect, useRef, useMemo, useCallback } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import "./DateSearch.css";

const toDateObject = (value) => {
  if (!value) return null;
  if (value instanceof DateObject) return value;
  try {
    return new DateObject(value);
  } catch {
    return null;
  }
};

const sameDay = (a, b) => {
  if (!a || !b) return false;
  return (
    a.year === b.year &&
    a.month?.number === b.month?.number &&
    a.day === b.day
  );
};

const sameRange = (a, b) => {
  if (!Array.isArray(a) || !Array.isArray(b) || a.length !== b.length) {
    return false;
  }
  if (a.length === 0) return true;
  if (a.length === 1) return sameDay(toDateObject(a[0]), toDateObject(b[0]));
  return (
    sameDay(toDateObject(a[0]), toDateObject(b[0])) &&
    sameDay(toDateObject(a[1]), toDateObject(b[1]))
  );
};

const DateSearch = ({
  onDateChange,
  disabled = false,
  minDate: minDateProp,
  maxDate: maxDateProp,
  value: valueProp,
  blockedRanges = [],
  notifyOnMount = false,
  calendarPosition = "bottom-center",
}) => {
  const today = useMemo(() => new DateObject(), []);
  const resolvedMin = useMemo(
    () => toDateObject(minDateProp) || today,
    [minDateProp, today]
  );
  const resolvedMax = useMemo(
    () => toDateObject(maxDateProp),
    [maxDateProp]
  );

  const getFallbackRange = useCallback(() => {
    if (Array.isArray(valueProp) && valueProp.length === 2) {
      return [toDateObject(valueProp[0]), toDateObject(valueProp[1])];
    }
    let start = new DateObject(resolvedMin);
    let end = new DateObject(resolvedMin).add(1, "day");
    if (resolvedMax && end > resolvedMax) {
      end = new DateObject(resolvedMax);
    }
    if (resolvedMax && start > resolvedMax) {
      start = new DateObject(resolvedMax);
      end = new DateObject(resolvedMax);
    }
    return [start, end];
  }, [valueProp, resolvedMin, resolvedMax]);

  const [dates, setDates] = useState(() => getFallbackRange());
  const [isMobile, setIsMobile] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const datePickerRef = useRef(null);
  const didNotifyMount = useRef(false);
  const isSelectingRef = useRef(false);
  const onDateChangeRef = useRef(onDateChange);

  useEffect(() => {
    onDateChangeRef.current = onDateChange;
  }, [onDateChange]);

  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth < 768);
    checkMobile();
    window.addEventListener("resize", checkMobile);
    return () => window.removeEventListener("resize", checkMobile);
  }, []);

  useEffect(() => {
    if (!disabled) return;
    setIsOpen(false);
    try {
      datePickerRef.current?.closeCalendar?.();
    } catch {
      // ignore
    }
  }, [disabled]);

  useEffect(() => {
    if (isSelectingRef.current) return;
    if (!Array.isArray(valueProp) || valueProp.length !== 2) return;
    const next = [toDateObject(valueProp[0]), toDateObject(valueProp[1])];
    if (!sameRange(dates, next)) {
      setDates(next);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [valueProp]);

  useEffect(() => {
    if (isSelectingRef.current) return;
    if (!Array.isArray(dates) || dates.length !== 2) return;

    let start = new DateObject(dates[0]);
    let end = new DateObject(dates[1]);
    let changed = false;

    if (resolvedMin && start < resolvedMin) {
      start = new DateObject(resolvedMin);
      changed = true;
    }
    if (resolvedMax && end > resolvedMax) {
      end = new DateObject(resolvedMax);
      changed = true;
    }
    if (start > end) {
      end = new DateObject(start);
      changed = true;
    }

    if (changed) {
      const next = [start, end];
      setDates(next);
      onDateChangeRef.current?.(next);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [minDateProp, maxDateProp]);

  useEffect(() => {
    if (!notifyOnMount || didNotifyMount.current) return;
    didNotifyMount.current = true;
    if (dates?.length === 2) {
      onDateChangeRef.current?.(dates);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const blockedStartEnds = useMemo(() => {
    if (!blockedRanges?.length) return [];
    return blockedRanges
      .map((range) => {
        if (!range || range.length < 2) return null;
        const start = toDateObject(range[0]);
        const end = toDateObject(range[1]);
        if (!start || !end) return null;
        return { start, end };
      })
      .filter(Boolean);
  }, [blockedRanges]);

  const mapDays = useCallback(
    ({ date }) => {
      if (!blockedStartEnds.length) return {};
      const blocked = blockedStartEnds.some(
        ({ start, end }) => date >= start && date < end
      );
      if (!blocked) return {};
      return {
        disabled: true,
        style: { color: "#ccc", textDecoration: "line-through" },
      };
    },
    [blockedStartEnds]
  );

  const handleDateChange = useCallback(
    (newDates) => {
      if (Array.isArray(newDates) && newDates.length === 1) {
        isSelectingRef.current = true;
        setDates(newDates);
        return;
      }

      isSelectingRef.current = false;

      if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
        const fallback = getFallbackRange();
        setDates(fallback);
        onDateChangeRef.current?.(fallback);
        return;
      }

      setDates(newDates);
      onDateChangeRef.current?.(newDates);
    },
    [getFallbackRange]
  );

  const handleOpen = useCallback(() => {
    isSelectingRef.current = false;
    setIsOpen(true);
  }, []);

  const handleClose = useCallback(() => {
    isSelectingRef.current = false;
    setIsOpen(false);
    setDates((prev) => {
      if (Array.isArray(prev) && prev.length === 1) {
        return getFallbackRange();
      }
      return prev;
    });
  }, [getFallbackRange]);

  return (
    <div
      className={`text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker ${
        isOpen ? "is-open" : ""
      }`}
    >
      <DatePicker
        ref={datePickerRef}
        inputClass="custom_input-picker"
        containerClassName={`custom_container-picker ${disabled ? "disabled" : ""}`}
        value={dates}
        onChange={handleDateChange}
        onOpen={handleOpen}
        onClose={handleClose}
        numberOfMonths={isMobile ? 1 : 2}
        offsetY={8}
        range
        rangeHover
        format="MMM DD"
        minDate={resolvedMin}
        maxDate={resolvedMax || undefined}
        editable={false}
        disabled={disabled}
        mapDays={mapDays}
        portal
        calendarPosition={calendarPosition}
        className="enhanced-date-picker"
      />
    </div>
  );
};

export default React.memo(DateSearch);
