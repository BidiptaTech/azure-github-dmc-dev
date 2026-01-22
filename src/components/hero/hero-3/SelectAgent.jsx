import { useState, useRef, useEffect, useMemo } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchAgentList } from "@/slice/common/agentListSlice";

const SelectAgent = ({ onAgentSelect, initialValue = null }) => {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const [isDropdownVisible, setIsDropdownVisible] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);
  
  // Get agents list from Redux state
  const { agents, loading, error } = useSelector((state) => state.agentList);
  
  // Fetch agents when component mounts
  useEffect(() => {
    dispatch(fetchAgentList());
  }, [dispatch]);

  // Process available agents - memoize to prevent recreating on every render
  const agentSearchContent = useMemo(() => {
    if (agents && Array.isArray(agents)) {
      return agents.map((agent) => ({
        id: agent.agent_id,
        name: agent.name,
        key: `agent-${agent.agent_id}`
      }));
    } else {
      return [];
    }
  }, [agents]); // Only recalculate when agents changes

  // Set initial value if provided
  useEffect(() => {
    if (initialValue && !selectedItem && agentSearchContent.length > 0) {
      const foundAgent = agentSearchContent.find(
        agent => agent.id === initialValue || agent.name === initialValue
      );
      
      if (foundAgent) {
        setSelectedItem(foundAgent);
        setSearchValue(foundAgent.name);
      } else if (typeof initialValue === 'string') {
        // If we can't find the agent but have a string, use it as is
        setSelectedItem({ name: initialValue });
        setSearchValue(initialValue);
      }
    }
  }, [initialValue, agentSearchContent, selectedItem]);

  // Filter suggestions based on search input
  useEffect(() => {
    if (!selectedItem) {
      if (searchValue && searchValue.trim().length > 0) {
        const filtered = agentSearchContent.filter(agent => 
          agent.name.toLowerCase().includes(searchValue.toLowerCase())
        );
        setSuggestions(filtered);
      } else {
        // When dropdown is open but no search text, show all agents
        setSuggestions(agentSearchContent);
      }
    }
  }, [searchValue, selectedItem, agentSearchContent]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (dropdownRef.current && highlightedIndex !== -1 && isDropdownVisible) {
      const activeItem = dropdownRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isDropdownVisible]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (
        dropdownRef.current && 
        !dropdownRef.current.contains(event.target) && 
        inputRef.current && 
        !inputRef.current.contains(event.target)
      ) {
        setIsDropdownVisible(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleOptionClick = (item) => {
    setSearchValue(item.name);
    setSelectedItem(item);
    setIsDropdownVisible(false);
    
    // Call the onAgentSelect callback if provided
    if (onAgentSelect) {
      onAgentSelect(item);
    }
    
    // Remove focus
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  const handleInputChange = (e) => {
    // If an agent is already selected, prevent typing
    if (selectedItem) return;
    
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
    
    // Ensure dropdown is visible when typing
    if (!isDropdownVisible) {
      setIsDropdownVisible(true);
    }
  };

  const handleInputFocus = () => {
    // Show dropdown on focus
    setIsDropdownVisible(true);
  };
  
  const toggleDropdown = () => {
    setIsDropdownVisible(!isDropdownVisible);
    
    // Focus the input when opening dropdown
    if (!isDropdownVisible && inputRef.current) {
      inputRef.current.focus();
    }
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownVisible(false);
    
    // Inform parent component about cleared selection
    if (onAgentSelect) {
      onAgentSelect(null);
    }
    
    // Focus the input
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  // Handle keyboard navigation
  const handleKeyDown = (e) => {
    // If an agent is already selected, only allow Escape or Backspace to clear
    if (selectedItem) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearSelection(e);
      }
      return;
    }
    
    if (!suggestions.length) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
      if (!isDropdownVisible) {
        setIsDropdownVisible(true);
      }
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleOptionClick(suggestions[highlightedIndex]);
    } else if (e.key === "Escape") {
      setIsDropdownVisible(false);
    }
  };

  return (
    <>
      <div className="searchMenu-loc px-30 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
        <div className="dropdown-container">
          <h4 className="text-15 fw-500 ls-2 lh-16">Select Agent</h4>
          <div className="text-15 text-light-1 ls-2 lh-16 position-relative dropdown-field">
            <input
              ref={inputRef}
              autoComplete="off"
              type="search"
              placeholder="Search for agent..."
              className="js-search js-dd-focus pr-30"
              value={searchValue}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onKeyDown={handleKeyDown}
              readOnly={selectedItem !== null}
            />
            {selectedItem && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y pe-3 border-0 bg-transparent cursor-pointer" 
                onClick={handleClearSelection}
                type="button"
              >
                <i className="icon-close text-10" />
              </button>
            )}
          </div>
        </div>

        <div 
          className={`shadow-2 dropdown-menu min-width-400 ${isDropdownVisible ? 'show' : ''}`}
          style={{
            position: 'absolute',
            zIndex: 9999,
            display: isDropdownVisible ? 'block' : 'none',
            top: '100%',
            left: 0,
            maxHeight: '300px',
            overflowY: 'auto',
          }}
        >
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            {loading ? (
              <div className="d-flex justify-content-center py-10">
                <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span className="ms-2">Loading agents...</span>
              </div>
            ) : (
              <>
                {searchValue && (
                  <div className="px-20 mb-10">
                    <small className="text-light-1">Searching: "{searchValue}"</small>
                  </div>
                )}
                {error && (
                  <div className="alert alert-danger m-3">
                    {error}
                  </div>
                )}
                <ul className="y-gap-5 js-results" ref={dropdownRef} tabIndex="-1">
                  {!loading && !error && suggestions.length === 0 && (
                    <li className="d-block col-12 text-left rounded-4 px-20 py-15">
                      No agents found
                    </li>
                  )}
                  {suggestions.map((item, index) => (
                    <li
                      className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                        highlightedIndex === index ? "active" : ""
                      }`}
                      key={item.key || index}
                      role="button"
                      onClick={() => handleOptionClick(item)}
                    >
                      <div className="d-flex">
                        <div className="ml-10">
                          <div className="text-15 lh-12 fw-500">{item.name}</div>
                          <div className="text-13 lh-12 text-light-1 mt-5">
                            Agent ID: {item.id}
                          </div>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              </>
            )}
          </div>
        </div>
      </div>

      <style jsx global>{`
        .highlighted,
        .active {
          background-color: rgba(5, 117, 230, 0.05);
        }
        
        .searchMenu-loc .js-search {
          border: none;
          background-color: transparent;
          width: 100%;
          outline: none;
          height: 40px;
        }
        
        .dropdown-container {
          position: relative;
          cursor: pointer;
        }
        
        .dropdown-field {
          display: flex;
          align-items: center;
          border-radius: 4px;
        }
      `}</style>
    </>
  );
};

export default SelectAgent; 