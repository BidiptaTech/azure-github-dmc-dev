import React, { useEffect, useRef, useState } from "react";

const MultiSelectDropdown = ({
  label,
  placeholder = "Select options",
  options,
  onChange,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [selectedValues, setSelectedValues] = useState([]);
  const dropdownRef = useRef(null);

  const handleToggleDropdown = () => {
    setIsOpen(!isOpen);
  };

  const handleCheckboxChange = (option) => {
    const newSelectedValues = selectedValues.includes(option.value)
      ? selectedValues.filter((val) => val !== option.value)
      : [...selectedValues, option.value];

    setSelectedValues(newSelectedValues);

    if (onChange) {
      onChange(newSelectedValues);
    }
  };

  const handleRemoveTag = (valueToRemove) => {
    const newSelectedValues = selectedValues.filter(
      (val) => val !== valueToRemove
    );
    setSelectedValues(newSelectedValues);

    if (onChange) {
      onChange(newSelectedValues);
    }
  };

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const getSelectedLabels = () => {
    return options
      .filter((option) => selectedValues.includes(option.value))
      .map((option) => option.label);
  };

  return (
    <div className="form-input-select" ref={dropdownRef}>
      {label && <label className="lh-1 text-16 text-light-1">{label}</label>}
      <div className="custom-multi-select">
        <div
          className={`select-box ${isOpen ? "active" : ""}`}
          onClick={handleToggleDropdown}
        >
          <div className="selected-options">
            {selectedValues.length === 0 ? (
              <span className="placeholder">{placeholder}</span>
            ) : (
              getSelectedLabels().map((label, index) => (
                <span className="selected-tag" key={index}>
                  {label}
                  <span
                    className="remove-tag"
                    onClick={(e) => {
                      e.stopPropagation();
                      handleRemoveTag(
                        options.find((opt) => opt.label === label).value
                      );
                    }}
                  >
                    ×
                  </span>
                </span>
              ))
            )}
          </div>
          <div className="arrow-down">
            <i className="icon-chevron-down"></i>
          </div>
        </div>
        <div className={`options-container ${isOpen ? "active" : ""}`}>
          {options.map((option, index) => (
            <div className="option" key={index}>
              <input
                type="checkbox"
                id={`option-${option.value}`}
                checked={selectedValues.includes(option.value)}
                onChange={() => handleCheckboxChange(option)}
                className="option-checkbox"
              />
              <label htmlFor={`option-${option.value}`}>{option.label}</label>
            </div>
          ))}
        </div>
      </div>

      <style jsx>{`
        .custom-multi-select {
          position: relative;
          width: 100%;
          font-family: inherit;
        }

        .select-box {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
          min-height: 45px;
          padding: 8px 15px;
          background: #fff;
          border: 1px solid #ddd;
          border-radius: 4px;
          cursor: pointer;
          transition: all 0.3s ease;
        }

        .select-box:hover,
        .select-box.active {
          border-color: #3554d1;
        }

        .selected-options {
          display: flex;
          flex-wrap: wrap;
          gap: 5px;
        }

        .placeholder {
          color: #999;
        }

        .selected-tag {
          display: inline-flex;
          align-items: center;
          background: #e9ecef;
          padding: 2px 8px;
          border-radius: 20px;
          font-size: 14px;
          margin-right: 5px;
          margin-bottom: 3px;
        }

        .remove-tag {
          margin-left: 5px;
          font-weight: bold;
          cursor: pointer;
          font-size: 16px;
          line-height: 1;
        }

        .remove-tag:hover {
          color: #dc3545;
        }

        .arrow-down {
          font-size: 12px;
          transition: transform 0.3s ease;
        }

        .select-box.active .arrow-down {
          transform: rotate(180deg);
        }

        .options-container {
          position: absolute;
          top: 100%;
          left: 0;
          width: 100%;
          background: #fff;
          border: 1px solid #ddd;
          border-top: none;
          border-radius: 0 0 4px 4px;
          max-height: 0;
          overflow: hidden;
          z-index: 100;
          transition: all 0.3s ease;
          opacity: 0;
          margin-top: 5px;
        }

        .options-container.active {
          max-height: 200px;
          opacity: 1;
          overflow-y: auto;
          border: 1px solid #3554d1;
        }

        .option {
          padding: 10px 15px;
          cursor: pointer;
          transition: background-color 0.2s;
          display: flex;
          align-items: center;
        }

        .option:hover {
          background-color: #f5f5f5;
        }

        .option input {
          margin-right: 10px;
        }

        .option label {
          cursor: pointer;
          flex: 1;
        }
      `}</style>
    </div>
  );
};

export default MultiSelectDropdown;
