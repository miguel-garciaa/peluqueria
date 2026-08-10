import { ChevronLeft, ChevronRight, X } from "lucide-react";
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
          <button type="button" onClick={() => onMove(-1)} aria-label="Fotografía anterior" className="absolute left-3 top-1/2 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 transition-colors hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass sm:left-8 sm:size-14"><ChevronLeft /></button>
          <figure key={item.id} className="gallery-lightbox-figure flex min-h-0 max-h-full max-w-[min(82vw,88rem)] flex-col items-center">
            <img src={largeSrc} alt={item.alt} className="min-h-0 max-h-[76svh] w-auto max-w-full rounded-xl object-contain" />
            <figcaption className="mt-4 flex w-full items-center justify-between gap-4 text-sm">
              <span id="gallery-lightbox-title" className="font-semibold">{item.alt}</span>
              <span className="shrink-0 text-brass">{item.category}</span>
            </figcaption>
          </figure>
          <button type="button" onClick={() => onMove(1)} aria-label="Siguiente fotografía" className="absolute right-3 top-1/2 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 transition-colors hover:bg-brass hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass sm:right-8 sm:size-14"><ChevronRight /></button>
        </div>
      )}
    </dialog>
  );
}
