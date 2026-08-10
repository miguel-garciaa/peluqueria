import { useCallback, useEffect, useMemo, useState } from "react";
import { galleryItems } from "@/data/content";
import { cn } from "@/lib/utils";
import type { GalleryCategory } from "@/types";
import { GalleryFallback } from "./gallery/GalleryFallback";
import { GalleryLightbox } from "./gallery/GalleryLightbox";
import { GalleryRibbon } from "./gallery/GalleryRibbon";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

const categories: GalleryCategory[] = ["Todos", "Cortes", "Color", "Tratamientos"];

export function Gallery() {
  const [selectedCategory, setSelectedCategory] = useState<GalleryCategory>("Todos");
  const [useFallback, setUseFallback] = useState(false);
  const [activeItem, setActiveItem] = useState<(typeof galleryItems)[number] | null>(null);

  useEffect(() => {
    const query = window.matchMedia("(prefers-reduced-motion: reduce)");
    const sync = () => setUseFallback(query.matches);
    sync(); query.addEventListener("change", sync);
    return () => query.removeEventListener("change", sync);
  }, []);

  const filtered = useMemo(() => selectedCategory === "Todos" ? galleryItems : galleryItems.filter((item) => item.category === selectedCategory), [selectedCategory]);
  const handleContextLost = useCallback(() => setUseFallback(true), []);
  const handleSelectItem = useCallback((item: (typeof galleryItems)[number]) => setActiveItem(item), []);
  const moveLightbox = useCallback((direction: -1 | 1) => {
    setActiveItem((current) => {
      const currentIndex = filtered.findIndex((item) => item.id === current?.id);
      return filtered[(currentIndex + direction + filtered.length) % filtered.length];
    });
  }, [filtered]);

  return (
    <section id="galeria" className="bg-ink py-20 text-white md:py-24">
      <div className="container-shell relative z-10">
        <div className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
          <div><p className="mb-3 font-semibold text-brass">Trabajo reciente</p><RevealTitle label="Cabello en movimiento." className="display-title max-w-4xl" lines={[{ content: "Cabello en" }, { content: "movimiento.", className: "font-normal text-brass" }]} /></div>
          <p className="max-w-md leading-7 text-white/55">Una selección de cortes, matices y texturas creados en el estudio.</p>
        </div>
        <ScrollReveal className="mt-8 flex flex-wrap items-center gap-2 md:mt-6" role="group" aria-label="Filtrar galería">
          {categories.map((category) => <button key={category} type="button" onClick={() => setSelectedCategory(category)} aria-pressed={selectedCategory === category} className={cn("min-h-11 rounded-full border px-4 text-sm font-semibold transition-colors", selectedCategory === category ? "border-brass bg-brass text-ink" : "border-white/15 text-white/70 hover:border-white/40 hover:text-white")}>{category}</button>)}
        </ScrollReveal>
      </div>
      <div className="mt-6 md:mt-4">{useFallback ? <div className="container-shell"><GalleryFallback items={filtered} onSelectItem={handleSelectItem} /></div> : <GalleryRibbon items={filtered} onContextLost={handleContextLost} onSelectItem={handleSelectItem} />}</div>
      <ul className="sr-only">{filtered.map((item) => <li key={item.id}>{item.alt}</li>)}</ul>
      <GalleryLightbox item={activeItem} onClose={() => setActiveItem(null)} onMove={moveLightbox} />
    </section>
  );
}
