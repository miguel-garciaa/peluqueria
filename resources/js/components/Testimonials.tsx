import { useEffect, useRef, useState } from "react";
import { StaggerTestimonials } from "@/components/ui/stagger-testimonials";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

const reviewMetrics = [
  { end: 4.7, value: "4,7", format: (value: number) => value.toFixed(1).replace(".", ","), label: "Valoración en Google", detail: "Una puntuación pública que refleja la experiencia de sus clientes" },
  { end: 157, value: "157", format: (value: number) => Math.round(value).toString(), label: "Reseñas publicadas", detail: "Opiniones compartidas sobre la experiencia en el salón" },
  { end: 6, value: "6 días", format: (value: number) => `${Math.round(value)} días`, label: "Abiertos cada semana", detail: "De lunes a sábado para adaptarnos a tu agenda" },
  { end: 1, value: "1 a 1", format: (value: number) => `${Math.round(value)} a ${Math.round(value)}`, label: "Asesoramiento personal", detail: "Cada servicio parte de tu cabello, tu estilo y tus objetivos" },
];

function CountUpMetric({ active, end, format, value, delay }: { active: boolean; end: number; format: (value: number) => string; value: string; delay: number }) {
  const reducedMotion = useReducedMotion();
  const [current, setCurrent] = useState(reducedMotion ? end : 0);
  const frameRef = useRef<number | null>(null);

  useEffect(() => {
    if (!active) return;
    if (reducedMotion) {
      setCurrent(end);
      return;
    }

    const duration = 680;
    let startTime: number | null = null;
    const tick = (time: number) => {
      if (startTime === null) startTime = time + delay;
      const progress = Math.min(Math.max((time - startTime) / duration, 0), 1);
      const eased = 1 - Math.pow(1 - progress, 4);
      setCurrent(end * eased);
      if (progress < 1) frameRef.current = requestAnimationFrame(tick);
    };

    frameRef.current = requestAnimationFrame(tick);
    return () => {
      if (frameRef.current !== null) cancelAnimationFrame(frameRef.current);
    };
  }, [active, delay, end, reducedMotion]);

  return (
    <strong aria-label={value} className="block font-display text-6xl font-medium leading-none tracking-[-0.035em] text-ink tabular-nums lg:text-8xl">
      <span aria-hidden="true">{format(current)}</span>
    </strong>
  );
}

export function Testimonials() {
  const metricsRef = useRef<HTMLDivElement>(null);
  const [countStarted, setCountStarted] = useState(false);
  const reducedMotion = useReducedMotion();

  useEffect(() => {
    const metrics = metricsRef.current;
    if (!metrics || reducedMotion || !("IntersectionObserver" in window)) {
      setCountStarted(true);
      return;
    }

    const observer = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) return;
      setCountStarted(true);
      observer.disconnect();
    }, { threshold: 0.3, rootMargin: "0px 0px -8%" });
    observer.observe(metrics);
    return () => observer.disconnect();
  }, [reducedMotion]);

  return (
    <section id="valoraciones" className="section-space overflow-hidden bg-mist" aria-labelledby="testimonials-title">
      <div className="showcase-shell">
        <div className="grid gap-8 lg:grid-cols-12 lg:items-end">
          <div className="lg:col-span-9">
            <p className="mb-4 font-semibold text-brass-deep">Lo cuentan quienes vuelven</p>
            <RevealTitle id="testimonials-title" label="La confianza se nota." className="display-title" lines={[{ content: "La confianza" }, { content: "se nota.", className: "font-normal text-brass-deep" }]} />
          </div>
          <p className="max-w-md text-pretty text-base leading-7 text-taupe lg:col-span-3 lg:pb-3">Resultados que se ven, atención que se recuerda. Estas cifras resumen lo que ocurre después de cada cita.</p>
        </div>

        <ScrollReveal ref={metricsRef} className="mt-16 grid border-t border-ink/15 sm:grid-cols-2">
          {reviewMetrics.map((metric, index) => (
            <article key={metric.label} className={`py-10 sm:px-8 lg:py-14 ${index % 2 === 0 ? "sm:border-r sm:border-ink/15" : ""} ${index > 1 ? "border-t border-ink/15" : index === 1 ? "border-t border-ink/15 sm:border-t-0" : ""}`}>
              <CountUpMetric active={countStarted} end={metric.end} format={metric.format} value={metric.value} delay={index * 55} />
              <h3 className="mt-6 border-t border-ink/15 pt-4 text-base font-bold text-ink">{metric.label}</h3>
              <p className="mt-2 max-w-sm text-sm leading-6 text-taupe">{metric.detail}</p>
            </article>
          ))}
        </ScrollReveal>

        <div className="mt-20"><StaggerTestimonials /></div>
      </div>
    </section>
  );
}
