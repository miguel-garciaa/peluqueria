import { ArrowLeft, ArrowRight, CalendarDays, Check, CheckCircle2, Clock3, Scissors, Sparkles, UserRound, X } from "lucide-react";
import { type FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { CalendarPicker } from "@/components/booking/CalendarPicker";
import { cn } from "@/lib/utils";
import type { AvailabilitySlot, BookingCatalog, BookingFormData, CurrentUser } from "@/types";

type BookingIntent = { serviceId?: string; professionalId?: string };
type BookingError = Partial<Record<keyof BookingFormData, string>>;

interface BookingModalProps {
  open: boolean;
  onClose: () => void;
  currentUser: CurrentUser;
  catalog: BookingCatalog;
  intent: BookingIntent;
  bookingEndpoint: string;
  availabilityEndpoint: string;
  csrfToken: string;
}

const emptyForm = (user: CurrentUser, intent: BookingIntent): BookingFormData => ({
  fullName: user.name,
  phone: user.phone ?? "",
  serviceId: intent.serviceId ?? "",
  professionalId: intent.professionalId ?? "any",
  customDetails: "",
  date: "",
  timeSlot: "",
});
const wordCount = (value: string) => value.trim() ? value.trim().split(/\s+/u).length : 0;
const humanDate = (value: string) => new Intl.DateTimeFormat("es-ES", { weekday: "long", day: "numeric", month: "long" }).format(new Date(`${value}T12:00:00`));

export function BookingModal({ open, onClose, currentUser, catalog, intent, bookingEndpoint, availabilityEndpoint, csrfToken }: BookingModalProps) {
  const dialogRef = useRef<HTMLDialogElement>(null);
  const [step, setStep] = useState(1);
  const [form, setForm] = useState<BookingFormData>(() => emptyForm(currentUser, intent));
  const [errors, setErrors] = useState<BookingError>({});
  const [slots, setSlots] = useState<AvailabilitySlot[]>([]);
  const [period, setPeriod] = useState<"morning" | "afternoon">("morning");
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [availabilityError, setAvailabilityError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState<{ message: string; reference: string; professional: string } | null>(null);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    if (open && !dialog.open) dialog.showModal();
    if (!open && dialog.open) dialog.close();
  }, [open]);

  useEffect(() => {
    if (!open) return;
    setStep(1);
    setForm(emptyForm(currentUser, intent));
    setErrors({});
    setSlots([]);
    setSuccess(null);
    setAvailabilityError("");
  }, [open, currentUser, intent]);

  useEffect(() => {
    if (!open) return;
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = previous; };
  }, [open]);

  useEffect(() => {
    if (!open || step !== 3 || !form.date || !form.serviceId || !form.professionalId) return;
    const controller = new AbortController();
    const params = new URLSearchParams({ date: form.date, service: form.serviceId, professional: form.professionalId });
    setLoadingSlots(true);
    setAvailabilityError("");
    setSlots([]);
    fetch(`${availabilityEndpoint}?${params}`, { credentials: "same-origin", headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }, signal: controller.signal })
      .then(async (response) => {
        const payload = await response.json().catch(() => ({})) as { slots?: AvailabilitySlot[]; message?: string };
        if (!response.ok) throw new Error(payload.message ?? "No se pudo consultar la disponibilidad.");
        setSlots(payload.slots ?? []);
      })
      .catch((error) => { if (error instanceof Error && error.name !== "AbortError") setAvailabilityError(error.message); })
      .finally(() => { if (!controller.signal.aborted) setLoadingSlots(false); });
    return () => controller.abort();
  }, [availabilityEndpoint, form.date, form.professionalId, form.serviceId, open, step]);

  const selectedService = catalog.services.find((service) => service.id === form.serviceId);
  const selectedProfessional = form.professionalId === "any" ? null : catalog.professionals.find((professional) => professional.id === form.professionalId);
  const visibleSlots = useMemo(() => slots.filter((slot) => slot.period === period), [period, slots]);
  const words = wordCount(form.customDetails);

  const update = (field: keyof BookingFormData, value: string) => {
    setForm((current) => ({
      ...current,
      [field]: value,
      ...(["serviceId", "professionalId", "date"].includes(field) ? { timeSlot: "" } : {}),
      ...(field === "serviceId" && !catalog.services.find((service) => service.id === value)?.isCustom ? { customDetails: "" } : {}),
    }));
    setErrors((current) => ({ ...current, [field]: undefined }));
  };

  const validateStep = () => {
    const next: BookingError = {};
    if (step === 1) {
      if (form.fullName.trim().length < 2) next.fullName = "Escribe tu nombre completo.";
      if (!/^(?:\+34\s?)?[6789](?:[\s-]?\d){8}$/.test(form.phone.trim())) next.phone = "Introduce un teléfono español válido.";
    }
    if (step === 2) {
      if (!form.serviceId) next.serviceId = "Selecciona un servicio.";
      if (!form.professionalId) next.professionalId = "Selecciona quién te atenderá.";
      if (selectedService?.isCustom && !form.customDetails.trim()) next.customDetails = "Cuéntanos qué necesitas.";
      if (words > 40) next.customDetails = "Utiliza un máximo de 40 palabras.";
    }
    if (step === 3) {
      if (!form.date) next.date = "Selecciona una fecha.";
      if (!form.timeSlot) next.timeSlot = "Selecciona una hora disponible.";
    }
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const continueToNextStep = () => { if (validateStep()) setStep((current) => Math.min(4, current + 1)); };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      const response = await fetch(bookingEndpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken, "X-Requested-With": "XMLHttpRequest" },
        body: JSON.stringify(form),
      });
      const payload = await response.json().catch(() => ({})) as { message?: string; errors?: Partial<Record<keyof BookingFormData, string[]>>; appointment?: { reference: string; professional: string } };
      if (response.status === 422) {
        const serverErrors: BookingError = {};
        for (const field of Object.keys(payload.errors ?? {}) as Array<keyof BookingFormData>) serverErrors[field] = payload.errors?.[field]?.[0];
        setErrors(serverErrors);
        if (serverErrors.timeSlot || serverErrors.date) setStep(3); else if (serverErrors.serviceId || serverErrors.professionalId || serverErrors.customDetails) setStep(2); else setStep(1);
        return;
      }
      if (!response.ok || !payload.appointment) throw new Error(payload.message ?? "No se pudo confirmar la cita.");
      setSuccess({ message: payload.message ?? "Cita confirmada.", reference: payload.appointment.reference, professional: payload.appointment.professional });
    } catch (error) {
      setAvailabilityError(error instanceof Error ? error.message : "No se pudo confirmar la cita.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <dialog ref={dialogRef} className="booking-dialog" onCancel={(event) => { event.preventDefault(); onClose(); }} onClose={onClose} onMouseDown={(event) => { if (event.target === event.currentTarget) onClose(); }} aria-labelledby="booking-modal-title">
      <form onSubmit={submit} className="flex h-full min-h-0 flex-col bg-porcelain">
        <header className="flex shrink-0 items-center justify-between border-b border-ink/10 px-5 py-4 sm:px-7">
          <div>
            <p className="text-xs font-bold uppercase tracking-[0.12em] text-brass-deep">Reserva online</p>
            <h2 id="booking-modal-title" className="mt-1 font-display text-2xl font-semibold tracking-tight">Tu próxima cita</h2>
          </div>
          <button type="button" onClick={onClose} className="grid size-11 place-items-center rounded-full border border-ink/10 bg-white transition-colors hover:bg-ink hover:text-white" aria-label="Cerrar reserva"><X className="size-5" /></button>
        </header>

        {!success && <div className="shrink-0 border-b border-ink/10 px-5 py-3 sm:px-7">
          <ol className="grid grid-cols-4 gap-2" aria-label={`Paso ${step} de 4`}>
            {["Tus datos", "Servicio", "Fecha y hora", "Confirmar"].map((label, index) => { const number = index + 1; return <li key={label} className={cn("flex items-center gap-2 text-[0.68rem] font-bold text-taupe sm:text-xs", number <= step && "text-ink")}><span className={cn("grid size-6 shrink-0 place-items-center rounded-full border border-ink/15", number < step && "border-brass bg-brass", number === step && "border-ink bg-ink text-white")}>{number < step ? <Check className="size-3.5" /> : number}</span><span className="hidden sm:inline">{label}</span></li>; })}
          </ol>
        </div>}

        <div className="min-h-0 flex-1 overflow-y-auto px-5 py-6 sm:px-7 sm:py-7">
          {success ? <div className="mx-auto flex min-h-full max-w-lg flex-col items-center justify-center py-10 text-center">
            <span className="grid size-20 place-items-center rounded-full bg-brass text-ink"><CheckCircle2 className="size-9" /></span>
            <p className="mt-7 text-sm font-bold text-brass-deep">Reserva completada</p>
            <h3 className="mt-2 font-display text-4xl font-semibold">Nos vemos pronto.</h3>
            <p className="mt-4 max-w-md leading-7 text-taupe">{success.message} Te atenderá {success.professional}.</p>
            <p className="mt-5 rounded-full bg-mist px-4 py-2 font-mono text-xs font-bold">Ref. {success.reference}</p>
            <div className="mt-8 flex w-full flex-col gap-3 sm:flex-row sm:justify-center"><a href="/mis-citas" className="rounded-full bg-ink px-6 py-3.5 font-bold text-white">Ver mis citas</a><button type="button" onClick={onClose} className="rounded-full border border-ink/15 px-6 py-3.5 font-bold">Cerrar</button></div>
          </div> : <>
            {step === 1 && <section className="mx-auto max-w-2xl" aria-labelledby="step-personal-title">
              <span className="grid size-12 place-items-center rounded-2xl bg-mist text-brass-deep"><UserRound /></span>
              <h3 id="step-personal-title" className="mt-5 font-display text-3xl font-semibold">Confirma tus datos</h3>
              <p className="mt-2 text-sm leading-6 text-taupe">Usaremos estos datos únicamente para gestionar tu cita. El correo está asociado a tu sesión.</p>
              <div className="mt-7 grid gap-5 sm:grid-cols-2">
                <label className="text-sm font-bold">Nombre completo<input value={form.fullName} onChange={(event) => update("fullName", event.target.value)} autoComplete="name" className={cn("booking-input", errors.fullName && "booking-input-error")} />{errors.fullName && <span className="mt-1.5 block text-xs text-danger">{errors.fullName}</span>}</label>
                <label className="text-sm font-bold">Teléfono<input type="tel" inputMode="tel" value={form.phone} onChange={(event) => update("phone", event.target.value)} autoComplete="tel" placeholder="600 000 000" className={cn("booking-input", errors.phone && "booking-input-error")} />{errors.phone && <span className="mt-1.5 block text-xs text-danger">{errors.phone}</span>}</label>
                <label className="text-sm font-bold sm:col-span-2">Correo<input readOnly value={currentUser.email} className="booking-input bg-mist/65 text-taupe" /></label>
              </div>
            </section>}

            {step === 2 && <section aria-labelledby="step-service-title">
              <div className="flex items-end justify-between gap-4"><div><p className="text-sm font-bold text-brass-deep">Elige la experiencia</p><h3 id="step-service-title" className="mt-1 font-display text-3xl font-semibold">Servicio y profesional</h3></div><Scissors className="hidden size-8 text-brass-deep sm:block" /></div>
              <div className="mt-6 grid gap-2 sm:grid-cols-2">
                {catalog.services.map((service) => <button key={service.id} type="button" onClick={() => update("serviceId", service.id)} aria-pressed={form.serviceId === service.id} className={cn("flex min-h-20 items-center justify-between rounded-2xl border border-ink/10 bg-white p-4 text-left transition-[border-color,background-color,transform] hover:-translate-y-0.5 hover:border-brass-deep", form.serviceId === service.id && "border-ink bg-ink text-white")}><span><strong className="block">{service.name}</strong><span className={cn("mt-1 block text-xs text-taupe", form.serviceId === service.id && "text-white/60")}>{service.durationMinutes} min{service.priceFrom !== null ? ` · desde ${service.priceFrom} €` : " · valoración previa"}</span></span>{service.isCustom ? <Sparkles className="size-5 text-brass" /> : <span className={cn("size-3 rounded-full border border-ink/20", form.serviceId === service.id && "border-brass bg-brass")} />}</button>)}
              </div>
              {errors.serviceId && <p className="mt-2 text-xs font-semibold text-danger">{errors.serviceId}</p>}
              {selectedService?.isCustom && <div className="mt-5"><label htmlFor="custom-details" className="block text-sm font-bold">Cuéntanos qué necesitas</label><textarea id="custom-details" value={form.customDetails} onChange={(event) => update("customDetails", event.target.value)} rows={3} className={cn("booking-input resize-none py-3", errors.customDetails && "booking-input-error")} placeholder="Ej.: quiero valorar un cambio de color y corte…" aria-describedby="custom-details-count" /><span id="custom-details-count" className={cn("mt-1.5 block text-right text-xs text-taupe", words > 40 && "text-danger")}>{words}/40 palabras</span>{errors.customDetails && <span className="block text-xs text-danger">{errors.customDetails}</span>}</div>}
              <fieldset className="mt-6"><legend className="text-sm font-bold">¿Quién quieres que te atienda?</legend><div className="mt-3 flex flex-wrap gap-2"><button type="button" onClick={() => update("professionalId", "any")} aria-pressed={form.professionalId === "any"} className={cn("rounded-full border border-ink/15 bg-white px-4 py-3 text-sm font-bold", form.professionalId === "any" && "border-brass-deep bg-brass text-ink")}>Primera disponibilidad</button>{catalog.professionals.map((professional) => <button key={professional.id} type="button" onClick={() => update("professionalId", professional.id)} aria-pressed={form.professionalId === professional.id} className={cn("rounded-full border border-ink/15 bg-white px-4 py-3 text-sm font-bold", form.professionalId === professional.id && "border-ink bg-ink text-white")}>{professional.name.split(" ")[0]}</button>)}</div>{errors.professionalId && <p className="mt-2 text-xs text-danger">{errors.professionalId}</p>}</fieldset>
            </section>}

            {step === 3 && <section aria-labelledby="step-date-title">
              <p className="text-sm font-bold text-brass-deep">Disponibilidad en tiempo real</p><h3 id="step-date-title" className="mt-1 font-display text-3xl font-semibold">Elige fecha y hora</h3>
              <div className="mt-6 grid gap-5 lg:grid-cols-[1fr_.9fr]">
                <div><CalendarPicker value={form.date} onChange={(value) => update("date", value)} />{errors.date && <p className="mt-2 text-xs font-semibold text-danger">{errors.date}</p>}</div>
                <div className="rounded-2xl bg-ink p-4 text-white sm:p-5">
                  <div className="flex rounded-xl bg-white/8 p-1" role="tablist" aria-label="Franja horaria"><button type="button" role="tab" aria-selected={period === "morning"} onClick={() => setPeriod("morning")} className={cn("flex min-h-11 flex-1 items-center justify-center gap-2 rounded-lg text-sm font-bold", period === "morning" && "bg-white text-ink")}><span aria-hidden="true">☀</span>Mañana</button><button type="button" role="tab" aria-selected={period === "afternoon"} onClick={() => setPeriod("afternoon")} className={cn("flex min-h-11 flex-1 items-center justify-center gap-2 rounded-lg text-sm font-bold", period === "afternoon" && "bg-white text-ink")}><span aria-hidden="true">◐</span>Tarde</button></div>
                  <div className="mt-5 min-h-48">{!form.date ? <div className="grid min-h-44 place-items-center text-center text-sm text-white/55"><div><CalendarDays className="mx-auto mb-3 size-7 text-brass" />Elige un día en el calendario.</div></div> : loadingSlots ? <div className="grid min-h-44 place-items-center text-sm text-white/60">Consultando agenda…</div> : availabilityError ? <p className="rounded-xl bg-danger/20 p-4 text-sm">{availabilityError}</p> : visibleSlots.length ? <div className="grid grid-cols-3 gap-2">{visibleSlots.map((slot) => <button key={slot.time} type="button" onClick={() => update("timeSlot", slot.time)} aria-pressed={form.timeSlot === slot.time} title={form.professionalId === "any" ? `Con ${slot.professional.name}` : undefined} className={cn("min-h-11 rounded-xl border border-white/12 text-sm font-bold transition-colors hover:border-brass hover:text-brass", form.timeSlot === slot.time && "border-brass bg-brass text-ink hover:text-ink")}>{slot.time}</button>)}</div> : <div className="grid min-h-44 place-items-center text-center text-sm text-white/55">No quedan horas en esta franja.<br />Prueba otra fecha.</div>}</div>
                  {errors.timeSlot && <p className="mt-3 text-xs font-semibold text-brass">{errors.timeSlot}</p>}
                </div>
              </div>
            </section>}

            {step === 4 && <section className="mx-auto max-w-2xl" aria-labelledby="step-confirm-title">
              <p className="text-sm font-bold text-brass-deep">Un último vistazo</p><h3 id="step-confirm-title" className="mt-1 font-display text-3xl font-semibold">Confirma tu cita</h3>
              <div className="mt-7 overflow-hidden rounded-2xl border border-ink/10 bg-white">
                <div className="grid gap-6 bg-ink p-6 text-white sm:grid-cols-[auto_1fr]"><span className="grid size-14 place-items-center rounded-2xl bg-brass text-ink"><CalendarDays /></span><div><p className="font-display text-2xl font-semibold capitalize">{humanDate(form.date)}</p><p className="mt-1 flex items-center gap-2 text-white/65"><Clock3 className="size-4 text-brass" />{form.timeSlot} · {selectedService?.durationMinutes} min</p></div></div>
                <dl className="divide-y divide-ink/10 px-6"><div className="flex justify-between gap-4 py-4"><dt className="text-sm text-taupe">Servicio</dt><dd className="text-right text-sm font-bold">{selectedService?.name}</dd></div><div className="flex justify-between gap-4 py-4"><dt className="text-sm text-taupe">Profesional</dt><dd className="text-right text-sm font-bold">{selectedProfessional?.name ?? slots.find((slot) => slot.time === form.timeSlot)?.professional.name ?? "Primera disponibilidad"}</dd></div><div className="flex justify-between gap-4 py-4"><dt className="text-sm text-taupe">A nombre de</dt><dd className="text-right text-sm font-bold">{form.fullName}<br /><span className="font-normal text-taupe">{form.phone}</span></dd></div>{form.customDetails && <div className="py-4"><dt className="text-sm text-taupe">Petición</dt><dd className="mt-2 text-sm leading-6">{form.customDetails}</dd></div>}</dl>
              </div>
              {availabilityError && <p className="mt-4 rounded-xl bg-danger/10 p-4 text-sm font-semibold text-danger">{availabilityError}</p>}
              <p className="mt-4 text-xs leading-5 text-taupe">Al confirmar, recibirás un correo con todos los detalles de la cita.</p>
            </section>}
          </>}
        </div>

        {!success && <footer className="flex shrink-0 items-center justify-between gap-3 border-t border-ink/10 bg-white px-5 py-4 sm:px-7">
          <button type="button" onClick={() => step === 1 ? onClose() : setStep((current) => current - 1)} className="flex min-h-12 items-center gap-2 rounded-full px-4 font-bold text-ink transition-colors hover:bg-mist"><ArrowLeft className="size-4" />{step === 1 ? "Cancelar" : "Atrás"}</button>
          {step < 4 ? <button type="button" onClick={continueToNextStep} className="flex min-h-12 items-center gap-3 rounded-full bg-ink px-6 font-bold text-white transition-transform hover:-translate-y-0.5">Continuar <ArrowRight className="size-4" /></button> : <button type="submit" disabled={submitting} className="flex min-h-12 items-center gap-3 rounded-full bg-brass px-6 font-bold text-ink transition-transform hover:-translate-y-0.5 disabled:opacity-60"><Check className="size-4" />{submitting ? "Confirmando…" : "Confirmar cita"}</button>}
        </footer>}
      </form>
    </dialog>
  );
}
