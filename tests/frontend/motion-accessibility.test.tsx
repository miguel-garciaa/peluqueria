import { act, render, screen } from "@testing-library/react";
import { createRef } from "react";
import { describe, expect, it } from "vitest";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

const media = (matches: boolean) => (query: string) => ({
  matches,
  media: query,
  onchange: null,
  addListener: () => undefined,
  removeListener: () => undefined,
  addEventListener: () => undefined,
  removeEventListener: () => undefined,
  dispatchEvent: () => false,
});

describe("motion-aware reveals", () => {
  it("renders content immediately when reduced motion is requested", () => {
    Object.defineProperty(window, "matchMedia", { configurable: true, value: media(true) });
    render(<RevealTitle label="Título accesible" lines={[{ content: "Primera" }, { content: "Segunda" }]} />);

    const title = screen.getByRole("heading", { name: "Título accesible" });
    expect(title).toHaveAttribute("data-visible", "true");
    expect(title).toHaveAttribute("data-motion-ready", "false");
    expect(title.querySelectorAll(".reveal-title-mark")).toHaveLength(1);
  });

  it("reveals off-screen content only after intersection and disconnects", () => {
    Object.defineProperty(window, "matchMedia", { configurable: true, value: media(false) });
    let callback: IntersectionObserverCallback = () => undefined;
    let disconnected = false;
    class ControlledObserver {
      constructor(next: IntersectionObserverCallback) { callback = next; }
      observe() {}
      unobserve() {}
      takeRecords() { return []; }
      disconnect() { disconnected = true; }
      readonly root = null;
      readonly rootMargin = "0px";
      readonly thresholds = [0.12];
    }
    window.IntersectionObserver = ControlledObserver as unknown as typeof IntersectionObserver;

    const ref = createRef<HTMLDivElement>();
    render(<ScrollReveal ref={ref}>Contenido</ScrollReveal>);
    expect(ref.current).toHaveAttribute("data-motion-ready", "true");
    expect(ref.current).toHaveAttribute("data-visible", "false");

    act(() => callback([{ isIntersecting: false } as IntersectionObserverEntry], {} as IntersectionObserver));
    expect(ref.current).toHaveAttribute("data-visible", "false");
    act(() => callback([{ isIntersecting: true } as IntersectionObserverEntry], {} as IntersectionObserver));
    expect(ref.current).toHaveAttribute("data-visible", "true");
    expect(disconnected).toBe(true);
  });

  it("falls back to visible content when IntersectionObserver is unavailable", () => {
    Object.defineProperty(window, "matchMedia", { configurable: true, value: media(false) });
    Reflect.deleteProperty(window, "IntersectionObserver");
    render(<ScrollReveal>Contenido seguro</ScrollReveal>);

    expect(screen.getByText("Contenido seguro")).toHaveAttribute("data-visible", "true");
  });
});
