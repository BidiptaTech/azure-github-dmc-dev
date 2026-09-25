import AppButton from "./AppButton";
import ContactInfo from "./ContactInfo";
import Copyright from "./Copyright";
import FooterContent from "./FooterContent";

const index = () => {
  return (
    <footer
      className="footer -type-1 footer-blue_custom"
      style={{
        background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
        color: "rgba(255,255,255,0.88)",
        marginTop: 8,
      }}
    >
      <div className="container">
        <div className="pt-60 pb-60">
          <div className="row y-gap-40 justify-between xl:justify-start">
            <div className="col-xl-2 col-lg-4 col-sm-6">
              <h5 className="text-16 fw-500 mb-30" style={{ color: "#fff" }}>
                Contact Us
              </h5>
              <ContactInfo />
            </div>

            <FooterContent />

            <div className="col-xl-2 col-lg-4 col-sm-6">
              <h5 className="text-16 fw-500 mb-30" style={{ color: "#fff" }}>
                Mobile
              </h5>
              <AppButton />
            </div>
          </div>
        </div>

        <div
          className="py-20"
          style={{ borderTop: "1px solid rgba(255,255,255,0.15)" }}
        >
          <Copyright />
        </div>
      </div>
    </footer>
  );
};

export default index;
