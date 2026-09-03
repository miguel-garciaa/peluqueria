import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Hero } from "@/components/Hero";
import { getHeroMotionMode } from "@/lib/hero-motion";

describe("Hero", () => {
  it("keeps the mobile actions compact without changing their desktop sizing", () => {
    render(<Hero onBook={vi.fn()} />);

    expect(screen.getByRole("button", { name: "Reservar cita" })).toHaveClass("w-[min(100%,21rem)]", "sm:w-auto", "p-1.5");
    expect(screen.getByRole("link", { name: "Ver servicios" })).toHaveClass("w-[min(100%,21rem)]", "sm:w-auto", "py-3");
    expect(screen.queryByRole("link", { name: "Ir a servicios" })).not.toBeInTheDocument();
  });
});

describe("hero motion mode", () => {
  it("plays only on a fresh navigation and stays settled on reload", () => {
    expect(getHeroMotionMode("navigate", false)).toBe("play");
    expect(getHeroMotionMode("reload", false)).toBe("settled");
    expect(getHeroMotionMode("back_forward", false)).toBe("settled");
    expect(getHeroMotionMode("navigate", true)).toBe("settled");
  });
});
