import { ArrowUpRight, CalendarDays, CheckCircle2, Clock3, Scissors } from "lucide-react";
import { CustomCursor } from "@/components/CustomCursor";
import { Footer } from "@/components/Footer";
import { Navbar } from "@/components/Navbar";
import type { CurrentUser, UserAppointment } from "@/types";

const statusLabels: Record<UserAppointment["status"], string> = {
  confirmed: "Confirmada",
  pending: "Pendiente",
  cancelled: "Cancelada",
  completed: "Completada",
};

export function MyAppointmentsPage({ currentUser, appointments, csrfToken }: { currentUser: CurrentUser; appointments: UserAppointment[]; csrfToken: string }) {
  return (
    <>
      <a className="skip-link" href="#appointments-content">Saltar al contenido</a>
      <Navbar currentUser={currentUser} csrfToken={csrfToken} solid />
      <main id="appointments-content" className="min-h-screen bg-porcelain pb-24 pt-32 text-ink lg:pt-40">
        <section className="container-shell">
          <div className="border-b border-ink/12 pb-10 lg:flex lg:items-end lg:justify-between">
            <div><p className="font-bold text-brass-deep">Tu agenda personal</p><h1 className="mt-3 font-display text-5xl font-semibold tracking-[-0.04em] sm:text-7xl">Mis citas<span className="text-brass-deep">*</span></h1><p className="mt-4 max-w-xl leading-7 text-taupe">Consulta aquí cada reserva y los detalles necesarios para llegar con todo claro.</p></div>
            <a href="/#reservas" className="group mt-7 inline-flex min-h-14 items-center gap-3 rounded-full bg-ink p-2 pr-6 font-bold text-white transition-transform hover:-translate-y-0.5 lg:mt-0"><span className="grid size-10 place-items-center rounded-full bg-brass text-ink"><ArrowUpRight className="size-4" /></span>Nueva cita</a>
          </div>

          {appointments.length === 0 ? <div className="mt-10 grid min-h-80 place-items-center rounded-2xl border border-dashed border-ink/20 bg-white p-8 text-center"><div><span className="mx-auto grid size-16 place-items-center rounded-full bg-mist text-brass-deep"><CalendarDays className="size-7" /></span><h2 className="mt-5 font-display text-3xl font-semibold">Todavía no tienes citas</h2><p className="mx-auto mt-3 max-w-md leading-7 text-taupe">Cuando reserves, encontrarás aquí el servicio, profesional, fecha y estado.</p><a href="/#reservas" className="mt-6 inline-flex rounded-full bg-ink px-6 py-3.5 font-bold text-white">Reservar mi primera cita</a></div></div> : <div className="mt-10 grid gap-4 lg:grid-cols-2">
            {appointments.map((appointment) => {
              const startsAt = new Date(appointment.startsAt);
              const day = new Intl.DateTimeFormat("es-ES", { day: "2-digit" }).format(startsAt);
              const month = new Intl.DateTimeFormat("es-ES", { month: "short" }).format(startsAt).replace(".", "");
              const longDate = new Intl.DateTimeFormat("es-ES", { weekday: "long", day: "numeric", month: "long", year: "numeric" }).format(startsAt);
              const time = new Intl.DateTimeFormat("es-ES", { hour: "2-digit", minute: "2-digit" }).format(startsAt);
              return <article key={appointment.reference} className="grid overflow-hidden rounded-2xl border border-ink/10 bg-white sm:grid-cols-[7.5rem_1fr]">
                <div className="flex items-center justify-center gap-2 bg-ink p-5 text-white sm:flex-col"><strong className="font-display text-5xl leading-none text-brass">{day}</strong><span className="font-bold uppercase tracking-[0.12em] text-white/60">{month}</span></div>
                <div className="p-5 sm:p-6"><div className="flex items-start justify-between gap-4"><div><p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.08em] text-brass-deep"><Scissors className="size-3.5" />{appointment.service}</p><h2 className="mt-2 font-display text-2xl font-semibold">Con {appointment.professional}</h2></div><span className={`rounded-full px-3 py-1.5 text-xs font-bold ${appointment.status === "confirmed" ? "bg-mist text-espresso" : appointment.status === "cancelled" ? "bg-danger/10 text-danger" : "bg-ink/5 text-taupe"}`}>{statusLabels[appointment.status]}</span></div><div className="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-t border-ink/10 pt-4 text-sm text-taupe"><span className="flex items-center gap-2 capitalize"><CalendarDays className="size-4 text-brass-deep" />{longDate}</span><span className="flex items-center gap-2"><Clock3 className="size-4 text-brass-deep" />{time}</span></div>{appointment.customDetails && <p className="mt-4 rounded-xl bg-mist/65 p-3 text-sm leading-6 text-ink/70">{appointment.customDetails}</p>}<p className="mt-4 flex items-center gap-2 text-[0.68rem] font-bold text-taupe"><CheckCircle2 className="size-3.5" />Referencia {appointment.reference}</p></div>
              </article>;
            })}
          </div>}
        </section>
      </main>
      <Footer />
      <CustomCursor />
    </>
  );
}
