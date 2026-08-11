import { useCallback, useRef } from "react";
import type { GalleryItem } from "@/types";

interface GalleryRibbonProps {
  items: GalleryItem[];
  onSelectItem: (item: GalleryItem) => void;
}

export function GalleryRibbon({ items, onSelectItem }: GalleryRibbonProps) {
  const rootRef = useRef<HTMLDivElement>(null);
  const trackRef = useRef<HTMLDivElement>(null);
  const speedFrameRef = useRef(0);

  const setPlaybackRate = useCallback((targetRate: number) => {
    const root = rootRef.current;
    const animation = trackRef.current?.getAnimations?.()[0];
    if (!root) return;

    root.dataset.slowed = String(targetRate < 1);
    if (!animation) return;

    cancelAnimationFrame(speedFrameRef.current);
    const startRate = animation.playbackRate;
    const startedAt = performance.now();
    const duration = targetRate < 1 ? 500 : 300;

    const updateRate = (now: number) => {
      const progress = Math.min((now - startedAt) / duration, 1);
      const eased = 1 - ((1 - progress) ** 4);
      animation.playbackRate = startRate + ((targetRate - startRate) * eased);

      if (progress < 1) speedFrameRef.current = requestAnimationFrame(updateRate);
    };

    speedFrameRef.current = requestAnimationFrame(updateRate);
  }, []);

  const renderItems = (duplicate = false) => (
    <ul className="gallery-marquee-group" aria-hidden={duplicate || undefined}>
      {items.map((item) => (
        <li key={`${duplicate ? "copy" : "original"}-${item.id}`}>
          <button
            type="button"
            tabIndex={duplicate ? -1 : 0}
            onClick={() => onSelectItem(item)}
            className="gallery-marquee-card group"
            aria-label={`Ver detalles: ${item.alt}`}
          >
            <img src={item.src} alt="" loading="lazy" draggable="false" />
            <span className="gallery-marquee-caption">
              <span>{item.category}</span>
              <strong>{item.alt}</strong>
            </span>
          </button>
        </li>
      ))}
    </ul>
  );

  return (
    <div
      ref={rootRef}
      className="gallery-marquee"
      role="region"
      aria-label="Galería de trabajos. Pasa el cursor para ralentizar las fotografías."
      onPointerEnter={() => setPlaybackRate(0.16)}
      onPointerLeave={() => setPlaybackRate(1)}
      onFocus={() => setPlaybackRate(0.16)}
      onBlur={() => setPlaybackRate(1)}
    >
      <div ref={trackRef} className="gallery-marquee-track" style={{ animationDuration: `${Math.max(items.length * 5, 32)}s` }}>
        {renderItems()}
        {renderItems(true)}
      </div>
    </div>
  );
}
