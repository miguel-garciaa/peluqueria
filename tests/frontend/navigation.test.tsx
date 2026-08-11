import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Navbar } from "@/components/Navbar";

describe("Navbar", () => {
  it("uses a full-width shell and keeps the compact menu through tablet widths", () => {
    render(<Navbar />);

    expect(screen.getByRole("navigation", { name: "Navegación principal" })).toHaveClass("navbar-shell");
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("xl:hidden");
    expect(screen.getAllByRole("link", { name: "Inicio 01" })[0].parentElement).toHaveClass("xl:flex");
    const accountLink = screen.getAllByRole("link", { name: "Iniciar sesión con Google" })[0];
    expect(accountLink).toHaveAttribute("href", "/auth/google");
    expect(accountLink.querySelector("svg")).toBeInTheDocument();
    expect(accountLink).not.toHaveTextContent(/^GCuenta$/);
  });

  it("shows the authenticated account and a secure logout form", () => {
    render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    expect(screen.getByRole("group")).toBeInTheDocument();
    expect(screen.getAllByText("ana@example.com")[0]).toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Mis citas" })[0]).toHaveAttribute("href", "/mis-citas");
    const logoutButtons = screen.getAllByRole("button", { name: "Cerrar sesión" });
    expect(logoutButtons[0].closest("form")).toHaveAttribute("action", "/logout");
    expect(logoutButtons[0].closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
  });

  it("replaces booking with the control panel action for administrators", () => {
    const { rerender } = render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} />);
    expect(screen.queryByRole("link", { name: "Panel de control" })).not.toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Reservar cita" })).not.toHaveLength(0);

    rerender(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null, isAdmin: true }} />);
    expect(screen.getAllByRole("link", { name: "Panel de control" })[0]).toHaveAttribute("href", "/admin");
    expect(screen.queryByRole("link", { name: "Mis citas" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Reservar cita" })).not.toBeInTheDocument();
  });

  it("centers the primary action in the mobile menu", () => {
    render(<Navbar />);

    const mobileBookingAction = screen.getAllByRole("link", { name: "Reservar cita" }).find((link) => link.parentElement?.classList.contains("justify-center"));
    expect(mobileBookingAction?.parentElement).toHaveClass("flex", "justify-center");
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
