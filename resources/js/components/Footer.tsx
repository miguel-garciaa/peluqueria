import { Clock3, MapPin, Phone } from "lucide-react";
import { BrandMark } from "@/components/BrandMark";

export function Footer() {
  return (
    <footer className="bg-ink pb-8 text-white">
      <div className="container-shell border-t border-white/10 pt-10">
        <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <a href="#inicio" className="flex items-center gap-3 font-display text-xl font-semibold">
              <BrandMark className="size-8" />
              BASKUÑANA <span className="font-normal text-white/55">Peluqueros</span>
            </a>
            <p className="mt-4 max-w-xs text-sm leading-6 text-white/45">Corte, color y cuidado capilar con atención personalizada en Cartagena.</p>
          </div>
          <div>
            <h2 className="text-sm font-bold">Explora</h2>
            <nav className="mt-4 flex flex-col gap-3 text-sm text-white/50" aria-label="Navegación del pie">
              <a href="#servicios" className="hover:text-white">Servicios</a>
              <a href="#galeria" className="hover:text-white">Galería</a>
              <a href="#valoraciones" className="hover:text-white">Valoraciones</a>
              <a href="#reservas" className="hover:text-white">Reservas</a>
            </nav>
          </div>
          <div>
            <h2 className="text-sm font-bold">Visítanos</h2>
            <div className="mt-4 space-y-3 text-sm text-white/50">
              <p className="flex gap-2"><MapPin className="size-4 shrink-0" />Paseo Alfonso XIII, 28 · Cartagena</p>
              <a href="tel:+34968124445" className="flex gap-2 transition-colors hover:text-white"><Phone className="size-4 shrink-0" />968 12 44 45</a>
            </div>
          </div>
          <div>
            <h2 className="text-sm font-bold">Horario</h2>
            <p className="mt-4 flex gap-2 text-sm leading-6 text-white/50"><Clock3 className="mt-1 size-4 shrink-0" /><span>L–V · 9:30–20:00<br />Sábado · 9:00–15:00<br />Domingo · Cerrado</span></p>
          </div>
        </div>
        <div className="mt-10 flex flex-col justify-between gap-3 border-t border-white/10 pt-6 text-xs text-white/35 sm:flex-row">
          <p>© {new Date().getFullYear()} Baskuñana Peluqueros. Todos los derechos reservados.</p>
          <p>Privacidad · Cookies · Accesibilidad</p>
        </div>
      </div>
    </footer>
  );
}
