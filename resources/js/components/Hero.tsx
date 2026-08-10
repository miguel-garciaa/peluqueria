import { ArrowDown, ArrowUpRight } from "lucide-react";
import { heroImage } from "@/data/content";

export function Hero() {
  return (
    <section id="inicio" className="relative grid min-h-[100svh] place-items-center overflow-hidden bg-ink text-white">
      <div className="hero-media absolute inset-0"><img src={heroImage} alt="Interior de un salón de peluquería contemporáneo" className="hero-image h-full w-full object-cover opacity-70" fetchPriority="high" /></div>
      <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.12_0.01_65/0.58),oklch(0.12_0.01_65/0.28)_45%,oklch(0.12_0.01_65/0.78))]" />
      <div className="hero-content showcase-shell relative z-10 flex min-h-[100svh] flex-col items-center justify-center pb-20 pt-28 text-center">
        <p className="hero-kicker mb-5 text-sm font-bold tracking-[0.06em] text-brass">Cartagena · Corte · Color · Tratamientos</p>
        <h1 className="hero-title mx-auto w-full" aria-label="Tu estilo, elevado.">
          <span aria-hidden="true" className="hero-line-mask"><span className="hero-line">Tu estilo,</span></span>
          <span aria-hidden="true" className="hero-line-mask"><span className="hero-line hero-line-accent">elevado.<span className="hero-title-mark text-brass">*</span></span></span>
        </h1>
        <div className="hero-aside mx-auto mt-8 max-w-2xl text-center">
          <p className="hero-copy text-base leading-7 text-white/80 sm:text-lg">En Baskuñana Peluqueros escuchamos tu idea y adaptamos corte, color y tratamiento a ti. Técnica actual y trato cercano en el centro de Cartagena.</p>
          <div className="hero-actions mt-6 flex w-full flex-col items-center justify-center gap-3 sm:flex-row sm:items-stretch">
            <a href="#reservas" className="group flex w-[min(100%,21rem)] items-center justify-center gap-3 rounded-full bg-white p-1.5 pr-5 font-bold text-ink transition-transform duration-200 hover:-translate-y-0.5 sm:w-auto sm:p-2 sm:pr-6"><span className="grid size-9 place-items-center rounded-full bg-brass-deep text-white sm:size-10"><ArrowUpRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" /></span>Reservar cita</a>
            <a href="#servicios" className="w-[min(100%,21rem)] rounded-full border border-white/35 px-6 py-3 font-bold text-white transition-colors hover:bg-white hover:text-ink sm:w-auto sm:px-7 sm:py-4">Ver servicios</a>
          </div>
        </div>
        <a href="#servicios" aria-label="Ir a servicios" className="hero-scroll-cue absolute bottom-6 right-0 grid size-11 place-items-center text-white/75 transition-colors hover:text-brass lg:bottom-10"><ArrowDown className="size-6" /></a>
      </div>
    </section>
  );
}
