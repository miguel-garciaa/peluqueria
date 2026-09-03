import { act, fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { BeforeAfter } from "@/components/BeforeAfter";
import { CompareReveal } from "@/components/ui/compare-reveal";

describe("BeforeAfter", () => {
  it("uses a crisp boundary instead of blur-like gradient overlays", () => {
    const { container } = render(<BeforeAfter />);

    expect(container.querySelectorAll(".bg-gradient-to-b")).toHaveLength(0);
  });

  it("places the comparison navigation outside the image on larger screens", () => {
    render(<BeforeAfter />);

    expect(screen.getByRole("button", { name: "Transformación anterior" })).toHaveClass("md:static");
    expect(screen.getByRole("button", { name: "Siguiente transformación" })).toHaveClass("md:static");
    expect(screen.getByRole("button", { name: "Transformación anterior" }).parentElement).toHaveClass("md:grid");
  });

  it("navigates through transformation comparisons", () => {
    render(<BeforeAfter />);
    expect(screen.getByText("Luz sin perder naturalidad")).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Siguiente transformación" }));
    expect(screen.getByText("Una forma que se mueve contigo")).toBeInTheDocument();
    expect(screen.getByText("2 / 4")).toBeInTheDocument();
  });

  it("keeps the comparison handle still until the user moves it", () => {
    const originalMatchMedia = window.matchMedia;
    Object.defineProperty(window, "matchMedia", { writable: true, value: (query: string) => ({ matches: false, media: query, onchange: null, addListener: () => {}, removeListener: () => {}, addEventListener: () => {}, removeEventListener: () => {}, dispatchEvent: () => false }) });
    vi.useFakeTimers();

    try {
      render(<CompareReveal before={{ src: "/before.jpg", alt: "Antes" }} after={{ src: "/after.jpg", alt: "Después" }} />);
      act(() => vi.advanceTimersByTime(1000));
      expect(screen.getByRole("slider")).toHaveAttribute("aria-valuenow", "50");
    } finally {
      vi.useRealTimers();
      Object.defineProperty(window, "matchMedia", { writable: true, value: originalMatchMedia });
    }
  });

  it("tracks fractional pointer movement without rounding the handle position", () => {
    const originalPointerEvent = window.PointerEvent;
    Object.defineProperty(window, "PointerEvent", { writable: true, value: MouseEvent });
    render(<CompareReveal before={{ src: "/before.jpg", alt: "Antes" }} after={{ src: "/after.jpg", alt: "Después" }} />);
    const comparison = screen.getByRole("group", { name: /Comparación:/ });
    Object.defineProperty(comparison, "getBoundingClientRect", {
      value: () => ({ left: 0, right: 200, top: 0, bottom: 100, width: 200, height: 100, x: 0, y: 0, toJSON: () => ({}) }),
    });
    Object.defineProperty(comparison, "setPointerCapture", { value: vi.fn() });

    fireEvent.pointerDown(comparison, { pointerType: "mouse", button: 0, pointerId: 1, clientX: 151 });

    expect(screen.getByRole("slider").parentElement).toHaveStyle({ left: "75.5%" });
    Object.defineProperty(window, "PointerEvent", { writable: true, value: originalPointerEvent });
  });
});
