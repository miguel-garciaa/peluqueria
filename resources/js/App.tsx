import { useEffect, useState } from "react";
import { BeforeAfter } from "@/components/BeforeAfter";
import { BookingSection } from "@/components/BookingSection";
import { BookingModal } from "@/components/booking/BookingModal";
import { CustomCursor } from "@/components/CustomCursor";
import { Footer } from "@/components/Footer";
import { Gallery } from "@/components/Gallery";
import { Hero } from "@/components/Hero";
import { Navbar } from "@/components/Navbar";
import { Services } from "@/components/Services";
import { Testimonials } from "@/components/Testimonials";
import { Team } from "@/components/Team";
import type { BookingCatalog, CurrentUser } from "@/types";

export type { CurrentUser } from "@/types";

type AppProps = {
  bookingEndpoint: string;
  availabilityEndpoint: string;
  bookingCatalog: BookingCatalog;
  csrfToken: string;
  currentUser: CurrentUser | null;
  authMessage: string | null;
  authMessageType: "success" | "error";
};

export default function App({ bookingEndpoint, availabilityEndpoint, bookingCatalog, csrfToken, currentUser, authMessage, authMessageType }: AppProps) {
  const [bookingOpen, setBookingOpen] = useState(false);
  const [bookingIntent, setBookingIntent] = useState<{ serviceId?: string; professionalId?: string }>({});
  const [authNotice, setAuthNotice] = useState(false);

  useEffect(() => {
    if (!authNotice) return;
    const timeout = window.setTimeout(() => setAuthNotice(false), 7000);
    return () => window.clearTimeout(timeout);
  }, [authNotice]);

  const openBooking = (intent: { serviceId?: string; professionalId?: string } = {}) => {
    if (!currentUser) {
      setAuthNotice(true);
      return;
    }
    setBookingIntent(intent);
    setBookingOpen(true);
  };

  return (
    <>
      <a className="skip-link" href="#main-content">Saltar al contenido</a>
      <Navbar currentUser={currentUser} csrfToken={csrfToken} onBook={() => openBooking()} />
      {authMessage && <div role="status" className={`fixed right-4 top-24 z-[60] max-w-sm rounded-2xl px-5 py-3 text-sm font-bold shadow-xl ${authMessageType === "error" ? "bg-red-700 text-white" : "bg-white text-ink"}`}>{authMessage}</div>}
      {authNotice && <div role="alert" className="fixed inset-x-4 top-24 z-[90] ml-auto max-w-md rounded-2xl border border-white/10 bg-ink p-5 text-white shadow-2xl"><button type="button" onClick={() => setAuthNotice(false)} className="absolute right-3 top-3 grid size-8 place-items-center rounded-full text-white/60 hover:bg-white/10 hover:text-white" aria-label="Cerrar aviso">×</button><p className="font-display text-xl font-semibold">Inicia sesión para reservar</p><p className="mt-2 pr-5 text-sm leading-6 text-white/65">Tu cuenta nos permite guardar la cita y enviarte la confirmación.</p><a href="/auth/google" className="mt-4 inline-flex min-h-11 items-center rounded-full bg-brass px-5 text-sm font-bold text-ink">Iniciar sesión o registrarme</a></div>}
      <main id="main-content">
        <Hero onBook={() => openBooking()} />
        <Services catalogServices={bookingCatalog.services} onBook={(serviceId) => openBooking({ serviceId })} />
        <Team onBook={(professionalId) => openBooking({ professionalId })} />
        <Gallery />
        <BeforeAfter />
        <Testimonials />
        <BookingSection onBook={() => openBooking()} />
      </main>
      <Footer />
      <CustomCursor />
      {currentUser && <BookingModal open={bookingOpen} onClose={() => setBookingOpen(false)} currentUser={currentUser} catalog={bookingCatalog} intent={bookingIntent} bookingEndpoint={bookingEndpoint} availabilityEndpoint={availabilityEndpoint} csrfToken={csrfToken} />}
    </>
  );
}
