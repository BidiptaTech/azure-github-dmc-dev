import Social from "../../common/social/Social";

const Copyright = () => {
  return (
    <div className="row justify-center items-center y-gap-10">
      <div className="col-auto">
        <div className="row x-gap-30 y-gap-10">
          <div className="col-auto">
            <div className="d-flex items-center">
              © {new Date().getFullYear()} Travclicks. All rights reserved.
              <span className="mx-2">| Powered by Travclicks</span>
            </div>
          </div>
          {/* End .col */}

          <div className="col-auto">
            <div className="d-flex x-gap-15">
              <a href="#">Privacy</a>
              <a href="#">Terms</a>
              <a href="#">Site Map</a>
            </div>
          </div>
          {/* End .col */}
        </div>
        {/* End .row */}
      </div>
   
    </div>
  );
};

export default Copyright;
