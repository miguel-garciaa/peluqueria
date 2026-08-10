import { useState } from "react";
import { BeforeAfter } from "@/components/BeforeAfter";
import { BookingSection } from "@/components/BookingSection";
import { CustomCursor } from "@/components/CustomCursor";
import { Footer } from "@/components/Footer";
import { Gallery } from "@/components/Gallery";
import { Hero } from "@/components/Hero";
import { Navbar } from "@/components/Navbar";
import { Services } from "@/components/Services";
import { Testimonials } from "@/components/Testimonials";
import { Team } from "@/components/Team";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

export type CurrentUser = { name: string; email: string; avatarUrl: string | null };

type AppProps = {
  bookingEndpoint: string;
  csrfToken: string;
  currentUser: CurrentUser | null;
  authMessage: string | null;
  authMessageType: "success" | "error";
};

export default function App({ bookingEndpoint, csrfToken, currentUser, authMessage, authMessageType }: AppProps) {
  const [selectedServiceId, setSelectedServiceId] = useState("");
  const [selectedProfessionalId, setSelectedProfessionalId] = useState("");
  const reducedMotion = useReducedMotion();
  const bookService = (serviceId: string) => {
    setSelectedServiceId(serviceId);
    window.requestAnimationFrame(() => document.getElementById("reservas")?.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" }));
  };
  const bookProfessional = (professionalId: string) => {
    setSelectedProfessionalId(professionalId);
    window.requestAnimationFrame(() => document.getElementById("reservas")?.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" }));
  };

  return (
    <>
      <a className="skip-link" href="#main-content">Saltar al contenido</a>
      <Navbar currentUser={currentUser} csrfToken={csrfToken} />
      {authMessage && <div role="status" className={`fixed right-4 top-24 z-[60] max-w-sm rounded-2xl px-5 py-3 text-sm font-bold shadow-xl ${authMessageType === "error" ? "bg-red-700 text-white" : "bg-white text-ink"}`}>{authMessage}</div>}
      <main id="main-content">
        <Hero />
        <Services onBook={bookService} />
        <Team onBook={bookProfessional} />
        <Gallery />
        <BeforeAfter />
        <Testimonials />
        <BookingSection selectedServiceId={selectedServiceId} selectedProfessionalId={selectedProfessionalId} bookingEndpoint={bookingEndpoint} csrfToken={csrfToken} />
      </main>
      <Footer />
      <CustomCursor />
    </>
  );
}
