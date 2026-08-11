import { ArrowUpRight, CalendarCheck2, Check, Clock3, MapPin, Phone } from "lucide-react";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

export function BookingSection({ onBook }: { onBook: () => void }) {
  return (
    <section id="reservas" className="section-space bg-ink text-white">
      <div className="container-shell">
        <div className="mb-10 lg:mb-12"><p className="mb-3 font-semibold text-brass">Tu momento empieza aquí</p><RevealTitle label="Reserva fácil." className="display-title max-w-5xl" lines={[{ content: "Reserva" }, { content: "fácil.", className: "font-normal text-brass" }]} /></div>
        <ScrollReveal className="grid overflow-hidden rounded-2xl bg-porcelain text-ink fine-shadow lg:grid-cols-[.88fr_1.12fr]">
          <div className="flex flex-col bg-charcoal p-6 text-white sm:p-10">
            <p className="text-sm font-bold text-brass">Baskuñana Peluqueros</p>
            <h3 className="mt-3 font-display text-4xl font-semibold tracking-tight">Todo listo en cuatro pasos.</h3>
            <div className="mt-8 space-y-5 text-sm text-white/68"><p className="flex gap-3"><MapPin className="mt-0.5 size-5 shrink-0 text-brass" /><span>Paseo Alfonso XIII, 28<br />30201 Cartagena, Murcia</span></p><a className="flex items-center gap-3 transition-colors hover:text-white" href="tel:+34968124445"><Phone className="size-5 text-brass" />968 12 44 45</a><p className="flex gap-3"><Clock3 className="mt-0.5 size-5 shrink-0 text-brass" /><span>L–V · 9:30–20:00<br />Sábado · 9:00–15:00</span></p></div>
          </div>
          <div className="p-6 sm:p-10 lg:p-12">
            <p className="font-semibold text-brass-deep">Agenda en tiempo real</p>
            <h3 className="mt-3 max-w-xl font-display text-4xl font-semibold tracking-tight sm:text-5xl">Elige tu hora sin esperar una llamada.</h3>
            <p className="mt-4 max-w-xl leading-7 text-taupe">Confirma tus datos, servicio, profesional y horario. La cita queda guardada al instante y recibirás todos los detalles por email.</p>
            <ol className="mt-8 grid gap-3 sm:grid-cols-2">{["Tus datos", "Servicio y profesional", "Fecha y hora", "Confirmación por email"].map((item) => <li key={item} className="flex items-center gap-3 rounded-xl border border-ink/10 bg-white p-3 text-sm font-bold"><span className="grid size-7 place-items-center rounded-full bg-mist text-brass-deep"><Check className="size-4" /></span>{item}</li>)}</ol>
            <button type="button" onClick={onBook} className="group mt-8 flex min-h-16 w-full items-center justify-between rounded-xl bg-ink px-4 font-bold text-white shadow-[0_5px_0_var(--color-espresso)] transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-[0_7px_0_var(--color-espresso)] sm:px-5"><span className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-lg bg-brass text-ink"><CalendarCheck2 className="size-5" /></span>Reservar cita</span><ArrowUpRight className="size-5 text-brass transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" /></button>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
