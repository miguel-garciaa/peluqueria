import { ChevronLeft, ChevronRight, Star } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { testimonials } from "@/data/content";
import { cn } from "@/lib/utils";

export function StaggerTestimonials() {
  const [activeIndex, setActiveIndex] = useState(0);
  const [revealed, setRevealed] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const pointerStart = useRef<number | null>(null);
  const count = testimonials.length;
  const move = (step: number) => setActiveIndex((current) => (current + step + count) % count);

  useEffect(() => {
    const root = rootRef.current;
    if (!root) return;
    const observer = new IntersectionObserver(([entry]) => entry.isIntersecting && setRevealed(true), { threshold: 0.3 });
    observer.observe(root);
    return () => observer.disconnect();
  }, []);

  const positionFor = (index: number) => {
    const raw = (index - activeIndex + count) % count;
    return raw > Math.floor(count / 2) ? raw - count : raw;
  };

  return (
    <div ref={rootRef} role="region" aria-roledescription="carrusel" aria-label="Valoraciones de clientes" tabIndex={0} onKeyDown={(event) => { if (event.key === "ArrowLeft") move(-1); if (event.key === "ArrowRight") move(1); }} onPointerDown={(event) => { pointerStart.current = event.clientX; }} onPointerUp={(event) => { if (pointerStart.current === null) return; const delta = event.clientX - pointerStart.current; if (Math.abs(delta) > 50) move(delta > 0 ? -1 : 1); pointerStart.current = null; }} className="relative h-[34rem] w-full overflow-hidden outline-none sm:h-[38rem]">
      {testimonials.map((testimonial, index) => {
        const position = positionFor(index);
        const active = position === 0;
        const visible = Math.abs(position) <= 2;
        return (
          <button key={testimonial.id} type="button" onClick={() => setActiveIndex(index)} aria-label={`Mostrar valoración de ${testimonial.author}`} aria-hidden={!visible} tabIndex={active ? 0 : -1} className={cn("absolute left-1/2 top-[46%] flex h-[24rem] w-[min(82vw,23rem)] flex-col rounded-2xl border p-7 text-left transition-all duration-500 [transition-timing-function:var(--ease-premium)] sm:p-8", active ? "z-20 border-ink bg-ink text-white shadow-[0_8px_18px_-12px_oklch(0.17_0.012_65/0.7)]" : "z-10 border-ink/10 bg-white text-ink", !visible && "pointer-events-none opacity-0")} style={{ transform: revealed ? `translate(-50%, -50%) translateX(${position * 58}%) translateY(${active ? -16 : Math.abs(position) * 12}px) rotate(${position * 2.3}deg) scale(${active ? 1 : 0.9})` : "translate(-50%, -50%) scale(.88)", opacity: visible ? (revealed ? (active ? 1 : 0.68) : 1) : 0 }}>
            <div className="flex gap-1 text-brass-deep" aria-label={`${testimonial.rating} de 5 estrellas`}>{Array.from({ length: testimonial.rating }, (_, star) => <Star key={star} className="size-4 fill-current" aria-hidden="true" />)}</div>
            <blockquote className="mt-7 flex-1 font-display text-2xl font-medium leading-snug tracking-[-0.02em]">“{testimonial.quote}”</blockquote>
            <div className="mt-6 flex items-center gap-3 border-t border-current/10 pt-5"><img src={testimonial.avatarSrc} alt="" className="size-12 rounded-full object-cover" loading="lazy" /><div><strong className="block text-sm">{testimonial.author}</strong><span className={cn("text-xs", active ? "text-white/55" : "text-taupe")}>{testimonial.service}</span></div></div>
          </button>
        );
      })}
      <p className="sr-only" aria-live="polite">Valoración activa: {testimonials[activeIndex].quote}, {testimonials[activeIndex].author}</p>
      <div className="absolute bottom-2 left-1/2 z-30 flex -translate-x-1/2 gap-2">
        <button type="button" onClick={() => move(-1)} className="grid size-12 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-colors hover:bg-ink hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass-deep" aria-label="Valoración anterior"><ChevronLeft /></button>
        <button type="button" onClick={() => move(1)} className="grid size-12 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-colors hover:bg-ink hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass-deep" aria-label="Valoración siguiente"><ChevronRight /></button>
      </div>
    </div>
  );
}
