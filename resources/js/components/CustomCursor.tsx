import { useEffect, useRef } from "react";

const interactiveSelector = "a, button:not(:disabled), [role='button'], [role='slider'], summary, label[for], input[type='button'], input[type='submit'], input[type='checkbox'], input[type='radio']";
const nativeCursorSelector = "input:not([type='button']):not([type='submit']):not([type='reset']):not([type='checkbox']):not([type='radio']), textarea, select, [contenteditable='true'], :disabled, [aria-disabled='true']";

export function CustomCursor() {
  const cursorRef = useRef<HTMLDivElement>(null);
  const lastPosition = useRef({ x: -32, y: -32 });

  useEffect(() => {
    const cursor = cursorRef.current;
    if (!cursor) return;

    const finePointer = window.matchMedia("(pointer: fine) and (hover: hover)");
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    if (!finePointer.matches || reducedMotion.matches) return;

    const root = document.documentElement;
    root.classList.add("has-custom-cursor");

    let moveFrame = 0;
    let raiseFrame = 0;

    const paintPosition = () => {
      moveFrame = 0;
      const { x, y } = lastPosition.current;
      cursor.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    };

    const onPointerMove = (event: PointerEvent) => {
      if (event.pointerType !== "mouse") return;
      lastPosition.current = { x: event.clientX, y: event.clientY };
      cursor.dataset.visible = "true";
      if (!moveFrame) moveFrame = window.requestAnimationFrame(paintPosition);
    };

    const onPointerOver = (event: PointerEvent) => {
      const target = event.target instanceof Element ? event.target : null;
      const usesNativeCursor = Boolean(target?.closest(nativeCursorSelector));
      cursor.classList.toggle("is-native", usesNativeCursor);
      cursor.classList.toggle("is-interactive", !usesNativeCursor && Boolean(target?.closest(interactiveSelector)));
    };

    const hideCursor = () => { delete cursor.dataset.visible; };

    const raiseAboveDialogs = () => {
      if (typeof cursor.showPopover !== "function") return;
      try { cursor.hidePopover(); } catch { /* Already hidden. */ }
      try { cursor.showPopover(); } catch { /* Popover API unavailable in this context. */ }
    };

    raiseAboveDialogs();
    const dialogObserver = new MutationObserver((entries) => {
      if (!entries.some((entry) => entry.target instanceof HTMLDialogElement && entry.attributeName === "open")) return;
      if (raiseFrame) window.cancelAnimationFrame(raiseFrame);
      raiseFrame = window.requestAnimationFrame(raiseAboveDialogs);
    });
    dialogObserver.observe(document.body, { attributes: true, attributeFilter: ["open"], subtree: true });

    window.addEventListener("pointermove", onPointerMove, { passive: true });
    window.addEventListener("pointerover", onPointerOver, { passive: true });
    window.addEventListener("blur", hideCursor);
    document.documentElement.addEventListener("mouseleave", hideCursor);

    return () => {
      root.classList.remove("has-custom-cursor");
      if (moveFrame) window.cancelAnimationFrame(moveFrame);
      if (raiseFrame) window.cancelAnimationFrame(raiseFrame);
      dialogObserver.disconnect();
      window.removeEventListener("pointermove", onPointerMove);
      window.removeEventListener("pointerover", onPointerOver);
      window.removeEventListener("blur", hideCursor);
      document.documentElement.removeEventListener("mouseleave", hideCursor);
      try { cursor.hidePopover(); } catch { /* Already hidden. */ }
    };
  }, []);

  return (
    <div ref={cursorRef} popover="manual" className="custom-cursor" aria-hidden="true">
      <span className="custom-cursor__center">
        <span className="custom-cursor__dot" />
      </span>
    </div>
  );
}
