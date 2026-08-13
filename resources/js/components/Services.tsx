import { ArrowRight, ArrowUpRight, CalendarDays, Check, Clock, Sparkles, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { heroImage, services } from "@/data/content";
import type { BookingCatalogService, Service } from "@/types";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

interface ServicesProps {
  onBook: (serviceId: string) => void;
  catalogServices?: BookingCatalogService[];
}

const priceLabel = (price: number | null) => price === null
  ? "A consultar"
  : `Desde ${new Intl.NumberFormat("es-ES", { maximumFractionDigits: 2 }).format(price)} €`;

export function Services({ onBook, catalogServices }: ServicesProps) {
  const [activeService, setActiveService] = useState<Service | null>(null);
  const dialogRef = useRef<HTMLDialogElement>(null);
  const displayedServices: Service[] = catalogServices === undefined
    ? services
    : catalogServices.map((current) => {
      const presentation = services.find((service) => service.id === current.id);
      const description = current.description?.trim()
        || presentation?.description
        || "Un servicio adaptado a tus necesidades, con valoración profesional previa.";

      return {
        id: current.id,
        title: current.name,
        description,
        longDescription: current.description?.trim() || presentation?.longDescription || description,
        benefits: presentation?.benefits ?? ["Valoración personalizada", "Servicio adaptado a ti", "Recomendaciones de mantenimiento"],
        imageSrc: current.imageUrl || presentation?.imageSrc || heroImage,
        imageAlt: current.imageUrl
          ? `Fotografía del servicio ${current.name}`
          : presentation?.imageAlt ?? `Servicio ${current.name} en la peluquería`,
        priceFrom: current.priceFrom,
        duration: `${current.durationMinutes} min`,
        icon: presentation?.icon ?? Sparkles,
      };
    });

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    if (activeService && !dialog.open) dialog.showModal();
    if (!activeService && dialog.open) dialog.close();
  }, [activeService]);

  useEffect(() => {
    if (!activeService) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = previousOverflow; };
  }, [activeService]);

  const closeDetails = () => setActiveService(null);
  const bookActiveService = () => {
    if (!activeService) return;
    const serviceId = activeService.id;
    closeDetails();
    window.setTimeout(() => onBook(serviceId), 180);
  };

  return (
    <section id="servicios" className="section-space bg-porcelain">
      <div className="container-shell">
        <div className="mb-12 flex flex-col justify-between gap-5 md:flex-row md:items-end lg:mb-8">
          <div><p className="mb-3 font-semibold text-brass-deep">Servicios de autor</p><RevealTitle label="Técnica precisa. Resultado muy tuyo." className="display-title max-w-4xl" lines={[{ content: "Técnica precisa." }, { content: "Resultado muy tuyo.", className: "text-brass-deep" }]} /></div>
          <p className="max-w-md leading-7 text-taupe">Cada cita comienza con una conversación. Adaptamos técnica, tiempo y producto a tu cabello.</p>
        </div>
        <ScrollReveal className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {displayedServices.map((service) => { const Icon = service.icon; return (
            <article key={service.id} className="min-h-72 lg:min-h-64">
              <button type="button" onClick={() => setActiveService(service)} aria-label={`Ver detalles de ${service.title}`} className="group flex h-full w-full flex-col rounded-2xl bg-white p-6 text-left text-ink ring-1 ring-ink/8 outline-none transition-[background-color,color,transform] duration-300 hover:-translate-y-1 hover:bg-ink hover:text-white focus-visible:ring-2 focus-visible:ring-brass-deep focus-visible:ring-offset-4 focus-visible:ring-offset-porcelain active:translate-y-0 lg:p-5">
                <div className="mb-8 grid size-12 place-items-center rounded-full bg-mist text-brass-deep transition-colors group-hover:bg-white/10 group-hover:text-brass lg:mb-5 lg:size-11"><Icon className="size-5" aria-hidden="true" /></div>
                <h3 className="font-display text-3xl font-semibold leading-tight tracking-[-0.025em] lg:text-2xl">{service.title}</h3>
                <p className="mt-3 flex-1 text-base leading-7 text-taupe group-hover:text-white/65 lg:mt-2 lg:text-sm lg:leading-6">{service.description}</p>
                <div className="mt-6 flex w-full items-center justify-between border-t border-ink/10 pt-5 group-hover:border-white/15 lg:mt-4 lg:pt-4"><div><strong className="block">{priceLabel(service.priceFrom)}</strong><span className="mt-1 flex items-center gap-1.5 text-xs text-taupe group-hover:text-white/55"><Clock className="size-3.5" />{service.duration}</span></div><span aria-hidden="true" className="grid size-11 place-items-center rounded-full border border-ink/15 transition-[background-color,border-color,transform] group-hover:rotate-45 group-hover:border-brass group-hover:bg-brass group-hover:text-ink lg:size-10"><ArrowUpRight className="size-5" /></span></div>
              </button>
            </article>
          ); })}
        </ScrollReveal>
      </div>

      <dialog ref={dialogRef} className="service-dialog" onClose={closeDetails} onCancel={closeDetails} onMouseDown={(event) => { if (event.target === event.currentTarget) closeDetails(); }}>
        {activeService && (() => {
          const Icon = activeService.icon;
          return (
            <div className="service-dialog-layout relative grid h-full bg-porcelain">
              <button type="button" onClick={closeDetails} aria-label="Cerrar detalles del servicio" className="absolute right-4 top-4 z-20 grid size-11 place-items-center rounded-full bg-white text-ink shadow-[0_4px_8px_oklch(0.17_0.012_65/0.12)] outline-none transition-colors hover:bg-ink hover:text-white focus-visible:ring-2 focus-visible:ring-brass-deep md:right-8 md:top-8 md:size-12"><X className="size-5" /></button>

              <div className="service-dialog-visual relative min-h-0 overflow-hidden bg-charcoal">
                <img src={activeService.imageSrc} alt={activeService.imageAlt} className="absolute inset-0 h-full w-full object-cover" />
                <div className="absolute inset-0 bg-gradient-to-t from-ink/55 via-transparent to-ink/12" />
                <p className="absolute left-4 top-4 rounded-full bg-ink/78 px-3 py-2 text-xs font-semibold text-white backdrop-blur-sm md:bottom-8 md:left-8 md:top-auto">Asesoramiento incluido</p>
                <div className="absolute bottom-4 left-5 right-5 text-white md:hidden">
                  <p className="mb-1.5 flex items-center gap-2 text-sm font-semibold text-brass"><Icon className="size-4" />Servicio de autor</p>
                  <h3 className="font-display text-4xl font-semibold leading-none tracking-[-0.03em]">{activeService.title}</h3>
                </div>
              </div>

              <div className="service-dialog-body relative flex min-h-0 flex-col p-5 md:p-10 md:pt-20">
                <div className="hidden md:block">
                  <div className="flex items-center gap-3 text-brass-deep"><span className="grid size-11 place-items-center rounded-full bg-mist"><Icon className="size-5" /></span><span className="font-semibold">Servicio de autor</span></div>
                  <h3 className="panel-title mt-7">{activeService.title}</h3>
                </div>
                <p className="service-dialog-description max-w-xl text-sm leading-6 text-taupe md:mt-5 md:text-base md:leading-7">{activeService.longDescription}</p>

                <div className="service-dialog-benefits mt-4 border-y border-ink/10 py-4 md:mt-8 md:py-6">
                  <p className="mb-3 text-sm font-bold md:mb-4">La experiencia incluye</p>
                  <ul className="service-benefit-list flex flex-col gap-2 md:gap-3">
                    {activeService.benefits.map((benefit) => <li key={benefit} className="flex items-start gap-3 text-sm text-ink/78"><span className="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-brass text-ink"><Check className="size-3" /></span>{benefit}</li>)}
                  </ul>
                </div>

                <div className="service-dialog-meta mt-4 flex items-center gap-6 md:mt-7 md:gap-8">
                  <div><span className="block text-xs font-semibold text-taupe">Precio</span><strong className="mt-1 block text-lg">{priceLabel(activeService.priceFrom)}</strong></div>
                  <div><span className="block text-xs font-semibold text-taupe">Duración estimada</span><strong className="mt-1 flex items-center gap-2 text-lg"><Clock className="size-4 text-brass-deep" />{activeService.duration}</strong></div>
                </div>

                <button type="button" onClick={bookActiveService} className="service-dialog-cta group mt-5 flex min-h-14 w-full items-center justify-between rounded-xl bg-ink px-4 text-sm font-bold text-white shadow-[0_5px_0_var(--color-espresso)] outline-none transition-[transform,background-color,box-shadow] duration-200 hover:-translate-y-0.5 hover:bg-charcoal hover:shadow-[0_7px_0_var(--color-espresso)] focus-visible:ring-2 focus-visible:ring-brass-deep focus-visible:ring-offset-4 focus-visible:ring-offset-porcelain active:translate-y-1 active:shadow-none sm:text-base md:mt-8 md:min-h-16"><span className="flex items-center gap-2 sm:gap-3"><span className="grid size-9 place-items-center rounded-lg bg-brass text-ink sm:size-10"><CalendarDays className="size-5" /></span>Reservar este servicio</span><span className="grid size-9 place-items-center rounded-lg bg-white/8 text-brass transition-transform group-hover:translate-x-1"><ArrowRight className="size-4" /></span></button>
              </div>
            </div>
          );
        })()}
      </dialog>
    </section>
  );
}
