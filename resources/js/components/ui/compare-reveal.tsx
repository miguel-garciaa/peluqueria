import * as React from "react";
import { cn } from "@/lib/utils";

export interface CompareRevealProps extends Omit<React.HTMLAttributes<HTMLDivElement>, "onChange"> {
  before: { src: string; alt: string };
  after: { src: string; alt: string };
  labels?: [string, string];
  defaultPosition?: number;
  onPositionChange?: (position: number) => void;
}

const clamp = (value: number) => Math.min(100, Math.max(0, value));

export function CompareReveal({ before, after, labels = ["Antes", "Después"], defaultPosition = 50, onPositionChange, className, ...props }: CompareRevealProps) {
  const rootRef = React.useRef<HTMLDivElement>(null);
  const [position, setPosition] = React.useState(clamp(defaultPosition));
  const dragging = React.useRef(false);
  const positionRef = React.useRef(position);

  const commit = React.useCallback((next: number) => {
    const value = clamp(next);
    positionRef.current = value;
    setPosition(value);
    onPositionChange?.(value);
  }, [onPositionChange]);

  const fromClientX = (clientX: number) => {
    const rect = rootRef.current?.getBoundingClientRect();
    if (rect) commit(((clientX - rect.left) / Math.max(1, rect.width)) * 100);
  };

  const onPointerDown = (event: React.PointerEvent<HTMLDivElement>) => {
    if (event.pointerType === "mouse" && event.button !== 0) return;
    dragging.current = true;
    event.currentTarget.setPointerCapture(event.pointerId);
    fromClientX(event.clientX);
  };
  const onPointerMove = (event: React.PointerEvent<HTMLDivElement>) => dragging.current && fromClientX(event.clientX);
  const stopDragging = () => { dragging.current = false; };
  const onKeyDown = (event: React.KeyboardEvent<HTMLButtonElement>) => {
    const step = event.shiftKey ? 10 : 2;
    if (["ArrowRight", "ArrowUp"].includes(event.key)) commit(positionRef.current + step);
    else if (["ArrowLeft", "ArrowDown"].includes(event.key)) commit(positionRef.current - step);
    else if (event.key === "Home") commit(0);
    else if (event.key === "End") commit(100);
    else return;
    event.preventDefault();
  };

  const shown = Math.round(position);
  return (
    <div ref={rootRef} role="group" aria-label={`Comparación: ${labels[0]} y ${labels[1]}`} onPointerDown={onPointerDown} onPointerMove={onPointerMove} onPointerUp={stopDragging} onPointerCancel={stopDragging} onDoubleClick={() => commit(50)} className={cn("relative aspect-[4/5] w-full touch-pan-y select-none overflow-hidden rounded-2xl bg-charcoal sm:aspect-[16/10]", className)} {...props}>
      <img src={after.src} alt={after.alt} loading="lazy" decoding="async" className="absolute inset-0 h-full w-full object-cover" draggable={false} />
      <div className="absolute inset-0 will-change-[clip-path]" style={{ clipPath: `inset(0 ${100 - position}% 0 0)` }}><img src={before.src} alt={before.alt} loading="lazy" decoding="async" className="h-full w-full object-cover saturate-[.25] contrast-[.86] brightness-[.7]" draggable={false} /></div>
      <span aria-hidden="true" className="absolute left-4 top-4 z-10 rounded-full bg-ink/70 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm">{labels[0]}</span>
      <span aria-hidden="true" className="absolute right-4 top-4 z-10 rounded-full bg-ink/70 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm">{labels[1]}</span>
      <div className="pointer-events-none absolute inset-y-0 z-10 w-0.5 bg-brass" style={{ left: `${position}%` }}>
        <button type="button" role="slider" aria-label="Control de comparación antes y después" aria-valuemin={0} aria-valuemax={100} aria-valuenow={shown} aria-valuetext={`${shown}% antes`} onKeyDown={onKeyDown} className="pointer-events-auto absolute left-1/2 top-1/2 grid size-12 -translate-x-1/2 -translate-y-1/2 cursor-ew-resize place-items-center rounded-full border-2 border-brass bg-ink text-brass shadow-[0_0_0_6px_oklch(0.83_0.082_78/0.2)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
          <svg width="20" height="14" viewBox="0 0 20 14" fill="none" aria-hidden="true"><path d="M7 1 1 7l6 6M13 1l6 6-6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" /></svg>
        </button>
      </div>
    </div>
  );
}
