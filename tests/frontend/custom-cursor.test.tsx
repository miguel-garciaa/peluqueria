import { act, render } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { CustomCursor } from "@/components/CustomCursor";

const originalMatchMedia = window.matchMedia;
const originalAnimate = Object.getOwnPropertyDescriptor(HTMLElement.prototype, "animate");

afterEach(() => {
  Object.defineProperty(window, "matchMedia", { writable: true, value: originalMatchMedia });
  if (originalAnimate) Object.defineProperty(HTMLElement.prototype, "animate", originalAnimate);
  else delete (HTMLElement.prototype as Partial<HTMLElement>).animate;
  vi.restoreAllMocks();
});

function pointerEvent(type: string, x: number, y: number) {
  const event = new Event(type, { bubbles: true });
  Object.defineProperties(event, {
    pointerType: { value: "mouse" },
    clientX: { value: x },
    clientY: { value: y },
  });
  return event;
}

describe("CustomCursor", () => {
  it("updates through one animation frame and uses compositor-only click animations", () => {
    Object.defineProperty(window, "matchMedia", { writable: true, value: (query: string) => ({ matches: !query.includes("prefers-reduced-motion"), media: query, onchange: null, addListener: () => {}, removeListener: () => {}, addEventListener: () => {}, removeEventListener: () => {}, dispatchEvent: () => false }) });

    let frame: FrameRequestCallback | undefined;
    const requestFrame = vi.spyOn(window, "requestAnimationFrame").mockImplementation((callback) => { frame = callback; return 1; });
    const animate = vi.fn(() => ({ cancel: vi.fn() }) as unknown as Animation);
    Object.defineProperty(HTMLElement.prototype, "animate", { configurable: true, value: animate });

    const { container } = render(<CustomCursor />);
    const cursor = container.querySelector<HTMLElement>(".custom-cursor");
    expect(document.documentElement).toHaveClass("has-custom-cursor");

    act(() => {
      window.dispatchEvent(pointerEvent("pointermove", 100, 60));
      window.dispatchEvent(pointerEvent("pointermove", 110, 70));
      window.dispatchEvent(pointerEvent("pointermove", 120, 80));
    });
    expect(requestFrame).toHaveBeenCalledTimes(1);
    act(() => frame?.(16));
    expect(cursor).toHaveStyle({ transform: "translate3d(120px, 80px, 0)" });

    act(() => window.dispatchEvent(pointerEvent("pointerdown", 120, 80)));
    expect(animate).toHaveBeenCalledTimes(2);
  });
});
