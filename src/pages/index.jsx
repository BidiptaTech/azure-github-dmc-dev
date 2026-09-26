import Wrapper from "@/components/layout/Wrapper";
import MainHome from "../pages/homes/home_1";
import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Home-1 || Travclick - Travel & Tour ",
  description: "Travclick - Travel & Tour ",
};

export default function Home() {
  return (
    <>
      <MetaComponent meta={metadata} />
      <Wrapper>
        <MainHome />
      </Wrapper>
    </>
  );
}
