import { useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchLists } from "@/slice/common/TourlistSlice";

const Pagination = ({ currentPage, setCurrentPage, totalPages }) => {
  const dispatch = useDispatch();
  const { start, limit, type } = useSelector((state) => state.lists);
  
    const handlePageClick = (pageNumber) => {
    // console.log('Pagination: handlePageClick called with:', { pageNumber, currentPage, totalPages });
    
    // Calculate how many items we need to skip to reach this page
    // Since UI shows 5 items per page but API fetches 30 at a time
    const itemsPerPage = 5; // This should match rowsPerPage in the component
    const itemsToSkip = (pageNumber - 1) * itemsPerPage;
    
    // Calculate the API start position
    // We need to fetch data starting from the beginning of the chunk that contains our target page
    // For example: if we want page 7 (items 30-34), we need to fetch starting from position 30
    const newStart = Math.floor(itemsToSkip / limit) * limit;
    
    // console.log('Pagination: Dispatching fetchLists with:', { newStart, limit, type, reset: false });
    
    // Always fetch data when jumping to a new page to ensure we have enough data
    // This handles the case where we jump to the last page and need more data
    dispatch(fetchLists({ start: newStart, limit, type, reset: false })).then(() => {
      // console.log('Pagination: First fetchLists completed successfully');
      
      // Only update the current page after the data is fetched successfully
      setCurrentPage(pageNumber);
      
      // If we're going to the last page, always fetch the next chunk of data
      // This ensures that when you reach the last page, more pages will be added if available
      if (pageNumber === totalPages) {
        // Calculate the next chunk start position
        const nextStart = Math.ceil(itemsToSkip / limit) * limit ;
        
        // Fetch the next chunk to see if more data is available
        // Make sure we're using the correct type for the current component
        const currentType = type || "past"; // Default to "past" for Deleted.jsx
        
        // console.log('Fetching next chunk of data:', { 
          currentPage: pageNumber, 
        //   totalPages, 
        //   nextStart,
        //   limit,
        //   type,
        //   currentType
        // });
        
        dispatch(fetchLists({ start: nextStart, limit, type: currentType, reset: false }));
      }
    }).catch((error) => {
      console.error('Failed to fetch data for page:', pageNumber, error);
      // Don't update the page if the fetch failed
    });
  };

  const renderPage = (pageNumber, isActive = false) => {
    const className = `size-40 flex-center rounded-full cursor-pointer ${
      isActive ? "bg-dark-1 text-white" : ""
    }`;
    return (
      <div key={pageNumber} className="col-auto">
        <div className={className} onClick={() => handlePageClick(pageNumber)}>
          {pageNumber}
        </div>
      </div>
    );
  };

  const renderPages = () => {
    const pages = [];
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    // Ensure we always show exactly 5 pages
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
      pages.push(renderPage(1, currentPage === 1)); // First Page
      if (startPage > 2) {
        pages.push(
          <div key="dots1" className="col-auto">
            ...
          </div>
        ); // Ellipsis
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(renderPage(i, currentPage === i));
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        pages.push(
          <div key="dots2" className="col-auto">
            ...
          </div>
        ); // Ellipsis
      }
      pages.push(renderPage(totalPages, currentPage === totalPages)); // Last Page
    }

    return pages;
  };

  return (
    <div className="border-top-light mt-30 pt-30">
      <div className="row x-gap-10 y-gap-20 justify-between md:justify-center">
        {/* Previous Button */}
        <div className="col-auto md:order-1">
          <button
            className="button -blue-1 size-40 rounded-full border-light"
            onClick={() => handlePageClick(currentPage - 1)}
            disabled={currentPage === 1}
          >
            <i className="icon-chevron-left text-12" />
          </button>
        </div>

        {/* Page Numbers */}
        <div className="col-md-auto md:order-3">
          <div className="row x-gap-20 y-gap-20 items-center md:d-none">
            {renderPages()}
          </div>
        </div>

        {/* Next Button */}
        <div className="col-auto md:order-2">
          <button
            className="button -blue-1 size-40 rounded-full border-light"
            onClick={() => handlePageClick(currentPage + 1)}
            disabled={currentPage === totalPages}
          >
            <i className="icon-chevron-right text-12" />
          </button>
        </div>
      </div>
    </div>
  );
};

export default Pagination;
