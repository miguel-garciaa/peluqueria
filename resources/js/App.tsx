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
import { useReducedMotion } from "@/hooks/use-reduced-motion";

export default function App({ bookingEndpoint, csrfToken }: { bookingEndpoint: string; csrfToken: string }) {
  const [selectedServiceId, setSelectedServiceId] = useState("");
  const reducedMotion = useReducedMotion();
  const bookService = (serviceId: string) => {
    setSelectedServiceId(serviceId);
    window.requestAnimationFrame(() => document.getElementById("reservas")?.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" }));
  };

  return (
    <>
      <a className="skip-link" href="#main-content">Saltar al contenido</a>
      <Navbar />
      <main id="main-content">
        <Hero />
        <Services onBook={bookService} />
        <Gallery />
        <BeforeAfter />
        <Testimonials />
        <BookingSection selectedServiceId={selectedServiceId} bookingEndpoint={bookingEndpoint} csrfToken={csrfToken} />
      </main>
      <Footer />
      <CustomCursor />
    </>
  );
}
