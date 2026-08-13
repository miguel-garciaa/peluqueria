import { useCallback, useEffect, useState } from "react";
import { BeforeAfter } from "@/components/BeforeAfter";
import { BookingModal } from "@/components/booking/BookingModal";
import { BookingSection } from "@/components/BookingSection";
import { CustomCursor } from "@/components/CustomCursor";
import { Footer } from "@/components/Footer";
import { Gallery } from "@/components/Gallery";
import { Hero } from "@/components/Hero";
import { MobileAccountPage } from "@/components/MobileAccountPage";
import { Navbar } from "@/components/Navbar";
import { PwaStatus } from "@/components/PwaStatus";
import { Services } from "@/components/Services";
import { Testimonials } from "@/components/Testimonials";
import { Team } from "@/components/Team";
import { TimedNotice } from "@/components/TimedNotice";
import { mobileViewFromPath, mobileViewPaths, mobileViewTitles } from "@/lib/mobile-navigation";
import { cn } from "@/lib/utils";
import type { BookingCatalog, CurrentUser, MobileView } from "@/types";

export type { CurrentUser } from "@/types";

const isPhoneViewport = () => window.matchMedia("(max-width: 47.999rem)").matches;

type AppProps = {
  bookingEndpoint: string;
  availabilityEndpoint: string;
  bookingCatalog: BookingCatalog;
  csrfToken: string;
  currentUser: CurrentUser | null;
  initialMobileView: MobileView;
  authMessage: string | null;
  authMessageType: "success" | "error";
  pushPublicKey?: string;
  pushSubscriptionEndpoint?: string;
};

