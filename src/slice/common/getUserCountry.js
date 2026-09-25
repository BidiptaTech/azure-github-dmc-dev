import Cookies from "js-cookie";
//import { useIPGeolocation } from 'react-ipgeolocation';

const getUserCountry = async () => {
  try {
    // Fetch the user's IP address and country from a public API
    const response = await fetch("https://ipwhois.app/json/");
    const data = await response.json();
    // Extract the country name
    const country = data.country;
    const country_code = data.country_code;
    const expiryDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 days
    // Set the country name in cookies

    Cookies.set("countryCode", country_code, {
      expires: expiryDate,
      secure: true,
      sameSite: "Strict",
    });
    return { country, country_code };
  } catch (error) {
    console.error("Error fetching user country:", error);
    return null;
  }
  // const country = useIPGeolocation;
  // Cookies.set("userCountry", country.country_name, {
  //   expires: expiryDate,
  //   secure: true,
  //   sameSite: "Strict",
  // });
  // return country.country_name;
};

export default getUserCountry;
