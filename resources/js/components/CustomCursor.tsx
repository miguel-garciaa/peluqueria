import { useEffect, useRef } from "react";

const interactiveSelector = "a, button:not(:disabled), [role='button'], [role='slider'], summary, label[for], input[type='button'], input[type='submit'], input[type='checkbox'], input[type='radio']";
const nativeCursorSelector = "input:not([type='button']):not([type='submit']):not([type='reset']):not([type='checkbox']):not([type='radio']), textarea, select, [contenteditable='true'], :disabled, [aria-disabled='true']";

export function CustomCursor() {
  const cursorRef = useRef<HTMLDivElement>(null);
  const dotRef = useRef<HTMLSpanElement>(null);
  const pulseRef = useRef<HTMLSpanElement>(null);
  const lastPosition = useRef({ x: -32, y: -32 });

  useEffect(() => {
    const cursor = cursorRef.current;
    const dot = dotRef.current;
    const pulse = pulseRef.current;
    if (!cursor || !dot || !pulse) return;

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

    const onPointerDown = (event: PointerEvent) => {
      if (event.pointerType !== "mouse" || cursor.classList.contains("is-native")) return;
      if (typeof dot.animate !== "function" || typeof pulse.animate !== "function") return;
      dot.getAnimations?.().forEach((animation) => animation.cancel());
      pulse.getAnimations?.().forEach((animation) => animation.cancel());
      dot.animate(
        [{ transform: "scale(1)" }, { transform: "scale(.55)", offset: .35 }, { transform: "scale(1)" }],
        { duration: 220, easing: "cubic-bezier(.2,.8,.2,1)" },
      );
      pulse.animate(
        [{ opacity: .8, transform: "scale(.65)" }, { opacity: 0, transform: "scale(2.8)" }],
        { duration: 360, easing: "cubic-bezier(.16,1,.3,1)" },
      );
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
    window.addEventListener("pointerdown", onPointerDown, { passive: true });
    window.addEventListener("blur", hideCursor);
    document.documentElement.addEventListener("mouseleave", hideCursor);

    return () => {
      root.classList.remove("has-custom-cursor");
      if (moveFrame) window.cancelAnimationFrame(moveFrame);
      if (raiseFrame) window.cancelAnimationFrame(raiseFrame);
      dialogObserver.disconnect();
      window.removeEventListener("pointermove", onPointerMove);
      window.removeEventListener("pointerover", onPointerOver);
      window.removeEventListener("pointerdown", onPointerDown);
      window.removeEventListener("blur", hideCursor);
      document.documentElement.removeEventListener("mouseleave", hideCursor);
      try { cursor.hidePopover(); } catch { /* Already hidden. */ }
    };
  }, []);

  return (
    <div ref={cursorRef} popover="manual" className="custom-cursor" aria-hidden="true">
      <span className="custom-cursor__center">
        <span ref={pulseRef} className="custom-cursor__pulse" />
        <span ref={dotRef} className="custom-cursor__dot" />
      </span>
    </div>
  );
}