export default function App({ bookingEndpoint, availabilityEndpoint, bookingCatalog, csrfToken, currentUser, initialMobileView, authMessage, authMessageType, pushPublicKey = "", pushSubscriptionEndpoint = "/notificaciones/suscripcion" }: AppProps) {
  const phoneViewport = isPhoneViewport();
  const startsInMobileBooking = initialMobileView === "reservar" && phoneViewport;
  const [bookingOpen, setBookingOpen] = useState(startsInMobileBooking && currentUser !== null);
  const [bookingIntent, setBookingIntent] = useState<{ serviceId?: string; professionalId?: string }>({});
  const [authNotice, setAuthNotice] = useState(startsInMobileBooking && currentUser === null);
  const [mobileView, setMobileView] = useState<MobileView>(initialMobileView);

  const openBooking = useCallback((intent: { serviceId?: string; professionalId?: string } = {}) => {
    if (!currentUser) {
      setAuthNotice(true);
      return;
    }
    setBookingIntent(intent);
    setBookingOpen(true);
  }, [currentUser]);

  const focusMobileScreen = useCallback(() => {
    window.requestAnimationFrame(() => {
      document.querySelector<HTMLElement>(".mobile-app-screen--active")?.scrollIntoView({ behavior: "auto", block: "start" });
    });
  }, []);

  const navigateMobileView = useCallback((view: MobileView) => {
    if (window.location.pathname !== mobileViewPaths[view]) {
      window.history.pushState({ mobileView: view }, "", mobileViewPaths[view]);
    }
    setMobileView(view);
    if (view === "reservar") openBooking();
    else setBookingOpen(false);
    focusMobileScreen();
  }, [focusMobileScreen, openBooking]);

  const closeBooking = useCallback(() => {
    setBookingOpen(false);
    if (!isPhoneViewport() || mobileView !== "reservar") return;

    window.history.replaceState({ mobileView: "inicio" }, "", mobileViewPaths.inicio);
    setMobileView("inicio");
    focusMobileScreen();
  }, [focusMobileScreen, mobileView]);

  useEffect(() => {
    const onPopState = () => {
      const nextView = mobileViewFromPath(window.location.pathname);
      setMobileView(nextView);
      if (isPhoneViewport() && nextView === "reservar") openBooking();
      else setBookingOpen(false);
      focusMobileScreen();
    };
    window.addEventListener("popstate", onPopState);
    return () => window.removeEventListener("popstate", onPopState);
  }, [focusMobileScreen, openBooking]);

  useEffect(() => {
    document.title = mobileViewTitles[mobileView];
  }, [mobileView]);

  useEffect(() => {
    if (initialMobileView === "inicio" || !window.matchMedia("(min-width: 48rem)").matches) return;
    const sectionId = initialMobileView === "reservar" ? "reservas" : initialMobileView;
    window.requestAnimationFrame(() => document.getElementById(sectionId)?.scrollIntoView({ behavior: "auto", block: "start" }));
  }, [initialMobileView]);

  useEffect(() => {
    if (!authNotice) return;
    const timeout = window.setTimeout(() => setAuthNotice(false), 3000);
    return () => window.clearTimeout(timeout);
  }, [authNotice]);

  return (
    <>
      <a className="skip-link" href="#main-content">Saltar al contenido</a>
      <Navbar currentUser={currentUser} csrfToken={csrfToken} onBook={() => openBooking()} activeMobileView={mobileView} onMobileViewChange={navigateMobileView} />
      <TimedNotice
        message={authMessage}
        role={authMessageType === "error" ? "alert" : "status"}
        className={`fixed right-4 top-24 z-[60] max-w-sm rounded-2xl px-5 py-3 text-sm font-bold shadow-xl ${authMessageType === "error" ? "bg-red-700 text-white" : "bg-white text-ink"}`}
      />
      {authNotice && <div role="alert" className="fixed inset-x-4 top-24 z-[90] ml-auto max-w-md rounded-2xl border border-white/10 bg-ink p-5 text-white shadow-2xl"><button type="button" onClick={() => setAuthNotice(false)} className="absolute right-3 top-3 grid size-8 place-items-center rounded-full text-white/60 hover:bg-white/10 hover:text-white" aria-label="Cerrar aviso">×</button><p className="font-display text-xl font-semibold">Inicia sesión para reservar</p><p className="mt-2 pr-5 text-sm leading-6 text-white/65">Tu cuenta nos permite guardar la cita y enviarte la confirmación.</p><a href="/auth/google" className="mt-4 inline-flex min-h-11 items-center rounded-full bg-brass px-5 text-sm font-bold text-ink">Iniciar sesión o registrarme</a></div>}
      <main id="main-content">
        <div className={cn("mobile-app-screen", mobileView === "inicio" ? "mobile-app-screen--active" : "hidden md:block")}>
          <Hero onBook={() => openBooking()} onViewServices={() => navigateMobileView("servicios")} />
        </div>
        <div className={cn("mobile-app-screen", mobileView === "servicios" ? "mobile-app-screen--active" : "hidden md:block")}>
          <Services catalogServices={bookingCatalog.services} onBook={(serviceId) => openBooking({ serviceId })} />
        </div>
        <div className={cn("mobile-app-screen", mobileView === "equipo" ? "mobile-app-screen--active" : "hidden md:block")}>
          <Team catalogProfessionals={bookingCatalog.professionals} catalogServices={bookingCatalog.services} onBook={(professionalId) => openBooking({ professionalId })} />
        </div>
        <div className={cn("mobile-app-screen", mobileView === "galeria" ? "mobile-app-screen--active" : "hidden md:block")}>
          <Gallery />
          <BeforeAfter />
        </div>
        <div className="hidden md:block"><Testimonials /></div>
        {!phoneViewport && <div className={cn("mobile-app-screen", mobileView === "reservar" ? "mobile-app-screen--active" : "hidden md:block")}>
          <BookingSection onBook={() => openBooking()} onViewHome={() => navigateMobileView("inicio")} />
        </div>}
        <div className={cn("mobile-app-screen md:hidden", mobileView === "cuenta" ? "mobile-app-screen--active" : "hidden")}>
          <MobileAccountPage currentUser={currentUser} csrfToken={csrfToken} pushPublicKey={pushPublicKey} pushSubscriptionEndpoint={pushSubscriptionEndpoint} />
        </div>
      </main>
      <div className="hidden md:block"><Footer /></div>
      <CustomCursor />
      <PwaStatus />
      {currentUser && bookingOpen && (
        <BookingModal open onClose={closeBooking} currentUser={currentUser} catalog={bookingCatalog} intent={bookingIntent} bookingEndpoint={bookingEndpoint} availabilityEndpoint={availabilityEndpoint} csrfToken={csrfToken} />
      )}
    </>
  );
}
