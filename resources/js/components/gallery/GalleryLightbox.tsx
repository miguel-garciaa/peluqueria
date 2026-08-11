import { ChevronLeft, ChevronRight, Scissors, Sparkles, UserRound, X } from "lucide-react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import type { GalleryItem } from "@/types";

interface GalleryLightboxProps {
  item: GalleryItem | null;
  onClose: () => void;
  onMove: (direction: -1 | 1) => void;
}

export function GalleryLightbox({ item, onClose, onMove }: GalleryLightboxProps) {
  const dialogRef = useRef<HTMLDialogElement>(null);
  const closeTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const [isClosing, setIsClosing] = useState(false);
  const [isEntered, setIsEntered] = useState(false);
  const [isPhotoReady, setIsPhotoReady] = useState(false);
  const reducedMotion = useReducedMotion();
  const largeSrc = useMemo(() => item?.src.replace(/([?&])w=\d+/, "$1w=1800"), [item]);

  const requestClose = useCallback(() => {
    if (isClosing) return;
    if (reducedMotion) {
      onClose();
      return;
    }
    setIsClosing(true);
    setIsEntered(false);
    setIsPhotoReady(false);
    closeTimerRef.current = setTimeout(() => {
      setIsClosing(false);
      onClose();
    }, 300);
  }, [isClosing, onClose, reducedMotion]);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    let entranceFrame: number | null = null;
    let photoFrame: number | null = null;

    if (item) {
      const isNewOpening = !dialog.open;
      if (isNewOpening) {
        setIsEntered(reducedMotion);
        dialog.showModal();
        if (!reducedMotion) entranceFrame = requestAnimationFrame(() => setIsEntered(true));
      }

      setIsPhotoReady(reducedMotion);
      if (!reducedMotion) photoFrame = requestAnimationFrame(() => setIsPhotoReady(true));
    } else {
      setIsEntered(false);
      setIsPhotoReady(false);
      if (dialog.open) dialog.close();
    }

    return () => {
      if (entranceFrame !== null) cancelAnimationFrame(entranceFrame);
      if (photoFrame !== null) cancelAnimationFrame(photoFrame);
    };
  }, [item, reducedMotion]);

  useEffect(() => {
    if (!item) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = previousOverflow; };
  }, [item]);

  useEffect(() => () => {
    if (closeTimerRef.current) clearTimeout(closeTimerRef.current);
  }, []);

  return (
    <dialog
      ref={dialogRef}
      onCancel={(event) => { event.preventDefault(); requestClose(); }}
      onClick={(event) => { if (event.target === event.currentTarget) requestClose(); }}
      data-entered={isEntered}
      data-photo-ready={isPhotoReady}
      data-closing={isClosing || undefined}
      className="gallery-lightbox m-auto h-[100svh] w-screen max-w-none overflow-hidden bg-ink/96 p-0 text-white"
      aria-labelledby="gallery-lightbox-title"
    >
      {item && (
        <div className="gallery-lightbox-panel relative flex h-full w-full flex-col items-center justify-center px-4 py-16 sm:px-16">
          <button type="button" onClick={requestClose} aria-label="Cerrar fotografía" className="absolute right-4 top-4 grid size-12 place-items-center rounded-full bg-white text-ink transition-[transform,background-color,color] duration-200 hover:scale-105 hover:bg-ink hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass sm:right-8 sm:top-8"><X /></button>
          <button type="button" onClick={() => onMove(-1)} aria-label="Fotografía anterior" className="absolute bottom-4 left-4 grid size-12 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 transition-colors hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass sm:bottom-auto sm:left-8 sm:top-1/2 sm:size-14 sm:-translate-y-1/2"><ChevronLeft /></button>
          <figure key={item.id} className="gallery-lightbox-figure grid min-h-0 max-h-full w-full max-w-[min(86vw,76rem)] overflow-hidden rounded-xl bg-charcoal lg:grid-cols-[minmax(0,1.45fr)_minmax(18rem,.65fr)]">
            <div className="flex min-h-0 items-center justify-center bg-black/25">
              <img src={largeSrc} alt={item.alt} className="h-[48svh] w-full object-contain lg:h-[76svh]" />
            </div>
            <figcaption className="max-h-[29svh] overflow-y-auto p-5 sm:p-7 lg:max-h-none lg:self-center lg:p-9">
              <p className="text-sm font-semibold text-brass">{item.category}</p>
              <h3 id="gallery-lightbox-title" className="mt-2 text-balance font-display text-2xl font-semibold sm:text-3xl">{item.alt}</h3>
              <dl className="mt-6 space-y-5">
                <div><dt className="flex items-center gap-2 text-xs font-semibold text-white/55"><Scissors className="size-4 text-brass" />Tipo de corte</dt><dd className="mt-1.5 text-sm font-semibold leading-6">{item.cut}</dd></div>
                <div><dt className="flex items-center gap-2 text-xs font-semibold text-white/55"><Sparkles className="size-4 text-brass" />Tratamiento</dt><dd className="mt-1.5 text-sm font-semibold leading-6">{item.treatment}</dd></div>
                <div><dt className="flex items-center gap-2 text-xs font-semibold text-white/55"><UserRound className="size-4 text-brass" />Profesional</dt><dd className="mt-1.5 text-sm font-semibold leading-6">{item.professional}</dd></div>
              </dl>
            </figcaption>
          </figure>
          <button type="button" onClick={() => onMove(1)} aria-label="Siguiente fotografía" className="absolute bottom-4 right-4 grid size-12 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 transition-colors hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass sm:bottom-auto sm:right-8 sm:top-1/2 sm:size-14 sm:-translate-y-1/2"><ChevronRight /></button>
        </div>
      )}
    </dialog>
  );
}
