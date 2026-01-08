import { useState } from "react";

const Pagination = ({ currentPage, setCurrentPage, totalPages }) => {
  const handlePageClick = (pageNumber) => {
    setCurrentPage(pageNumber);
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
