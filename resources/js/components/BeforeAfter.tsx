import { ChevronLeft, ChevronRight, MoveHorizontal } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { transformations } from "@/data/content";
import { CompareReveal } from "@/components/ui/compare-reveal";
import { cn } from "@/lib/utils";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

export function BeforeAfter() {
  const sectionRef = useRef<HTMLElement>(null);
  const [activeIndex, setActiveIndex] = useState(0);
  const [isNearViewport, setIsNearViewport] = useState(false);
  const active = transformations[activeIndex];

  const move = (direction: number) => {
    setActiveIndex((current) => (current + direction + transformations.length) % transformations.length);
  };

  useEffect(() => {
    const section = sectionRef.current;
    if (!section || isNearViewport) return;

    const observer = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) return;
      setIsNearViewport(true);
      observer.disconnect();
    }, { rootMargin: "600px 0px" });

    observer.observe(section);
    return () => observer.disconnect();
  }, [isNearViewport]);

  useEffect(() => {
    if (!isNearViewport) return;

    const adjacent = [
      transformations[(activeIndex + 1) % transformations.length],
      transformations[(activeIndex - 1 + transformations.length) % transformations.length],
    ];
    adjacent.forEach((item) => {
      [item.before.src, item.after.src].forEach((src) => { const preload = new Image(); preload.src = src; });
    });
  }, [activeIndex, isNearViewport]);

  return (
    <section ref={sectionRef} id="transformaciones" className="relative overflow-hidden bg-charcoal py-24 text-white sm:py-32" aria-labelledby="before-after-title">
      <div className="showcase-shell relative z-10">
        <div className="grid gap-8 lg:grid-cols-[1.12fr_0.88fr] lg:items-end lg:gap-16">
          <div>
          <p className="mb-3 font-semibold text-brass">Una transformación honesta</p>
          <RevealTitle id="before-after-title" label="Entras siendo tú. Sales más tú." className="display-title" lines={[{ content: "Entras siendo tú." }, { content: "Sales más tú.", className: "font-normal text-brass" }]} />
          </div>
          <div aria-live="polite" aria-atomic="true">
            <p className="font-semibold text-brass">{String(activeIndex + 1).padStart(2, "0")} · {active.service}</p>
            <h3 className="mt-2 font-display text-2xl font-semibold text-balance sm:text-3xl">{active.title}</h3>
            <p className="mt-3 max-w-xl leading-7 text-white/62">{active.description}</p>
          </div>
        </div>

        <ScrollReveal className="relative mt-10 sm:mt-12 md:grid md:grid-cols-[3.5rem_minmax(0,1fr)_3.5rem] md:items-center md:gap-3">
          <button type="button" onClick={() => move(-1)} aria-label="Transformación anterior" className="absolute left-3 top-1/2 z-20 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-ink/88 text-white shadow-[0_4px_8px_oklch(0.12_0.01_65/0.24)] ring-1 ring-white/20 backdrop-blur-sm transition-[transform,background-color,color] duration-200 hover:-translate-y-1/2 hover:scale-105 hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass md:static md:size-14 md:translate-y-0 md:justify-self-center md:hover:translate-y-0"><ChevronLeft className="size-5" /></button>
          <div key={active.id} className="comparison-enter min-w-0">
            <CompareReveal before={active.before} after={active.after} labels={["Antes", "Después"]} className="aspect-[4/5] rounded-2xl sm:aspect-[16/10] lg:aspect-[21/10]" />
          </div>
          <button type="button" onClick={() => move(1)} aria-label="Siguiente transformación" className="absolute right-3 top-1/2 z-20 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-ink/88 text-white shadow-[0_4px_8px_oklch(0.12_0.01_65/0.24)] ring-1 ring-white/20 backdrop-blur-sm transition-[transform,background-color,color] duration-200 hover:-translate-y-1/2 hover:scale-105 hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass md:static md:size-14 md:translate-y-0 md:justify-self-center md:hover:translate-y-0"><ChevronRight className="size-5" /></button>
        </ScrollReveal>

        <div className="mt-6 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
          <p className="flex items-center gap-2 text-sm font-semibold text-white/80"><MoveHorizontal className="size-4 text-brass" />Arrastra el centro para comparar</p>
          <div className="flex items-center justify-between gap-5 sm:justify-end">
            <div className="flex gap-2" role="group" aria-label="Elegir transformación">
              {transformations.map((item, index) => <button key={item.id} type="button" onClick={() => setActiveIndex(index)} aria-label={`Mostrar ${item.title}`} aria-pressed={index === activeIndex} className={cn("h-2.5 rounded-full transition-[width,background-color] duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal", index === activeIndex ? "w-8 bg-brass" : "w-2.5 bg-white/28 hover:bg-white/55")} />)}
            </div>
            <span className="min-w-12 text-right text-sm font-semibold tabular-nums text-white/58">{activeIndex + 1} / {transformations.length}</span>
          </div>
        </div>
      </div>
    </section>
  );
}
