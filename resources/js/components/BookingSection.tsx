import { ArrowUp, ArrowUpRight, CalendarCheck2, Check, Clock3, MapPin, Phone } from "lucide-react";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";
import { site } from "@/data/site";

export function BookingSection({ onBook, onViewHome }: { onBook: () => void; onViewHome?: () => void }) {
  return (
    <section id="reservas" className="section-space bg-ink text-white">
      <div className="container-shell">
        <div className="mb-10 lg:mb-12"><p className="mb-3 font-semibold text-brass">Tu momento empieza aquí</p><RevealTitle label="Reserva fácil." className="display-title max-w-5xl" lines={[{ content: "Reserva" }, { content: "fácil.", className: "font-normal text-brass" }]} /></div>
        <ScrollReveal className="grid overflow-hidden rounded-2xl bg-porcelain text-ink fine-shadow lg:grid-cols-[.88fr_1.12fr]">
          <div className="flex flex-col bg-charcoal p-6 text-white sm:p-10">
            <p className="text-sm font-bold text-brass">{site.descriptor}</p>
            <h3 className="mt-3 font-display text-4xl font-semibold tracking-tight">Todo listo en cuatro pasos.</h3>
            <div className="mt-8 space-y-5 text-sm text-white/68"><p className="flex gap-3"><MapPin className="mt-0.5 size-5 shrink-0 text-brass" /><span>{site.addressLine1}<br />{site.postalAndCity}</span></p><a className="flex items-center gap-3 transition-colors hover:text-white" href={site.phoneHref}><Phone className="size-5 text-brass" />{site.phoneDisplay}</a><p className="flex gap-3"><Clock3 className="mt-0.5 size-5 shrink-0 text-brass" /><span>L–V · 9:30–20:00<br />Sábado · 9:00–15:00</span></p></div>
            <a
              href={site.mapsHref}
              target="_blank"
              rel="noreferrer"
              className="group relative mt-10 min-h-56 flex-1 overflow-hidden rounded-xl bg-espresso p-5 outline-none ring-1 ring-white/12 transition-colors hover:ring-brass/55 focus-visible:ring-2 focus-visible:ring-brass"
              aria-label={`Abrir ${site.name} en Google Maps`}
            >
              <span aria-hidden="true" className="absolute inset-0 opacity-40 [background-image:linear-gradient(25deg,transparent_48%,oklch(0.83_0.082_78/0.38)_49%,oklch(0.83_0.082_78/0.38)_51%,transparent_52%),linear-gradient(115deg,transparent_48%,oklch(1_0_0/0.2)_49%,oklch(1_0_0/0.2)_51%,transparent_52%)] [background-size:72px_72px]" />
              <span aria-hidden="true" className="absolute left-[58%] top-[42%] grid size-12 place-items-center rounded-full bg-brass text-ink shadow-lg transition-transform duration-300 group-hover:-translate-y-1">
                <MapPin />
              </span>
              <span className="absolute bottom-4 left-4 flex items-center gap-2 rounded-full bg-ink/80 px-3 py-2 text-xs font-semibold text-white backdrop-blur-sm">
                Ver en Google Maps
                <ArrowUpRight aria-hidden="true" className="size-4 text-brass transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
              </span>
            </a>
          </div>
          <div className="p-6 sm:p-10 lg:p-12">
            <p className="font-semibold text-brass-deep">Agenda en tiempo real</p>
            <h3 className="mt-3 max-w-xl font-display text-4xl font-semibold tracking-tight sm:text-5xl">Elige tu hora sin esperar una llamada.</h3>
            <p className="mt-4 max-w-xl leading-7 text-taupe">Confirma tus datos, servicio, profesional y horario. La cita queda guardada al instante y recibirás todos los detalles por email.</p>
            <ol className="mt-8 grid gap-3 sm:grid-cols-2">{["Tus datos", "Servicio y profesional", "Fecha y hora", "Confirmación por email"].map((item) => <li key={item} className="flex items-center gap-3 rounded-xl border border-ink/10 bg-white p-3 text-sm font-bold"><span className="grid size-7 place-items-center rounded-full bg-mist text-brass-deep"><Check className="size-4" /></span>{item}</li>)}</ol>
            <button type="button" onClick={onBook} className="group mt-8 flex min-h-16 w-full items-center justify-between rounded-xl bg-ink px-4 font-bold text-white shadow-[0_5px_0_var(--color-espresso)] transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-[0_7px_0_var(--color-espresso)] sm:px-5"><span className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-lg bg-brass text-ink"><CalendarCheck2 className="size-5" /></span>Reservar cita</span><ArrowUpRight className="size-5 text-brass transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" /></button>
          </div>
        </ScrollReveal>
        <div className="mt-8 flex justify-end">
          <a href="/" onClick={(event) => { if (!onViewHome || !window.matchMedia("(max-width: 47.999rem)").matches) return; event.preventDefault(); onViewHome(); }} className="group inline-flex min-h-11 items-center gap-3 rounded-full border border-white/20 px-3 py-2 pr-5 text-sm font-bold text-white/80 outline-none transition-[background-color,color,border-color,transform] hover:-translate-y-0.5 hover:border-white hover:bg-white hover:text-ink focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-4 focus-visible:ring-offset-ink">
            <span className="grid size-8 place-items-center rounded-full bg-brass text-ink"><ArrowUp aria-hidden="true" className="size-4 transition-transform group-hover:-translate-y-0.5" /></span>
            Volver al inicio
          </a>
        </div>
      </div>
    </section>
  );
}
