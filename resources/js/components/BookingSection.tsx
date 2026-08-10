import { ArrowRight, CalendarDays, Check, Clock3, MapPin, Phone, Star, UserRound } from "lucide-react";
import { type FormEvent, useEffect, useMemo, useState } from "react";
import { services } from "@/data/content";
import { cn } from "@/lib/utils";
import type { BookingFormData, SubmissionStatus } from "@/types";
import { DateField, SelectField } from "@/components/ui/booking-controls";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

const emptyForm: BookingFormData = { fullName: "", phone: "", serviceId: "", date: "", timeSlot: "" };
const localDate = (date = new Date()) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;

export function BookingSection({ selectedServiceId, bookingEndpoint, csrfToken }: { selectedServiceId: string; bookingEndpoint: string; csrfToken: string }) {
  const [formData, setFormData] = useState<BookingFormData>({ ...emptyForm, serviceId: selectedServiceId });
  const [errors, setErrors] = useState<Partial<Record<keyof BookingFormData, string>>>({});
  const [status, setStatus] = useState<SubmissionStatus>("idle");
  const [submissionMessage, setSubmissionMessage] = useState("");

  useEffect(() => { if (selectedServiceId) setFormData((current) => ({ ...current, serviceId: selectedServiceId })); }, [selectedServiceId]);

  const availableTimes = useMemo(() => {
    if (!formData.date) return [];
    const selected = new Date(`${formData.date}T12:00:00`);
    const day = selected.getDay();
    if (day === 0) return [];
    const startMinutes = day === 6 ? 9 * 60 : 9 * 60 + 30;
    const endMinutes = day === 6 ? 14 * 60 + 30 : 19 * 60 + 30;
    const slots = Array.from({ length: (endMinutes - startMinutes) / 30 + 1 }, (_, index) => {
      const minutes = startMinutes + index * 30;
      return `${String(Math.floor(minutes / 60)).padStart(2, "0")}:${String(minutes % 60).padStart(2, "0")}`;
    });
    if (formData.date !== localDate()) return slots;
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    return slots.filter((slot) => Number(slot.slice(0, 2)) * 60 + Number(slot.slice(3)) > currentMinutes);
  }, [formData.date]);

  const update = (field: keyof BookingFormData, value: string) => {
    setFormData((current) => ({ ...current, [field]: value, ...(field === "date" ? { timeSlot: "" } : {}) }));
    setErrors((current) => ({ ...current, [field]: undefined }));
    if (status === "success" || status === "error") {
      setStatus("idle");
      setSubmissionMessage("");
    }
  };

  const validate = () => {
    const next: typeof errors = {};
    if (formData.fullName.trim().length < 2) next.fullName = "Escribe tu nombre completo.";
    if (!/^(?:\+34\s?)?[6789](?:[\s-]?\d){8}$/.test(formData.phone.trim())) next.phone = "Introduce un teléfono español válido.";
    if (!formData.serviceId) next.serviceId = "Selecciona un servicio.";
    if (!formData.date || formData.date < localDate()) next.date = "Selecciona una fecha válida.";
    else if (new Date(`${formData.date}T12:00:00`).getDay() === 0) next.date = "El estudio cierra los domingos.";
    if (!formData.timeSlot) next.timeSlot = "Selecciona una hora disponible.";
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    if (!validate()) return;
    setStatus("submitting");
    setSubmissionMessage("");

    try {
      const response = await fetch(bookingEndpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });
      const payload = await response.json().catch(() => ({})) as { message?: string; errors?: Partial<Record<keyof BookingFormData, string[]>> };

      if (response.status === 422) {
        const serverErrors: typeof errors = {};
        for (const field of Object.keys(payload.errors ?? {}) as Array<keyof BookingFormData>) {
          serverErrors[field] = payload.errors?.[field]?.[0];
        }
        setErrors(serverErrors);
        setStatus("idle");
        setSubmissionMessage(payload.message ?? "Revisa los datos de la solicitud.");
        return;
      }

      if (!response.ok) throw new Error(payload.message ?? "No se pudo enviar la solicitud.");
      setStatus("success");
      setSubmissionMessage(payload.message ?? "Solicitud recibida. Te contactaremos muy pronto.");
    } catch (error) {
      setStatus("error");
      setSubmissionMessage(error instanceof Error ? error.message : "No se pudo enviar la solicitud. Inténtalo de nuevo.");
    }
  };

  const serviceOptions = services.map((service) => ({ value: service.id, label: service.title, meta: `desde ${service.priceFrom} €` }));
  const timeOptions = availableTimes.map((time) => ({ value: time, label: time, meta: "Disponible" }));
  const inputShellClass = "mt-2 flex min-h-14 items-center gap-3 rounded-xl bg-white px-4 shadow-[inset_0_0_0_1px_oklch(0.17_0.012_65/0.13)] transition-[box-shadow,background-color] duration-200 focus-within:bg-mist/35 focus-within:shadow-[inset_0_0_0_2px_var(--color-brass-deep),0_0_0_4px_oklch(0.83_0.082_78/0.22)]";
  return (
    <section id="reservas" className="section-space bg-ink text-white">
      <div className="container-shell">
        <div className="mb-14"><p className="mb-3 font-semibold text-brass">Tu momento empieza aquí</p><RevealTitle label="Reserva fácil." className="display-title max-w-5xl" lines={[{ content: "Reserva" }, { content: "fácil.", className: "font-normal text-brass" }]} /></div>
        <ScrollReveal className="grid rounded-2xl bg-porcelain text-ink fine-shadow lg:grid-cols-[0.82fr_1.18fr]">
          <div className="flex flex-col rounded-t-2xl bg-charcoal p-6 text-white sm:p-10 lg:rounded-l-2xl lg:rounded-tr-none">
            <h3 className="font-display text-2xl font-semibold">Baskuñana Peluqueros</h3>
            <div className="mt-8 space-y-5 text-sm text-white/68">
              <p className="flex gap-3"><MapPin className="mt-0.5 size-5 shrink-0 text-brass" /><span>Paseo Alfonso XIII, 28<br />30201 Cartagena, Murcia</span></p>
              <a className="flex items-center gap-3 transition-colors hover:text-white" href="tel:+34968124445"><Phone className="size-5 text-brass" />968 12 44 45</a>
              <p className="flex items-center gap-3"><Star className="size-5 fill-brass text-brass" /><span>4,7 de 5 · 157 reseñas en Google</span></p>
              <p className="flex gap-3"><Clock3 className="mt-0.5 size-5 shrink-0 text-brass" /><span>L–V · 9:30–20:00<br />Sábado · 9:00–15:00<br />Domingo · Cerrado</span></p>
            </div>
            <a href="https://www.google.com/maps/search/?api=1&query=Paseo+Alfonso+XIII+28+30201+Cartagena+Murcia" target="_blank" rel="noreferrer" className="relative mt-10 min-h-56 overflow-hidden rounded-xl border border-white/12 bg-espresso p-5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass" aria-label="Abrir Baskuñana Peluqueros en Google Maps">
              <div className="absolute inset-0 opacity-40 [background-image:linear-gradient(25deg,transparent_48%,oklch(0.83_0.082_78/0.38)_49%,oklch(0.83_0.082_78/0.38)_51%,transparent_52%),linear-gradient(115deg,transparent_48%,oklch(1_0_0/0.2)_49%,oklch(1_0_0/0.2)_51%,transparent_52%)] [background-size:72px_72px]" />
              <span className="absolute left-[58%] top-[42%] grid size-12 place-items-center rounded-full bg-brass text-ink shadow-lg"><MapPin /></span>
              <span className="absolute bottom-4 left-4 rounded-full bg-ink/75 px-3 py-2 text-xs font-semibold text-white backdrop-blur-sm">Ver en Google Maps ↗</span>
            </a>
          </div>
          <form onSubmit={submit} noValidate className="relative rounded-b-2xl bg-porcelain p-6 sm:p-10 lg:rounded-r-2xl lg:rounded-bl-none" aria-labelledby="booking-form-title">
            <h3 id="booking-form-title" className="font-display text-2xl font-semibold">Solicita tu cita</h3>
            <p className="mt-2 text-sm text-taupe">Te contactaremos para confirmar disponibilidad.</p>
            <div className="mt-8 grid gap-5 sm:grid-cols-2">
              <label className="text-sm font-semibold sm:col-span-2">Nombre completo<div className={cn(inputShellClass, errors.fullName && "shadow-[inset_0_0_0_1px_var(--color-danger)]")}><UserRound className="size-4 shrink-0 text-brass-deep" /><input className="min-w-0 flex-1 bg-transparent text-sm text-ink outline-none placeholder:text-ink/55" value={formData.fullName} onChange={(e) => update("fullName", e.target.value)} autoComplete="name" placeholder="Tu nombre y apellidos" aria-invalid={!!errors.fullName} aria-describedby={errors.fullName ? "name-error" : undefined} /></div>{errors.fullName && <span id="name-error" className="mt-1.5 block text-xs font-medium text-danger">{errors.fullName}</span>}</label>
              <label className="text-sm font-semibold sm:col-span-2">Teléfono<div className={cn(inputShellClass, errors.phone && "shadow-[inset_0_0_0_1px_var(--color-danger)]")}><Phone className="size-4 shrink-0 text-brass-deep" /><input type="tel" inputMode="tel" className="min-w-0 flex-1 bg-transparent text-sm text-ink outline-none placeholder:text-ink/55" value={formData.phone} onChange={(e) => update("phone", e.target.value)} autoComplete="tel" placeholder="600 000 000" aria-invalid={!!errors.phone} /></div>{errors.phone && <span className="mt-1.5 block text-xs font-medium text-danger">{errors.phone}</span>}</label>
              <div className="sm:col-span-2"><SelectField label="Servicio" value={formData.serviceId} placeholder="Selecciona un servicio" options={serviceOptions} onChange={(value) => update("serviceId", value)} error={errors.serviceId} /></div>
              <DateField value={formData.date} min={localDate()} onChange={(value) => update("date", value)} error={errors.date} />
              <SelectField label="Hora" value={formData.timeSlot} placeholder={formData.date ? "Selecciona una hora" : "Elige primero la fecha"} options={timeOptions} onChange={(value) => update("timeSlot", value)} error={errors.timeSlot} disabled={!formData.date || availableTimes.length === 0} icon="time" />
            </div>
            <button type="submit" disabled={status === "submitting"} className="group mt-8 flex min-h-16 w-full items-center justify-between rounded-xl bg-ink px-4 font-bold text-white shadow-[0_5px_0_var(--color-espresso)] outline-none transition-[transform,background-color,box-shadow] duration-200 hover:-translate-y-0.5 hover:bg-charcoal hover:shadow-[0_7px_0_var(--color-espresso)] focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-4 focus-visible:ring-offset-porcelain active:translate-y-1 active:shadow-none disabled:cursor-wait disabled:opacity-60 disabled:hover:translate-y-0 sm:px-5">{status === "submitting" ? <span className="mx-auto">Enviando solicitud…</span> : <><span className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-lg bg-brass text-ink"><CalendarDays className="size-5" /></span><span>Confirmar reserva</span></span><span className="grid size-9 place-items-center rounded-lg bg-white/8 text-brass transition-transform duration-200 group-hover:translate-x-1"><ArrowRight className="size-4" /></span></>}</button>
            <div aria-live="polite">
              {status === "success" && <p className="mt-4 flex items-center gap-2 rounded-xl bg-mist p-4 text-sm font-semibold"><Check className="size-5 text-brass-deep" />{submissionMessage}</p>}
              {status === "error" && <p className="mt-4 rounded-xl bg-danger/10 p-4 text-sm font-semibold text-danger">{submissionMessage}</p>}
              {status === "idle" && submissionMessage && <p className="mt-4 text-sm font-semibold text-danger">{submissionMessage}</p>}
            </div>
          </form>
        </ScrollReveal>
      </div>
    </section>
  );
}
