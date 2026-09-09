import { Link } from "react-router-dom";

const TravelServices = () => {
  const services = [
    {
      id: 1,
      title: "Hotels & Resorts",
      description: "Find the perfect accommodation for your stay",
      icon: "icon-bed-2",
      image: "/img/featureToursCard/1/1.png",
      link: "/login",
      stats: "50,000+ Properties"
    },
    {
      id: 2,
      title: "Tours & Activities",
      description: "Discover amazing experiences and adventures",
      icon: "icon-destination",
      image: "/img/featureToursCard/1/2.png",
      link: "/login",
      stats: "15,000+ Tours"
    },
    {
      id: 3,
      title: "Car Rentals",
      description: "Rent a car for your perfect road trip",
      icon: "icon-car",
      image: "/img/featureToursCard/1/3.png",
      link: "/login",
      stats: "25,000+ Cars"
    },
    {
      id: 4,
      title: "Restaurants",
      description: "Taste the best local cuisine and delicacies",
      icon: "icon-restaurant",
      image: "/img/featureToursCard/1/4.png",
      link: "/login",
      stats: "10,000+ Restaurants"
    }
  ];

  return (
    <section className="layout-pt-lg layout-pb-lg bg-light-2">
      <div className="container">
        <div className="row justify-center text-center">
          <div className="col-auto">
            <div className="sectionTitle -md">
              <h2 className="sectionTitle__title">Explore Our Services</h2>
              <p className="sectionTitle__text mt-5 sm:mt-0">
                Everything you need for your perfect travel experience
              </p>
            </div>
          </div>
        </div>
        
        <div className="row y-gap-40 justify-between pt-40 sm:pt-20">
          {services.map((service) => (
            <div key={service.id} className="col-xl-3 col-lg-6">
              <div className="featureCard -type-1 -hover-1">
                <div className="featureCard__image">
                  <img src={service.image} alt={service.title} />
                </div>
                <div className="featureCard__content">
                  <div className="featureCard__icon">
                    <i className={service.icon}></i>
                  </div>
                  <h4 className="featureCard__title text-18 fw-600 mt-15">
                    {service.title}
                  </h4>
                  <p className="featureCard__text text-14 mt-5">
                    {service.description}
                  </p>
                  <div className="featureCard__stats text-12 text-blue-1 fw-600 mt-10">
                    {service.stats}
                  </div>
                  <Link 
                    to={service.link} 
                    className="button -md -blue-1 bg-blue-1 text-white mt-20"
                  >
                    Explore Now
                  </Link>
                </div>
              </div>
            </div>
          ))}
        </div>
        
        <div className="row justify-center pt-60 sm:pt-40">
          <div className="col-auto">
            <div className="text-center">
              <h3 className="text-30 fw-600 mb-10">Ready to Start Your Journey?</h3>
              <p className="text-15 text-dark-1 mb-20">
                Join thousands of happy travelers who trust us for their adventures
              </p>
              <Link to="/login" className="button -md -blue-1 bg-blue-1 text-white">
                Get Started Today
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default TravelServices; 