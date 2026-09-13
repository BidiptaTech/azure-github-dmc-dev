// const CallToActions = () => {
//   return (
//     <section className="layout-pt-md layout-pb-md bg-dark-2">
//       <div className="container">
//         <div className="row y-gap-10 justify-between items-center mt-20">
//           <div className="col-6">
//             <div 
//               className="row y-gap-20 flex-wrap items-center" 
//               style={{ width: "40%", margin: "0 auto", padding: "10px 10px", borderRadius: "10px" }}
//             >
//               <img src="/Images/Singapore.jpg" alt="Singapore" style={{ backgroundColor: "#ffffff", borderRadius: "10px" }}/>
//             </div>
//           </div>
//           {/* End .col */}

//           <div className="col-6">
//             <div 
//               className="row y-gap-20 flex-wrap items-center" 
//               style={{ width: "40%", margin: "0 auto", padding: "10px 10px", borderRadius: "10px" }}
//             >
//               <img src="/Images/India.jpg" alt="India" style={{ backgroundColor: "#ffffff", borderRadius: "10px" }}/>
//               {/* End subscribe btn */}
//             </div>
//           </div>
//           {/* End .col */}
//         </div>
//       </div>
//     </section>
//   );
// };

// export default CallToActions;


const CallToActions = () => {
  return (
    <section className="layout-pt-md layout-pb-md bg-dark-2">
      <div className="container">
        <div className="row y-gap-10 justify-between items-center mt-20">
          <div className="col-6">
            <div
              className="row y-gap-20 flex-wrap items-center"
              style={{
                width: "40%",
                margin: "0 auto",
                padding: "10px 10px",
                borderRadius: "10px",
              }}
            >
              <div
                style={{
                  width: "100%",
                  paddingTop: "56.25%", // Aspect ratio placeholder
                  // backgroundColor: "#ffffff",
                  borderRadius: "10px",
                  height: "300px"
                }}
              />
            </div>
          </div>

          <div className="col-6">
            <div
              className="row y-gap-20 flex-wrap items-center"
              style={{
                width: "40%",
                margin: "0 auto",
                padding: "10px 10px",
                borderRadius: "10px",
              }}
            >
              <div
                style={{
                  width: "100%",
                  paddingTop: "56.25%", // Aspect ratio placeholder
                  // backgroundColor: "#ffffff",
                  borderRadius: "10px",
                  height: "300px"

                }}
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default CallToActions;
