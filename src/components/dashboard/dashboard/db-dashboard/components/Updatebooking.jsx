import { useSelector, useDispatch } from "react-redux";
import { useState, useEffect } from "react";
import DateRangeSelector from "./DateRangeSelector";
import GuestSelector from "./GuestSelector";

export default function Updatebooking() {
    const dispatch = useDispatch();
    
    const viewDetails = useSelector((state) => state.viewDetails.bookings);

    const [bookingData, setBookingData] = useState({
        checkin_date: "",
        checkout_date: "",
        adult: 1,
        child: 0,
        infant: 0,
        male: 0,
        female: 0,
        child_ages: []
    });
    const [hotel, setHotel] = useState({});
    const [entry_port, setEntryPort] = useState({});
    const [exit_port, setExitPort] = useState({});
    const [attraction, setAttraction] = useState({});
    const [restaurant, setRestaurant] = useState({});
    const [guide, setGuide] = useState({});
    const [travel, setTravel] = useState({});
    const [newBookingDate, setNewBookingDate] = useState({
        new_checkin_date: "",
        new_checkout_date: ""
    });
    
    useEffect(() => {
        if (viewDetails && viewDetails.tour) {
            const { tour,hotel,entry_port,exit_port,attraction,restaurant,guide,travel_point,travel_hourly,local_transport } = viewDetails;
            const travel = {
                travel_point: travel_point,
                travel_hourly: travel_hourly,
                local_transport: local_transport
            }
            setBookingData({
                checkin_date: tour.checkin_date || "",
                checkout_date: tour.checkout_date || "",
                adult: parseInt(tour.adult) || 1,
                child: parseInt(tour.child) || 0,
                infant: parseInt(tour.infant) || 0,
                male: parseInt(tour.male) || 0,
                female: parseInt(tour.female) || 0,
                child_ages: tour.child_ages || ""
            });
            setHotel(hotel);
            setEntryPort(entry_port);
            setExitPort(exit_port);
            setAttraction(attraction);
            setRestaurant(restaurant);
            setGuide(guide);
            setTravel(travel);
        }
    }, [viewDetails]);

    console.log("hotel",hotel);
    console.log("entry_port",entry_port);
    console.log("exit_port",exit_port);
    console.log("attraction",attraction);
    console.log("restaurant",restaurant);
    console.log("guide",guide);
    console.log("travel",travel);
    
    const handleDateChange = (dates) => {
        if (dates && dates.length === 2) {
            setNewBookingDate({
                new_checkin_date: dates[0].format("YYYY-MM-DD"),
                new_checkout_date: dates[1].format("YYYY-MM-DD")
            });
        }
    };
    
    const handleGuestChange = (guestCounts) => {
        setBookingData({
            ...bookingData,
            adult: guestCounts.Adults,
            child: guestCounts.Children,
            infant: guestCounts.Infants,
            male: guestCounts.maleCount,
            female: guestCounts.femaleCount,
            child_ages: guestCounts.ages?.join(', ') || ""
        });
    };
    
    return (
        <div className="dashboard-booking-update">
            <div className="row y-gap-30 mt-20">
                <div className="col-xl-12">
                    <div className="py-30 px-30 rounded-4 bg-white shadow-3">
                        {/* Header Section with Tour ID */}
                        <div className="border-bottom-light pb-20">
                            <div className="d-flex justify-between items-center">
                                <div className="d-flex items-center">
                                    <i className="icon-calendar-2 text-24 text-blue-1 mr-10"></i>
                                    <h3 className="text-22 fw-500">Update Booking Details</h3>
                                </div>
                                {viewDetails?.tour && (
                                    <div className="tour-id-badge py-5 px-15 rounded-100 bg-blue-1-05 text-15 fw-500 text-blue-1">
                                        <i className="icon-ticket text-blue-1 mr-5"></i>
                                        Tour ID: {viewDetails.tour.tour_id}
                                    </div>
                                )}
                            </div>
                            {viewDetails?.tour?.title && (
                                <div className="mt-10 text-15 text-light-1">
                                    {viewDetails.tour.title}
                                </div>
                            )}
                        </div>
                        
                        <div className="row y-gap-20 pt-20">
                            {/* Current Booking Info Section */}
                            <div className="col-12">
                                <div className="d-flex items-center">
                                    <div className="size-40 flex-center rounded-4 bg-blue-1-05 mr-10">
                                        <i className="icon-calendar text-yellow-1"></i>
                                    </div>
                                    <div className="text-15 fw-500">Previous Booking Dates</div>
                                </div>
                                <div className="row mt-15 py-15 px-10 bg-blue-1-05 rounded-4">
                                    <div className="col-md-6">
                                        <div className="text-14 text-light-1">Check-in Date</div>
                                        <div className="text-15 fw-500">{bookingData.checkin_date || "Not specified"}</div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="text-14 text-light-1">Check-out Date</div>
                                        <div className="text-15 fw-500">{bookingData.checkout_date || "Not specified"}</div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Date Range Selector */}
                            <div className="col-xl-6">
                                <div className="mb-15">
                                    <div className="d-flex items-center">
                                        <div className="size-40 flex-center rounded-4 bg-blue-2 mr-10">
                                            <i className="icon-calendar text-blue-1"></i>
                                        </div>
                                        <div className="text-15 fw-500">Select New Dates</div>
                                    </div>
                                </div>
                                <DateRangeSelector 
                                    initialCheckin={bookingData.checkin_date}
                                    initialCheckout={bookingData.checkout_date}
                                    onDateChange={handleDateChange}
                                />
                            </div>
                            
                            {/* Guest Selector */}
                            <div className="col-xl-6">
                                <div className="mb-15">
                                    <div className="d-flex items-center">
                                        <div className="size-40 flex-center rounded-4 bg-blue-2 mr-10">
                                            <i className="icon-user text-blue-1"></i>
                                        </div>
                                        <div className="text-15 fw-500">Update Guest Information</div>
                                    </div>
                                </div>
                                <GuestSelector 
                                    initialAdult={bookingData.adult}
                                    initialChild={bookingData.child}
                                    initialInfant={bookingData.infant}
                                    initialMale={bookingData.male}
                                    initialFemale={bookingData.female}
                                    initialChildAges={bookingData.child_ages}
                                    onGuestChange={handleGuestChange}
                                />
                            </div>

                            
                            {/* Summary Section */}
                            <div className="col-12 mt-20">
                                <div className="py-15 px-20 rounded-4 bg-green-1-05">
                                    <div className="d-flex items-center">
                                        <div className="size-40 flex-center rounded-4 bg-green-1 mr-10">
                                            <i className="icon-check text-white"></i>
                                        </div>
                                        <div className="text-16 fw-500">Booking Update Summary</div>
                                    </div>
                                    <div className="row y-gap-10 pt-15">
                                        <div className="col-md-6">
                                            <div className="text-14 text-light-1">New Check-in Date</div>
                                            <div className="text-15 fw-500">{newBookingDate.new_checkin_date || "No change"}</div>
                                        </div>
                                        <div className="col-md-6">
                                            <div className="text-14 text-light-1">New Check-out Date</div>
                                            <div className="text-15 fw-500">{newBookingDate.new_checkout_date || "No change"}</div>
                                        </div>
                                        <div className="col-md-6 mt-10">
                                            <div className="text-14 text-light-1">Total Guests</div>
                                            <div className="text-15 fw-500">{bookingData.adult + bookingData.child + bookingData.infant} (Adults: {bookingData.adult}, Children: {bookingData.child}, Infants: {bookingData.infant})</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Action Buttons */}
                            <div className="col-12 d-flex justify-end pt-20 border-top-light mt-20">
                                <button className="button h-50 px-24 -outline-blue-1 text-blue-1 mr-15">
                                    <i className="icon-refresh text-16 mr-10"></i>
                                    Reset
                                </button>
                                <button className="button h-50 px-30 -dark-1 bg-blue-1 text-white">
                                    <i className="icon-check text-16 mr-10"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
