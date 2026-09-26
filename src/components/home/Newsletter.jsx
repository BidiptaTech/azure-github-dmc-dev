import { useState } from "react";
import { Link } from "react-router-dom";

const Newsletter = () => {
  const [email, setEmail] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    // Handle newsletter subscription
    console.log("Newsletter subscription:", email);
    // You can add your subscription logic here
    alert("Thank you for subscribing! Please login to access all features.");
    setEmail("");
  };

  return (
    <section className="layout-pt-lg layout-pb-lg bg-blue-1">
      <div className="container">
        <div className="row justify-center text-center">
          <div className="col-xl-6 col-lg-8">
            <div className="sectionTitle -md">
              <h2 className="sectionTitle__title text-white">
                Stay Updated with Travel Deals
              </h2>
              <p className="sectionTitle__text mt-5 sm:mt-0 text-white">
                Get exclusive travel deals, destination guides, and travel tips delivered to your inbox
              </p>
            </div>
          </div>
        </div>
        
        <div className="row justify-center pt-40 sm:pt-20">
          <div className="col-xl-6 col-lg-8">
            <form onSubmit={handleSubmit} className="newsletter">
              <div className="newsletter__form">
                <input
                  type="email"
                  placeholder="Enter your email address"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  className="newsletter__input"
                />
                <button type="submit" className="newsletter__button button -md -blue-1 bg-white text-blue-1">
                  Subscribe Now
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <div className="row justify-center pt-40 sm:pt-20">
          <div className="col-auto">
            <div className="d-flex items-center justify-center x-gap-30 text-white">
              <div className="text-center">
                <div className="text-20 fw-600">100K+</div>
                <div className="text-14">Newsletter Subscribers</div>
              </div>
              <div className="text-center">
                <div className="text-20 fw-600">Weekly</div>
                <div className="text-14">Travel Updates</div>
              </div>
              <div className="text-center">
                <div className="text-20 fw-600">Exclusive</div>
                <div className="text-14">Member Deals</div>
              </div>
            </div>
          </div>
        </div>
        
        <div className="row justify-center pt-60 sm:pt-40">
          <div className="col-auto">
            <div className="text-center">
              <h3 className="text-white text-24 fw-600 mb-20">
                Ready to Start Your Next Adventure?
              </h3>
              <p className="text-white text-15 mb-30">
                Join TravClan today and discover a world of amazing travel experiences
              </p>
              <Link to="/login" className="button -md -white bg-white text-blue-1 fw-600">
                Join TravClan Now
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Newsletter; 