import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Navbar } from "@/components/Navbar";

describe("Navbar", () => {
  it("uses a full-width shell and keeps the compact menu through tablet widths", () => {
    render(<Navbar />);

    expect(screen.getByRole("navigation", { name: "Navegación principal" })).toHaveClass("navbar-shell");
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("lg:hidden");
    expect(screen.getAllByRole("link", { name: "Inicio 01" })[0].parentElement).toHaveClass("lg:flex");
  });

  it("scrolls smoothly to the selected section", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockReturnValue({
        matches: false,
        media: "(prefers-reduced-motion: reduce)",
        onchange: null,
        addListener: () => {},
        removeListener: () => {},
        addEventListener: () => {},
        removeEventListener: () => {},
        dispatchEvent: () => false,
      });
    const scrollIntoView = vi.spyOn(HTMLElement.prototype, "scrollIntoView");

    render(
      <>
        <Navbar />
        <section id="inicio" />
        <section id="servicios" />
        <section id="equipo" />
        <section id="galeria" />
        <section id="reservas" />
      </>,
    );

    fireEvent.click(screen.getAllByRole("link", { name: "Servicios 02" })[0]);

    expect(scrollIntoView).toHaveBeenCalledWith({ behavior: "smooth", block: "start" });
    scrollIntoView.mockRestore();
    matchMedia.mockRestore();
  });

  it("uses an instant scroll when reduced motion is preferred", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockReturnValue({
      matches: true,
      media: "(prefers-reduced-motion: reduce)",
      onchange: null,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
      dispatchEvent: () => false,
    });
    const scrollIntoView = vi.spyOn(HTMLElement.prototype, "scrollIntoView");

    render(
      <>
        <Navbar />
        <section id="inicio" />
        <section id="servicios" />
        <section id="equipo" />
        <section id="galeria" />
        <section id="reservas" />
      </>,
    );

    fireEvent.click(screen.getAllByRole("link", { name: "Galería 04" })[0]);

    expect(scrollIntoView).toHaveBeenCalledWith({ behavior: "auto", block: "start" });
    scrollIntoView.mockRestore();
    matchMedia.mockRestore();
  });
});
