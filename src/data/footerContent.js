const footercontent = [
  {
    id: 1,
    title: "Company",
    menuList: [
      { name: "About Us", routerPath: "/" },
      { name: "Careers", routerPath: "/" },
      { name: "Blog", routerPath: "/" },
      { name: "Press", routerPath: "/" },
      { name: "Gift Cards", routerPath: "/" },
    ],
  },
  {
    id: 2,
    title: "Support",
    menuList: [
      { name: "Contact", routerPath: "/" },
      { name: "Legal Notice", routerPath: "/" },
      { name: "Privacy Policy", routerPath: "/" },
      { name: "Terms and Conditions", routerPath: "/" },
      { name: "Sitemap", routerPath: "/" },
    ],
  },
  {
    id: 3,
    title: "Other Services",
    menuList: [
      { name: "Hotel", routerPath: "/dashboard/db-dashboard/view-hotel-search/:id" },
      { name: "Pick up drop", routerPath: "/dashboard/db-dashboard/pickupdrop" },
      { name: "Attraction", routerPath: "/dashboard/db-dashboard/attractions" },
      { name: "Local Transfer", routerPath: "/dashboard/db-dashboard/localtransfer" },
      { name: "Tour Guide", routerPath: "/dashboard/db-dashboard/tourguide" },
      { name: "Restaurants", routerPath: "/dashboard/db-dashboard/restaurants" },
    ],
  },
];
export default footercontent;
